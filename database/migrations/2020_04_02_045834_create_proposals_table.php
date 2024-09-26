<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'proposals', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->unsignedBigInteger('proposal_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('issue_date');
            $table->date('send_date')->nullable();
            $table->integer('category_id');
            $table->integer('status')->default('0');
            $table->integer('discount_apply')->default('0');
            $table->integer('is_convert')->default('0');
            $table->integer('converted_invoice_id')->default('0');
            $table->string('currency_code')->nullable()->default(null);
            $table->string('currency_symbol')->nullable()->default(null);
            $table->float('exchange_rate')->default(0);
            $table->integer('created_by')->default('0');
            $table->unsignedBigInteger('created_user');
            $table->timestamps();
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposals');
    }
}
