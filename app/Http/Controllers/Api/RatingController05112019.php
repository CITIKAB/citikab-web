<?php

/**
 * Rating Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Rating
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\CarType;
use App\Models\Fees;
use App\Models\Rating;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Models\ManageFare;
use App\Models\User;
use App\Models\UsersPromoCode;
use App\Models\Wallet;
use App\Models\ScheduleRide;
use App\Models\Company;
use Auth;
use DateTime;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class RatingController extends Controller {
	protected $request_helper; // Global variable for Helpers instance

	public function __construct(RequestHelper $request) {
		$this->request_helper = $request;
		$this->helper = new Helpers;
	}

/**
 * Display the Diver rating
 *@param  Post method request inputs
 *
 * @return Response Json
 */

	public function driver_rating(Request $request) {

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
			$user = User::where('id', $user_details->id)->first();

			if (count($user)) {
				$total_rated_trips = DB::table('rating')->select(DB::raw('count(id) as total_rated_trips'))
					->where('driver_id', $user_details->id)->where('rider_rating', '>', 0)->first()->total_rated_trips;

				$total_rating = DB::table('rating')->select(DB::raw('sum(rider_rating) as rating'))
					->where('driver_id', $user_details->id)->where('rider_rating', '>', 0)->where('driver_id', $user_details->id)->first()->rating;

				$total_rating_count = Rating::where('driver_id', $user_details->id)->where('rider_rating','>', 0)->get()->count();

				$life_time_trips = DB::table('trips')->select(DB::raw('count(id) as total_trips'))
					->where('driver_id', $user_details->id)->first()->total_trips;

				$five_rating_count = Rating::where('driver_id', $user_details->id)->where('rider_rating', 5)->get()->count();

				if ($total_rating_count != 0) {
					$driver_rating = (string) round(($total_rating / $total_rating_count), 2);
				} else {
					$driver_rating = '0.00';
				}

				return response()->json([

					'total_rating' => @$total_rated_trips != '' ? $total_rated_trips : '0',

					'total_rating_count' => @$life_time_trips != '' ? $life_time_trips : '0',

					'driver_rating' => @$driver_rating != '' ? $driver_rating : '0.00',

					'five_rating_count' => @$five_rating_count != '' ? $five_rating_count : '0',

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

	/**
	 * Get The Invoice of the given Trip id
	 *
	 * @param  Get method request inputs
	 * @return Response Json
	 */

	public function getinvoice(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'user_type' => 'required|in:Rider,rider,Driver,driver',
			'trip_id' => 'required',
		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}

			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		}
		\Log::error('259 line end this first payment start');
		$user = User::where('id', $user_details->id)->first();

		if(strtolower($user->user_type)=='rider'){
			sleep(5);
		}
		$trips = Trips::where('id', $request->trip_id)->first();

		if ($trips->status == 'Payment') {
					\Log::error('259 line end this payment start');
			//Time calculation
			$begin_time = new DateTime($trips->begin_trip);
			$end_time = new DateTime($trips->end_trip);
			$timeDiff = date_diff($begin_time, $end_time);

			$trip_hours = $timeDiff->format("%H");
			$trip_minute = $timeDiff->format("%I");

			$request_details = RideRequest::where('id',$trips->request_id)->first();

			$fare_details = ManageFare::where('location_id',$request_details->location_id)->where('vehicle_id',$trips->car_id)->first();

			$total_minute = ($trip_hours * 60) + $trip_minute;

			$trip_time_fare = number_format(($fare_details->per_min * $total_minute), 2, '.', '');

			$trip_km_fare = number_format(($fare_details->per_km * $trips->total_km), 2, '.', '');

			$schedule_fare_amount = 0;

			if($request_details->schedule_id != '') {
				if($request_details->schedule_ride->booking_type != "Manual Booking") {
					$schedule_fare_amount = number_format($fare_details->schedule_fare, 2, '.', '');
				}
			}

			/* Standard fare */
			$trip_base_fare = $fare_details->base_fare;
			$driver_total_fare = $trip_total_fare = $subtotal_fare = number_format(($trip_base_fare + $trip_km_fare + $trip_time_fare ), 2, '.', '');

			/* minimun fare */
			if($driver_total_fare < $fare_details->min_fare) {
				$trip_base_fare =  $fare_details->min_fare - ($trip_km_fare + $trip_time_fare );
				$driver_total_fare = $trip_total_fare =	$subtotal_fare = number_format(($trip_base_fare + $trip_km_fare + $trip_time_fare ), 2, '.', '');			
			}

			/* Peak fare */
			$peak_amount = 0;
			$driver_peak_amount = 0;

			if($trips->peak_fare!=0) {   
				$trip_total_fare = $subtotal_fare * $trips->peak_fare ;
				$peak_amount = $trip_total_fare - $subtotal_fare;

				$driver_per = Fees::find(2)->value;
			    $driver_peak_amount = number_format(($driver_per / 100) *  $peak_amount , 2, '.', '');
			    $driver_total_fare = $subtotal_fare + $driver_peak_amount;
			}

			//for driver payout variable - total_trip_fare_for
			// access fee calculation

			$percentage = Fees::find(1)->value;

			$access_fee = number_format(($percentage / 100) * $trip_total_fare, 2, '.', '');

			$owe_amount = 0;
			$remaining_wallet = 0;
			$applied_wallet = 0;
			$promo_amount = 0;

			if($trips->is_calculation == 0) {

				$total_fare = $trip_total_fare + $access_fee + $schedule_fare_amount;

				$driver_payout = $driver_total_fare;

				$company_id = User::find($trips->driver_id);
				$company_id = @$company_id->company_id;

				if ($company_id == null || $company_id == 1) {
					$driver_service_fee_percentage = Fees::find(3)->value;
					$driver_or_company_commission = number_format(($driver_service_fee_percentage / 100) * $driver_total_fare, 2, '.', '');
				}
				else {
					$company_commission_percentage = Company::find($company_id)->company_commission;
					$driver_or_company_commission = number_format(($company_commission_percentage / 100) * $driver_total_fare, 2, '.', '');
				}
				
				$driver_payout = $driver_total_fare-$driver_or_company_commission;

				//Apply promo code if promocode is available
				$promo_codes = UsersPromoCode::whereUserId($trips->user_id)->whereTripId(0)->with('promo_code_many')->whereHas('promo_code_many')->orderBy('created_at', 'asc')->first();

				if ($promo_codes) {
					$apply = UsersPromoCode::whereId($promo_codes->id)->update(['trip_id' => $request->trip_id]);
					$promo_amount = $promo_codes->promo_code_many[0]->amount;

					if($promo_amount >= $total_fare) {
						$total_fare = '0';
					}
					else {
						$total_fare = $total_fare - $promo_amount;
					}
				}

				//Wallet Amount

				$wallet_amount = 0;
				$wallet = Wallet::whereUserId($trips->user_id)->first();

				if($wallet) {
					$wallet_amount = str_replace(',','',$wallet->original_amount);
					\Log::error('259 line end'.$wallet_amount);
				}

				if ($trips->payment_mode == 'Cash & Wallet' || $trips->payment_mode == 'Gladepay & Wallet' || $trips->payment_mode == 'Wallet' ) {
					if ($total_fare >= $wallet_amount) {
							\Log::error('264 line end'.$wallet_amount);
							\Log::error('265 line end'.$total_fare);
						$amount = $total_fare - $wallet_amount;
						$remaining_wallet = 0;
						$applied_wallet = $wallet_amount;

						if ($trips->payment_mode == 'Cash & Wallet') {
							$owe_amount = abs($total_fare - ($driver_total_fare-$driver_or_company_commission));
						}
					}
					else if ($total_fare < $wallet_amount) {
						\Log::error('275 line end'.$wallet_amount);
						\Log::error('276 line end'.$total_fare);
						$remaining_wallet = $wallet_amount - $total_fare;
						$amount = 0;
						$applied_wallet = $total_fare;
					}
					\Log::error('281 line end'.$remaining_wallet);
					Wallet::whereUserId($trips->user_id)->update(['amount' => $remaining_wallet, 'currency_code' => $user->currency->code]);
					//owe amount deduction for driver 
				}
				elseif ($trips->payment_mode == 'Cash') {
					$owe_amount = abs($total_fare - ($driver_total_fare-$driver_or_company_commission));
					$amount = $total_fare;
				}
				else {
					$amount = $total_fare;
				}

                if($trips->payment_mode != 'Cash' && $trips->payment_mode != 'Cash & Wallet') {
			       $driver_payout = $this->oweAmount($driver_payout,$trips->driver_id,$trips->id);
			       $driver_payout = $driver_payout['driver_payout'];
			    }
			    else {
			    	$converted_owe_amount = $this->helper->currency_convert($user->currency->code,$trips->getOriginal('currency_code'),$owe_amount);
			    	Trips::where('id', $request->trip_id)->update(['owe_amount' => $converted_owe_amount]);
			       	$this->oweAmount(0,$trips->driver_id,$trips->id);
			    }

				Trips::where('id', $request->trip_id)->update(['total_time' => $total_minute, 'time_fare' => $trip_time_fare, 'distance_fare' => $trip_km_fare, 'base_fare' => $trip_base_fare,'subtotal_fare' => $subtotal_fare,'total_fare' => $amount, 'driver_payout' => $driver_payout , 'access_fee' => $access_fee, 'owe_amount' => $owe_amount,'wallet_amount' => $applied_wallet, 'is_calculation' => 1, 'promo_amount' => $promo_amount, 'currency_code' => $user->currency->code, 'schedule_fare' => $schedule_fare_amount,'peak_amount' => $peak_amount ,'driver_peak_amount' => $driver_peak_amount,'driver_or_company_commission' => $driver_or_company_commission]);
				\Log::error('304 line end wallet amount applied '.$applied_wallet);
				\Log::error('305 line end wallet amount is '.$remaining_wallet);
			}
			\Log::error('307 line end wallet');

			$trips = Trips::find($request->trip_id);

			$symbol = html_entity_decode($user->currency->symbol);
			$total_trip_amount = number_format($trips->subtotal_fare + $peak_amount + $trips->access_fee + $trips->schedule_fare,2,'.','');
			$peak_subtotal_fare = number_format($trips->peak_amount + $trips->subtotal_fare,2,'.','');

			$invoice = [];

			if($trips->base_fare!=0) 
				$invoice[] = array('key' => trans('messages.base_fare'), 'value' => $symbol . $trips->base_fare,'bar'=>0,'colour'=>'');

			if($trips->distance_fare!=0)
				$invoice[] = array('key' => trans('messages.distance_fare'), 'value' => $symbol  .(string) $trips->distance_fare,'bar'=>0,'colour'=>'');

		   	if($trips->time_fare!=0)
				$invoice[] = array('key' => trans('messages.time_fare'), 'value' => $symbol  .(string) $trips->time_fare,'bar'=>0,'colour'=>'');

		   	if($trips->schedule_fare!=0 && $request->user_type == 'rider')
				$invoice[] = array('key' => trans('messages.schedule_fare'), 'value' => $symbol  .(string) $trips->schedule_fare,'bar'=>0,'colour'=>'');

			if($trips->peak_fare!=0) {
				$invoice[] = array('key' => trans('messages.normal_fare'), 'value' =>  $symbol .(string) $trips->subtotal_fare,'bar'=>1,'colour'=>'black');
				
				if($request->user_type == 'rider') {
					$invoice[] = array('key' => trans('messages.peak_time_fare').'  x'.($trips->peak_fare + 0), 'value' => $symbol.(string) $trips->peak_amount,'bar'=>0,'colour'=>'');
					$invoice[] = array('key' => trans('messages.peak_subtotal_fare'), 'value' =>   $symbol.(string) ($peak_subtotal_fare),'bar'=>1,'colour'=>'black');
				}
				else {
					$invoice[] = array('key' => trans('messages.peak_time_fare').'  x'.($trips->peak_fare + 0), 'value' => $symbol.(string) $trips->driver_peak_amount,'bar'=>0,'colour'=>'');
					$invoice[] = array('key' => trans('messages.peak_subtotal_fare'), 'value' =>   $symbol.(string) ($trips->driver_peak_amount + $trips->subtotal_fare),'bar'=>1,'colour'=>'black');
				}
			}

			if($request->user_type != 'rider' && $trips->driver->company_id == 1 && $trips->driver_or_company_commission > 0) {
					$invoice[] = array('key' => trans('messages.service_fee'), 'value' => '-'.$symbol.$trips->driver_or_company_commission,'bar'=> 0 ,'colour'=>'');
			}

			if($trips->access_fee!=0 && $request->user_type == 'rider') {
				$invoice[] = array('key' => trans('messages.access_fee'), 'value' => $symbol .$trips->access_fee,'bar'=>0,'colour'=>'');
				$invoice[] = array('key' => trans('messages.total_trip_fare'), 'value' => $symbol .$total_trip_amount,'bar'=>1,'colour'=>'black');
			}


			if($trips->promo_amount!=0 && $request->user_type == 'rider')
				$invoice[] = array('key' => trans('messages.promo_amount'), 'value' => '-'.$symbol .$trips->promo_amount,'bar'=>0,'colour'=>'');

			if($trips->wallet_amount!=0 && $request->user_type == 'rider')
				$invoice[] = array('key' => trans('messages.wallet_amount'), 'value' =>'-'.$symbol .$trips->wallet_amount,'bar'=>0,'colour'=>'');

		    if($request->user_type == 'rider' && ( $trips->promo_amount!=0 || $trips->wallet_amount!=0 ))
				$invoice[] = array('key' => trans('messages.payable_amount'), 'value' => $symbol  .$trips->total_fare,'bar'=>0,'colour'=>'green');

   			$is_first = 1;

			if($trips->owe_amount!=0 && $request->user_type != 'rider') {
				
				if($trips->total_fare!=0) {
					$invoice[] = array('key' => trans('messages.cash_collected'), 'value' => $symbol  .$trips->total_fare,'bar'=>$is_first,'colour'=>'green');
					$is_first = 0;
				}
				
		       	if($trips->driver->company_id == 1)
			  		$invoice[] = array('key' => trans('messages.owe_amount') , 'value' => '-'.$symbol  .$trips->owe_amount ,'bar'=>$is_first,'colour'=>'');	
			}


			if($trips->applied_owe_amount!=0 && $request->user_type != 'rider' && $trips->driver->company_id == 1) {
				$invoice[] = array('key' => trans('messages.applied_owe_amount'), 'value' => '-'.$symbol  .$trips->applied_owe_amount,'bar'=>$is_first,'colour'=>'');
				$is_first = 0;
			}

			if($request->user_type != 'rider' && $trips->driver->company_id == 1) {

				$invoice[] = array('key' => trans('messages.driver_payout'), 'value' => $symbol  .$trips->driver_payout,'bar'=>$is_first,'colour'=>'');
			}

			$users_promo_codes = UsersPromoCode::whereUserId($trips->user_id)->whereTripId(0)->with('promo_code')->get();

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

			$schedule_ride = ScheduleRide::find($trips->ride_request->schedule_id);

			if(isset($schedule_ride) && $schedule_ride->booking_type == 'Manual Booking') {
				$to=$trips->users->country_code.$trips->users->mobile_number;
				$text = trans('messages.sms_payment_detail');
				$text = $text.trans('messages.cash_collected_payment_detail',['base_fare'=>$trips->base_fare,'time_fare'=>$trips->time_fare,'distance_fare'=>$trips->distance_fare,'total_fare'=>$trips->total_fare]);
				
				if ($trips->peak_amount!=0) {
					$text = $text.trans('messages.peak_fare_detail',['peak_fare'=>$trips->peak_amount]);
				}

				if ($trips->schedule_fare!=0) {
					$text = $text.trans('messages.schedule_fare_detail',['schedule_fare'=>$trips->schedule_fare]);
				}

				$text = $text.trans('messages.service_fare_detail',['service_fare'=>$trips->access_fee]);
        		$this->request_helper->send_nexmo_message($to,$text);
        	}
	

			return response()->json([

				'status_message' => "Success",

				'status_code' => '1',

				'total_time' => $total_minute,

				'pickup_location' => $trips->pickup_location,

				'drop_location' => $trips->drop_location,

				'payment_method' => $trips->payment_mode,

				'payment_status' => $trips->payment_status,

				'applied_owe_amount' => $trips->applied_owe_amount,

				'remaining_owe_amount' => $trips->remaining_owe_amount,

				'invoice' => $invoice,

				'total_fare' => $trips->total_fare,
				
				'driver_payout' => $trips->driver_payout,

				'paypal_app_id' => PAYPAL_CLIENT_ID,

				'paypal_mode' =>PAYPAL_MODE,

				'promo_amount' => $trips->promo_amount,
				
				'promo_details' => $final_promo_details,

				'trip_status' => $trips->status,

				'trip_id' => $trips->id,

				'driver_image' =>$trips->driver_thumb_image,

				'driver_name' =>$trips->driver->first_name,

				'rider_image' => @$trips->rider_profile_picture,

				'rider_name' => @$trips->users->first_name
			]);
		}

		return response()->json([

			'status_message' => "Something went end trip",

			'status_code' => '2',

		]);
	}

	/**
	 * Update the trip Rating given by Driver or Rider
	 *@param  Post method request inputs
	 *
	 * @return Response Json
	 */

	public function trip_rating(Request $request) 
	{
        
        $user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'user_type' => 'required|in:Rider,rider,Driver,driver',
			'rating' => 'required',
			'trip_id' => 'required',
		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];

		}
		 else {

			$user = User::where('id', $user_details->id)->first();

			$trips = Trips::where('id', $request->trip_id)->first();

			if (count($user)) {

				$rating = Rating::where('trip_id', $request->trip_id)->first();

				if ($request->user_type == 'Rider' || $request->user_type == 'rider') {

					$data = [

						'trip_id' => $request->trip_id,
						'user_id' => $trips->user_id,
						'driver_id' => $trips->driver_id,
						'rider_rating' => $request->rating,
						'rider_comments' => @$request->rating_comments != '' ? $request->rating_comments : '',

					];

					Rating::updateOrCreate(['trip_id' => $request->trip_id], $data);

				} else{

						$data = [

							'trip_id' => $request->trip_id,
							'user_id' => $trips->user_id,
							'driver_id' => $trips->driver_id,
							'driver_rating' => $request->rating,
							'driver_comments' => @$request->rating_comments != '' ? $request->rating_comments : '',
						];

					Rating::updateOrCreate(['trip_id' => $request->trip_id], $data);


				}

				Trips::where('id', $request->trip_id)->update(['status' => 'Completed']);

					return response()->json([

					'status_message' => "Rating successfully",

					'status_code' => '1',

					


				]);

			}

			return response()->json([

					'status_message' => "Rating un uccessfully",

					'status_code' => '0',

					]);
		}
	}


	public function oweAmount($driver_payout, $driver_id, $trip_id)
	{ 
		$company_id = User::find($driver_id)->company_id;
		if($company_id == 1)
	   		$driver_owe = Trips::whereDriverId($driver_id)->get();
	   	else
	   		$driver_owe = Trips::CompanyTripsOnly($company_id)->get();
	   	
	  	//deduction
	   	$owe_amount = $driver_owe->sum('owe_amount') - $driver_owe->sum('applied_owe_amount');

       	$remaining_owe_amount =0;

	   	if($owe_amount != 0) {
			if($owe_amount >= $driver_payout) {
				$applied_owe_amount = $driver_payout;
				$remaining_owe_amount  = $owe_amount - $driver_payout;
				$driver_payout =0;
			}
			else if($owe_amount < $driver_payout) {
				$applied_owe_amount = $driver_payout - ($driver_payout-$owe_amount);
				$driver_payout = $driver_payout - $owe_amount ;
				$remaining_owe_amount  = 0;
			}

		   	Trips::where('id', $trip_id)->update(['remaining_owe_amount' => $remaining_owe_amount, 'applied_owe_amount' => $applied_owe_amount]);

		   	return array('remaining' => $remaining_owe_amount, 'applied' => $applied_owe_amount, 'driver_payout' => $driver_payout);
	   	}

	    return array('remaining' => 0, 'applied' => 0, 'driver_payout' => $driver_payout);
	}

	/**
	 * Display the Rider Feedback
	 *@param  Post method request inputs
	 *
	 * @return Response Json
	 */

	public function rider_feedback(Request $request) {

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
			$user = User::where('id', $user_details->id)->first();

			if (count($user)) {

				$rider_comments = DB::table('rating')->select(DB::raw('DATE_FORMAT(created_at, "%d %M %Y") AS date,rider_rating,rider_comments,trip_id'))->where('driver_id', $user_details->id)->where('rider_rating', '>', 0)->orderBy('trip_id', 'DESC')->get();

				return response()->json([

					'rider_feedback' => $rider_comments,

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

}