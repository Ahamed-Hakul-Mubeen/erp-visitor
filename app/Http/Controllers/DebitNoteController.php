<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\ChartOfAccount;
use App\Models\DebitNote;
use App\Models\ProductService;
use App\Models\TransactionLines;
use App\Models\Utility;
use App\Models\Vender;
use Illuminate\Http\Request;

class DebitNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage debit note')) {


            $bills = Bill::where('created_by', \Auth::user()->creatorId())->get();

            // if (!empty($request->date)) {
            //     $date_range = explode('to', $request->date);
            //     $bills->whereBetween('issue_date', $date_range);
            // }

            // dd($bills);
            return view('debitNote.index', compact('bills'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create($bill_id)
    {
        if (\Auth::user()->can('create debit note')) {

            $billDue = Bill::where('id', $bill_id)->first();

            return view('debitNote.create', compact('billDue', 'bill_id'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request, $bill_id)
    {

        if (\Auth::user()->can('create debit note')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $debit_note_product_id = $request->product_id;
            $debit_note_qty = $request->qty;
            $debit_note_pid_arr = array();

            $bill_products = BillProduct::where('bill_id', $bill_id)->get();
            foreach ($bill_products as $bill_product) {
                for ($i = 0; $i < count($debit_note_product_id); $i++) {
                    if ($bill_product->product_id == $debit_note_product_id[$i]) {
                        if ($debit_note_qty[$i] < $debit_note_qty[$i]) {
                            return redirect()->back()->with('error', __('Invalid Quantity.'));
                        }
                        if ($debit_note_qty[$i]) {
                            $debit_note_pid_arr[] = $debit_note_product_id[$i];
                        }
                    }
                }
            }

            $billDue = Bill::where('id', $bill_id)->first();

            $bill               = Bill::where('id', $bill_id)->first();
            $debit              = new DebitNote();
            $debit->bill        = $bill_id;
            $debit->vendor      = $bill->vender_id;
            $debit->date        = $request->date;
            $debit->amount      = 0;
            $debit->description = $request->description;
            $debit->created_user = \Auth::user()->id;
            $debit->save();

            $total_debit_amount = 0;

            $bill_products = BillProduct::where('bill_id', $bill->id)->whereIn('product_id', $debit_note_pid_arr)->get();
            foreach ($bill_products as $bill_product) {
                for ($i = 0; $i < count($debit_note_product_id); $i++) {
                    if ($debit_note_product_id[$i] == $bill_product->product_id &&  $debit_note_qty[$i]) {

                        $product = ProductService::find($bill_product->product_id);
                        $totalTaxPrice = 0;
                        if ($bill_product->tax != null) {
                            $taxes = \App\Models\Utility::tax($bill_product->tax);
                            foreach ($taxes as $tax) {
                                $taxPrice = \App\Models\Utility::taxRate($tax->rate, $bill_product->price, $debit_note_qty[$i], $bill_product->discount);
                                $totalTaxPrice += $taxPrice;
                            }
                        }

                        $itemAmount = ($bill_product->price * $debit_note_qty[$i]) - ($bill_product->discount) + $totalTaxPrice;
                        $product_price = ($bill_product->price * $debit_note_qty[$i]) - ($bill_product->discount);

                        $total_debit_amount = $total_debit_amount + ($product_price) + ($totalTaxPrice);

                        $data = [
                            'account_id' => $product->expense_chartaccount_id,
                            'transaction_type' => 'Credit',
                            'transaction_amount' => $product_price,
                            'reference' => 'Bill Debit Note',
                            'reference_id' => $bill->id,
                            'reference_sub_id' => $debit->id,
                            'date' => $bill->bill_date,
                        ];
                        Utility::addTransactionLines($data, "new");
                        // Purchase Tax
                        if ($totalTaxPrice != 0) {
                            $chart_accounts = ChartOfAccount::where('code', 2150)->where('created_by', \Auth::user()->creatorId())->first();
                            $data = [
                                'account_id' => $chart_accounts->id,
                                'transaction_type' => 'Credit',
                                'transaction_amount' => $totalTaxPrice,
                                'reference' => 'Bill Debit Note',
                                'reference_id' => $bill->id,
                                'reference_sub_id' => $debit->id,
                                'date' => $bill->bill_date,
                            ];
                            Utility::addTransactionLines($data, "new");
                        }

                        // Account Payable
                        $chart_accounts = ChartOfAccount::where('code', 2100)->where('created_by', \Auth::user()->creatorId())->first();
                        $data = [
                            'account_id' => $chart_accounts->id,
                            'transaction_type' => 'Debit',
                            'transaction_amount' => $itemAmount,
                            'reference' => 'Bill Debit Note',
                            'reference_id' => $bill->id,
                            'reference_sub_id' => $debit->id,
                            'date' => $bill->bill_date,
                        ];
                        Utility::addTransactionLines($data, "new");
                    }
                }
            }

            if ($bill->getDue() == 0) {
                $customer = Vender::find($bill->vender_id);
                $balance = $customer->debit_balance + $total_debit_amount;
                $customer->debit_balance = $balance;
                $customer->save();
            } else {
                Utility::updateUserBalance('vendor', $bill->vender_id, $total_debit_amount, 'credit');
            }

            $debit->amount      = $total_debit_amount;
            $debit->save();

            // Utility::updateUserBalance('vendor', $bill->vender_id, $request->amount, 'credit');


            return redirect()->back()->with('success', __('Debit Note successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($bill_id, $debitNote_id)
    {
        if (\Auth::user()->can('edit debit note')) {

            $debitNote = DebitNote::find($debitNote_id);

            return view('debitNote.edit', compact('debitNote'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $bill_id, $debitNote_id)
    {

        if (\Auth::user()->can('edit debit note')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'amount' => 'required|numeric',
                    'date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $billDue = Bill::where('id', $bill_id)->first();
            if ($request->amount > $billDue->getDue()) {
                return redirect()->back()->with('error', 'Maximum ' . \Auth::user()->priceFormat($billDue->getDue()) . ' credit limit of this bill.');
            }


            $debit = DebitNote::find($debitNote_id);
            //            Utility::userBalance('vendor', $billDue->vender_id, $debit->amount, 'credit');
            Utility::updateUserBalance('vendor', $billDue->vender_id, $debit->amount, 'debit');



            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = $request->description;
            $debit->created_user = \Auth::user()->id;
            $debit->save();
            //            Utility::userBalance('vendor', $billDue->vender_id, $request->amount, 'debit');
            Utility::updateUserBalance('vendor', $billDue->vender_id, $request->amount, 'credit');


            return redirect()->back()->with('success', __('Debit Note successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($bill_id, $debitNote_id)
    {
        if (\Auth::user()->can('delete debit note')) {
            $debitNote = DebitNote::find($debitNote_id);
            $debitNote->delete();

            $bill  = Bill::where('id', $bill_id)->first();

            if ($bill->getDue() == 0) {
                $vendor = Vender::find($debitNote->vendor);
                $balance = $vendor->debit_balance - $debitNote->amount;
                $vendor->debit_balance = $balance;
                $vendor->save();
            } else {
                Utility::updateUserBalance('vendor', $debitNote->vendor, $debitNote->amount, 'debit');
            }

            TransactionLines::where('reference', 'Bill Debit Note')->where('reference_sub_id', $debitNote_id)->delete();

            // Utility::updateUserBalance('vendor', $debitNote->vendor, $debitNote->amount, 'debit');
            return redirect()->back()->with('success', __('Debit Note successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customCreate()
    {
        if (\Auth::user()->can('create debit note')) {
            $bills = Bill::where('created_by', \Auth::user()->creatorId())->where('type', 'Bill')->get()->pluck('bill_id', 'id');

            return view('debitNote.custom_create', compact('bills'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customStore(Request $request)
    {
        if (\Auth::user()->can('create debit note')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'bill' => 'required|numeric',
                    'amount' => 'required|numeric',
                    'date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $bill_id = $request->bill;
            $billDue = Bill::where('id', $bill_id)->first();

            if ($request->amount > $billDue->getDue()) {
                return redirect()->back()->with('error', 'Maximum ' . \Auth::user()->priceFormat($billDue->getDue()) . ' credit limit of this bill.');
            }
            $bill               = Bill::where('id', $bill_id)->first();
            $debit              = new DebitNote();
            $debit->bill        = $bill_id;
            $debit->vendor      = $bill->vender_id;
            $debit->date        = $request->date;
            $debit->amount      = $request->amount;
            $debit->description = $request->description;
            $debit->created_user = \Auth::user()->id;
            $debit->save();
            //            Utility::userBalance('vendor', $bill->vender_id, $request->amount, 'debit');
            Utility::updateUserBalance('vendor', $bill->vender_id, $request->amount, 'credit');


            return redirect()->back()->with('success', __('Debit Note successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getbill(Request $request)
    {

        $bill = Bill::where('id', $request->bill_id)->first();
        echo json_encode($bill->getDue());
    }
}
