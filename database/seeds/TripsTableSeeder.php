<?php

use Illuminate\Database\Seeder;

class TripsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('trips')->delete();

        DB::table('trips')->insert(array(
  array('id' => '1','user_id' => '10006','pickup_latitude' => '9.9243651','pickup_longitude' => '78.1376701','drop_latitude' => '9.9243649','drop_longitude' => '78.1376701','pickup_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','drop_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','car_id' => '1','request_id' => '2','driver_id' => '10002','trip_path' => 'ykq{@ug|{MIbA?\\@^Gz@G^IRIFO@}BUiBfIi@zAmDbJMp@lAh@jB|@hBz@p@^RDt@JlCn@`@PM^a@dASx@UvAfAn@hHpE`CtBh@n@ZRRDd@?l@CBSDURg@d@aBb@w@z@kAxAwBhAgB|@iARQz@aAp@k@x@u@XSh@c@nB{An@_@`@MNAN_BJsAD}@AYRiBTgBD_@XcB','map_image' => '47f17c90.jpg','total_time' => '0.00','total_km' => '0.00','time_fare' => '0.00','distance_fare' => '0.00','base_fare' => '50.00','total_fare' => '55.00','access_fee' => '5.00','schedule_fare' => '0.00','subtotal_fare' => '50.00','driver_payout' => '50.00','driver_or_company_commission'=>'0.00','owe_amount' => '5.00','remaining_owe_amount' => '0.00','applied_owe_amount' => '0.00','wallet_amount' => '0.00','promo_amount' => '0.00','to_trip_id' => '','begin_trip' => '2019-01-03 15:18:16','end_trip' => '2019-01-03 15:18:29','paykey' => '','payment_mode' => 'Cash','payment_status' => 'Completed','is_calculation' => '1','currency_code' => 'USD','status' => 'Completed','created_at' => '2019-01-03 15:18:04','updated_at' => '2019-01-03 09:48:37','deleted_at' => NULL),
  array('id' => '2','user_id' => '10006','pickup_latitude' => '9.9243592','pickup_longitude' => '78.1376538','drop_latitude' => '9.924235','drop_longitude' => '78.137542','pickup_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','drop_location' => '7, Azad Rd, Shenoy Nagar, Madurai, Tamil Nadu 625020, India','car_id' => '1','request_id' => '3','driver_id' => '10002','trip_path' => 'ykq{@ug|{Mh@_Gh@BvAJfFTd@cFf@{ClDr@|@NZPhB\\pNfCIn@qAxEi@bBg@jCs@hCQj@o@hEtCl@RFlAXfALMtB`AH','map_image' => '0af4025f.jpg','total_time' => '0.00','total_km' => '0.02','time_fare' => '0.00','distance_fare' => '0.19','base_fare' => '70.50','total_fare' => '77.76','access_fee' => '7.07','schedule_fare' => '0.00','subtotal_fare' => '63.64','driver_payout' => '63.64','driver_or_company_commission'=>'0.00','owe_amount' => '0.00','remaining_owe_amount' => '0.00','applied_owe_amount' => '7.05','wallet_amount' => '0.00','promo_amount' => '0.00','to_trip_id' => '','begin_trip' => '2019-01-03 15:21:17','end_trip' => '2019-01-03 15:21:27','paykey' => '20E13968PF1676837','payment_mode' => 'PayPal','payment_status' => 'Completed','is_calculation' => '1','currency_code' => 'AUD','status' => 'Completed','created_at' => '2019-01-03 15:21:08','updated_at' => '2019-01-03 09:52:13','deleted_at' => NULL),
  array('id' => '3','user_id' => '10006','pickup_latitude' => '9.9243647','pickup_longitude' => '78.1376703','drop_latitude' => '9.913172750818587','drop_longitude' => '78.13787061721085','pickup_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','drop_location' => '6-17, Pandiyan Street, Meenakshi Nagar, Madurai, Tamil Nadu 625009, India','car_id' => '1','request_id' => '5','driver_id' => '10002','trip_path' => 'ykq{@ug|{Mh@_Gh@BvAJfFTd@cFf@{ClDr@|@NZPhB\\pNfCz@L\\@PAdAUpBm@x@UbC_AvAi@ZcBp@uD`AgDz@kCl@iB`@gAjBh@pBf@bATt@V~An@nBv@D@Yl@g@bAuAdDk@dBUr@_@r@QTWTaAx@cArAc@jA}@~D`AL','map_image' => '','total_time' => '0.00','total_km' => '0.00','time_fare' => '0.00','distance_fare' => '0.00','base_fare' => '0.00','total_fare' => '0.00','access_fee' => '0.00','schedule_fare' => '0.00','subtotal_fare' => '0.00','driver_payout' => '0.00','driver_or_company_commission'=>'0.00','owe_amount' => '0.00','remaining_owe_amount' => '0.00','applied_owe_amount' => '0.00','wallet_amount' => '0.00','promo_amount' => '0.00','to_trip_id' => '','begin_trip' => '0000-00-00 00:00:00','end_trip' => '0000-00-00 00:00:00','paykey' => '','payment_mode' => 'Cash','payment_status' => 'Trip Cancelled','is_calculation' => '0','currency_code' => 'AUD','status' => 'Cancelled','created_at' => '2019-01-03 15:24:18','updated_at' => '2019-01-03 09:54:44','deleted_at' => NULL)
));
    }
}
