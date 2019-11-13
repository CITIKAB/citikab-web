<?php

use Illuminate\Database\Seeder;

class SiteSettingsTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table('site_settings')->delete();

		DB::table('site_settings')->insert([
			['name' => 'site_name', 'value' => 'Gofer'],
			['name' => 'paypal_currency', 'value' => 'USD'],
			['name' => 'version', 'value' => '2.0'],
			['name' => 'logo', 'value' => 'logo.png'],
			['name' => 'page_logo', 'value' => 'page_logo.png'],
			['name' => 'favicon', 'value' => 'favicon.png'],
			['name' => 'driver_km', 'value' => '5'],
			['name' => 'head_code', 'value' => ''],
			['name' => 'admin_contact', 'value' => '1234567890'],
			['name' => 'admin_country_code', 'value' => '91'],
			['name' => 'site_url', 'value' => ''],
		]);
	}
}
