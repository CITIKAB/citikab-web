<?php

use Illuminate\Database\Seeder;

class RequestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('request')->delete();

        DB::table('request')->insert(array(
  array('id' => '1','user_id' => '10006','pickup_latitude' => '9.9243649','pickup_longitude' => '78.13767','drop_latitude' => '9.915143137971787','drop_longitude' => '78.13790515065193','pickup_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','drop_location' => '171-1B, Kamarajar Salai, Madurai, Tamil Nadu 625002, India','car_id' => '2','group_id' => '1','driver_id' => '10004','payment_mode' => 'Cash','schedule_id' => '','timezone' => 'Asia/Kolkata','status' => 'Cancelled','created_at' => '2019-01-03 09:36:59','updated_at' => '2019-01-03 09:38:00','deleted_at' => NULL),
  array('id' => '2','user_id' => '10006','pickup_latitude' => '9.9243649','pickup_longitude' => '78.1376699','drop_latitude' => '9.917003534548344','drop_longitude' => '78.13569668680431','pickup_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','drop_location' => 'No. 110, Kamarajar Salai, Madurai, Tamil Nadu 625009, India','car_id' => '1','group_id' => '2','driver_id' => '10002','payment_mode' => 'Cash','schedule_id' => '','timezone' => 'Asia/Kolkata','status' => 'Accepted','created_at' => '2019-01-03 09:48:00','updated_at' => '2019-01-03 09:48:03','deleted_at' => NULL),
  array('id' => '3','user_id' => '10006','pickup_latitude' => '9.9243654','pickup_longitude' => '78.1376694','drop_latitude' => '9.917482090383615','drop_longitude' => '78.13373699784279','pickup_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','drop_location' => '8, Srinivasa Perumal Koil St, Munichallai, Madurai, Tamil Nadu 625009, India','car_id' => '1','group_id' => '3','driver_id' => '10002','payment_mode' => 'PayPal','schedule_id' => '','timezone' => 'Asia/Kolkata','status' => 'Accepted','created_at' => '2019-01-03 09:50:48','updated_at' => '2019-01-03 09:51:07','deleted_at' => NULL),
  array('id' => '4','user_id' => '10006','pickup_latitude' => '9.9243654','pickup_longitude' => '78.1376694','drop_latitude' => '9.917482090383615','drop_longitude' => '78.13373699784279','pickup_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','drop_location' => '8, Srinivasa Perumal Koil St, Munichallai, Madurai, Tamil Nadu 625009, India','car_id' => '1','group_id' => '4','driver_id' => '10002','payment_mode' => 'PayPal','schedule_id' => '','timezone' => 'Asia/Kolkata','status' => 'Cancelled','created_at' => '2019-01-03 09:51:06','updated_at' => '2019-01-03 09:51:22','deleted_at' => NULL),
  array('id' => '5','user_id' => '10006','pickup_latitude' => '9.9243647','pickup_longitude' => '78.1376703','drop_latitude' => '9.913172750818587','drop_longitude' => '78.13787061721085','pickup_location' => '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India','drop_location' => '6-17, Pandiyan Street, Meenakshi Nagar, Madurai, Tamil Nadu 625009, India','car_id' => '1','group_id' => '5','driver_id' => '10002','payment_mode' => 'Cash','schedule_id' => '','timezone' => 'Asia/Kolkata','status' => 'Accepted','created_at' => '2019-01-03 09:54:15','updated_at' => '2019-01-03 09:54:18','deleted_at' => NULL)
));
    }
}
