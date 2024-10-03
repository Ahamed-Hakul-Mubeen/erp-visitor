<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\TransactionLines;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateOpeningBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-opening-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically create opening balance amount';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("CreateOpeningBalance Cron Run at : ".date("Y-m-d h:i:s A"));
        $company_list = User::where("type", 'company')->get();
        foreach ($company_list as $company) {
            $total_opening_balance = 0;
            $start = date('Y-01-01');
            $end = date('Y-m-d', strtotime('+1 day'));

            $find_start_date = TransactionLines::where('created_by', $company->id)->orderBy("date", "ASC")->first();
            $find_end_date = TransactionLines::where('created_by', $company->id)->orderBy("date", "DESC")->first();
            if($find_start_date) {
                $start = $find_start_date->date;
            }
            if($find_end_date) {
                $end = $find_end_date->date;
            }

            $types = ChartOfAccountType::where('created_by', $company->id)->whereIn('name', ['Assets', 'Liabilities'])->get();
            foreach ($types as $type) {
                $chart_acc = ChartOfAccount::where("type", $type->id)->get();
                foreach ($chart_acc as $ca) {
                    $parentAccs = TransactionLines::select('account_id', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'))
                        ->where('account_id', $ca->id)
                        ->where('created_by', $ca->created_by)
                        ->where('reference', "!=", "Opening Balance")
                        ->where('date', '>=', $start)
                        ->where('date', '<=', $end)
                        ->first()->toArray();
                    if ($parentAccs['account_id'] && $parentAccs['totalDebit'] && $parentAccs['totalCredit']) {
                        $balance = $parentAccs['totalCredit'] - $parentAccs['totalDebit'];
                        $total_opening_balance += $balance;
                        if ($balance) {
                            $transaction_line = TransactionLines::where("account_id", $parentAccs['account_id'])
                                ->where('date', date("Y-01-01", strtotime("+1 year")))
                                ->where('created_by', $ca->created_by)
                                ->where('reference', 'Opening Balance')
                                ->first();
                            if (!$transaction_line) {
                                $transaction_line = new TransactionLines();
                            }
                            $transaction_line->account_id = $parentAccs['account_id'];
                            $transaction_line->date = date("Y-01-01", strtotime("+1 year"));
                            $transaction_line->created_by = $ca->created_by;
                            $transaction_line->reference = 'Opening Balance';
                            $transaction_line->reference_id = 0;
                            $transaction_line->reference_sub_id = 0;
                            if ($balance > 0) {
                                $transaction_line->credit = $balance;
                                $transaction_line->debit = 0;
                            } else {
                                $transaction_line->credit = 0;
                                $transaction_line->debit = - ($balance);
                            }
                            $transaction_line->save();
                        }
                        // echo "<br>".$parentAccs['account_id']." - Debit : ".$parentAccs['totalDebit']." - Credit : ".$parentAccs['totalCredit'];
                    }
                }
            }

            if ($total_opening_balance) {
                $openbal_coa = ChartOfAccount::where("name", "Opening Balances and adjustments")->where('created_by', $company->id)->first();
                if ($openbal_coa) {
                    $transaction_line = TransactionLines::where("account_id", $openbal_coa->id)
                        ->where('date', date("Y-01-01", strtotime("+1 year")))
                        ->where('created_by', $company->id)
                        ->where('reference', 'Opening Balance')
                        ->first();
                    if (!$transaction_line) {
                        $transaction_line = new TransactionLines();
                    }
                }
                $transaction_line->account_id = $openbal_coa->id;
                $transaction_line->date = date("Y-01-01", strtotime("+1 year"));
                $transaction_line->created_by = $company->id;
                $transaction_line->reference = 'Opening Balance';
                $transaction_line->reference_id = 0;
                $transaction_line->reference_sub_id = 0;
                if ($total_opening_balance > 0) {
                    $transaction_line->credit = 0;
                    $transaction_line->debit = $total_opening_balance;
                } else {
                    $transaction_line->credit = - ($total_opening_balance);
                    $transaction_line->debit = 0;
                }
                $transaction_line->save();
            }
        }
    }
}
