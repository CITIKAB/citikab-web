<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware gro up. Now create something great!
|
 */

// Route::get('/', 'HomeController@index');


Route::get('check_push', 'Api\RiderController@check_push');

Route::get('oweAmount', 'Api\RatingController@oweAmount');

Route::get('driver_invoice', 'DriverDashboardController@driver_invoice');


Route::get('call_again', 'Api\RiderController@call_again');
Route::group(['middleware' =>'canInstall'], function () {
	Route::group(['middleware' =>'locale'], function () {
	Route::get('currency_cron', 'HomeController@currency_cron');
	Route::get('/', 'HomeController@index');

});
	});
Route::group(['middleware' =>'locale'], function () {

Route::get('help', 'HomeController@help');
Route::get('help/topic/{id}/{category}', 'HomeController@help');
Route::get('help/article/{id}/{question}', 'HomeController@help');
Route::get('ajax_help_search', 'HomeController@ajax_help_search');

Route::post('set_session', 'HomeController@set_session');

Route::get('user_disabled', 'UserController@user_disabled');

// Route::get('test', 'HomeController@test');

Route::match(array('GET', 'POST'), 'signin_driver', 'UserController@signin_driver');
Route::match(array('GET', 'POST'),'signin_rider', 'UserController@signin_rider');
Route::match(array('GET', 'POST'),'signin_company', 'UserController@signin_company');
Route::get('facebook_login', 'UserController@facebook_login');
Route::get('forgot_password_driver', 'UserController@forgot_password');
Route::get('forgot_password_rider', 'UserController@forgot_password');
Route::get('forgot_password_company', 'UserController@forgot_password');
Route::post('forgotpassword', 'UserController@forgotpassword');
Route::match(array('GET', 'POST'), 'reset_password', 'UserController@reset_password');
Route::match(array('GET', 'POST'), 'company/reset_password', 'UserController@company_reset_password');
Route::get('forgot_password_link/{id}', 'EmailController@forgot_password_link');
Route::match(array('GET', 'POST'),'signup_rider', 'UserController@signup_rider');
Route::match(array('GET', 'POST'),'signup_driver', 'UserController@signup_driver');
Route::match(array('GET', 'POST'),'signup_company', 'UserController@signup_company');

Route::get('facebookAuthenticate', 'UserController@facebookAuthenticate');
Route::get('googleAuthenticate', 'UserController@googleAuthenticate');
Route::get('signin', 'UserController@signin');

Route::get('signup', 'UserController@signup');

Route::get('safety', 'RideController@safety');
Route::get('ride', 'RideController@ride');
Route::get('how_it_works', 'RideController@how_it_works');

Route::get('drive', 'DriveController@drive');
Route::get('requirements', 'DriveController@requirements');
Route::get('driver_app', 'DriveController@driver_app');
Route::get('drive_safety', 'DriveController@drive_safety');

// signup functionality
Route::post('rider_register', 'UserController@rider_register');
Route::post('driver_register', 'UserController@driver_register');
Route::post('company_register', 'UserController@company_register');
Route::post('login', 'UserController@login');
Route::post('login_driver', 'UserController@login_driver');
Route::post('ajax_trips/{id}', 'DashboardController@ajax_trips');

Route::post('profile_upload', 'DriverDashboardController@profile_upload');
Route::get('download_invoice/{id}', 'DriverDashboardController@download_invoice');
Route::get('download_rider_invoice/{id}', 'DashboardController@download_rider_invoice');

Route::get('print_invoice/{id}', 'DriverDashboardController@print_invoice');

});

// Rider Routes..
Route::group(['middleware' => ['locale','rider_guest']], function () {
	Route::get('trip', 'DashboardController@trip');
	Route::get('profile', 'DashboardController@profile');
	Route::get('payment', 'DashboardController@payment');
	Route::get('trip_detail/{id}', 'DashboardController@trip_detail');
	Route::post('rider_rating/{rating}/{trip_id}', 'DashboardController@rider_rating');
	Route::post('trip_detail/rider_rating/{rating}/{trip_id}', 'DashboardController@rider_rating');
	Route::get('trip_invoice/{id}', 'DashboardController@trip_invoice');
	Route::get('invoice_download/{id}', 'DashboardController@invoice_download');
	Route::post('rider_update_profile/{id}', 'DashboardController@update_profile');

});

// Driver Routes..
Route::group(['middleware' => ['locale','driver_guest']], function () {
	Route::get('driver_profile', 'DriverDashboardController@driver_profile');
	Route::get('documents/{id}', 'DriverDashboardController@documents');
	Route::post('document_upload/{id}', 'DriverDashboardController@document_upload');
	Route::get('add_vehicle', 'DriverDashboardController@add_vehicle');
	Route::get('driver_payment', 'DriverDashboardController@driver_payment');

	Route::get('driver_invoice/{id}', 'DriverDashboardController@driver_invoice');
	Route::get('driver_banking', 'DriverDashboardController@driver_banking');
	Route::get('driver_trip', 'DriverDashboardController@driver_trip');
	Route::get('driver_trip_detail/{id}', 'DriverDashboardController@driver_trip_detail');

	Route::post('ajax_payment', 'DriverDashboardController@ajax_payment');

	// profile update
	Route::post('driver_update_profile/{id}', 'DriverDashboardController@driver_update_profile');
	Route::get('driver_invoice', 'DriverDashboardController@show_invoice');

	//Payout Preferences
	Route::match(['get', 'post'], 'payout_preferences/{id}', 'UserController@payout_preferences')->where('id', '[0-9]+');
		Route::match(['get', 'post'], 'update_payout_preferences/{id}', 'UserController@update_payout_preferences')->where('id', '[0-9]+');
		Route::match(['get', 'post'], 'stripe_payout_preferences', 'UserController@stripe_payout_preferences');
		Route::get('payout_delete/{id}', 'UserController@payout_delete')->where('id', '[0-9]+');
		Route::get('payout_default/{id}', 'UserController@payout_default')->where('id', '[0-9]+');
		Route::get(
			'driver_dashboard',
			function () {
				return Redirect::to('payout_preferences/' . Auth::user()->id);
			}
		);

});

Route::get('sign_out', function () {
	//dd('fdf');
	$user_type = @Auth::user()->user_type;
	Auth::logout();
	if (@$user_type == 'Rider') {
		return redirect('signin_rider');
	} else {
		return redirect('signin_driver');
	}

});

// Admin Panel Routes

Route::group(['prefix' => 'admin', 'middleware' =>'admin_auth'], function () {
	Route::get('login', 'Admin\AdminController@login');
});
	Route::post('admin/authenticate', 'Admin\AdminController@authenticate');


Route::group(['prefix' => (LOGIN_USER_TYPE=='company')?'company':'admin', 'middleware' =>'admin_guest'], function () {

	Route::get('/', function () {
		return Redirect::to(LOGIN_USER_TYPE.'/dashboard');
	});
	Route::get('dashboard', 'Admin\AdminController@index');

	if (LOGIN_USER_TYPE=='company') {
		Route::get('logout', function () {
			Auth::guard('company')->logout();
		    return redirect('signin_company');
		});
		Route::get('profile', function () {
		    return redirect('company/edit_company/'.Auth::guard('company')->user()->id);
		});

		Route::match(['get', 'post'],'payout_preferences','CompanyController@payout_preferences');
		Route::get('payout_default/{id}', 'CompanyController@payout_default')->where('id', '[0-9]+');
		Route::get('payout_delete/{id}', 'CompanyController@payout_delete')->where('id', '[0-9]+');
		Route::match(['get', 'post'], 'company/stripe_payout_preferences', 'CompanyController@stripe_payout_preferences');
		Route::match(['get', 'post'], 'update_payout_preferences', 'CompanyController@update_payout_preferences');
		Route::get(
			'driver_dashboard',
			function () {
				return Redirect::to('payout_preferences/' . Auth::user()->id);
			}
		);
		Route::post('set_session', 'HomeController@set_session');

	}else{
		Route::get('logout', 'Admin\AdminController@logout');
	}

	//Admin Users,roles and permission routes
	Route::group(['middleware' => 'admin_can:manage_admin'], function() {
        Route::get('admin_user', 'Admin\AdminController@view');
        Route::match(array('GET', 'POST'),'add_admin_user', 'Admin\AdminController@add');
        Route::match(array('GET', 'POST'),'edit_admin_users/{id}', 'Admin\AdminController@update');
        Route::match(array('GET', 'POST'),'delete_admin_user/{id}', 'Admin\AdminController@delete');

        Route::get('roles', 'Admin\RolesController@index');
        Route::match(array('GET', 'POST'), 'add_role', 'Admin\RolesController@add');
        Route::match(array('GET', 'POST'), 'edit_role/{id}', 'Admin\RolesController@update')->where('id', '[0-9]+');
        Route::get('delete_role/{id}', 'Admin\RolesController@delete')->where('id', '[0-9]+');
    });

    // Manage Help Routes
        Route::group(['middleware' => 'admin_can:manage_help'],function () {
            Route::get('help_category', 'Admin\HelpCategoryController@index');
            Route::match(array('GET', 'POST'), 'add_help_category', 'Admin\HelpCategoryController@add');
            Route::match(array('GET', 'POST'), 'edit_help_category/{id}', 'Admin\HelpCategoryController@update')->where('id', '[0-9]+');
            Route::get('delete_help_category/{id}', 'Admin\HelpCategoryController@delete')->where('id', '[0-9]+');
            Route::get('help_subcategory', 'Admin\HelpSubCategoryController@index');
            Route::match(array('GET', 'POST'), 'add_help_subcategory', 'Admin\HelpSubCategoryController@add');
            Route::match(array('GET', 'POST'), 'edit_help_subcategory/{id}', 'Admin\HelpSubCategoryController@update')->where('id', '[0-9]+');
            Route::get('delete_help_subcategory/{id}', 'Admin\HelpSubCategoryController@delete')->where('id', '[0-9]+');
            Route::get('help', 'Admin\HelpController@index');
            Route::match(array('GET', 'POST'), 'add_help', 'Admin\HelpController@add');
            Route::match(array('GET', 'POST'), 'edit_help/{id}', 'Admin\HelpController@update')->where('id', '[0-9]+');
            Route::get('delete_help/{id}', 'Admin\HelpController@delete')->where('id', '[0-9]+');
            Route::post('ajax_help_subcategory/{id}', 'Admin\HelpController@ajax_help_subcategory')->where('id', '[0-9]+');
        });

	// Send message
	Route::group(['middleware' => 'admin_can:manage_send_message'], function() {
		Route::match(array('GET', 'POST'), 'send_message', 'Admin\SendmessageController@index');
		Route::post('get_send_users', 'Admin\SendmessageController@get_send_users');
	});
	
	//Rider
	Route::group(['middleware' =>'admin_can:view_rider'], function() {
		Route::get('rider', 'Admin\RiderController@index');
	});
	Route::group(['middleware' => 'admin_can:add_rider'], function() {
		Route::match(array('GET', 'POST'), 'add_rider', 'Admin\RiderController@add');
	});
	Route::group(['middleware' =>'admin_can:edit_rider'], function() {
		Route::match(array('GET', 'POST'), 'edit_rider/{id}', 'Admin\RiderController@update');
	});
	Route::group(['middleware' =>  'admin_can:delete_rider'], function() {
		Route::match(array('GET', 'POST'), 'delete_rider/{id}', 'Admin\RiderController@delete');
	});

	//Driver
	Route::group(['middleware' =>  'admin_can:view_driver'], function() {
		Route::get('driver', 'Admin\DriverController@index');
	});
	Route::group(['middleware' =>  'admin_can:add_driver'], function() {
		Route::match(array('GET', 'POST'), 'add_driver', 'Admin\DriverController@add');
	});
	Route::group(['middleware' =>  'admin_can:edit_driver'], function() {
		Route::match(array('GET', 'POST'), 'edit_driver/{id}', 'Admin\DriverController@update');
	});
	Route::group(['middleware' =>  'admin_can:delete_driver'], function() {
		Route::match(array('GET', 'POST'), 'delete_driver/{id}', 'Admin\DriverController@delete');
	});

	//Company
	Route::group(['middleware' =>  'admin_can:view_company'], function() {
		Route::get('company', 'Admin\CompanyController@index');
	});
	Route::group(['middleware' =>  'admin_can:add_company'], function() {
		Route::match(array('GET', 'POST'), 'add_company', 'Admin\CompanyController@add');
	});
	Route::group(['middleware' =>  'admin_can:edit_company'], function() {
		Route::match(array('GET', 'POST'), 'edit_company/{id}', 'Admin\CompanyController@update');
	});
	Route::group(['middleware' =>  'admin_can:delete_company'], function() {
		Route::match(array('GET', 'POST'), 'delete_company/{id}', 'Admin\CompanyController@delete');
	});

	//Manage Statements
	Route::group(['middleware' =>  'admin_can:manage_statements'], function() {
		Route::post('get_statement_counts', 'Admin\StatementController@get_statement_counts');
		Route::get('statements/{type}', 'Admin\StatementController@index');
		Route::get('view_driver_statement/{driver_id}', 'Admin\StatementController@view_driver_statement');
		Route::get('driver_statement', 'Admin\StatementController@driver_statement');
		Route::get('statement_all', 'Admin\StatementController@custom_statement');
	});


	// Manage Location routes
	Route::group(['middleware' => 'admin_can:manage_locations'], function() {
		Route::get('locations', 'Admin\LocationsController@index');
	    Route::match(array('GET', 'POST'),'add_location', 'Admin\LocationsController@add');
	    Route::match(array('GET', 'POST'),'edit_location/{id}', 'Admin\LocationsController@update');
	    Route::get('delete_location/{id}', 'Admin\LocationsController@delete');
	});

    //Manage Peak Based Fare Details
	Route::group(['middleware' => 'admin_can:manage_peak_based_fare'], function() {
		Route::get('manage_fare', 'Admin\ManageFareController@index');
	    Route::match(array('GET', 'POST'),'add_manage_fare', 'Admin\ManageFareController@add');
	    Route::match(array('GET', 'POST'),'edit_manage_fare/{id}', 'Admin\ManageFareController@update');
	    Route::get('delete_manage_fare/{id}', 'Admin\ManageFareController@delete');
	});

	//Manage Location Based Price routes
	Route::group(['middleware' =>  'admin_can:manage_location_based_fare'], function() {
		Route::get('location_based_fare', 'Admin\LocationBasedFareController@index');
	    Route::match(array('GET', 'POST'),'add_location_based_fare', 'Admin\LocationBasedFareController@add');
	    Route::match(array('GET', 'POST'),'edit_location_based_fare/{id}', 'Admin\LocationBasedFareController@update');
	    Route::match(array('GET', 'POST'),'delete_location_based_fare/{id}', 'Admin\LocationBasedFareController@delete');

	});

	//Map
	Route::group(['middleware' =>  'admin_can:manage_map'], function() {
		Route::match(array('GET', 'POST'), 'map', 'Admin\MapController@index');
		Route::match(array('GET', 'POST'), 'mapdata', 'Admin\MapController@mapdata');
	});

	Route::group(['middleware' =>  'admin_can:manage_heat_map'], function() {
		Route::match(array('GET', 'POST'), 'heat-map', 'Admin\MapController@heat_map');
		Route::match(array('GET', 'POST'), 'heat-map-data', 'Admin\MapController@heat_map_data');
	});

	//Car Type
	Route::group(['middleware' =>  'admin_can:manage_car_type'], function() {
		Route::get('car_type', 'Admin\CarTypeController@index');
		Route::match(array('GET', 'POST'), 'add_car_type', 'Admin\CarTypeController@add');
		Route::match(array('GET', 'POST'), 'edit_car_type/{id}', 'Admin\CarTypeController@update');
		Route::match(array('GET', 'POST'), 'delete_car_type/{id}', 'Admin\CarTypeController@delete');
	});

	//Vehicle
	Route::group(['middleware' =>  'admin_can:manage_vehicle'], function() {
		Route::get('vehicle', 'Admin\VehicleController@index');
		Route::match(array('GET', 'POST'), 'add_vehicle', 'Admin\VehicleController@add');
		Route::post('manage_vehicle/{company_id}/get_driver', 'Admin\VehicleController@get_driver');
		Route::match(array('GET', 'POST'), 'edit_vehicle/{id}', 'Admin\VehicleController@update');
		Route::match(array('GET', 'POST'), 'delete_vehicle/{id}', 'Admin\VehicleController@delete');
	});
	

	//Trips
	Route::group(['middleware' =>  'admin_can:manage_trips'], function() {
		Route::match(array('GET', 'POST'), 'trips', 'Admin\TripsController@index');
		Route::get('view_trips/{id}', 'Admin\TripsController@view');
		Route::post('trips/payout/{id}', 'Admin\TripsController@payout');
		Route::get('trips/export/{from}/{to}', 'Admin\TripsController@export');
	});

	// Manage Company Payout Routes
	Route::group(['middleware' =>  'admin_can:manage_company_payment'], function() {
		Route::get('payout/company/overall', 'Admin\CompanyPayoutController@overall_payout');
		Route::get('weekly_payout/company/{company_id}', 'Admin\CompanyPayoutController@weekly_payout');
		Route::get('per_week_report/company/{company_id}/{start_date}/{end_date}', 'Admin\CompanyPayoutController@payout_per_week_report');
		Route::get('per_day_report/company/{company_id}/{date}', 'Admin\CompanyPayoutController@payout_per_day_report');
		Route::post('make_payout/company', 'Admin\CompanyPayoutController@payout_to_company');
	});

	// Manage Driver Payout Routes
	Route::group(['middleware' =>  'admin_can:manage_driver_payments'], function() {
		Route::get('payout/overall', 'Admin\PayoutController@overall_payout');
		Route::get('weekly_payout/{driver_id}', 'Admin\PayoutController@weekly_payout');
		Route::get('per_week_report/{driver_id}/{start_date}/{end_date}', 'Admin\PayoutController@payout_per_week_report');
		Route::get('per_day_report/{driver_id}/{date}', 'Admin\PayoutController@payout_per_day_report');
		Route::post('make_payout', 'Admin\PayoutController@payout_to_driver');
	});

	// Wallet
	Route::group(['middleware' =>  'admin_can:manage_wallet'], function() {
		Route::match(array('GET', 'POST'), 'wallet', 'Admin\WalletController@index');
		Route::match(array('GET', 'POST'), 'add_wallet', 'Admin\WalletController@add');		
		Route::match(array('GET', 'POST'), 'edit_wallet/{id}', 'Admin\WalletController@update')->where('id', '[0-9]+');
		Route::get('delete_wallet/{id}', 'Admin\WalletController@delete')->where('id', '[0-9]+');
	});
	
	//Owe Amount
	Route::group(['middleware' =>  'admin_can:manage_owe_amount'], function() {
		Route::match(array('GET', 'POST'), 'owe', 'Admin\OweController@index')->name('owe');
		Route::get('details/{type}', 'Admin\OweController@owe_details')->name('owe_details');
		Route::get('update_driver_payment', 'Admin\OweController@update_payment')->name('update_payment');
	});

	// Company Owe amount
	Route::get('driver_payment', 'Admin\OweController@driver_payment')->name('driver_payment');

	// Promo Code
	Route::group(['middleware' =>  'admin_can:manage_promo_code'], function() {
		Route::get('promo_code', 'Admin\PromocodeController@index');
		Route::match(array('GET', 'POST'), 'add_promo_code', 'Admin\PromocodeController@add');		
		Route::match(array('GET', 'POST'), 'edit_promo_code/{id}', 'Admin\PromocodeController@update')->where('id', '[0-9]+');
		Route::get('delete_promo_code/{id}', 'Admin\PromocodeController@delete');
	});

	//Payments
	Route::group(['middleware' =>  'admin_can:manage_payments'], function() {
		Route::match(array('GET', 'POST'), 'payments', 'Admin\PaymentsController@index');
		Route::get('view_payments/{id}', 'Admin\PaymentsController@view');
		Route::get('payments/export/{from}/{to}', 'Admin\PaymentsController@export');
	});

	//Cancelled Trips
	Route::group(['middleware' =>  'admin_can:manage_cancel_trips'], function() {
		Route::get('cancel_trips', 'Admin\TripsController@cancel_trips');
	});

	//Rating
	Route::group(['middleware' =>  'admin_can:manage_rating'], function() {
		Route::get('rating', 'Admin\RatingController@index');
		Route::get('delete_rating/{id}', 'Admin\RatingController@delete');
	});

	//Manage fees
	Route::group(['middleware' =>  'admin_can:manage_fees'], function() {
		Route::match(array('GET', 'POST'), 'fees', 'Admin\FeesController@index');
	});

	//SiteSetting
	Route::group(['middleware' =>  'admin_can:manage_site_settings'], function() {
		Route::match(array('GET', 'POST'), 'site_setting', 'Admin\SiteSettingsController@index');
	});
	
	//Api credentials
	Route::group(['middleware' =>  'admin_can:manage_api_credentials'], function() {
		Route::match(array('GET', 'POST'), 'api_credentials', 'Admin\ApiCredentialsController@index');
	});

	//Payment Gateway
	Route::group(['middleware' =>  'admin_can:manage_payment_gateway'], function() {
		Route::match(array('GET', 'POST'), 'payment_gateway', 'Admin\PaymentGatewayController@index');
	});

	//Request data table
	Route::group(['middleware' =>  'admin_can:manage_requests'], function() {
		Route::get('detail_request/{id}', 'Admin\RequestController@detail_request');
		Route::match(array('GET', 'POST'), 'request', 'Admin\RequestController@index');
	});

	// Join us management
	Route::group(['middleware' =>  'admin_can:manage_join_us'], function() {
		Route::match(array('GET', 'POST'), 'join_us', 'Admin\JoinUsController@index');
	});

	//Manage Static pages
	Route::group(['middleware' =>  'admin_can:manage_static_pages'], function() {
		Route::get('pages', 'Admin\PagesController@index');
		Route::match(array('GET', 'POST'), 'add_page', 'Admin\PagesController@add');
		Route::match(array('GET', 'POST'), 'edit_page/{id}', 'Admin\PagesController@update')->where('id', '[0-9]+');
		Route::get('delete_page/{id}', 'Admin\PagesController@delete')->where('id', '[0-9]+');
	});

	Route::group(['middleware' =>  'admin_can:manage_metas'], function() {
		Route::match(array('GET', 'POST'), 'metas', 'Admin\MetasController@index');
		Route::match(array('GET', 'POST'), 'edit_meta/{id}', 'Admin\MetasController@update')->where('id', '[0-9]+');
	});

	// Manage Currency Routes
	Route::group(['middleware' =>  'admin_can:manage_currency'], function() {
		Route::get('currency', 'Admin\CurrencyController@index');
		Route::match(array('GET', 'POST'), 'add_currency', 'Admin\CurrencyController@add');
		Route::match(array('GET', 'POST'), 'edit_currency/{id}', 'Admin\CurrencyController@update')->where('id', '[0-9]+');
		Route::get('delete_currency/{id}', 'Admin\CurrencyController@delete')->where('id', '[0-9]+');
	});
	// Manage Language Routes
	Route::group(['middleware' =>  'admin_can:manage_language'], function() {
		Route::get('language', 'Admin\LanguageController@index');
		Route::match(array('GET', 'POST'), 'add_language', 'Admin\LanguageController@add');
		Route::match(array('GET', 'POST'), 'edit_language/{id}', 'Admin\LanguageController@update')->where('id', '[0-9]+');
		Route::get('delete_language/{id}', 'Admin\LanguageController@delete')->where('id', '[0-9]+');
	});

	Route::group(['middleware' => 'admin_can:manage_country'],function () {
        Route::get('country', 'Admin\CountryController@index');
        Route::match(array('GET', 'POST'), 'add_country', 'Admin\CountryController@add');
        Route::match(array('GET', 'POST'), 'edit_country/{id}', 'Admin\CountryController@update')->where('id', '[0-9]+');
        Route::get('delete_country/{id}', 'Admin\CountryController@delete')->where('id', '[0-9]+');
    });

    Route::group(['middleware' => 'admin_can:manage_manual_booking'],function () {
        Route::get('manual_booking/{id?}', 'Admin\ManualBookingController@index');
        Route::post('manual_booking/store', 'Admin\ManualBookingController@store');
        Route::post('search_phone', 'Admin\ManualBookingController@search_phone');
        Route::post('search_cars', 'Admin\ManualBookingController@search_cars');
        Route::post('get_driver', 'Admin\ManualBookingController@get_driver');
        Route::post('driver_list', 'Admin\ManualBookingController@driver_list');
        Route::get('later_booking', 'Admin\LaterBookingController@index');
        Route::post('immediate_request', 'Admin\LaterBookingController@immediate_request');
        Route::post('manual_booking/cancel', 'Admin\LaterBookingController@cancel');
    });
	
	// Manage Email Settings Routes
	Route::match(array('GET', 'POST'), 'email_settings', 'Admin\EmailController@index')->middleware(['admin_can:email_settings']);
    Route::match(array('GET', 'POST'), 'send_email', 'Admin\EmailController@send_email')->middleware(['admin_can:send_email']);

// Route::filter('guest', function() {
//     if (Auth::user()->guest()) {
//         $request_url = Request::url();
//         dd($request_url);
//         return Redirect::guest('login');
//     }

// });

});

// Static page route
Route::get('{name}', 'HomeController@static_pages');