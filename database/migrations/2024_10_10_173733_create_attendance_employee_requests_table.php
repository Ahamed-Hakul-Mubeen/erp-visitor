<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_employee_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id');
            $table->date('date');
            $table->string('status');
            $table->time('clock_in');
            $table->time('clock_out');
            $table->time('late');
            $table->time('early_leaving');
            $table->time('overtime');
            $table->time('total_rest');
            $table->time('total_break_duration')->nullable();
            $table->boolean('work_from_home')->default(0);
            $table->integer('created_by');
            $table->string('approval_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_employee_requests');
    }
};
