<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;

class UpdateUserLoginStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-user-login-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $employees = Employee::with(['user', 'termination'])
        ->whereHas('termination', function ($query) {
            $query->where('termination_date', '<=', Carbon::now());
        })
        ->get();

        foreach ($employees as $employee) {
            if ($employee->user) {
                $employee->user->is_enable_login = 0;
                $employee->user->save();
            }
        }

        $employee_resignation = Employee::with(['user', 'resignation'])
        ->whereHas('resignation', function ($query) {
            $query->where('resignation_date', '<=', Carbon::now());
        })
        ->get();

    foreach ($employee_resignation as $employee) {
        if ($employee->user) {
            $employee->user->is_enable_login = 0;
            $employee->user->save();
        }
    }

    $this->info('User login status updated on termination and resignation dates.');
    }
}
