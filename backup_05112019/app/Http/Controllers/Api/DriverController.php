<?php

/**
 * Driver Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Driver
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\DriverLocation;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\Request as RideRequest;
use App\Models\ScheduleRide;
use App\Models\Trips;
use App\Models\User;
use App\Models\UsersPromoCode;
use App\Models\PayoutPreference;
use App\Models\PayoutCredentials;
use App\Models\Country;
use App\Models\BankDetail;
use Auth;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;
use File;

class DriverController extends Controller {
	protected $request_helper; // Global variable for Helpers instance

	public function __construct(RequestHelper $request) {
		$this->request_helper = $request;
		$this->helper = new Helpers;
	}

	/**
	 * Update Location of Driver & calculate the trip distance while trip
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */

	public function updatelocation(Request $request) {

	 	$user_details = JWTAuth::parseToken()->authenticate();

		if ($request->trip_id) {

			$rules = array(
				'total_km' => 'required',
				'latitude' => 'required',
				'longitude' => 'required',
				'user_type' => 'required|in:Driver,driver',
				'car_id' => 'required|exists:car_type,id',
				'status' => 'required|in:Online,Offline,online,offline,Trip,trip',
				'trip_id' => 'required|exists:trips,id',

			);
		} else {
			$rules = array(
				'latitude' => 'required',
				'longitude' => 'required',
				'user_type' => 'required|in:Driver,driver',
				'car_id' => 'required|exists:car_type,id',
				'status' => 'required|in:Online,Offline,online,offline,Trip,trip',

			);
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->get();


			if (count($user)) {
				$driver_location = DriverLocation::where('user_id', $user_details->id)->first();

				if ($request->trip_id) {

					$old_km = Trips::where('id', $request->trip_id)->first()->total_km;
					$user_id = Trips::where('id', $request->trip_id)->first()->user_id;

					$user_rider = User::where('id', $user_id)->get()->first();

					$device_type = $user_rider->device_type;

					$device_id = $user_rider->device_id;
					$user_type = $user_rider->user_type;
					$push_tittle = "Live Tracking";
					$data = array('live_tracking' => array('trip_id' => $request->trip_id, 'driver_latitude' => @$request->latitude, 'driver_longitude' => @$request->longitude));
					// if($device_id!="")
					// {
					// if($user_rider->device_type == 1)
					// {
					//   $this->request_helper->push_notification_ios($push_tittle,$data,$user_type,$device_id);
					// }
					// else
					// {
					//   $this->request_helper->push_notification_android($push_tittle,$data,$user_type,$device_id);
					// }

					// }
					if ($user[0]->device_type == 3) {
						$old_latitude = $driver_location->latitude;

						$old_longitude = $driver_location->longitude;

						$earthRadius = 6371000;
						$latFrom = deg2rad($old_latitude);
						$lonFrom = deg2rad($old_longitude);
						$latTo = deg2rad($request->latitude);
						$lonTo = deg2rad($request->longitude);

						$latDelta = $latTo - $latFrom;
						$lonDelta = $lonTo - $lonFrom;

						$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

						$meter = number_format((($angle * $earthRadius)), 2);

						$km = (($meter) / 1000);

					} else {

						$km = $request->total_km;

					}

					$new_km = $old_km + $km;


					    /* json file */

						$trip_id = $request->trip_id;		

						$file = $trip_id. '_file.json';
						$destinationPath=public_path()."/trip_file/";

						if (!is_dir($destinationPath)) 
						{ 
						  mkdir($destinationPath,0777,true);  
						}

						$old_path = base_path('public/trip_file/'.$trip_id.'_file.json');

						if(file_exists($old_path))
						{
							$jsonString = file_get_contents($old_path);
							$datas = json_decode($jsonString, true);
						}

						$datas[] = array(

							'latitude' => $request->latitude,
							'longitude'=>$request->longitude,
							'current_km' =>  $km,
							'old_km'=>$old_km,
							'new_km'=> (string)$new_km,
							'time' => date('H:i:s')
						);

						$data= json_encode($datas ,JSON_PRETTY_PRINT);
						

						File::put($destinationPath.$file,$data);

					/* json file */

					Trips::where('id', $request->trip_id)->update(['total_km' => $new_km]);

					$data = [
						'user_id' => $user_details->id,

						'latitude' => $request->latitude,

						'longitude' => $request->longitude,

					];

					DriverLocation::updateOrCreate(['user_id' => $user_details->id], $data);

					$test = Trips::where('id', $request->trip_id)->first();

					$new_meter = $request->total_km + $test->meter;

					$new = $test->test . ',' . $request->latitude . '-' . $request->longitude . '--' . $request->total_km . '--' . $test->total_km . ')';

					$new_count = $test->count + 1;

					//Trips::where('id',$request->trip_id)->update(['test'   => $new, 'count'   => $new_count ,'meter' => $new_meter]);

					return response()->json([

						'status_message' => "updated successfully",

						'status_code' => '1',

					]);

				}

				if (count($driver_location) && $driver_location->status == 'Trip') {

					return response()->json([

						'status_message' => trans('messages.please_complete_your_current_trip'),

						'status_code' => '0',

					]);

				}

				$data = [
					'user_id' => $user_details->id,

					'latitude' => $request->latitude,

					'longitude' => $request->longitude,

					'car_id' => $request->car_id,

				];

				if ($request->status == "Online" || $request->status == "Offline") {
					$data['status'] = $request->status;
				}
				DriverLocation::updateOrCreate(['user_id' => $user_details->id], $data);

				return response()->json([

					'status_message' => "updated successfully",

					'status_code' => '1',

				]);

			} else {
				return response()->json([

					'status_message' => "Invalid credentials",

					'status_code' => '0',

				]);

			}

		}
	}

	/**
	 * Check the Document status from driver side
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */

	public function check_status(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(

			'user_type' => 'required|in:Driver,driver,Rider,rider',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->first();

			if (count($user)) {

				return response()->json([

					'driver_status' => @$user->status != '' ? $user->status : '',

					'status_message' => trans('messages.success'),

					'status_code' => '1',

				]);
			} else {
				return response()->json([

					'status_message' => trans('messages.invalid_credentials'),

					'status_code' => '0',

				]);

			}

		}

	}

	/**
	 * Accept the request from Rider
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */

	public function accept_request(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'user_type' => 'required|in:Driver,driver',
			'status' => 'required|in:Online,online,Trip,trip',
			'request_id' => 'required',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->first();
			
			$req = @RideRequest::where('id', $request->request_id)->first();
			$request_group = $req->group_id;
			$request_status = @RideRequest::where('group_id', $request_group)->where('status', 'Accepted')->get()->count();
			if ($request_status == "0") {

				if (count($user)) {

					DriverLocation::where('user_id', $user->id)->update(['status' => $request->status]);

					RideRequest::where('id', $request->request_id)->update(['status' => 'Accepted']);

					$data = RideRequest::where('id', $request->request_id)->first();

					if ($data->schedule_id!= '') {

					ScheduleRide::where('id', $data->schedule_id)->update(['status' => 'Completed']);
					$schedule_ride = ScheduleRide::where('id', $data->schedule_id)->first();
				
				} 

					if($req->timezone=='')
					{

					 date_default_timezone_set($req->timezone);

					}
					else{

						$driver_location = DriverLocation::where('user_id', $user_details->id)->first();
						//  get user default location
						$user_timezone = $this->getTimeZone($driver_location->latitude, $driver_location->longitude);

						if ($user_timezone != '') {

						   date_default_timezone_set($user_timezone);
						}
					}

					//get polyline

					$polyline = @$this->request_helper->GetPolyline($data->pickup_latitude, $data->drop_latitude, $data->pickup_longitude, $data->drop_longitude);

					// Create Trip

					$trip = new Trips;

					$trip->user_id = $data->user_id;
					$trip->pickup_latitude = $data->pickup_latitude;
					$trip->pickup_longitude = $data->pickup_longitude;
					$trip->drop_latitude = $data->drop_latitude;
					$trip->drop_longitude = $data->drop_longitude;
					$trip->driver_id = $data->driver_id;
					$trip->car_id = $data->car_id;
					$trip->pickup_location = $data->pickup_location;
					$trip->drop_location = $data->drop_location;
					$trip->request_id = $data->id;
					$trip->trip_path = @$polyline;
					$trip->payment_mode = $data->payment_mode;
					$trip->status = 'Scheduled';
					$trip->currency_code = $user->currency->code;
					$trip->peak_fare = $data->peak_fare;
					$trip->save();

				
					$push_tittle = "Request Accepted";

					$user_type = $data->users->user_type;

					$device_id = $data->users->device_id;

					$push_data = array('accept_request' => array('trip_id' => $trip->id));

					if ($data->users->device_type != null && $data->users->device_type != '') {
						if ($data->users->device_type == 1) {
							$driver = User::where('id', $trip->driver_id)->first();

							$total_rating = DB::table('rating')->select(DB::raw('sum(rider_rating) as rating'))
								->where('driver_id', $trip->driver_id)->where('rider_rating', '>', 0)->first()->rating;

							$total_rating_count = Rating::where('driver_id', $trip->driver_id)->where('rider_rating','>', 0)->get()->count();

							if ($total_rating_count != 0) {
								$driver_rating = (string) round(($total_rating / $total_rating_count), 2);
							} else {
								$driver_rating = '0.0';
							}

							$get_min_time = $this->request_helper->GetDrivingDistance($data->pickup_latitude, $data->drop_latitude, $data->pickup_longitude, $data->drop_longitude);
							if ($get_min_time['status'] == "success") {

								$get_near_car_time = round(floor(round($get_min_time['time'] / 60)));

							} else {

								$get_near_car_time = 0;

							}
							$push_data = array('accept_request' => array('trip_id' => $trip->id,
								'pickup_latitude' => $trip->pickup_latitude,
								'pickup_longitude' => $trip->pickup_longitude,
								'drop_latitude' => $trip->drop_latitude,
								'drop_longitude' => $trip->drop_longitude,
								'pickup_location' => $trip->pickup_location,
								'drop_location' => $trip->drop_location,
								'driver_id' => $trip->driver_id,
								'driver_name' => $data->driver->first_name,
								'car_id' => $trip->car_id,
								'car_name' => $data->car_type->car_name,
								'car_active_image' =>$data->car_type->active_image,
								'rating' => $driver_rating,
								'arrival_time' => (string) $get_near_car_time,
								'driver_thumb_image' => @$driver->profile_picture->src != '' ? $driver->profile_picture->src : url('images/user.jpeg'),
								'vehicle_number' => @$driver->driver_documents->vehicle_number != '' ? $driver->driver_documents->vehicle_number : '',
								'vehicle_name' => @$driver->driver_documents->vehicle_name != '' ? $driver->driver_documents->vehicle_name : '',
								'trip_status' => @$trip->status != '' ? $trip->status : '',
								'mobile_number' => '+' . $driver->country_code . $driver->mobile_number,
								'driver_latitude' => $driver->driver_location->latitude,
								'driver_longitude' => $driver->driver_location->longitude,

							),
							);

							$this->request_helper->push_notification_ios($push_tittle, $push_data, $user_type, $device_id);
						} else {

							$this->request_helper->push_notification_android($push_tittle, $push_data, $user_type, $device_id);

						}
					}
					if (isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
						$to=$data->users->country_code.$data->users->mobile_number;
						$text = trans('messages.request_accepted');
        				$this->request_helper->send_nexmo_message($to,$text);
					}

					$user = array(

						'status_message' => 'Success',

						'status_code' => '1',

						'trip_id' => $trip->id,
						
						'booking_type' => (@$trip->ride_request->schedule_ride->booking_type==null)?"":@$trip->ride_request->schedule_ride->booking_type,

						'rider_name' => $data->users->first_name,

						'mobile_number' => '+' . $data->users->country_code . $data->users->mobile_number,

						'rider_thumb_image' => @$data->profile_picture->src != '' ? $data->profile_picture->src : url('images/user.jpeg'),

						'rating_value' => '',

						'car_id' => $data->car_type->id,

						'rider_id' => $trip->user_id,

						'car_type' => $data->car_type->car_name,

						'car_active_image' =>$data->car_type->active_image,

						'pickup_location' => $data->pickup_location,

						'drop_location' => $data->drop_location,

						'pickup_latitude' => $data->pickup_latitude,

						'pickup_longitude' => $data->pickup_longitude,

						'drop_latitude' => $data->drop_latitude,

						'drop_longitude' => $data->drop_longitude,

						'payment_method' => $data->payment_mode,

					);

					return response()->json($user);
				} else {
					return response()->json([

						'status_message' => "Invalid credentials",

						'status_code' => '0',

					]);

				}
			} else {
				return response()->json([

					'status_message' => "Already Accepted",

					'status_code' => '0',

				]);
			}
		}
	}

	public function cash_collected(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'trip_id' => 'required|exists:trips,id',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			Trips::where('id', $request->trip_id)->update(['status' => 'Completed', 'paykey' => @$request->paykey, 'payment_status' => 'Completed']);

			$trip = Trips::where('id', $request->trip_id)->first();

			$data = [
				'trip_id' => $request->trip_id,

				'correlation_id' => @$request->paykey,

				'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',

			];

			Payment::updateOrCreate(['trip_id' => $request->trip_id], $data);

			$rider = User::where('id', $trip->user_id)->first();

			$driver_thumb_image = @$trip->driver_thumb_image != '' ? $trip->driver_thumb_image : url('images/user.jpeg');

			$push_data = array('trip_payment' => array('status' => 'Cash Collected','trip_id' => $request->trip_id,'driver_thumb_image' => $driver_thumb_image));

			$user_type = $rider->user_type;

			$device_id = $rider->device_id;

			$push_tittle = "Cash Collected";
			$schedule_ride = ScheduleRide::find($trip->ride_request->schedule_id);
			if ($rider->device_type!=null && $rider->device_type!='') {
				if ($rider->device_type == 1) {
					$this->request_helper->push_notification_ios($push_tittle, $push_data, $user_type, $device_id);
				} else {
					$this->request_helper->push_notification_android($push_tittle, $push_data, $user_type, $device_id);
				}

			}
			if (isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
				$to=$rider->country_code.$rider->mobile_number;
				$text = trans('messages.trip_cash_collected');
				$text = $text.trans('messages.cash_collected_payment_detail',['base_fare'=>$trip->base_fare,'time_fare'=>$trip->time_fare,'distance_fare'=>$trip->distance_fare,'total_fare'=>$trip->total_fare]);
				if ($trip->peak_amount!=0) {
					$text = $text.trans('messages.peak_fare_detail',['peak_fare'=>$trip->peak_amount]);
				}
				if ($trip->schedule_fare!=0) {
					$text = $text.trans('messages.schedule_fare_detail',['schedule_fare'=>$trip->schedule_fare]);
				}
				$text = $text.trans('messages.service_fare_detail',['service_fare'=>$trip->access_fee]);
				$this->request_helper->send_nexmo_message($to,$text);
			}

			$users_promo_codes = UsersPromoCode::whereUserId($trip->user_id)->whereTripId(0)->with('promo_code')->get();

			$final_promo_details = [];

			foreach ($users_promo_codes as $row) {
				if (@$row->promo_code) {
					$promo_details['id'] = $row->promo_code->id;
					$promo_details['amount'] = $row->promo_code->amount;
					$promo_details['code'] = $row->promo_code->code;
					$promo_details['expire_date'] = $row->promo_code->expire_date_dmy;
					$final_promo_details[] = $promo_details;
				}
			}

			return response()->json([

				'status_message' => "Cash Collected Successfully",

				'status_code' => '1',

				'promo_details' => $final_promo_details,

				'trip_id' => $trip->id,

				'rider_thumb_image' => $trip->rider_thumb_image,

			]);
		}
	}

	public function getTimeZone($lat1, $lat2) {

		$timestamp = strtotime(date('Y-m-d H:i:s'));

		$geo_timezone = file_get_contents('https://maps.googleapis.com/maps/api/timezone/json?location=' . @$lat1 . ',' . @$lat2 . '&timestamp=' . $timestamp . '&key=' . MAP_KEY);

		$timezone = json_decode($geo_timezone);

		if ($timezone->status == 'OK') {
			return $timezone->timeZoneId;
		} else {
			return '';
		}
	}

	/**
	 * Load payout Preferences
	 *
	 * @param  Get method inputs
	 * @return Response in Json
	 */
	public function add_payout_preference() {

		$request = request();
		$driver = JWTAuth::toUser($_POST['token']);

		//File::put('images/' . time() . '.txt', print_r($request->all(), true));

		$user_id = $driver->id;

		$user = User::find($user_id);

	//check payoutpreference is selected or not

	$payout_default_count = PayoutCredentials::where('user_id', $user->id)->where('default', '=', 'yes');
		

		// first get payout method and country validation

		$rules = array(

			'payout_method' => 'required|in:stripe,paypal,Stripe,Paypal,manual,Manual',

		);

		if ($request->payout_method == 'stripe' || $request->payout_method == 'Stripe') {

			$rules['country'] = 'required|exists:country,short_name';
		}


		$messages = array('required' => ':attribute is required.');

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}

			return response()->json(
				[

					'status_message' => $error_msg['0']['0']['0'],

					'status_code' => '0',

				]
			);
		}

		/**
		 * Add payout preference for Stripe --start--
		 */
		if (strtolower($request->payout_method) == 'stripe') {

			if (empty($request->file('document'))) {

				return response()->json(
					[

						'status_message' => 'document required',

						'status_code' => '0',

					]
				);
			}

			$country = $request->country;


			/**
			 * required field validation --start--
			 */
			$mandatory_field = PayoutPreference::getMandatory($country);

			$rules = $mandatory_field;

			// $rules['email'] = 'required';
			$rules['address1'] = 'required';
			$rules['city'] = 'required';
			$rules['state'] = 'required';
			$rules['postal_code'] = 'required';
			$rules['document'] = 'required';
			if ($country == 'JP') {
				$rules['phone_number'] = 'required';
				$rules['bank_name'] = 'required';
				$rules['branch_name'] = 'required';
				$rules['address1'] = 'required';
				$rules['kanji_address1'] = 'required';
				$rules['kanji_address2'] = 'required';
				$rules['kanji_city'] = 'required';
				$rules['kanji_state'] = 'required';
				$rules['kanji_postal_code'] = 'required';

				if (!$user->gender) {
					$rules['gender'] = 'required|in:male,female';
				}
			}
			$messages = array('required' => ':attribute is required.');

			$validator = Validator::make($request->all(), $rules, $messages);

			if ($validator->fails()) {
				$error = $validator->messages()->toArray();

				foreach ($error as $er) {
					$error_msg[] = array($er);
				}

				return response()->json(
					[

						'status_message' => $error_msg['0']['0']['0'],

						'status_code' => '0',

					]
				);
			}

			/**
			 * required field validation --end--
			 */

			$stripe_key = STRIPE_SECRET;

			\Stripe\Stripe::setApiKey($stripe_key);

			$account_holder_type = 'individual';
			


			// create Stripe Account //
			try {
				$recipient = \Stripe\Account::create(
					array(
						"country" => strtolower($country),
						"tos_acceptance" => array(
							"date" => time(),
							"ip" => $_SERVER['REMOTE_ADDR'],
						),
						"payout_schedule" => array(
							"interval" => "manual",
						),
						"type" => "custom",
						'email' => $driver->email ? $driver->email : 'abdul@gmail.com',
					)
				);
				

				$payout_preference_stripe_token = @$recipient->id;
			} catch (\Exception $e) {
				return response()->json(
					[

						'status_message' => $e->getMessage(),

						'status_code' => '0',

					]
				);
			}


			// create stripe token to add bank account //
			try {
				$routing_number = $request->routing_number ? $request->routing_number : '';

				$iban_supported_country = Country::getIbanRequiredCountries();
				if (in_array($country, $iban_supported_country)) {
					$account_number = $request->iban;
					$stripe_token = \Stripe\Token::create(
						array(
							"bank_account" => array(
								"country" => $country,
								"currency" => $request->currency,
								"account_holder_name" => $request->account_holder_name,
								"account_holder_type" => $account_holder_type,
								// "routing_number" => $routing_number,
								"account_number" => $account_number,
							),
						)
					);
				} else {
					$account_number = $request->account_number;
					if ($country == 'AU') {
						$routing_number = $request->bsb;
					} elseif ($country == 'HK') {
						$routing_number = $request->clearing_code . '-' . $request->branch_code;
					} elseif ($country == 'JP' || $country == 'SG') {
						$routing_number = $request->bank_code . $request->branch_code;
					} elseif ($country == 'GB') {
						$routing_number = $request->sort_code;
					}

					$stripe_token = \Stripe\Token::create(
						array(
							"bank_account" => array(
								"country" => $country,
								"currency" => $request->currency,
								"account_holder_name" => $request->account_holder_name,
								"account_holder_type" => $account_holder_type,
								"routing_number" => $routing_number,
								"account_number" => $request->account_number,
							),
						)
					);
				}
			} catch (\Exception $e) {
				return response()->json(
					[

						'status_message' => $e->getMessage(),

						'status_code' => '0',

					]
				);
			}
			// create external account using stripe token //
			try {
				$recipient->external_accounts->create(
					array(
						"external_account" => $stripe_token,
					)
				);
			} catch (\Exception $e) {
				return response()->json(
					[

						'status_message' => $e->getMessage(),

						'status_code' => '0',

					]
				);
			}
			try {

				// insert external account details //
				if ($country != 'JP') {
					$recipient->legal_entity->type = $account_holder_type;
					$recipient->legal_entity->first_name = $user->name;
					$recipient->legal_entity->last_name = $user->name;
					$recipient->legal_entity->dob->day = '03';
					$recipient->legal_entity->dob->month = '08';
					$recipient->legal_entity->dob->year = '1990';
					$recipient->legal_entity->address->line1 = @$request->address1;
					$recipient->legal_entity->address->line2 = @$request->address2 ? @$request->address2 : null;
					$recipient->legal_entity->address->city = @$request->city;
					$recipient->legal_entity->address->country = @$country;
					$recipient->legal_entity->address->state = @$request->state ? @$request->state : null;
					$recipient->legal_entity->address->postal_code = @$request->postal_code;
					if ($country == 'US') {
						$recipient->legal_entity->ssn_last_4 = $request->ssn_last_4;
					}
					$recipient->save();
				} else {
					$address_kana = array(
						'line1' => $request->address1,
						'town' => $request->address2,
						'city' => $request->city,
						'state' => $request->state,
						'postal_code' => $request->postal_code,
						'country' => $country,
					);
					$address_kanji = array(
						'line1' => $request->kanji_address1,
						'town' => $request->kanji_address2,
						'city' => $request->kanji_city,
						'state' => $request->kanji_state,
						'postal_code' => $request->kanji_postal_code,
						'country' => $country,
					);

					$recipient->legal_entity->type = $account_holder_type;
					$recipient->legal_entity->first_name_kana = $user->name;
					$recipient->legal_entity->last_name_kana = $user->name;
					$recipient->legal_entity->first_name_kanji = $user->name;
					$recipient->legal_entity->last_name_kanji = $user->name;
					$recipient->legal_entity->dob->day = '03';
					$recipient->legal_entity->dob->month = '08';
					$recipient->legal_entity->dob->year = '1990';
					$recipient->legal_entity->address_kana = $address_kana;
					$recipient->legal_entity->address_kanji = $address_kanji;
					$recipient->legal_entity->gender = $request->gender ? $request->gender : 'male';

					$recipient->legal_entity->phone_number = @$request->phone_number ? $request->phone_number : 0;

					$recipient->save();
				}
			} catch (\Exception $e) {
				return response()->json(
					[

						'status_message' => $e->getMessage(),

						'status_code' => '0',

					]
				);
			}

			// document upload to create stripe custome account end //

            $document = $request->file('document');
			$extension =   $document->getClientOriginalExtension();
            $filename  =   $user_id.'_driver_document_'.time().'.'.$extension;
            $filenamepath = dirname($_SERVER['SCRIPT_FILENAME']).'/images/driver/'.$user_id.'/uploads';
                                
            if(!file_exists($filenamepath))
            {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/driver/'.$user_id.'/uploads', 0777, true);
            }
            $success   =   $document->move('images/driver/'.$user_id.'/uploads/', $filename);

			
			try {
				$document_path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/driver/'.$user_id.'/uploads/'.$filename;

				$stripe_file_details = \Stripe\FileUpload::create(
					array(
						"purpose" => "identity_document",
						"file" => fopen($document_path, 'r'),
					),
					array("stripe_account" => $recipient->id)
				);

				$recipient->legal_entity->verification->document = $stripe_file_details->id;
				$recipient->save();
				$stripe_document = $stripe_file_details->id;
			} catch (\Exception $e) {
				return response()->json(
					[

						'status_message' => $e->getMessage(),

						'status_code' => '0',

					]
				);
			}

			// document upload to create stripe custome account end //

		
			$payout_preference = new PayoutPreference;

			$payout_preference->user_id = $user->id;

			$payout_preference->paypal_email = $recipient->id;

			$payout_preference->address1 = $request->address1 != ''

			? $request->address1 : '';

			$payout_preference->address2 = $request->address2 != ''

			? $request->address2 : '';

			$payout_preference->city = $request->city != ''

			? $request->city : '';

			$payout_preference->state = $request->state != ''

			? $request->state : '';

			$payout_preference->country = $country;

			$payout_preference->postal_code = $request->postal_code != ''

			? $request->postal_code : '';

			$payout_preference->currency_code = $request->currency != null

			? $request->currency : DEFAULT_CURRENCY;

			$payout_preference->routing_number = $routing_number ? $routing_number : '';

			$payout_preference->account_number = $account_number ? $account_number : '';

			$payout_preference->holder_name = $request->account_holder_name;

			$payout_preference->holder_type = $account_holder_type;

			$payout_preference->document_id = $stripe_document;

			$payout_preference->document_image = $filename;

			$payout_preference->phone_number = $request->phone_number ? $request->phone_number : '';

			$payout_preference->branch_code = $request->branch_code ? $request->branch_code : '';

			$payout_preference->bank_name = $request->bank_name ? $request->bank_name : '';

			$payout_preference->branch_name = $request->branch_name ? $request->branch_name : '';

			$payout_preference->payout_method = 'Stripe';

			$payout_preference->ssn_last_4 = $country == 'US' ? $request->ssn_last_4 : '';

			$payout_preference->address_kanji = isset($address_kanji) ? json_encode($address_kanji) : json_encode([]);

			$payout_preference->save(); //save Payout Details


		$payout_credentials = new PayoutCredentials;        
        $payout_credentials->user_id = $user_id;
        $payout_credentials->preference_id = $payout_preference->id;
        $payout_credentials->payout_id = @$recipient->id;
        $payout_credentials->type = 'Stripe';
		$payout_credentials->default = $payout_default_count->count() == 0 ? 'yes' : 'no';
		$payout_credentials->save();


		}

		if (strtolower($request->payout_method) == 'manual') {

			$filename = '';

			if ($request->file('document')) {

				$file = $request->file('document');

				$file_path = $this->fileUpload($file, 'public/images/other_payout_document/' . $user->id);

				$this->fileSave('stripe_document', $user->id, $file_path['file_name'], '1');

				$filename = $file_path['file_name'];

			}

			$payout_preference = new PayoutPreference;

			$payout_preference->user_id = $user->id;

			$payout_preference->address1 = $request->address != ''

			? $request->address : '';

			$payout_preference->city = $request->city != ''

			? $request->city : '';

			$payout_preference->state = $request->state != ''

			? $request->state : '';

			$payout_preference->country = 'OT';

			$payout_preference->postal_code = $request->postal_code != ''

			? $request->postal_code : '';

			$payout_preference->routing_number = $request->routing_number ? $request->routing_number : '';

			$payout_preference->account_number = $request->account_number ? $request->account_number : '';

			$payout_preference->holder_name = $request->account_holder_name;

			$payout_preference->holder_type = 'company';

			$payout_preference->document_image = $filename;

			$payout_preference->branch_code = $request->branch_code ? $request->branch_code : '';

			$payout_preference->bank_name = $request->bank_name ? $request->bank_name : '';

			$payout_preference->branch_name = $request->branch_name ? $request->branch_name : '';

			$payout_preference->payout_method = 'Manual';

			$payout_preference->paypal_email = $user->email;

			$payout_preference->save(); //save Payout Details

			
		}

		if (strtolower($request->payout_method) == 'paypal') {

			$country = $request->country;

			$payout_preference 				  = new PayoutPreference;
			$payout_preference->user_id 	  = $user->id;
			$payout_preference->paypal_email  = $request->email;
			$payout_preference->address1 	  = $request->address1 != '' ? $request->address1 : '';
			$payout_preference->address2 	  = $request->address2 != '' ? $request->address2 : '';
			$payout_preference->city 		  = $request->city != ''	? $request->city : '';
			$payout_preference->state 		  = $request->state != '' ? $request->state : '';
			$payout_preference->postal_code   = $request->postal_code != '' ? $request->postal_code : '';
			$payout_preference->country 	  = $country;
			$payout_preference->currency_code = PAYPAL_CURRENCY_CODE;
			$payout_preference->payout_method = 'PayPal';

			$payout_preference->save();

			$payout_credentials 				= new PayoutCredentials;        
			$payout_credentials->user_id 		= $user_id;
			$payout_credentials->preference_id 	= $payout_preference->id;
			$payout_credentials->payout_id 		= $request->email;
			$payout_credentials->type 			= $request->payout_method;
			$payout_credentials->default 		= $payout_default_count->count() == 0 ? 'yes' : 'no';

			$payout_credentials->save();

		}


				return response()->json(
					[

						'status_message' => 'Payout Details Is Added Successfully',

						'status_code' => '1',

					]
				);
	}
	/**
	 * Add payout preference for Stripe --end--
	 */

	/**
	 * Display payout details
	 *
	 * @param  Get method request inputs
	 * @return Response in Json
	 */
	public function payout_details() {

		$payout_details = $this->get_payout_details();

		if (count($payout_details) == 0) {
			return response()->json(['status_message' => 'No Data Found', 'status_code' => '0']);
		}

		return response()->json(
			[

				'status_message' => 'PayoutPreference Details Listed Successfully',

				'status_code' => '1',

				'payout_details' => $payout_details,

			]
		);
	}

	public function get_payout_details() {

		$request = request();

		$user = JWTAuth::parseToken()->authenticate();
		
		//get payout preferences details

		$payout_details = @PayoutCredentials::where('user_id', $user->id)->get();

		$data = [];

		foreach ($payout_details as $payout_result) {
			$data[] = array(

				'payout_id' => $payout_result->id,

				'user_id' => $payout_result->user_id,

				'payout_method' => $payout_result->type != null

				? $payout_result->type : '',

				'paypal_email' => $payout_result->payout_id != null

				? $payout_result->payout_id : '',

				'set_default' => ucfirst($payout_result->default),

			);
		}

		return $data;
	}

	/**
	 * Payout Set Default and Delete
	 *
	 * @param  Get method request inputs
	 * @param  Type  Default   Set Default payout
	 * @param  Type  Delete    Delete payout Details
	 * @return Response in Json
	 */
	public function payout_changes(Request $request) {

		$request = request();
		$driver = JWTAuth::parseToken()->authenticate();

		$rules = array(

			'payout_id' => 'required|exists:payout_credentials,id',

			'type' => 'required',

		);

		$niceNames = array('payout_id' => 'Payout Id');

		$messages = array('required' => ':attribute is required.');

		$validator = Validator::make($request->all(), $rules, $messages);

		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {

			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}

			return response()->json(
				[

					'status_message' => $error_msg['0']['0']['0'],

					'status_code' => '0',

				]
			);
		}

		//check valid user or not
		$check_user = PayoutCredentials::where('id', $request->payout_id)

			->where('user_id', $driver->id)

			->first();

		if (count($check_user) < 1) {
			return response()->json(
				[

					'status_message' => 'Permission Denied',

					'status_code' => '0',

				]
			);
		}

		//check valid type or not
		if ($request->type != 'default' && $request->type != 'delete') {
			return response()->json(
				[

					'status_message' => 'The Selected Type Is Invalid',

					'status_code' => '0',

				]
			);
		}

		//set default payout
		if ($request->type == 'default') {
			$payout = PayoutCredentials::where('id', $request->payout_id)->first();

			if ($payout->default == 'yes') {
				return response()->json(
					[

						'status_message' => 'The Given Payout Id is Already Defaulted',

						'status_code' => '0']
				);
			} else {
				//Changed default option No in all Payout based on user id
				$payout_all = PayoutCredentials::where('user_id', $driver->id)->update(['default' => 'no']);

				$payout->default = 'yes';

				$payout->save(); //save payout detils

				$payout_details = $this->get_payout_details();

				return response()->json(
					[

						'status_message' => 'Payout Preferences is Successfully Selected Default',

						'status_code' => '1',

						'payout_details' => $payout_details,

					]
				);
			}
		}
		//Delete payout

		if ($request->type == 'delete') {
			
			$payout = PayoutCredentials::where('id', $request->payout_id)->first();

			if ($payout->default == 'yes') {
				return response()->json(
					[

						'status_message' => 'Permission Denied to Delete the Default Payout',

						'status_code' => '0',

					]
				);
			} else {

				$payout->delete(); //Delete payout.

				$payout_details = $this->get_payout_details();

				return response()->json(
					[

						'status_message' => 'Payout Details Deleted Successfully',

						'status_code' => '1',

						'payout_details' => $payout_details,

					]
				);
			}
		}
	}

	/**
	 *Display Country List
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */
	public function country_list(Request $request) {

		$data = Country::select(
			'id as country_id',
			'long_name as country_name',
			'short_name as country_code'
		)->get();

		return response()->json([

			'success_message' => 'Country Listed Successfully',

			'status_code' => '1',

			'country_list' => $data,

		]);

	}


	/**
	 *Display Country List
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */
	public function stripe_supported_country_list(Request $request) {

		$data = Country::select(
			'id as country_id',
			'long_name as country_name',
			'short_name as country_code'
		)->where('stripe_country','Yes')->get();

		$data = $data->map(function($data){
			return [

			'country_id' => $data->country_id,
			'country_name' => $data->country_name,
			'country_code' => $data->country_code,
			'currency_code'	=> $this->helper->getStripeCurrency($data->country_code),

			];
		});
		
		return response()->json([

			'success_message' => 'Country Listed Successfully',

			'status_code' => '1',

			'country_list' => $data,

		]);

	}

    /**
	 *Driver Bank Details if company is private
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */
	public function driver_bank_details(Request $request) {


		$user = JWTAuth::parseToken()->authenticate();

		if(!$request)
		{
                  $bank_detail = BankDetail::where('user_id',$user->id)->first();
                  if(isset($bank_detail))
                  {
                     $bank_detail = (object)[];
                  }
		}
		else
		{
		
			$rules = array(
        			'account_holder_name' => 'required',
	                'account_number' => 'required',
	                'bank_name' => 'required',
	                'bank_location' => 'required',
	                'bank_code' => 'required',
	            );

	            $niceNames = array(
                        'account_holder_name'  => trans('messages.account.holder_name'),
                        'account_number'  => trans('messages.account.account_number'),
                        'bank_name'  => trans('messages.account.bank_name'),
                        'bank_location'  => trans('messages.account.bank_location'),
                        'bank_code'  => trans('messages.account.bank_code'),
                );

        		$messages   = array('required'=> ':attribute '.trans('messages.home.field_is_required').'',);
                $validator = Validator::make($request->all(), $rules,$messages);

                $validator->setAttributeNames($niceNames); 

	            if ($validator->fails()) 
	            {
	                $error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
	            }
	            else
	            {
            		$bank_detail = BankDetail::where('user_id',$user->id)->first();
            		if($bank_detail==null){
	            		$bank_detail = new BankDetail;
            		}
                    $bank_detail->user_id = Auth::user()->id;
                    $bank_detail->holder_name = $request->account_holder_name;
                    $bank_detail->account_number = $request->account_number;
                    $bank_detail->bank_name = $request->bank_name;
                    $bank_detail->bank_location = $request->bank_location;
                    $bank_detail->code = $request->bank_code;
                    $bank_detail->save();
                }
		}
        		
                
						return response()->json([

							'status_message' => 'Listed Successfully',

							'status_code' => '1',

							'bank_detail' =>  $bank_detail,

						]);
            }
        	

}
