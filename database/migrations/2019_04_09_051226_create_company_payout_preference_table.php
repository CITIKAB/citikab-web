<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyPayoutPreferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_payout_preference', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('city',100);
            $table->string('state',100)->nullable();
            $table->string('postal_code',25);
            $table->string('country',5);
          //  $table->foreign('country')->references('short_name')->on('country');
            $table->string('payout_method',20);
            $table->string('paypal_email');
            $table->string('currency_code',10);
           // $table->foreign('currency_code')->references('code')->on('currency');
            //$table->enum('default',['no','yes']);
            $table->string('routing_number', 100)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('holder_name', 100);
            $table->enum('holder_type', ['Individual', 'Company']);
            $table->string('document_id',100)->nullable();
            $table->string('document_image',100)->nullable();            
            $table->string('phone_number',100)->nullable();
            $table->string('address_kanji',255)->nullable();
            $table->string('bank_name',100)->nullable();
            $table->string('branch_name',100)->nullable();
            $table->string('branch_code',100)->nullable();
            $table->string('ssn_last_4',100)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('company_payout_preference');
    }
}
