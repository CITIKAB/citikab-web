<?php

/**
 * Trip Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Trip
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\Cancel;
use App\Models\DriverLocation;
use App\Models\ScheduleRide;
use App\Models\Trips;
use App\Models\User;
use App\Models\Rating;
use App\Models\UsersPromoCode;
use Auth;
use DateTime;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class TripController extends Controller {

	protected $request_helper; // Global variable for Helpers instance

	public function __construct(RequestHelper $request) {
		$this->request_helper = $request;
		$this->helper = new Helpers;
	}

/**
 * Display the Arive Now Status
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function arive_now(Request $request) {

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
			$data = Trips::where('id', $request->trip_id)->first();

			Trips::where('id', $request->trip_id)->update(['status' => 'Begin trip']);

			$push_data = array('arrive_now' => array('trip_id' => $request->trip_id, 'status' => 'Arrive Now'));

			$user_type = $data->users->user_type;

			$device_id = $data->users->device_id;

			$push_tittle = "Driver Arrived";
			$schedule_ride = ScheduleRide::find($data->ride_request->schedule_id);
			if ($data->users->device_type != null && $data->users->device_type != '') {
				if ($data->users->device_type == 1) {

					$this->request_helper->push_notification_ios($push_tittle, $push_data, $user_type, $device_id);
				} else {
					$this->request_helper->push_notification_android($push_tittle, $push_data, $user_type, $device_id);
				}

			} 
			//if booking is manual booking then send "Driver Arrived" SMS to rider
			if (isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
				$to=$data->users->country_code.$data->users->mobile_number;
				$text = trans('messages.driver_arrive');
        		$this->request_helper->send_nexmo_message($to,$text);
			}	

			return response()->json([

				'status_message' => "Success",

				'status_code' => '1',

			]);

		}

	}

/**
 * Begin Trip From Driver
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function begin_trip(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(

			'trip_id' => 'required|exists:trips,id',
			'begin_latitude' => 'required',
			'begin_longitude' => 'required',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			$pickup_location = $this->request_helper->GetLocation($request->begin_latitude, $request->begin_longitude);

			$user_location = DriverLocation::where('user_id', $user_details->id)->first();
			//  get user default location
			$user_timezone = $this->getTimeZone($user_location->latitude, $user_location->longitude);
			if ($user_timezone != '') {
				date_default_timezone_set($user_timezone);
			}

			$begin_time = new DateTime(date("Y-m-d H:i:s"));

			Trips::where('id', $request->trip_id)->update(['status' => 'End trip', 'begin_trip' => $begin_time, 'pickup_latitude' => $request->begin_latitude, 'pickup_longitude' => $request->begin_longitude, 'pickup_location' => $pickup_location]);

			$trip = Trips::where('id', $request->trip_id)->first();

			$push_data = array('begin_trip' => array('trip_id' => $request->trip_id));

			$user_type = $trip->users->user_type;

			$device_id = $trip->users->device_id;

			$push_tittle = "Trip Began by Driver";
			$schedule_ride = ScheduleRide::find($trip->ride_request->schedule_id);
			if ($trip->users->device_type != null && $trip->users->device_type!='') {
				if ($trip->users->device_type == 1) {
					$this->request_helper->push_notification_ios($push_tittle, $push_data, $user_type, $device_id);
				} else {
					$this->request_helper->push_notification_android($push_tittle, $push_data, $user_type, $device_id);
				}
			} 
			//if booking is manual booking then send "Trip Began" SMS to rider
			if (isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
				$to=$trip->users->country_code.$trip->users->mobile_number;
				$text = trans('messages.trip_begined');
        		$this->request_helper->send_nexmo_message($to,$text);
			}	

			return response()->json([

				'status_message' => "Trip Started",

				'status_code' => '1',

			]);

		}
	}

	/**
	 * End Trip From Driver
	 *@param  Get method request inputs
	 *
	 * @return Response Json
	 */

	public function end_trip(Request $request) {

		$user_details = $user = JWTAuth::toUser(request()->token);

		$rules = array(

			'trip_id' => 'required|exists:trips,id',
			'end_latitude' => 'required',
			'end_longitude' => 'required',
			'image'			=> 'required',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			$trip = Trips::where('id', $request->trip_id)->first();

			// Final Distance calcualtion

			$driver_location = DriverLocation::where('user_id', $user_details->id)->first();
			//  get user default location
			$user_timezone = $this->getTimeZone($driver_location->latitude, $driver_location->longitude);
			if ($user_timezone != '') {
				date_default_timezone_set($user_timezone);
			}

			if ($request->trip_id) {

				$old_km = $trip->total_km;

				$old_latitude = $driver_location->latitude;

				$old_longitude = $driver_location->longitude;

				$earthRadius = 6371000;
				$latFrom = deg2rad($old_latitude);
				$lonFrom = deg2rad($old_longitude);
				$latTo = deg2rad($request->end_latitude);
				$lonTo = deg2rad($request->end_longitude);

				$latDelta = $latTo - $latFrom;
				$lonDelta = $lonTo - $lonFrom;

				$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

				$meter = number_format((($angle * $earthRadius)), 2);

				$km = (intval($meter) / 1000);

				$new_km = $old_km + $km;

				$data = [
					'user_id' => $user_details->id,

					'latitude' => $request->end_latitude,

					'longitude' => $request->end_longitude,

				];

				DriverLocation::updateOrCreate(['user_id' => $user_details->id], $data);

			}

			//Final Distance calcualtion

			$end_time = new DateTime(date("Y-m-d H:i:s"));

			$drop_location = $this->request_helper->GetLocation($request->end_latitude, $request->end_longitude);

			//check uploaded image is set or not

			if (isset($_FILES['image'])) {

				$errors = array();

				$acceptable = array(
					'image/jpeg',
					'image/jpg',
					'image/gif',
					'image/png',
				);

				if ((!in_array($_FILES['image']['type'], $acceptable)) && (!empty($_FILES["image"]["type"]))) {

					return response()->json([

						'status_message' => "Invalid file type. Only  JPG, GIF and PNG types are accepted.",

						'status_code' => "0",

					]);

				}

				$type = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

				$file_name = substr(md5(uniqid(rand(), true)), 0, 8) . ".$type";

				$trip_id = $request->trip_id;

				$file_tmp = $_FILES['image']['tmp_name'];

				$dir_name = dirname($_SERVER['SCRIPT_FILENAME']) . '/images/map/' . $trip_id;

				$f_name = dirname($_SERVER['SCRIPT_FILENAME']) . '/images/map/' . $trip_id . '/' . $file_name;

				//check file directory is created or not

				if (!file_exists($dir_name)) {
					//create file directory
					mkdir(dirname($_SERVER['SCRIPT_FILENAME']) . '/images/map/' . $trip_id, 0777, true);
				}
				//upload image from temp_file  to server file
				if (move_uploaded_file($file_tmp, $f_name)) {

				}

				//return file based on image size.

				$image_url = url('/') . '/images/map/' . $trip_id . '/' . $file_name;

			}

			//End check uploaded image is set or not
			$status = 'Payment';
			$schedule_ride = ScheduleRide::find($trip->ride_request->schedule_id);
			if (!isset($schedule_ride) || $schedule_ride->booking_type == 'Schedule Booking') {
				$status = 'Payment';
			}
		  		Trips::where('id', $request->trip_id)->update(['drop_latitude' => $request->end_latitude, 'drop_longitude' => $request->end_longitude, 'drop_location' => $drop_location, 'status' => $status, 'end_trip' => $end_time, 'total_km' => $new_km,'map_image' => @$file_name]);

			// push notification

			$driver_thumb_image = @$trip->driver_thumb_image != '' ? $trip->driver_thumb_image : url('images/user.jpeg');


			$push_data = array('end_trip' => array('trip_id' => $request->trip_id, 'driver_thumb_image' => $driver_thumb_image));


			$user_type = $trip->users->user_type;

			$device_id = $trip->users->device_id;

			//if booking is manual booking then send "Trip Began" SMS to rider
			$push_tittle = "Trip Ended by Driver.";

			// Start push notification
			if ($trip->users->device_type != null && $trip->users->device_type!= '') {
				if ($trip->users->device_type == 1) {

					$this->request_helper->push_notification_ios($push_tittle, $push_data, $user_type, $device_id);

				} else {

					$this->request_helper->push_notification_android($push_tittle, $push_data, $user_type, $device_id);

				}
			} 
			//if booking is manual booking then send "Trip Ended" SMS to rider
			if (isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
				$to=$trip->users->country_code.$trip->users->mobile_number;
				$text = trans('messages.trip_ended');
        		$this->request_helper->send_nexmo_message($to,$text);
			}	

			// End push notification

			DriverLocation::where('user_id', $user_details->id)->update(['status' => 'Online']);

			$driver = User::where('id', $trip->driver_id)->first();

			
			return response()->json([

				'status_message' => "Trip Completed",

				'status_code' => '1',

			    'image_url' => isset($image_url)?$image_url:'',

			]);

		}
	}

	/**
	 * Display the Rider Trips
	 * @param  Get method request inputs
	 *
	 * @return Response Json
	 */

	public function get_rider_trips(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'user_type' => 'required|in:Rider,rider',
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

				$schedule_ride = ScheduleRide::select('*')->where('user_id', $user_details->id)->where('status', 'Pending')->get();

				$collection = collect($schedule_ride);

				$schedule_ride = $collection->except(['created_at', 'updated_at',''])->all();

				$trips = Trips::select('id','user_id','pickup_latitude','pickup_longitude','pickup_location','drop_latitude','drop_longitude','drop_location','car_id','trip_path','request_id','driver_id','map_image','schedule_fare','peak_fare','peak_amount','subtotal_fare','begin_trip','end_trip','paykey','payment_status','is_calculation','currency_code','payment_mode as payment_method','status',DB::raw('"" as schedule_date,"" as schedule_time, "" as timezone, Convert(total_time,char) As total_time,Convert(total_km,char) As total_km,Convert(time_fare,char) As time_fare,Convert(distance_fare,char) As distance_fare, Convert(base_fare,char) As base_fare,Convert(total_fare,char) As total_fare,Convert(access_fee,char) As access_fee,Convert(driver_payout,char) As driver_payout,Convert(owe_amount,char) As owe_amount,Convert(applied_owe_amount,char) As applied_owe_amount,Convert(remaining_owe_amount,char) As remaining_owe_amount,Convert(wallet_amount,char) As wallet_amount,Convert(promo_amount,char) As promo_amount,"Trip" as source'),'created_at','updated_at')->where('user_id', $user_details->id)->union(DB::table('schedule_ride')->select('id','user_id','pickup_latitude','pickup_longitude','pickup_location','drop_latitude','drop_longitude','drop_location','car_id','trip_path',DB::raw('"" as request_id, "" as driver_id, "" as map_image, "" as schedule_fare, "0" as peak_fare,"0" as peak_amount,"0" as subtotal_fare,"" as begin_trip, "" as end_trip, "" as paykey, "" as payment_status,"" as total_time,"" as is_calculation, "USD" as currency_code '),'status','schedule_date','schedule_time','timezone',DB::raw('"" as payment_method,  "" as total_km, "0" as time_fare, "0","0","0","0","0","0","0","0","0","0","ScheduleRide"'),'created_at','updated_at')->where('user_id', $user_details->id)->where('status', 'Cancelled'))->orderBy('id', 'DESC')->get();


					
				$details = $this->common_map($trips,$request->user_type);


				return response()->json([

					'trip_details' => $details,

					'schedule_ride' => $schedule_ride,

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
	 * Display the Driver Trip History
	 *@param  Get method request inputs
	 *
	 * @return Response Json
	 */

	public function driver_trips_history(Request $request) {


		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'user_type' => 'required|in:Driver,driver',
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
				$current_date = date("Y-m-d");
				$trips = Trips::where('driver_id', $user_details->id)->orderBy('id','DESC')->get();

				$pending_trips = collect($trips)->whereNotIn('status',['Rating','Completed','Cancelled'])->values();		

				$past_trips = collect($trips)->whereIn('status',['Rating','Completed','Cancelled'])->values();

				$schedule = ScheduleRide::where('driver_id', $user_details->id)->where('status','Pending')->get();

				// merge projects,
				foreach($pending_trips as $mergeProject) {
				    $schedule->add($mergeProject);
				}
				
				if(count($schedule) > 0)
					$pending_trips = $this->common_map($schedule,$request->user_type);
				else
					$pending_trips = [];

				if(count($past_trips) > 0)
					$past_trips = $this->common_map($past_trips,$request->user_type);
				else
					$past_trips = [];


				return response()->json([

					'pending_trips' => @$pending_trips,

					'completed_trips' => @$past_trips,

					'status_message' => "Success",

					'status_code' => '1',

				]);

				return response()->json($user);

			} else {
				return response()->json([

					'status_message' => "Invalid credentials",

					'status_code' => '0',

				]);

			}

		}

	}


	/**
	 * Common function for trip history both rider and driver
	 */

	public function common_map($request,$type='') {


   return $details =  $request->map(function($item) use($type){

   $invoice = [];

	if(!isset($item->booking_type))
	{
			$symbol = html_entity_decode($item->currency->symbol);

			$total_trip_amount = number_format($item->subtotal_fare + $item->peak_amount + $item->access_fee + $item->schedule_fare,2,'.','');

			if($item->base_fare!=0)
			$invoice[] = array('key' => trans('messages.base_fare'), 'value' => $symbol . $item->base_fare,'bar'=>0,'colour'=>'');

			if($item->distance_fare!=0)
			$invoice[] = array('key' => trans('messages.distance_fare'), 'value' => $symbol  .(string) $item->distance_fare,'bar'=>0,'colour'=>'');

			if($item->time_fare!=0)
			$invoice[] = array('key' => trans('messages.time_fare'), 'value' => $symbol  .(string) $item->time_fare,'bar'=>0,'colour'=>'');

			if($item->schedule_fare!=0)
			$invoice[] = array('key' => trans('messages.schedule_fare'), 'value' => $symbol  .(string) $item->schedule_fare,'bar'=>0,'colour'=>'');

			if($item->peak_fare!=0)
			{
			$invoice[] = array('key' => trans('messages.normal_fare'), 'value' =>  $symbol .(string) $item->subtotal_fare,'bar'=>1,'colour'=>'black');
			if($type == 'rider')
			{

			$invoice[] = array('key' => trans('messages.peak_time_fare').'  x'.($item->peak_fare+0), 'value' =>  $symbol.(string) number_format($item->peak_amount,2),'bar'=>0,'colour'=>'');
			$invoice[] = array('key' => trans('messages.peak_subtotal_fare'), 'value' =>   $symbol.(string) number_format($item->peak_amount + $item->subtotal_fare,2),'bar'=>1,'colour'=>'black');
			}

			else
			{
				$invoice[] = array('key' => trans('messages.peak_time_fare').'  x'.($item->peak_fare+0), 'value' =>  $symbol.(string) $item->driver_peak_amount,'bar'=>0,'colour'=>'');

				$invoice[] = array('key' => trans('messages.peak_subtotal_fare'), 'value' =>   $symbol.(string) number_format($item->driver_peak_amount + $item->subtotal_fare,2),'bar'=>1,'colour'=>'black');

			}

			}

			if($item->access_fee!=0 && $type == 'rider')
			{
			$invoice[] = array('key' => trans('messages.access_fee'), 'value' => $symbol .$item->access_fee,'bar'=>0,'colour'=>'');

			$invoice[] = array('key' => trans('messages.total_trip_fare'), 'value' => $symbol.number_format($total_trip_amount,2),'bar'=>1,'colour'=>'black');
			}

			if($item->promo_amount!=0 && $type == 'rider')
			$invoice[] = array('key' => trans('messages.promo_amount'), 'value' => '-'.$symbol .$item->promo_amount,'bar'=>0,'colour'=>'');

			if($item->wallet_amount!=0 && $type == 'rider')
			$invoice[] = array('key' => trans('messages.wallet_amount'), 'value' =>'-'.$symbol .$item->wallet_amount,'bar'=>0,'colour'=>'');

			if($type == 'rider' && ( $item->promo_amount!=0 || $item->wallet_amount!=0 ))
			$invoice[] = array('key' => trans('messages.payable_amount'), 'value' => $symbol  .$item->total_fare,'bar'=>0,'colour'=>'green');


			if($item->owe_amount!=0 && $type != 'rider')
			{ 

			if($item->total_fare!=0)
			$invoice[] = array('key' => trans('messages.cash_collected'), 'value' => $symbol  .$item->total_fare,'bar'=>0,'colour'=>'green');
             if($item->driver->company_id == 1)
			$invoice[] = array('key' => trans('messages.owe_amount') , 'value' => '-'.$symbol  .$item->owe_amount ,'bar'=>0,'colour'=>'');	

			}

			if($item->applied_owe_amount!=0 && $type != 'rider' && $item->driver->company_id == 1)
			$invoice[] = array('key' => trans('messages.applied_owe_amount'), 'value' => '-'.$symbol .$item->applied_owe_amount,'bar'=>0,'colour'=>'');

			if($type != 'rider' && $item->driver->company_id == 1 )    
			$invoice[] = array('key' => trans('messages.driver_payout'), 'value' => $symbol  .$item->driver_payout,'bar'=>0,'colour'=>'');

			$payment_mode  = isset($item->payment_mode) ? $item->payment_mode : $item->payment_method;
			$subtotal_fare = ($payment_mode == 'Cash' || $payment_mode == 'Cash & Wallet') ? $item->total_fare : $item->subtotal_fare;

			return array( 
	                "id" => $item->id,
	                "trip_id" => $item->id,
					"user_id" =>$item->user_id,
					"pickup_latitude" => $item->pickup_latitude,
					"pickup_longitude" => $item->pickup_longitude,
					"drop_latitude" => $item->drop_latitude,
					"drop_longitude" => $item->drop_longitude,
					"pickup_location" => $item->pickup_location,
					"drop_location" => $item->drop_location,
					"car_id" => $item->car_id,
					"driver_id" =>$item->driver_id,
					"driver_name" =>($item->driver_id)?$item->driver_name:'',
					"vehicle_name" =>($item->driver_id)?$item->vehicle_name:'',
					"trip_path" =>$item->trip_path,
					"map_image" =>$item->map_image,
					"total_time" =>$item->total_time,
					"begin_trip" =>$item->begin_trip,
					"end_trip" =>$item->end_trip,
					"payment_mode" => $payment_mode,
					"paypal_mode" => PAYPAL_MODE,
					'paypal_app_id' => PAYPAL_CLIENT_ID,								
					"payment_status" => $item->payment_status,  
					"sub_total_fare" => $symbol.$subtotal_fare,
					"created_at" =>($item->driver_id)?$item->created_at->format('Y-m-d H:i:s'):'' ,
					"updated_at" =>($item->driver_id)?$item->updated_at->format('Y-m-d H:i:s'):'',    
					"currency_code" =>$item->currency_code,
					"status" =>$item->status,
					"payment_method" => $payment_mode,
					'total_fare' => $item->total_fare ,
					'driver_thumb_image' => ($item->driver_id)?($item->driver->profile_picture->src != '' ?  $item->driver->profile_picture->src : url('images/user.jpeg')):'',
					'driver_payout' => $item->driver_payout ,
					'total_km' => $item->total_km ,
					'source' => $item->source,
					'booking_type' =>'',
					'rider_name' =>$item->rider_name,
			        'rider_thumb_image' => $item->rider_profile_picture,
					'invoice' => $invoice
				);
			}
			else
			{


					return array(

						"id" => $item->id,
						"trip_id" => $item->id,
						"user_id" =>$item->user_id,
						"pickup_latitude" => $item->pickup_latitude,
						"pickup_longitude" => $item->pickup_longitude,
						"drop_latitude" => $item->drop_latitude,
						"drop_longitude" => $item->drop_longitude,
						"pickup_location" => $item->pickup_location,
						"drop_location" => $item->drop_location,
						"car_id" => $item->car_id,
						"driver_id" =>$item->driver_id,
						"driver_name" =>($item->driver_id)? $item->driver->first_name:'',
						"trip_path" =>$item->trip_path,
						"schedule_time" =>$item->schedule_time,
						"schedule_date" =>$item->schedule_date,
						"status" =>$item->status,
						"payment_method" =>$item->payment_method,
						'booking_type' =>$item->booking_type,
						'rider_name' =>$item->rider_name,
						'rider_thumb_image' => $item->rider_thumb_image,
						'driver_thumb_image' => ($item->driver_id)?($item->driver->profile_picture->src != '' ?  $item->driver->profile_picture->src : url('images/user.jpeg')):'',
					);
			}


						 

					});

	}


	/**
	 * Map Image upload
	 *@param  Post method request inputs
	 *
	 * @return Response Json
	 */

	public function map_upload(Request $request) {

		$this->helper = new Helpers;

		$rules = array(

			'trip_id' => 'required|exists:trips,id',
			'image' => 'required',
			'token' => 'required',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			$user = JWTAuth::toUser($_POST['token']);

			$user_id = $user->id;
			//check uploaded image is set or not
			if (isset($_FILES['image'])) {

				$errors = array();

				$acceptable = array(
					'image/jpeg',
					'image/jpg',
					'image/gif',
					'image/png',
				);

				if ((!in_array($_FILES['image']['type'], $acceptable)) && (!empty($_FILES["image"]["type"]))) {

					return response()->json([

						'status_message' => "Invalid file type. Only  JPG, GIF and PNG types are accepted.",

						'status_code' => "0",

					]);

				}

				$type = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

				$file_name = substr(md5(uniqid(rand(), true)), 0, 8) . ".$type";

				$trip_id = $request->trip_id;

				//$file_name = time().'_'.str_replace(" ", "", $_FILES['image']['name']);

				$file_tmp = $_FILES['image']['tmp_name'];

				$dir_name = dirname($_SERVER['SCRIPT_FILENAME']) . '/images/map/' . $trip_id;

				$f_name = dirname($_SERVER['SCRIPT_FILENAME']) . '/images/map/' . $trip_id . '/' . $file_name;
				//check file directory is created or not

				if (!file_exists($dir_name)) {
					//create file directory
					mkdir(dirname($_SERVER['SCRIPT_FILENAME']) . '/images/map/' . $trip_id, 0777, true);
				}
				//upload image from temp_file  to server file
				if (move_uploaded_file($file_tmp, $f_name)) {

					//change compress image in 225*225
					// $li=$this->helper->compress_image("images/map/".$trip_id."/".$file_name, "images/map/".$trip_id."/".$file_name, 80, 225, 225);

				}

				//return file based on image size.

				Trips::where('id', $request->trip_id)->update(['map_image' => @$file_name]);

				$image_url = url('/') . '/images/map/' . $trip_id . '/' . $file_name;

				return response()->json([

					'status_message' => "Upload Successfully",

					'status_code' => "1",

					'image_url' => $image_url,

				]);
			}
		}

	}
	/**
	 * Trip Cancel by Driver or Rider
	 *@param  Get method request inputs
	 *
	 * @return Response Json
	 */

	public function cancel_trip(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'user_type' => 'required|in:Rider,rider,Driver,driver',
			'trip_id' => 'required',
			'cancel_reason' => 'required',
		);

		$messages = array(
			'user_type.required' => trans('messages.required.user_type') . ' ' . trans('messages.field_is_required') . '',
			'trip_id.required' => trans('messages.required.trip_id') . ' ' . trans('messages.field_is_required') . '',
			'cancel_reason.required' => trans('messages.required.cancel_reason') . ' ' . trans('messages.field_is_required') . '',
		);

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			$user = User::where('id', $user_details->id)->where('user_type', $request->user_type)->first();

			if (count($user)) {
				$cancelled_id = Trips::where('id', $request->trip_id)->first();

				if ($request->user_type == 'Rider' || $request->user_type == 'rider') {

					$data = [
						'trip_id' => $request->trip_id,
						'user_id' => $user_details->id,
						'cancel_reason' => $request->cancel_reason,
						'cancel_comments' => @$request->cancel_comments != '' ? $request->cancel_comments : '',
						'cancelled_by' => 'Rider',

					];

					Cancel::updateOrCreate(['trip_id' => $request->trip_id], $data);

					$driver_id = $cancelled_id->driver_id;

					$rider = User::where('id', $driver_id)->first();

					$device_id = $rider->device_id;

					$device_type = $rider->device_type;

					$user_type = $rider->user_type;

					$push_tittle = "Trip Cancelled by Rider";

				} else {

					$data = [
						'trip_id' => $request->trip_id,
						'user_id' => $user_details->id,
						'cancel_reason' => $request->cancel_reason,
						'cancel_comments' => @$request->cancel_comments != '' ? $request->cancel_comments : '',
						'cancelled_by' => 'Driver',

					];

					Cancel::updateOrCreate(['trip_id' => $request->trip_id], $data);

					$user_id = $cancelled_id->user_id;

					$driver_id = $cancelled_id->driver_id;

					$driver = User::where('id', $user_id)->first();

					$device_id = $driver->device_id;

					$device_type = $driver->device_type;

					$user_type = $driver->user_type;

					$push_tittle = "Trip Cancelled by Driver";

				}

				Trips::where('id', $request->trip_id)->update(['status' => 'Cancelled', 'payment_status' => 'Trip Cancelled']);

				DriverLocation::where('user_id', $cancelled_id->driver_id)->update(['status' => 'Online']);

				// push notification
				$push_data = array('cancel_trip' => array('trip_id' => $request->trip_id, 'status' => 'Cancelled'));

				if ($device_type == 1) {
					$this->request_helper->push_notification_ios($push_tittle, $push_data, $user_type, $device_id);
				} else {
					$this->request_helper->push_notification_android($push_tittle, $push_data, $user_type, $device_id);
				}
				// push notification

				return response()->json([

					'status_message' => "Success",

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
	//get incomplete trips
	public function incomplete_trips() {

		$user_details = JWTAuth::parseToken()->authenticate();
		
		if($user_details->user_type=='Rider'){
		//get incompleted, first get on trips 
			$incomplete_trips = Trips::where('user_id',$user_details->id)->whereNotIn('status',['Completed','Cancelled'])->orderBy('id','desc')->first();
			
		}
		else {
			//get incompleted, first get on trips 
			$incomplete_trips = Trips::where('driver_id',$user_details->id)->whereNotIn('status',['Completed','Cancelled'])->orderBy('id','desc')->first();
			
		}
		if(@$incomplete_trips)
			return $this->get_trip_detail($user_details->id,$incomplete_trips->id);
		else{
			return response()->json([

					'status_message' => "No trips found",

					'status_code' => '0',

				]);
		}
		
	}

	public function get_trip_detail($user_id,$trip_id){


		$user = User::where('id', $user_id)->first();

		if (count($user)) {

			$trip = Trips::where('id', $trip_id)->first();

			$driver = User::where('id', $trip->driver_id)->first();

			$total_rating = DB::table('rating')->select(DB::raw('sum(rider_rating) as rating'))
				->where('driver_id', $trip->driver_id)->where('rider_rating', '>', 0)->first()->rating;

			$total_rating_count = Rating::where('driver_id', $trip->driver_id)->where('rider_rating', '>', 0)->get()->count();

			if ($total_rating_count != 0) {
				$driver_rating = (string) round(($total_rating / $total_rating_count), 2);
			} else {
				$driver_rating = '0.0';
			}

			$users_promo_codes = UsersPromoCode::whereUserId($user->id)->whereTripId(0)->with('promo_code')->get();

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

			$get_min_time = $this->request_helper->GetDrivingDistance($trip->pickup_latitude, $trip->drop_latitude, $trip->pickup_longitude, $trip->drop_longitude);
			if ($get_min_time['status'] == "success") {
				$get_near_car_time = round(floor(round($get_min_time['time'] / 60)));

				$symbol = html_entity_decode( $trip->currency->symbol);

				$total_trip_amount = $trip->base_fare + $trip->distance_fare + $trip->time_fare + $trip->schedule_fare + $trip->access_fee;


				if($trip->base_fare!=0)
					$invoice[]=array('key' => trans('messages.base_fare'), 'value' => $symbol . $trip->base_fare );
				if($trip->time_fare!=0)
					$invoice[]=array('key' => trans('messages.time_fare'), 'value' => $symbol  .(string) $trip->time_fare);
			   	if($trip->distance_fare!=0)
					$invoice[] = array('key' =>  trans('messages.distance_fare'), 'value' => $symbol  .(string) $trip->distance_fare);
				if($trip->access_fee!=0)
					$invoice[]=array('key' => trans('messages.access_fee') , 'value' => $symbol .$trip->access_fee );
				if($trip->schedule_fare!=0)
					$invoice[]=array('key' => trans('messages.schedule_fare') , 'value' =>  $symbol . $trip->schedule_fare );

				$invoice[] = array('key' => trans('messages.total_trip_fare'), 'value' => $symbol .$total_trip_amount);

				if($trip->promo_amount!=0)
					$invoice[]=array('key' => trans('messages.promo_amount') , 'value' =>  $symbol . $trip->promo_amount );
				if($trip->wallet_amount!=0)
					$invoice[]=array('key' => trans('messages.wallet_amount') , 'value' => $symbol . $trip->wallet_amount);

				if( $trip->promo_amount!=0 || $trip->wallet_amount!=0 )
					$invoice[] = array('key' => trans('messages.payable_amount'), 'value' => $symbol  .$trip->total_fare );

				if($user->user_type=='Rider'){

					$payment_details = [

						'currency_code' => @$trip->currency_code != '' ? $trip->currency_code : '',

						'total_time' => @$trip->total_time != '' ? $trip->total_time : '0.00',

						'pickup_location' => @$trip->pickup_location != '' ? $trip->pickup_location : '0.00',

						'drop_location' => @$trip->drop_location != '' ? $trip->drop_location : '0.00',

						'driver_payout' => @$trip->driver_payout != '' ? $trip->driver_payout : '0.00',

						'payment_method' => @$trip->payment_mode != '' ? $trip->payment_mode : '',

						'owe_amount' => @$trip->owe_amount != '' ? $trip->owe_amount : '0.00',

						'applied_owe_amount' => @$trip->applied_owe_amount != '' ? $trip->applied_owe_amount : '0.00',

						'remaining_owe_amount' => @$trip->remaining_owe_amount != '' ? $trip->remaining_owe_amount : '0.00',

						'trip_status' => $trip->status,

						'admin_paypal_id' => PAYPAL_ID,

						'paypal_mode' => PAYPAL_MODE,

						'paypal_app_id' => PAYPAL_CLIENT_ID,

						'driver_paypal_id' => $trip->driver->payout_id,

				        'total_fare' => $trip->total_fare ,


					];

					$user = array(

						'status_message' => 'Success',

						'status_code' => '1',

						'trip_id' => $trip_id,

						'driver_name' => $driver->first_name,

						'mobile_number' => '+' . $driver->country_code . $driver->mobile_number,

						'driver_thumb_image' => @$driver->profile_picture->src != '' ? $driver->profile_picture->src : url('images/user.jpeg'),

						'rating_value' => @$driver_rating != '' ? $driver_rating : '0.0',

						'pickup_latitude' => $trip->pickup_latitude,

						'pickup_longitude' => $trip->pickup_longitude,

						'drop_latitude' => $trip->drop_latitude,

						'drop_longitude' => $trip->drop_longitude,

						'car_type' => $trip->car_type->car_name,

						'pickup_location' => $trip->pickup_location,

						'drop_location' => $trip->drop_location,

						'driver_latitude' => $driver->driver_location->latitude,

						'driver_longitude' => $driver->driver_location->longitude,

						'vehicle_number' => @$driver->driver_documents->vehicle_number != '' ? $driver->driver_documents->vehicle_number : '',

						'vehicle_name' => @$driver->driver_documents->vehicle_name != '' ? $driver->driver_documents->vehicle_name : '',

						'arrival_time' => $get_near_car_time,

						'trip_status' => @$trip->status != '' ? $trip->status : '',

						'payment_details' => @$payment_details != '' ? $payment_details : [''],

						'invoice' => $invoice ,

						'promo_details' => $final_promo_details,

						'is_verified'	=> $driver->is_verified,

						'booking_type' => (@$trip->ride_request->schedule_ride->booking_type==null)?"":@$trip->ride_request->schedule_ride->booking_type,

					);
				}
				else {
					
					$payment_details = [

						'currency_code' => @$trip->currency_code != '' ? $trip->currency_code : '',

						'total_time' => @$trip->total_time != '' ? $trip->total_time : '0.00',

						'pickup_location' => @$trip->pickup_location != '' ? $trip->pickup_location : '0.00',

						'drop_location' => @$trip->drop_location != '' ? $trip->drop_location : '0.00',

						'owe_amount' => @$trip->owe_amount != '' ? $trip->owe_amount : '0.00',

						'applied_owe_amount' => @$trip->applied_owe_amount != '' ? $trip->applied_owe_amount : '0.00',

						'remaining_owe_amount' => @$trip->remaining_owe_amount != '' ? $trip->remaining_owe_amount : '0.00',

						'trip_status' => $trip->status,

						'payment_status' => $trip->payment_status,

						'payment_method' => $trip->payment_mode,

						'driver_payout' => $trip->driver_payout,

						'total_fare' => $trip->total_fare ,

						
					];

					$user = [

						'status_message' => trans('messages.success'),

						'status_code' => '1',

						'trip_id' => $trip->id,

						'rider_name' => $trip->users->first_name,
						'payment_method' => @$trip->payment_mode,
						'mobile_number' => '+' . $trip->users->country_code . $trip->users->mobile_number,

						'rider_thumb_image' => @$trip->profile_picture->src != '' ? $trip->profile_picture->src : url('images/user.jpeg'),

						'rating_value' => '',

						'car_type' => $trip->car_type->car_name,

						'pickup_location' => $trip->pickup_location,

						'drop_location' => $trip->drop_location,

						'pickup_latitude' => $trip->pickup_latitude,

						'pickup_longitude' => $trip->pickup_longitude,

						'drop_latitude' => $trip->drop_latitude,

						'drop_longitude' => $trip->drop_longitude,

						'trip_status' => $trip->status,

						'is_verified' => $trip->users->is_verified,

						'payment_details' => @$payment_details != '' ? $payment_details : [''],

						'invoice'    => $invoice,

						'incomplete_trip_id'    => @$incomplete_trips->id?$incomplete_trips->id:'',
						
						'incomplete_trip_status'    => @$incomplete_trips->status?$incomplete_trips->status:'',

						'booking_type' => (@$trip->ride_request->schedule_ride->booking_type==null)?"":@$trip->ride_request->schedule_ride->booking_type,

					];
				}
				if(@$user)
					return response()->json($user);
				else {
					return response()->json([

						'status_message' => $get_min_time['msg'],

						'status_code' => '0',
					]);

				}
			} else {
				return response()->json([

					'status_message' => "Invalid credentials",

					'status_code' => '0',

				]);

			}
	
		} 

	}

}
