<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table('users')->delete();

		DB::table('profile_picture')->delete();

// 1 user
		DB::table('users')->insert(['id' => 10001, 'first_name' => 'Andre', 'last_name' => 'Wehner', 'email' => 'andre@trioangle.com', 'mobile_number' => 98765432101,'password' => Hash::make('123456'), 'user_type' => 'Driver', 'company_id' => 1, 'status' => 'Active', 'created_at' => date('Y-m-d H:i:s')]);

		DB::table('users')->insert(['id' => 10002, 'first_name' => 'Claudia', 'last_name' => 'Nikolaus', 'email' => 'claudia@trioangle.com', 'mobile_number' => 98765432102,'password' => Hash::make('123456'), 'user_type' => 'Driver', 'company_id' => 1, 'status' => 'Active', 'created_at' => date('Y-m-d H:i:s')]);

		DB::table('users')->insert(['id' => 10003, 'first_name' => 'Ray', 'last_name' => 'Harris', 'email' => 'ray@trioangle.com', 'mobile_number' => 98765432103,'password' => Hash::make('123456'), 'user_type' => 'Driver', 'company_id' => 1, 'status' => 'Active', 'created_at' => date('Y-m-d H:i:s')]);

		DB::table('users')->insert(['id' => 10004, 'first_name' => 'Elsa', 'last_name' => 'Mayert', 'email' => 'elsa@trioangle.com', 'mobile_number' => 98765432104,'password' => Hash::make('123456'), 'user_type' => 'Driver', 'company_id' => 1, 'status' => 'Active', 'created_at' => date('Y-m-d H:i:s')]);

		DB::table('users')->insert(['id' => 10005, 'first_name' => 'Rosario', 'last_name' => 'Heaney', 'email' => 'rosario@trioangle.com', 'mobile_number' => 98765432105,'password' => Hash::make('123456'), 'user_type' => 'Driver', 'company_id' => 1, 'status' => 'Active', 'created_at' => date('Y-m-d H:i:s')]);

		DB::table('users')->insert(['id' => 10006, 'first_name' => 'Jack', 'last_name' => 'Moen', 'email' => 'jack@trioangle.com', 'mobile_number' => 98765432106, 'password' => Hash::make('123456'), 'user_type' => 'Rider', 'status' => 'Active', 'created_at' => date('Y-m-d H:i:s')]);
		DB::table('users')->insert(['id' => 10007, 'first_name' => 'Krista', 'last_name' => 'Murray', 'email' => 'krista@trioangle.com', 'mobile_number' => 98765432107, 'password' => Hash::make('123456'), 'user_type' => 'Rider', 'status' => 'Active', 'created_at' => date('Y-m-d H:i:s')]);

		DB::table('profile_picture')->insert(['user_id' => 10001, 'src' => '', 'photo_source' => 'Local']);
		DB::table('profile_picture')->insert(['user_id' => 10002, 'src' => '', 'photo_source' => 'Local']);
		DB::table('profile_picture')->insert(['user_id' => 10003, 'src' => '', 'photo_source' => 'Local']);
		DB::table('profile_picture')->insert(['user_id' => 10004, 'src' => '', 'photo_source' => 'Local']);
		DB::table('profile_picture')->insert(['user_id' => 10005, 'src' => '', 'photo_source' => 'Local']);
		DB::table('profile_picture')->insert(['user_id' => 10006, 'src' => '', 'photo_source' => 'Local']);
		DB::table('profile_picture')->insert(['user_id' => 10007, 'src' => '', 'photo_source' => 'Local']);

		DB::table('driver_documents')->insert(['id' => '1', 'user_id' => 10001, 'license_front' => 'profile_pic_1462128586.jpg', 'license_back' => 'profile_pic_1462128585.jpg']);

		DB::table('driver_documents')->insert(['id' => '2', 'user_id' => 10002, 'license_front' => 'profile_pic_1462128586.jpg', 'license_back' => 'profile_pic_1462128585.jpg']);

		DB::table('driver_documents')->insert(['id' => '3', 'user_id' => 10003, 'license_front' => 'profile_pic_1462128586.jpg', 'license_back' => 'profile_pic_1462128585.jpg']);

		DB::table('driver_documents')->insert(['id' => '4', 'user_id' => 10004, 'license_front' => 'profile_pic_1462128586.jpg', 'license_back' => 'profile_pic_1462128585.jpg']);

		DB::table('driver_documents')->insert(['id' => '5', 'user_id' => 10005, 'license_front' => 'profile_pic_1462128586.jpg', 'license_back' => 'profile_pic_1462128585.jpg']);

		DB::table('vehicle')->insert(['id' => '1', 'user_id' => 10001, 'vehicle_id' => '1', 'vehicle_type' => 'GoferGo', 'vehicle_name' => 'TOYATA', 'vehicle_number' => 'TN 59 BL 9786', 'insurance' => 'profile_pic_1462128584.jpg', 'rc' => 'profile_pic_1462128583.jpg', 'permit' => 'profile_pic_1462128582.jpg','status' => 'Active','company_id'=>1]);

		DB::table('vehicle')->insert(['id' => '2', 'user_id' => 10002, 'vehicle_id' => '1', 'vehicle_type' => 'GoferGo', 'vehicle_name' => 'TOYATA', 'vehicle_number' => 'TN 59 BL 9786', 'insurance' => 'profile_pic_1462128584.jpg', 'rc' => 'profile_pic_1462128583.jpg', 'permit' => 'profile_pic_1462128582.jpg','status' => 'Active','company_id'=>1]);

		DB::table('vehicle')->insert(['id' => '3', 'user_id' => 10003, 'vehicle_id' => '1', 'vehicle_type' => 'GoferGo', 'vehicle_name' => 'TOYATA', 'vehicle_number' => 'TN 59 BL 9786', 'insurance' => 'profile_pic_1462128584.jpg', 'rc' => 'profile_pic_1462128583.jpg', 'permit' => 'profile_pic_1462128582.jpg','status' => 'Active','company_id'=>1]);

		DB::table('vehicle')->insert(['id' => '4', 'user_id' => 10004, 'vehicle_id' => '2', 'vehicle_type' => 'GoferX', 'vehicle_name' => 'TOYATA', 'vehicle_number' => 'TN 59 BL 9786', 'insurance' => 'profile_pic_1462128584.jpg', 'rc' => 'profile_pic_1462128583.jpg', 'permit' => 'profile_pic_1462128582.jpg','status' => 'Active','company_id'=>1]);

		DB::table('vehicle')->insert(['id' => '5', 'user_id' => 10005, 'vehicle_id' => '2', 'vehicle_type' => 'GoferX', 'vehicle_name' => 'TOYATA', 'vehicle_number' => 'TN 59 BL 9786','insurance' => 'profile_pic_1462128584.jpg', 'rc' => 'profile_pic_1462128583.jpg', 'permit' => 'profile_pic_1462128582.jpg','status' => 'Active','company_id'=>1]);

		DB::table('driver_address')->insert(['id' => '1', 'user_id' => 10001, 'address_line1' => '123', 'address_line2' => 'williiyam street', 'city' => 'newyork', 'state' => 'US', 'postal_code' => '4567']);
		DB::table('driver_address')->insert(['id' => '2', 'user_id' => 10002, 'address_line1' => '123', 'address_line2' => 'williiyam street', 'city' => 'newyork', 'state' => 'US', 'postal_code' => '4567']);
		DB::table('driver_address')->insert(['id' => '3', 'user_id' => 10003, 'address_line1' => '123', 'address_line2' => 'williiyam street', 'city' => 'newyork', 'state' => 'US', 'postal_code' => '4567']);
		DB::table('driver_address')->insert(['id' => '4', 'user_id' => 10004, 'address_line1' => '123', 'address_line2' => 'williiyam street', 'city' => 'newyork', 'state' => 'US', 'postal_code' => '4567']);
		DB::table('driver_address')->insert(['id' => '5', 'user_id' => 10005, 'address_line1' => '123', 'address_line2' => 'williiyam street', 'city' => 'newyork', 'state' => 'US', 'postal_code' => '4567']);

		DB::table('driver_location')->insert(['id' => '1', 'user_id' => '10001', 'latitude' => '9.9246092', 'longitude' => '78.1376492', 'car_id' => 1, 'status' => 'Offline']);
		DB::table('driver_location')->insert(['id' => '2', 'user_id' => '10002', 'latitude' => '9.9253912', 'longitude' => '78.1377565', 'car_id' => 1, 'status' => 'Offline']);
		DB::table('driver_location')->insert(['id' => '3', 'user_id' => '10003', 'latitude' => '9.9259619', 'longitude' => '78.1348597', 'car_id' => 2, 'status' => 'Offline']);
		DB::table('driver_location')->insert(['id' => '4', 'user_id' => '10004', 'latitude' => '9.9252221', 'longitude' => '78.1394517', 'car_id' => 2, 'status' => 'Offline']);
		DB::table('driver_location')->insert(['id' => '5', 'user_id' => '10005', 'latitude' => '9.9273992', 'longitude' => '78.1379711', 'car_id' => 3, 'status' => 'Offline']);

	}
}
