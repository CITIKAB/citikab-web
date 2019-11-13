<?php

use Illuminate\Http\Request;
use App\Http\Helper\PaymantHelper;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// cron request for schedule ride

Route::get('glade_pay', function(){
	$payment_helper = new PaymantHelper();
	$va  = [
  "action"=>"initiate",
  "paymentType"=>"card",
  "user"=> [
      "firstname"=>"John",
      "lastname"=>"Doe",
      "email"=>"hello@example.com",
      "ip"=>"192.168.33.10",
      "fingerprint"=> "cccvxbxbxb"
  ],
  "card"=>[
      "card_no"=>"5438898014560229",
      "expiry_month"=>"09",
      "expiry_year"=>"19",
      "ccv"=>"789",
      "pin"=>"3310"
  ],
  "amount"=>"10000",
  "country"=> "NG",
  "currency"=> "NGN",
];
$asd = $payment_helper->glade_way_payment('payment',$va);

$validate = [ 
				"action"=>"validate",
			  	"txnRef"=>$asd['data']['txnRef'],
			  	"otp"=>"123456",
			];
$asd1 = $payment_helper->glade_way_payment('payment',$validate);
	dd($asd1);
});

Route::get('glade_pay_toekn', function(){
	$payment_helper = new PaymantHelper();
	$va  = [
  "action"=>"charge",
  "paymentType"=>"token",
  "token"=>"d1azeb7d.1570515253",
  "user"=> [
      "firstname"=>"John",
      "lastname"=>"Doe",
      "email"=>"hello@example.com",
      "ip"=>"192.168.33.10",
      "fingerprint"=> "cccvxbxbxb"
  ],
  "amount"=>"10000",
  "country"=> "NG",
  "currency"=> "NGN",
];
$asd = $payment_helper->glade_way_payment('payment',$va);

// $validate = [ 
// 				"action"=>"validate",
// 			  	"txnRef"=>$asd['data']['txnRef'],
// 			  	"otp"=>"123456",
// 			];
// $asd1 = $payment_helper->glade_way_payment('payment',$validate);
	dd($asd);
});
Route::get('cron_request_car', 'RiderController@cron_request_car');
Route::get('check_version', 'RiderController@check_version');

//TokenAuthController

Route::get('register', 'TokenAuthController@register');
Route::get('language_list', 'TokenAuthController@language_list');

Route::get('authenticate', 'TokenAuthController@authenticate');

Route::get('token', 'TokenAuthController@token');

Route::get('signup', 'TokenAuthController@signup');

Route::get('socialsignup', 'TokenAuthController@socialsignup');

Route::get('login', 'TokenAuthController@login');

Route::get('numbervalidation', 'TokenAuthController@numbervalidation');

Route::get('emailvalidation', 'TokenAuthController@emailvalidation');

Route::get('forgotpassword', 'TokenAuthController@forgotpassword');

Route::get('paypal_currency_conversion', 'TokenAuthController@paypal_currency_conversion');
Route::get('currency_list', 'TokenAuthController@currency_list');

//TokenAuthController


// for Login check

Route::group(['middleware' => 'jwt.auth'], function () {

Route::get('logout', 'TokenAuthController@logout');
Route::get('check_login','TokenAuthController@check_login');
Route::get('language','TokenAuthController@language');

Route::get('update_device', 'TokenAuthController@update_device');
Route::get('authenticate/user', 'TokenAuthController@getAuthenticatedUser');

//DriverController

Route::get('updatelocation', 'DriverController@updatelocation');
Route::get('stripe_supported_country_list', 'DriverController@stripe_supported_country_list');
Route::get('country_list', 'DriverController@country_list');
Route::get('accept_request', 'DriverController@accept_request');
Route::get('check_status', 'DriverController@check_status');
Route::get('cash_collected', 'DriverController@cash_collected');

Route::get('payout_details', 'DriverController@payout_details');
Route::get('payout_changes', 'DriverController@payout_changes');


//get Incomplete trips
Route::get('incomplete_trip_details', 'TripController@incomplete_trips');


//DriverController

//RiderController

Route::get('search_cars', 'RiderController@search_cars');
Route::get('request_cars', 'RiderController@request_cars');
Route::get('driver_details', 'RiderController@driver_details');
Route::get('track_driver', 'RiderController@track_driver');
Route::get('updateriderlocation', 'RiderController@updateriderlocation');
Route::get('promo_details','RiderController@promo_details');
Route::get('glade_pay_key','RiderController@glade_pay_key');
Route::get('sos','RiderController@sos');
Route::get('sosalert','RiderController@sosalert');
Route::get('save_schedule_ride', 'RiderController@save_schedule_ride');
Route::get('schedule_ride_cancel', 'RiderController@schedule_ride_cancel');
Route::get('get_schedule_rides', 'RiderController@get_schedule_rides');

//RiderController

//TripController

Route::get('arive_now', 'TripController@arive_now');
Route::get('begin_trip', 'TripController@begin_trip');

Route::get('get_rider_trips', 'TripController@get_rider_trips');
Route::get('driver_trips_history', 'TripController@driver_trips_history');
Route::get('cancel_trip', 'TripController@cancel_trip');



//TripController

//EarningController

Route::get('earning_chart', 'EarningController@earning_chart');
Route::get('add_payout', 'EarningController@add_payout');
Route::get('after_payment', 'EarningController@after_payment');
Route::get('add_wallet', 'EarningController@add_wallet');
Route::get('add_promo_code', 'EarningController@add_promo_code');



//EarningController

//RatingController

Route::get('driver_rating', 'RatingController@driver_rating');
Route::get('rider_feedback', 'RatingController@rider_feedback');
Route::get('trip_rating', 'RatingController@trip_rating');
Route::get('get_invoice', 'RatingController@getinvoice');

//RatingController

//profileController
Route::get('update_rider_profile', 'ProfileController@update_rider_profile');
Route::get('get_rider_profile', 'ProfileController@get_rider_profile');
Route::get('get_driver_profile', 'ProfileController@get_driver_profile');
Route::get('update_driver_profile', 'ProfileController@update_driver_profile');
Route::get('vehicle_details', 'ProfileController@vehicle_details');
Route::get('update_rider_location', 'ProfileController@update_rider_location');
Route::get('update_user_currency', 'ProfileController@update_user_currency');

Route::get('add_card_details', 'ProfileController@add_card_details');
Route::get('get_card_details', 'ProfileController@get_card_details');

//ProfileController

});


Route::match(array('GET', 'POST'), 'upload_profile_image','ProfileController@upload_profile_image');
Route::match(array('GET', 'POST'), 'document_upload','ProfileController@document_upload');
Route::match(array('GET', 'POST'), 'map_upload','TripController@map_upload');
Route::match(array('GET', 'POST'), 'end_trip','TripController@end_trip');
Route::match(array('GET', 'POST'), 'add_payout_preference','DriverController@add_payout_preference');
Route::match(array('GET', 'POST'), 'driver_bank_details','DriverController@driver_bank_details');
