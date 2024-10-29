<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Utility;
use App\Models\Customer;
use App\Models\InvoiceProduct;
use App\Models\ProductService;
use App\Models\TransactionLines;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        if (\Auth::user()->can('manage credit note')) {
            $invoices = Invoice::where('created_by', \Auth::user()->creatorId())->get();

            return view('creditNote.index', compact('invoices'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create($invoice_id)
    {

        if (\Auth::user()->can('create credit note')) {

            $invoiceDue = Invoice::where('id', $invoice_id)->first();

            return view('creditNote.create', compact('invoiceDue', 'invoice_id'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request, $invoice_id)
    {
        if (\Auth::user()->can('create credit note')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'return_type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $credit_note_product_id = $request->product_id;
            $credit_note_qty = $request->qty;
            $credit_note_pid_arr = array();

            $invoice_products = InvoiceProduct::where('invoice_id', $invoice_id)->get();
            foreach ($invoice_products as $invoice_product) {
                for ($i = 0; $i < count($credit_note_product_id); $i++) {
                    if ($invoice_product->product_id == $credit_note_product_id[$i]) {
                        if ($invoice_product->quantity < $credit_note_qty[$i]) {
                            return redirect()->back()->with('error', __('Invalid Quantity.'));
                        }
                        if ($credit_note_qty[$i]) {
                            $credit_note_pid_arr[] = $credit_note_product_id[$i];
                        }
                    }
                }
            }

            $invoice = Invoice::where('id', $invoice_id)->first();

            $credit              = new CreditNote();
            $credit->invoice     = $invoice_id;
            $credit->customer    = $invoice->customer_id;
            $credit->date        = $request->date;
            $credit->amount      = 0;
            $credit->description = $request->description;
            $credit->created_user = \Auth::user()->id;
            $credit->save();

            $total_credit_amount = 0;

            $invoice_products = InvoiceProduct::where('invoice_id', $invoice->id)->whereIn('product_id', $credit_note_pid_arr)->get();
            foreach ($invoice_products as $invoice_product) {
                for ($i = 0; $i < count($credit_note_product_id); $i++) {
                    if ($credit_note_product_id[$i] == $invoice_product->product_id &&  $credit_note_qty[$i]) {

                        $product = ProductService::find($invoice_product->product_id);
                        $totalTaxPrice = 0;
                        if ($invoice_product->tax != null) {
                            $taxes = \App\Models\Utility::tax($invoice_product->tax);
                            foreach ($taxes as $tax) {
                                $taxPrice = \App\Models\Utility::taxRate($tax->rate, $invoice_product->price, $credit_note_qty[$i], $invoice_product->discount);
                                $totalTaxPrice += $taxPrice;
                            }
                        }

                        $itemAmount = ($invoice_product->price * $credit_note_qty[$i]) - ($invoice_product->discount) + $totalTaxPrice;
                        $product_price = ($invoice_product->price * $credit_note_qty[$i]) - ($invoice_product->discount);

                        $total_credit_amount = $total_credit_amount + ($product_price * $invoice->exchange_rate) + ($totalTaxPrice * $invoice->exchange_rate);

                        // Sales Income
                        $data = [
                            'account_id' => $product->sale_chartaccount_id,
                            'transaction_type' => 'Debit',
                            'transaction_amount' => $product_price * $invoice->exchange_rate,
                            'reference' => 'Invoice Credit Note',
                            'reference_id' => $invoice->id,
                            'reference_sub_id' => $credit->id,
                            'date' => $request->date,
                        ];
                        Utility::addTransactionLines($data, "new");

                        $chart_accounts = ChartOfAccount::where('code', 2150)->where('created_by', \Auth::user()->creatorId())->first();
                        $data = [
                            'account_id' => $chart_accounts->id,
                            'transaction_type' => 'Debit',
                            'transaction_amount' => $totalTaxPrice * $invoice->exchange_rate,
                            'reference' => 'Invoice Credit Note',
                            'reference_id' => $invoice->id,
                            'reference_sub_id' => $credit->id,
                            'date' => $request->date,
                        ];
                        Utility::addTransactionLines($data, "new");

                        // Account Recivable
                        $account_recivable = ChartOfAccount::where('code', 1200)->where('created_by', \Auth::user()->creatorId())->first();
                        $data2 = [
                            'account_id' => $account_recivable->id,
                            'transaction_type' => 'Credit',
                            'transaction_amount' => $itemAmount * $invoice->exchange_rate,
                            'reference' => 'Invoice Credit Note',
                            'reference_id' => $invoice->id,
                            'reference_sub_id' => $credit->id,
                            'date' => $request->date,
                        ];

                        Utility::addTransactionLines($data2, "new");


                        $purchase_price = $product->purchase_price * $credit_note_qty[$i];

                        // Cost of Sales on service
                        $cof_sale = ChartOfAccount::where('code', 5005)->where('created_by', \Auth::user()->creatorId())->first();
                        $data = [
                            'account_id' => $cof_sale->id,
                            'transaction_type' => 'Credit',
                            'transaction_amount' => $purchase_price,
                            'reference' => 'Invoice Credit Note',
                            'reference_id' => $invoice->id,
                            'reference_sub_id' => $credit->id,
                            'date' => $request->date,
                        ];

                        Utility::addTransactionLines($data, "new");

                        if($request->return_type == "Reusable") {
                            // Inventory
                            $inventory = ChartOfAccount::where('code', 1510)->where('created_by', \Auth::user()->creatorId())->first();
                            $data = [
                                'account_id' => $inventory->id,
                                'transaction_type' => 'Debit',
                                'transaction_amount' => $purchase_price,
                                'reference' => 'Invoice Credit Note',
                                'reference_id' => $invoice->id,
                                'reference_sub_id' => $credit->id,
                                'date' => $request->date,
                            ];
                            Utility::addTransactionLines($data, "new");
                        } else {
                            // Depreciation Expense
                            $depreciation_expense = ChartOfAccount::where('code', 5660)->where('created_by', \Auth::user()->creatorId())->first();
                            $data = [
                                'account_id' => $depreciation_expense->id,
                                'transaction_type' => 'Debit',
                                'transaction_amount' => $purchase_price,
                                'reference' => 'Invoice Credit Note',
                                'reference_id' => $invoice->id,
                                'reference_sub_id' => $credit->id,
                                'date' => $request->date,
                            ];
                            Utility::addTransactionLines($data, "new");
                        }
                    }
                }
            }

            if($invoice->getDue() == 0) {
                $customer = Customer::find($invoice->customer_id);
                $balance = $customer->credit_balance + $total_credit_amount;
                $customer->credit_balance = $balance;
                $customer->save();
            } else {
                Utility::updateUserBalance('customer', $invoice->customer_id, $total_credit_amount, 'credit');
            }

            $credit->amount      = $total_credit_amount;
            $credit->save();

            return redirect()->back()->with('success', __('Credit Note successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($invoice_id, $creditNote_id)
    {
        if (\Auth::user()->can('edit credit note')) {

            $creditNote = CreditNote::find($creditNote_id);

            return view('creditNote.edit', compact('creditNote'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $invoice_id, $creditNote_id)
    {

        if (\Auth::user()->can('edit credit note')) {

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

            $invoiceDue = Invoice::where('id', $invoice_id)->first();
            $credit = CreditNote::find($creditNote_id);
            if (0 > $invoiceDue->getDue() + $credit->amount) {
                return redirect()->back()->with('error', 'Maximum ' . \Auth::user()->priceFormat($invoiceDue->getDue()) . ' credit limit of this invoice.');
            }


            Utility::updateUserBalance('customer', $invoiceDue->customer_id, $credit->amount, 'credit');

            $credit->date        = $request->date;
            $credit->amount      = 0;
            $credit->description = $request->description;
            $credit->created_user = \Auth::user()->id;
            $credit->save();

            Utility::updateUserBalance('customer', $invoiceDue->customer_id, 0, 'debit');


            return redirect()->back()->with('success', __('Credit Note successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($invoice_id, $creditNote_id)
    {
        if (\Auth::user()->can('delete credit note')) {

            $invoice = Invoice::find($invoice_id);

            $creditNote = CreditNote::find($creditNote_id);
            $creditNote->delete();

            $customer = Customer::find($creditNote->customer);

            if($customer->credit_balance == 0) {
                Utility::updateUserBalance('customer', $creditNote->customer, $creditNote->amount, 'debit');
            } else {
                $balance = $customer->credit_balance - $creditNote->amount;
                $customer->credit_balance = $balance;
                $customer->save();
            }

            TransactionLines::where('reference', 'Invoice Credit Note')->where('reference_sub_id', $creditNote_id)->delete();

            return redirect()->back()->with('success', __('Credit Note successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customCreate()
    {
        if (\Auth::user()->can('create credit note')) {

            $invoices = Invoice::where('created_by', \Auth::user()->creatorId())->get()->pluck('invoice_id', 'id');

            return view('creditNote.custom_create', compact('invoices'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customStore(Request $request)
    {
        if (\Auth::user()->can('create credit note')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'invoice' => 'required|numeric',
                    'amount' => 'required|numeric',
                    'date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $invoice_id = $request->invoice;
            $invoiceDue = Invoice::where('id', $invoice_id)->first();

            if (0 > $invoiceDue->getDue()) {
                return redirect()->back()->with('error', 'Maximum ' . \Auth::user()->priceFormat($invoiceDue->getDue()) . ' credit limit of this invoice.');
            }
            $invoice             = Invoice::where('id', $invoice_id)->first();
            $credit              = new CreditNote();
            $credit->invoice     = $invoice_id;
            $credit->customer    = $invoice->customer_id;
            $credit->date        = $request->date;
            $credit->amount      = 0;
            $credit->description = $request->description;
            $credit->created_user = \Auth::user()->id;
            $credit->save();

            Utility::updateUserBalance('customer', $invoice->customer_id, 0, 'debit');

            return redirect()->back()->with('success', __('Credit Note successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getinvoice(Request $request)
    {
        $invoice = Invoice::where('id', $request->id)->first();

        echo json_encode($invoice->getDue());
    }
}
