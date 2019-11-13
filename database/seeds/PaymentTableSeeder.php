<?php

use Illuminate\Database\Seeder;

class PaymentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment')->delete();

        DB::table('payment')->insert( array(

        array('id' => '1','trip_id' => '1','correlation_id' => NULL,'admin_transaction_id' => NULL,'driver_transaction_id' => NULL,'driver_payout_status' => 'Pending'),
        
        array('id' => '2','trip_id' => '2','correlation_id' => '20E13968PF1676837','admin_transaction_id' => NULL,'driver_transaction_id' => NULL,'driver_payout_status' => 'Pending')

        ));
    }
}
