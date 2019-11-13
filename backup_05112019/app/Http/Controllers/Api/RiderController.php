<?php

/**
 * Rider Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Rider
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\Admin;
use App\Models\CarType;
use App\Models\DriverLocation;
use App\Models\EmergencySos;
use App\Models\Rating;
use App\Models\Request as RideRequest;
use App\Models\RiderLocation;
use App\Models\ScheduleRide;
use App\Models\Trips;
use App\Models\User;
use App\Models\UsersPromoCode;
use App\Models\PaymentMethod;
use App\Models\PeakFareDetail;
use App\Models\Location;
use App\Models\ManageFare;
use Auth;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class RiderController extends Controller {

	protected $request_helper; // Global variable for Helpers instance

	public function __construct(RequestHelper $request) {
		$this->request_helper = $request;
		$this->helper = new Helpers;
	}

/**
 * Display the  Nearest car details
 *@param  Get method request inputs
 *
 * @return Response Json
 */
	public function call_again(Request $request) {
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=9.9297408,78.1393286&destination=9.925981,78.211751&mode=driving&units=metric&sensor=true&&language=pl-PL&key=" . MAP_KEY;

		$geocode = @file_get_contents($url);
		$response_a = json_decode($geocode);
		echo "<pre>";
		print_r($response_a);exit;
		$dist_find = $response_a->rows[0]->elements[0]->distance->value;
		$time_find = $response_a->rows[0]->elements[0]->duration->value;

		$dist = @$dist_find != '' ? $dist_find : '';
		$time = @$time_find != '' ? $time_find : '';

		return array('distance' => $dist, 'time' => $time);
	}
	public function check_push() {
		$this->request_helper->check_push_ios();
	}
	/*
	*  rider request to search car	
	*/
	public function search_cars(Request $request) {

		
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'pickup_latitude' => 'required',
			'pickup_longitude' => 'required',
			'drop_latitude' => 'required',
			'drop_longitude' => 'required',
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

			
				if(@$request->timezone)
				{
					date_default_timezone_set($request->timezone);
					$current_time = date('H:i:00');

				}
					$current_time = date('H:i:00');
					$day = date('N');



				if(isset($request->schedule_date)!='')
				{
					$day = date('N',strtotime($request->schedule_date));
					$current_time = $request->schedule_time.':00';
				}
				

			// Find pickup location country
			$pickup_country = $this->request_helper->GetCountry($request->pickup_latitude, $request->pickup_longitude);

			// Find drop location country
			$drop_country = $this->request_helper->GetCountry($request->drop_latitude, $request->drop_longitude);



			if ($pickup_country == $drop_country) {

				$fare_estimation = 0;
				$get_near_car_time = 0;


				// Find location from pickup latitude & longitude
				$match_location = Location::select(DB::raw("id,status,(ST_WITHIN( GeomFromText(
						'POINT(".$request->pickup_latitude.' '.$request->pickup_longitude.")'),ST_GeomFromText(coordinates))) as available "))->having('available','1')->where('status','Active')->first();

				if(!$match_location)
				{
                    return response()->json([

										'status_message' => trans('messages.location_unavailable'),

										'status_code' => '0',
									]);
				}

			$location_cars =	ManageFare::where('location_id',$match_location->id)->get()->toArray();

            $vehicles =   array_column($location_cars,'vehicle_id');
            $location_id =  $match_location->id;

			// Find nearest cars in location
			$nearest_car = DriverLocation::select(DB::raw('*, ( 6371 * acos( cos( radians(' . $request->pickup_latitude . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $request->pickup_longitude . ') ) + sin( radians(' . $request->pickup_latitude . ') ) * sin( radians( latitude ) ) ) ) as distance'))->having('distance', '<=', Driver_Km)->where('driver_location.status', 'Online')->with(['car_type' =>function($q) use($location_id) 
				{ 
					$q->with(['manage_fare'  => function($q) use($location_id)
						{ $q->where('location_id',$location_id); }]);
			     }, 'users'])
				->whereHas('users', function ($q2) use($vehicles,$location_id) {
					$q2->where('status', 'Active')
						->whereHas('vehicle',function($q3){
                            $q3->where('status', 'Active');
                        })
                        ->whereHas('company',function($q3){
                            $q3->where('status', 'Active');
                        });
				})->whereHas('car_type', function ($q2) use($vehicles,$location_id) {
				$q2->where('status', 'Active')->whereIn('car_id',$vehicles);

			})->orderBy('distance', 'ASC')->get();

               

			$nearest_car = collect($nearest_car)->groupBy('car_id')->values();

			$get_fare_estimation = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);

			$minutes = round(floor(round($get_fare_estimation['time'] / 60)));
							$km = round(floor($get_fare_estimation['distance'] / 1000) . '.' . floor($get_fare_estimation['distance'] % 1000));


				if (isset($nearest_car) && !$nearest_car->isEmpty()) {


					if ($get_fare_estimation['status'] == "success") {

	                      /* Start Peak Price */
						 	$data = ManageFare::
						 		with(['peak_fare' => function ($query)use($day,$current_time) {
	                                $query->where(function($q) use($day) {
	                                    $q->where('day', $day)->orWhere('day', null);
	                                })
	                                ->where('start_time','<=',$current_time)
	                                ->where('end_time','>=',$current_time);
	                            }])
							 	->whereHas('peak_fare',function($q)use($day,$current_time) {
								 	$q->where(function($q) use($day) {
										$q->where('day', $day)->orWhere('day', null);
									})
	                            	->where('start_time','<=',$current_time)
	                            	->where('end_time','>=',$current_time);
							 	})
							 	->where('location_id',$location_id)
							 	->groupBy('vehicle_id')
							 	->get();


							$fare_details= [];

							if($data)
							{
	                            foreach($data as $fare) {
									$fare_details[$fare->vehicle_id]= array('id' =>$fare->peak_fare[0]->id ,'car_id' => $fare->vehicle_id ,'price' => $fare->peak_fare[0]->price,'type' => $fare->peak_fare[0]->type);
								}
							}
							
	                            /* End Peak Price */

							
							$location = [];
							$i = 0;
							foreach ($nearest_car as $key => $list_car) {

								$location = $list_car->map(function ($item) use ($km, $minutes) {

									return array('latitude' => $item->latitude, 'longitude' => $item->longitude);

								})->toArray();

								if (count($location) > 0) {

									$get_min_time = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $location[0]['latitude'], $request->pickup_longitude,
									$location[0]['longitude']);

									$base_fare = round($list_car[$i]->car_type->manage_fare->base_fare + ($list_car[$i]->car_type->manage_fare->per_km * $km));
									$fare_estimation = number_format(($base_fare + round($list_car[$i]->car_type->manage_fare->per_min * $minutes)), 2, '.', '');

									if($fare_estimation < $list_car[$i]->car_type->manage_fare->min_fare)
									{     
	                                     $fare_estimation = $list_car[$i]->car_type->manage_fare->min_fare;
									}

									$get_near_car_time = round(floor(round($get_min_time['time'] / 60)));

									if ($get_min_time['status'] == "success") {

									$get_near_car_time = round(floor(round($get_min_time['time'] / 60)));

									if ($get_near_car_time == 0) {

									$get_near_car_time = 1;

									}

									} else {

										return response()->json([

											'status_message' => $get_min_time['msg'],

											'status_code' => '0',
										]);

									}
								}
								
	                         $car_s[]  = array('car_id' => $list_car[$i]->car_id);

	                         $peak_price = 0;
	                         $apply_peak = "No";
	                         $peak_id =0;

	                         if(!empty($fare_details))
	                         {

		                         if(array_key_exists($list_car[$i]->car_id,$fare_details))
		                         { 
		                         	$peak_price = $fare_details[$list_car[$i]->car_id]['price'];
		                         	$peak_id = $fare_details[$list_car[$i]->car_id]['id'];
		                         	$apply_peak = "Yes";
		                         	$fare_estimation = $fare_estimation * $peak_price;

		                         }
		                         // else
		                         // { 
		                         // 	if(array_key_exists(0,$fare_details))
		                         // 	{
		                         // 		$peak_price = $fare_details[0]['price'];
		                         // 		$peak_id = $fare_details[0]['id'];
		                         // 	    $apply_peak = "Yes";
		                         // 	    $fare_estimation = $fare_estimation * $peak_price;
		                         // 	}
		                         	
		                         // }

	                          }

							 $car_array[$list_car[$i]->car_id] = 
								[ 
								    'car_id' => $list_car[$i]->car_id,
									'car_name' => $list_car[$i]->car_type->car_name,
									'driver_id' => $list_car[$i]->user_id,
									'capacity' => $list_car[$i]->car_type->manage_fare->capacity,
									'base_fare' => $list_car[$i]->car_type->manage_fare->base_fare,
									'per_min' => $list_car[$i]->car_type->manage_fare->per_min,
									'per_km' => $list_car[$i]->car_type->manage_fare->per_km,
									'min_fare' => $list_car[$i]->car_type->manage_fare->min_fare,
									'schedule_fare' => $list_car[$i]->car_type->manage_fare->schedule_fare,
									'schedule_cancel_fare' => $list_car[$i]->car_type->manage_fare->schedule_cancel_fare,
									'location' => $location,
									'fare_estimation' => (string) $fare_estimation ,
									'min_time' => (string) $get_near_car_time,
									'apply_peak' =>  $apply_peak,
									'peak_price' =>  $peak_price,
									'location_id' => $location_id,
									'peak_id' =>  $peak_id,
									'car_image' =>  $list_car[$i]->car_type->vehicle_image,
									'car_active_image' =>$list_car[$i]->car_type->active_image,
									

								 ];

							}											
					}
					else
					{
	                      return response()->json([

							'status_message' => $get_fare_estimation['msg'],

							'status_code' => '0',

						]);

					}
					

				} 


	$cars = CarType::with(['manage_fare' =>function($q) use($location_id){ $q->where('location_id',$location_id);}])->whereIn('id',$vehicles)->where('status', 'Active');

		if(isset($car_s))
		{
			$car_id = array_column($car_s, 'car_id');
			$cars= $cars ->whereNotIn('id', $car_id)->get();
		}
		else
			$cars = $cars->get();

						foreach ($cars as $key => $value) {

								$base_fare = round($value->manage_fare->base_fare + ($value->manage_fare->per_km * $km));
								$fare_estimation = number_format(($base_fare + round($value->manage_fare->per_min * $minutes)), 2, '.', '');

								if($fare_estimation < $value->manage_fare->min_fare)
								{     
                                     $fare_estimation = $value->manage_fare->min_fare;
								}


							$car_array[$value->id] = [

								'car_id' => $value->id,
								'car_name' => $value->car_name,
								'driver_id' => 0,
								'capacity' => $value->manage_fare->capacity,
								'base_fare' =>  $value->manage_fare->base_fare,
								'per_min' =>  $value->manage_fare->per_min,
								'per_km' =>   $value->manage_fare->per_km,
								'min_fare' =>  $value->manage_fare->min_fare,
								'schedule_fare' =>  $value->manage_fare->schedule_fare,
								'schedule_cancel_fare' =>  $value->manage_fare->schedule_cancel_fare,
								'location' => [],
								'fare_estimation' => $fare_estimation,
								'min_time' => 'No cabs',
								"apply_peak" => "No",
                                "peak_price" => 0,
                                'location_id' => $location_id,
								'peak_id' =>  0,								
							    'car_image' =>  $value->vehicle_image,
								'car_active_image' => $value->active_image,

							];

						}

						if(!isset($car_array)) {
							return response()->json([
								'status_message' => trans('messages.no_cars_found'),
								'status_code' => '0',
							]);
						}

						return response()->json([

							'nearest_car' =>$car_array,

							'status_message' => trans('messages.cars_found'),

							'status_code' => '1',
						]);


			}
			return response()->json([

						'status_message' => trans('messages.location_country'),

						'status_code' => '0',

					]);

		}

	}


	/**
	 * Update Location of Rider
	 *
	 * @param Get method request inputs
	 * @return @return Response in Json
	 */

	public function updateriderlocation(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'latitude' => 'required',
			'longitude' => 'required',
		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			$user_check = User::where('id', $user_details->id)->first();

			if (count($user_check)) {

				$data = [
					'user_id' => $user_details->id,
					'latitude' => $request->latitude,
					'longitude' => $request->longitude,

				];

				RiderLocation::updateOrCreate(['user_id' => $user_details->id], $data);

				return response()->json([

					'status_message' => 'Updated Successfully',

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
 * Ride Request from Rider
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function request_cars(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rider_id = $user_details->id;

		if ($request->request_id) {
			$rules = array(
				'status' => 'required|in:Cancelled,cancelled',
			);
		} else {
			$rules = array(
				'pickup_latitude' => 'required',
				'pickup_longitude' => 'required',
				'drop_latitude' => 'required',
				'drop_longitude' => 'required',
				'user_type' => 'required|in:Rider,rider',
				'car_id' => 'required|exists:car_type,id',
				'pickup_location' => 'required',
				'drop_location' => 'required',
				'device_id' => 'required',
				'device_type' => 'required',
				'payment_method' => 'required',

			);
			$group_id = '';
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

				$additional_fare = "";
				$peak_price = 0;

				if(isset($request->peak_id)!='')
				{
                     $fare = PeakFareDetail::find($request->peak_id);

                     if($fare)
                     {
             	       $peak_price = $fare->price; 
                       $additional_fare = "Peak";
                     }
               
				}


			if ($request->request_id) 
			{
				
				RideRequest::where('id', $request->request_id)->update(['status' => $request->status]);

				$rider = RideRequest::where('id', $request->request_id)->first();

				$data = [ 'rider_id' => $rider->user_id,
				 'pickup_latitude' => $rider->pickup_latitude,
				 'pickup_longitude' => $rider->pickup_longitude,
				 'drop_latitude' => $rider->drop_latitude,
				 'drop_longitude' => $rider->drop_longitude,
				 'user_type' => $rider->user_type,
				 'car_id' => $rider->car_id,
				 'driver_group_id' => $rider->group_id,
				 'pickup_location' => $rider->pickup_location,
				 'drop_location' => $rider->drop_location,
				 'payment_method' => $rider->payment_method,
				 'is_wallet' => $rider->is_wallet,
				 'timezone' => $rider->timezone,
				 'schedule_id' => $rider->schedule_id,
				 'additional_fare'  =>$additional_fare,
				 'location_id' => $rider->location_id,
				 'peak_price'  => $peak_price ];

				$car_details = $this->request_helper->find_driver($data);

				return $car_details;

			} else {

				User::whereId($rider_id)->update(['device_id' => $request->device_id, 'device_type' => $request->device_type]);


				$data = [ 'rider_id' => $rider_id,
				 'pickup_latitude' => $request->pickup_latitude,
				 'pickup_longitude' => $request->pickup_longitude,
				 'drop_latitude' => $request->drop_latitude,
				 'drop_longitude' => $request->drop_longitude,
				 'user_type' => $request->user_type,
				 'car_id' => $request->car_id,
				 'driver_group_id' => $request->group_id,
				 'pickup_location' => $request->pickup_location,
				 'drop_location' => $request->drop_location,
				 'payment_method' => $request->payment_method,
				 'is_wallet' => $request->is_wallet,
				 'timezone' => $request->timezone,
				 'schedule_id' => (string) $request->schedule_id,
				 'additional_fare'  =>$additional_fare,
				 'location_id' => $request->location_id,				 
				 'peak_price'  => $peak_price ];

				$car_details = $this->request_helper->find_driver($data);

				return $car_details;
			}

		}

	}

	/**
	 * Display the promo details
	 *@param  Get method request inputs
	 *
	 * @return Response Json
	 */

	public function promo_details(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();
		if (count($user)) {
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

			

			$customer_id = PaymentMethod::where('user_id', $user_details->id)->first();


			$card_brand ='';
			$card_last_digits = '';

			if(isset($customer_id))
			{
            
				$stripe_key = STRIPE_SECRET;

				try
				{
				
					\Stripe\Stripe::setApiKey($stripe_key);

					$customer_id = $customer_id->stripe_customer_id;

					$customer_details = \Stripe\Customer::retrieve($customer_id);

					$result = $customer_details->sources->data;

					$card_brand = @$result[0]['brand'];

					$card_last_digits = @$result[0]['last4'];

				}
				catch (\Exception $e) {
							return response()->json([ 'status_message' => $e->getMessage(),
								'status_code' => '1',
								'promo_details' => $final_promo_details,
								'brand'     =>  @$card_brand,
								'last4'     =>  @$card_last_digits,
								'stripe_key' =>  STRIPE_KEY,
				 ]);
						}

			}

			

			$user = array(
				'promo_details' => $final_promo_details,
				'brand'     =>  $card_brand,
				'last4'     =>  $card_last_digits,
				'stripe_key' =>  STRIPE_KEY,
				'status_message' => 'Success',
				'status_code' => '1',
			);
			return response()->json($user);
		} else {
			return response()->json([
				'status_message' => "Invalid credentials",
				'status_code' => '0',
			]);
		}
	}

	/**
	 * Display the driver details after requets accept in Rider
	 *@param  Get method request inputs
	 *
	 * @return Response Json
	 */

	public function driver_details(Request $request) {

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
			$user = User::where('id', $user_details->id)->first();

			if (count($user)) {

				$data = Trips::where('id', $request->trip_id)->first();

				$driver = User::where('id', $data->driver_id)->first();

				$total_rating = DB::table('rating')->select(DB::raw('sum(rider_rating) as rating'))
					->where('driver_id', $data->driver_id)->where('rider_rating', '>', 0)->first()->rating;

				$total_rating_count = Rating::where('driver_id', $data->driver_id)->where('rider_rating', '>', 0)->get()->count();

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

				$get_min_time = $this->request_helper->GetDrivingDistance($data->pickup_latitude, $data->drop_latitude, $data->pickup_longitude, $data->drop_longitude);
				if ($get_min_time['status'] == "success") {
					$get_near_car_time = round(floor(round($get_min_time['time'] / 60)));


				$symbol = html_entity_decode( $data->currency->symbol);

			

	$total_trip_amount = $data->subtotal_fare +  $data->peak_amount +  $data->access_fee;

	

	$invoice = [];

	if($data->base_fare!=0)
	$invoice[] = array('key' => trans('messages.base_fare'), 'value' => $symbol . $data->base_fare,'bar'=>0,'colour'=>'');

	if($data->distance_fare!=0)
	$invoice[] = array('key' => trans('messages.distance_fare'), 'value' => $symbol  .(string) $data->distance_fare,'bar'=>0,'colour'=>'');

	if($data->time_fare!=0)
	$invoice[] = array('key' => trans('messages.time_fare'), 'value' => $symbol  .(string) $data->time_fare,'bar'=>0,'colour'=>'');

	if($data->schedule_fare!=0)
	$invoice[] = array('key' => trans('messages.schedule_fare'), 'value' => $symbol  .(string) $data->schedule_fare,'bar'=>0,'colour'=>'');

	if($data->peak_fare!=0)
	{
	$invoice[] = array('key' => trans('messages.normal_fare'), 'value' =>  $symbol .(string) number_format($data->subtotal_fare,2,'.',''),'bar'=>1,'colour'=>'black');

	$invoice[] = array('key' => trans('messages.peak_time_fare').'  x'.($data->peak_fare + 0), 'value' => $symbol.(string) $data->peak_amount,'bar'=>0,'colour'=>'');

	$invoice[] = array('key' => trans('messages.peak_subtotal_fare'), 'value' =>   $symbol.(string) number_format($data->peak_amount + $data->subtotal_fare,2,'.',''),'bar'=>1,'colour'=>'black');

	}

	if($data->access_fee!=0 )
	{
	$invoice[] = array('key' => trans('messages.access_fee'), 'value' => $symbol .$data->access_fee,'bar'=>0,'colour'=>'');

	$invoice[] = array('key' => trans('messages.total_trip_fare'), 'value' => $symbol .
		number_format($total_trip_amount,2,'.',''),'bar'=>1,'colour'=>'black');
	}


	if($data->promo_amount!=0 )
	$invoice[] = array('key' => trans('messages.promo_amount'), 'value' => '-'.$symbol .$data->promo_amount,'bar'=>0,'colour'=>'');

	if($data->wallet_amount!=0 )
	$invoice[] = array('key' => trans('messages.wallet_amount'), 'value' =>'-'.$symbol .$data->wallet_amount,'bar'=>0,'colour'=>'');

	if( $data->promo_amount!=0 || $data->wallet_amount!=0 )
	$invoice[] = array('key' => trans('messages.payable_amount'), 'value' => $symbol.$data->total_fare,'bar'=>0,'colour'=>'green');





					$payment_details = [

						'currency_code' => @$data->currency_code != '' ? $data->currency_code : '',

						'total_time' => @$data->total_time != '' ? $data->total_time : '0.00',

						'pickup_location' => @$data->pickup_location != '' ? $data->pickup_location : '0.00',

						'drop_location' => @$data->drop_location != '' ? $data->drop_location : '0.00',

						'driver_payout' => @$data->driver_payout != '' ? $data->driver_payout : '0.00',

						'payment_method' => @$data->payment_mode != '' ? $data->payment_mode : '',

						'owe_amount' => @$data->owe_amount != '' ? $data->owe_amount : '0.00',

						'applied_owe_amount' => @$data->applied_owe_amount != '' ? $data->applied_owe_amount : '0.00',

						'remaining_owe_amount' => @$data->remaining_owe_amount != '' ? $data->remaining_owe_amount : '0.00',

						'trip_status' => $data->status,

						'admin_paypal_id' => PAYPAL_ID,

						'paypal_mode' => PAYPAL_MODE,

						'paypal_app_id' => PAYPAL_CLIENT_ID,

						'driver_paypal_id' => $data->driver->payout_id,

				        'total_fare' => $data->total_fare ,


						];

					$user = array(

						'status_message' => 'Success',

						'status_code' => '1',

						'trip_id' => $request->trip_id,

						'driver_name' => $driver->first_name,

						'mobile_number' => '+' . $driver->country_code . $driver->mobile_number,

						'driver_thumb_image' => @$driver->profile_picture->src != '' ? $driver->profile_picture->src : url('images/user.jpeg'),

						'rating_value' => @$driver_rating != '' ? $driver_rating : '0.0',

						'pickup_latitude' => $data->pickup_latitude,

						'pickup_longitude' => $data->pickup_longitude,

						'drop_latitude' => $data->drop_latitude,

						'drop_longitude' => $data->drop_longitude,

						'car_type' => $data->car_type->car_name,

						'pickup_location' => $data->pickup_location,

						'drop_location' => $data->drop_location,

						'driver_latitude' => $driver->driver_location->latitude,

						'driver_longitude' => $driver->driver_location->longitude,

						'vehicle_number' => @$driver->driver_documents->vehicle_number != '' ? $driver->driver_documents->vehicle_number : '',

						'vehicle_name' => @$driver->driver_documents->vehicle_name != '' ? $driver->driver_documents->vehicle_name : '',

						'arrival_time' => $get_near_car_time,

						'trip_status' => @$data->status != '' ? $data->status : '',

						'payment_details' => @$payment_details != '' ? $payment_details : [''],

						'invoice' => $invoice ,

						'promo_details' => $final_promo_details,

					);

					return response()->json($user);
				} else {
					return response()->json([

						'status_message' => $get_min_time['msg'],

						'status_code' => '0',
					]);
					exit;
				}
			} else {
				return response()->json([

					'status_message' => "Invalid credentials",

					'status_code' => '0',

				]);

			}

		}

	}

/**
 * Track the Driver Location
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function track_driver(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(

			'user_type' => 'required',
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
			$user = User::where('id', $user_details->id)->first();

			if (count($user)) {

				$driver_details = Trips::where('id', $request->trip_id)->first();

				$driver_latitude = $driver_details->driver_location->latitude;

				$driver_longitude = $driver_details->driver_location->longitude;

				$user = array(

					'status_message' => 'Success',

					'status_code' => '1',

					'driver_latitude' => $driver_latitude,

					'driver_longitude' => $driver_longitude,

				);

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
	 * Display the SOS details
	 * @param  Get method request inputs
	 * @return Response Json
	 */
	public function sos(Request $request) {
		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();
		$count = EmergencySos::where('user_id', $user_details->id)->get()->count();

		if ($request->input('mobile_number') != '') {
			$request->replace(array('mobile_number' => preg_replace("/[^\w]+/", "", $request->input('mobile_number')), 'action' => $request->input('action'), 'name' => $request->input('name'),'country_code' => $request->input('country_code'), 'id' => $request->input('id')));
		}

		if ($request->action != "view") {
			$rules = array('mobile_number' => 'required|numeric', 'action' => 'required');
			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				$error = $validator->messages()->toArray();
				foreach ($error as $er) {
					$error_msg[] = array($er);
				}
				return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0'], 'contact_count' => $count];
			}
		}
		$user = User::where('id', $user_details->id)->first();

		if (count($user)) {
			$mobile_number =  preg_replace('/^\+?'.$request->country_code.'|\|'.$request->country_code.'|\D/', '', ($request->mobile_number));
			$emer = EmergencySos::where('mobile_number', $mobile_number)->where('user_id', $user_details->id)->first();
			$count = EmergencySos::where('user_id', $user_details->id)->get()->count();
			$contact_details = EmergencySos::where('user_id', $user_details->id)->get();
			if ($request->action == 'update') {
				if ($emer) {
					return response()->json(['status_message' => trans('messages.mobile_number_exist'), 'status_code' => '0', 'contact_count' => $count, 'contact_details' => $contact_details]);
				}

				$emercency = new EmergencySos;
				$emercency->name = $request->name;
				$emercency->country_code = $request->country_code;
				$emercency->mobile_number = $mobile_number;
				$emercency->user_id = $user_details->id;
				$emercency->save();
				$count = EmergencySos::where('user_id', $user_details->id)->get()->count();
				$contact_details = EmergencySos::where('user_id', $user_details->id)->get();
				return response()->json(['status_message' => "Added Successfully", 'status_code' => '1', 'contact_count' => $count, 'contact_details' => $contact_details]);
			} else if ($request->action == 'delete') {
				$del = EmergencySos::find($request->id);

				if ($del == null) {
					return response()->json(['status_message' => "Not found given request", 'status_code' => '0', 'contact_count' => $count, 'contact_details' => $contact_details]);
				}

				$del->delete();
				$count = EmergencySos::where('user_id', $user_details->id)->get()->count();
				$contact_details = EmergencySos::where('user_id', $user_details->id)->get();

				return response()->json(['status_message' => "Delete Successfully", 'status_code' => '1', 'contact_count' => $count, 'contact_details' => $contact_details]);
			} else {

				return response()->json(['status_message' => trans('messages.success'), 'status_code' => '1', 'contact_count' => $count, 'contact_details' => $contact_details]);
			}
		} else {

			return response()->json(['status_message' => "Invalid credentials", 'status_code' => '0']);
		}
	}

	/**
	 * SOS alert Message to Admin and Rider Added Mobile numbers
	 * @param  Get method request inputs
	 * @return Response Json
	 */

	public function sosalert(Request $request) {
		$user_details = JWTAuth::parseToken()->authenticate();
		$contact_details = EmergencySos::where('user_id', $user_details->id)->get();
		$address = $this->request_helper->GetLocation($request->latitude, $request->longitude);

		if ($address == '') {
			sleep(5);
			$address = $this->request_helper->GetLocation($request->latitude, $request->longitude);
		}

		$admin_details = Admin::where('status', 'Active')->select('country_code','mobile_number')->first();
		$mobile = $admin_details->country_code.$admin_details->mobile_number;

		\Log::info('admin mobile'.$mobile);

		$message = 'Emercency Message';
		$message .= ' From : ' . $user_details->mobile_number;
		$message .= ' Address : ' . $address;

		if (count($contact_details) > 0) {

			foreach ($contact_details as $details) {
				$this->request_helper->send_nexmo_message($details->mobile_number, $message);
			}

			$this->request_helper->send_nexmo_message($mobile, $message);
			return response()->json(['status_message' => 'Success', 'status_code' => '1']);
		} else {
			$this->request_helper->send_nexmo_message($mobile, $message);
			return response()->json(['status_message' => 'Success', 'status_code' => '2']);
		}

	}

	/**
	 * Cron request to cars for scheduled ride
	 * @param
	 * @return Response Json
	 */
	public function cron_request_car() {

		// before 5 min from schedule time

		$ride = ScheduleRide::where('status','Pending')->get();

		if($ride)
		{
			foreach ($ride as $request_val) 
			{   
				if($request_val->timezone)
				date_default_timezone_set($request_val->timezone);
			
				$current_date = date('Y-m-d');				
				$current_time = date('H:i');
	            if(strtotime($request_val->schedule_date) == strtotime($current_date) && strtotime($request_val->schedule_time) == (strtotime($current_time) + 300)){

					$additional_fare = "";
					$peak_price = 0;

					if(isset($request_val->peak_id)!='')
					{

					   $fare = PeakFareDetail::find($request_val->peak_id);

						if($fare)
						{
							$peak_price = $fare->price; 
							$additional_fare = "Peak";
						}

					}

		            $schedule_id = $request_val->id;
					$payment_mode = $request_val->payment_method;
					$is_wallet = $request_val->is_wallet;

					$data = [ 
								'rider_id' =>$request_val->user_id,
								'pickup_latitude' => $request_val->pickup_latitude,
								'pickup_longitude' => $request_val->pickup_longitude,
								'drop_latitude' => $request_val->drop_latitude,
								'drop_longitude' => $request_val->drop_longitude,
								'user_type' => 'rider',
								'car_id' => $request_val->car_id,
								'driver_group_id' => null,
								'pickup_location' => $request_val->pickup_location,
								'drop_location' => $request_val->drop_location,
								'payment_method' => $payment_mode,
								'is_wallet' => $is_wallet,
								'timezone' => $request_val->timezone,
								'schedule_id' => $schedule_id,
								'additional_fare'  =>$additional_fare,
								'location_id' => $request_val->location_id,
								'peak_price'  => $peak_price,
								'booking_type'  => $request_val->booking_type, 
								'driver_id'  => $request_val->driver_id, 
							];
						
					if ($request_val->driver_id==0) {
						$car_details = $this->request_helper->find_driver($data);
					}else{
						$car_details = $this->request_helper->trip_assign($data);
					}
	            }elseif(strtotime($request_val->schedule_date.' '.$request_val->schedule_time) == strtotime(date('Y-m-d H:i')) + 1800){
			        $rider = User::find($request_val->user_id);
	            	if ($request_val->booking_type=='Manual Booking' && $request_val->driver_id!=0) {
		            	$driver_details = User::find($request_val->driver_id);

			            $device_type = $driver_details->device_type;
			            $device_id = $driver_details->device_id;
			            $user_type = $driver_details->user_type;
			            $push_tittle = "Trip Scheduled Remainder";
			            $data = array(
			                'manual_booking_trip_reminder' => array('date' => $request_val->schedule_date,'time'=>$request_val->schedule_time,'pickup_location' => $request_val->pickup_location,'pickup_latitude' => $request_val->pickup_latitude, 'pickup_longitude' => $request_val->pickup_longitude,'rider_first_name'=>$rider->first_name,'rider_last_name'=>$rider->last_name,'rider_mobile_number'=>$rider->mobile_number,'rider_country_code'=>$rider->country_code));
			            if ($device_type == 1) {
			                $this->request_helper->push_notification_ios($push_tittle, $data, $user_type, $device_id);
			            } else {
			                $this->request_helper->push_notification_android($push_tittle, $data, $user_type, $device_id);
			            }
				        $text = trans('messages.trip_booked_driver_remainder',['date'=>$request_val->schedule_date.' ' .$request_val->schedule_time,'pickup_location'=>$request_val->pickup_location,'drop_location'=>$request_val->drop_location]);
				        
				        $to=$driver_details->country_code.$driver_details->mobile_number;
				        $this->request_helper->send_nexmo_message($to,$text);
				        
				    }

				    //booking message to user
			            $text = trans('messages.trip_booked_user_remainder',['date'=>$request_val->schedule_date.' ' .$request_val->schedule_time]);
			            if ($request_val->booking_type=='Manual Booking' && $request_val->driver_id!=0) {
			            	$driver = User::find($request_val->driver_id);
			                $text = $text.trans('messages.trip_booked_driver_detail',['first_name'=>$driver->first_name,'phone_number'=>$driver->mobile_number]);
			                $text = $text.trans('messages.trip_booked_vehicle_detail',['name'=>$driver->driver_documents->vehicle_name,'number'=>$driver->driver_documents->vehicle_number]);
			            }
			            $to=$rider->country_code.$rider->mobile_number;
			            $this->request_helper->send_nexmo_message($to,$text);
	            }
	            else
	            {
					if(strtotime($request_val->schedule_date) < strtotime($current_date))
					{

                        $update_ride = ScheduleRide::find($request_val->id);
                        $update_ride->status ='Cancelled';
                        $update_ride->save();

					}

	            }
             }
		}

		
	}

	/**
	 * Save Schedule Ride
	 * @param  Get method request inputs
	 * @return Response Json
	 */
	public function save_schedule_ride(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();
		$rider_id = $user_details->id;
		if (@$request->schedule_id) {
			$rules = array('schedule_date' => 'required', 'schedule_time' => 'required', 'schedule_id' => 'required');
		} else {
			$rules = array('schedule_date' => 'required', 'schedule_time' => 'required', 'pickup_longitude' => 'required', 'pickup_latitude' => 'required', 'drop_latitude' => 'required', 'drop_longitude' => 'required', 'car_id' => 'required|exists:car_type,id', 'pickup_location' => 'required', 'drop_location' => 'required', 'device_id' => 'required', 'payment_method' => 'required');
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}

			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			if (@$request->schedule_id) {
				$request_table = ScheduleRide::find($request->schedule_id);
				$request_table->schedule_date = date('Y-m-d', strtotime($request->schedule_date));
				$request_table->schedule_time = $request->schedule_time;
				$request_table->status = 'Pending';

				$request_table->save();
			} else {
				//get polyline
				$polyline = @$this->request_helper->GetPolyline($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);

				$peak_id= 0;

				if(isset($request->peak_id))
				{
					$peak_id = $request->peak_id;
				}

				$request_table = new ScheduleRide;
				$request_table->user_id = $rider_id;
				$request_table->schedule_date = date('Y-m-d', strtotime($request->schedule_date));
				$request_table->schedule_time = $request->schedule_time;
				$request_table->pickup_latitude = $request->pickup_latitude;
				$request_table->pickup_longitude = $request->pickup_longitude;
				$request_table->drop_latitude = $request->drop_latitude;
				$request_table->drop_longitude = $request->drop_longitude;
				$request_table->car_id = $request->car_id;
				$request_table->pickup_location = $request->pickup_location;
				$request_table->drop_location = urldecode($request->drop_location);
				$request_table->status = 'Pending';
				$request_table->trip_path = @$polyline;
				$request_table->timezone = $request->timezone;
				$request_table->payment_method =$request->payment_method;
				$request_table->is_wallet = $request->is_wallet;
				$request_table->location_id = $request->location_id;
				$request_table->peak_id = $peak_id;
				$request_table->save();
			}

			$get_data_in_request = ScheduleRide::where('user_id', $rider_id)->where('status', 'Pending')->get();
			if (isset($request->schedule_id) && $request->schedule_id!='') {

				$schedule_rides = array('status_message' => 'Schedule ride updated sucessfully', 'status_code' => '1', 'schedule_rides' => $get_data_in_request);
			} else {

				$schedule_rides = array('status_message' => 'Schedule ride send sucessfully', 'status_code' => '1', 'schedule_rides' => $get_data_in_request);
			}

			return response()->json($schedule_rides);
		}
	}

	/**
	 * Cancel Saved Schedule Ride
	 * @param  Get method request inputs
	 * @return Response Json
	 */
	public function schedule_ride_cancel(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();
		$rider_id = $user_details->id;
		$request_table = ScheduleRide::find($request->trip_id);
		$request_table->status = 'Cancelled';
		$request_table->save();

		//Send Sms
		$trips = ScheduleRide::where('id', $request->trip_id)->first();
		$m_number = $trips->users->mobile_number;
		$message = 'Your Schedule Ride had been Cancelled.';
		$message_response = $this->request_helper->send_nexmo_message($m_number, $message);

		return ['status_code' => '1', 'status_message' => 'Success'];
	}

	/**
	 * Get All Scheduled Rides
	 * @param  Get method request inputs
	 * @return Response Json
	 */
	public function get_schedule_rides(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();

		if (count($user)) {
			$scheduled_rides = ScheduleRide::where('user_id', $user_details->id)->get();
			return response()->json([
				'scheduled_rides' => $scheduled_rides,
				'status_message' => "Success",
				'status_code' => '1',

			]);
		} else {
			return response()->json(['status_message' => "Invalid credentials", 'status_code' => '0']);
		}
	}

	public function check_version(Request $request)
	{
		$driver_supported_versions = array('1.0');
		$rider_supported_versions = array('1.0');

		if(strtolower($request->user_type) == 'driver') 
		{
		   $force_update = !in_array($request->version, $driver_supported_versions);
		}

		else 
		{
		  $force_update = !in_array($request->version, $rider_supported_versions);
		}

		return array(

			'status_code'	=> '1',
			'status_message' => 'Success',
			'force_update'	=> $force_update,
		);
	}


	

}
