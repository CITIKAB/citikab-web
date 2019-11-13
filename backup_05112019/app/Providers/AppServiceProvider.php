<?php

namespace App\Providers;

use App\Http\Helper\FacebookHelper;
use App\Models\Admin;
use App\Models\CarType;
use App\Models\Language;
use Config;
use DB;
use Session;
use App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use View;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		define('EMAIL_LOGO_URL', 'images/logo.png');
		define('LOGIN_USER_TYPE', request()->segment(1));

		//
		Schema::defaultStringLength(191);

		if (env('DB_DATABASE') != '') {
/*
			$mysql_version_check = DB::select(DB::raw('SHOW VARIABLES LIKE "version";'));
			$mysql_version = $mysql_version_check[0]->Value;
			if (substr($mysql_version,2, 1) < '7' AND substr($mysql_version,4, 1) < '6') {
			    $sql = '
			        CREATE FUNCTION `currency_calc` (from VARCHAR,to VARCHAR,amount FLOAT)

			            RETURNS FLOAT
			            no sql deterministic
			            BEGIN
			                declare amt FLOAT;
			            END;
			    ';

			    DB::unprepared($sql);

			}*/

			if (Schema::hasTable('site_settings')) {

				$site_settings = DB::table('site_settings')->get();
				View::share('logo_url', url('images/logos/' . $site_settings[3]->value).'?v='.str_random(4));
				define('LOGO_URL', 'images/logos/' . $site_settings[3]->value);
				define('PAGE_LOGO_URL', 'images/logos/' . $site_settings[4]->value);
				View::share('favicon', url('images/logos/' . $site_settings[5]->value).'?v='.str_random(5));
				View::share('site_name', $site_settings[0]->value);
				define('SITE_NAME', $site_settings[0]->value);

				define('MANUAL_BOOK_CONTACT', '+'.$site_settings[9]->value.' '.$site_settings[8]->value);

				define('PAYPAL_CURRENCY_CODE', $site_settings[1]->value);
				define('PHP_DATE_FORMAT','Y-m-d');
				define('SITE_URL',$site_settings[10]->value);

				Config::set([
					'swap.providers.yahoo_finance' => false,
					'swap.providers.google_finance' => true,
				]);

			}
			if (Schema::hasTable('country')) {
				$country = DB::table('country')->get();
				View::share('country', $country);

			}
			if (Schema::hasTable('car_type')) {
				$car_type = CarType::where('status', '=', 'Active')->get();
				View::share('car_type', $car_type);

			}

			if (Schema::hasTable('api_credentials')) {

				// For Google Key

				$google_map_result = DB::table('api_credentials')->where('site', 'GoogleMap')->get();


				define('MAP_KEY', $google_map_result[0]->value);

				define('MAP_SERVER_KEY', $google_map_result[1]->value);

				View::share('map_key', $google_map_result[0]->value);
				// For Google Key


				//For facebook

				$facebook_result = DB::table('api_credentials')->where('site', 'Facebook')->get();
				define('FB_CLIENT_ID', $facebook_result[0]->value);

	        	define('ACCOUNTKIT_APP_ID', $facebook_result[2]->value);
	        	define('ACCOUNTKIT_APP_SECRET',$facebook_result[3]->value);
	        	define('ACCOUNTKIT_VERSION', 'v1.0');

	        	// Share Google Credentials
	        	$google_result = DB::table('api_credentials')->where('site','Google')->get();
	        	define('GOOGLE_CLIENT_ID', $google_result[0]->value);

				// For NEXMO Key

				$nexmo_result = DB::table('api_credentials')->where('site', 'Nexmo')->get();

				define('NEXMO_KEY', $nexmo_result[0]->value);
				define('NEXMO_SECRET', $nexmo_result[1]->value);
				define('NEXMO_FROM', $nexmo_result[2]->value);

				// For NEXMO Key

				// For FCM Key

				$fcm_result = DB::table('api_credentials')->where('site', 'FCM')->get();

				Config::set(['fcm.http' => [
					'server_key' => $fcm_result[0]->value,
					'sender_id' => $fcm_result[1]->value,
					'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
					'server_group_url' => 'https://android.googleapis.com/gcm/notification',
					'timeout' => 10,
				],
				]);

				// For Facebook app id and secret
				$fb_result = DB::table('api_credentials')->where('site', 'Facebook')->get();

				Config::set(['facebook' => [
					'client_id' => @$fb_result[0]->value,
					'client_secret' => @$fb_result[1]->value,
					'redirect' => url('/facebookAuthenticate'),
				],
				]);

				$fb = new FacebookHelper;
				View::share('fb_url', $fb->getUrlLogin());
				define('FB_URL', $fb->getUrlLogin());

				//Stripe Key

				$stripe_result = DB::table('payment_gateway')->where('site', 'Stripe')->get();
				
				define('STRIPE_KEY', $stripe_result[0]->value);
				define('STRIPE_SECRET', $stripe_result[1]->value);

			}
			if (Schema::hasTable('admin')) {
				$admin_email = @Admin::find(1)->email;
				View::share('admin_email', $admin_email);
			}

			if (Schema::hasTable('payment_gateway')) {
				$paypal_credentials = DB::table('payment_gateway')->where('site', 'PayPal')->get();

				define('PAYPAL_ID', $paypal_credentials[0]->value);

				if ($paypal_credentials[4]->value == 'sandbox') {
					define('PAYPAL_MODE', 0);
				} else {
					define('PAYPAL_MODE', 1);
				}

				define('PAYPAL_APP_ID', $paypal_credentials[5]->value);
				define('PAYPAL_CLIENT_ID', $paypal_credentials[6]->value);

				Config::set(['paypal.sandbox' => [
					'username' => $paypal_credentials[1]->value,
					'password' => $paypal_credentials[2]->value,
					'secret' => $paypal_credentials[3]->value,
					'app_id' => $paypal_credentials[5]->value,
				],
				]);

				$site_settings = DB::table('site_settings')->get();

				Config::set(['paypal.currency' => $site_settings[1]->value]);

				Config::set(['paypal.mode' => $paypal_credentials[4]->value]);
				define('Driver_Km', $site_settings[6]->value);

			}

			// Configure Email settings from email_settings table
			if(Schema::hasTable('email_settings'))
	        {
	            $result = DB::table('email_settings')->get();

	            Config::set([
                    'mail.driver'     => @$result[0]->value,
                    'mail.host'       => @$result[1]->value,
                    'mail.port'       => @$result[2]->value,
                    'mail.from'       => ['address' => @$result[3]->value,'name'    => @$result[4]->value],
                    'mail.encryption' => @$result[5]->value,
                    'mail.username'   => @$result[6]->value,
                    'mail.password'   => @$result[7]->value       
                ]);

	            if(@$result[0]->value=='mailgun') {
		            Config::set([
	                    'services.mailgun.domain'     => @$result[8]->value,
	                    'services.mailgun.secret'     => @$result[9]->value,
	                ]);
	           	}

	            Config::set([
                    'laravel-backup.notifications.mail.from' => @$result[3]->value,
                    'laravel-backup.notifications.mail.to'   => @$result[3]->value,
	            ]);
	        }

	        if(Schema::hasTable('language')){

	        	// Language lists for footer
			        $language = Language::translatable()->pluck('name', 'value');
			        View::share('language', $language);
					
					// Default Language for footer
					$default_language = Language::translatable()->where('default_language', '=', '1')->first();
					View::share('default_language', $default_language);

			        if($default_language) {

						Session::put('language', $default_language->value);
						App::setLocale($default_language->value);


					}

	        }

		}

	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}
}
