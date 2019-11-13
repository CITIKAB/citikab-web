<?php

/**
 * Earning Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Earning
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Http\Helper\RequestHelper;
use App\Http\Helper\PaymantHelper;
use App\Http\Start\Helpers;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Trips;
use App\Models\User;
use App\Models\UsersPromoCode;
use App\Models\PaymentMethod;
use App\Models\Wallet;
use Auth;
use Illuminate\Http\Request;
use JWTAuth;
use PayPal;
use Srmklive\PayPal\Services\AdaptivePayments;
use Validator;

class EarningController extends Controller {

	protected $request_helper; // Global variable for Helpers instance

	protected $provider;

	public function __construct(RequestHelper $request) {
		$this->request_helper = $request;
		$this->helper = new Helpers;
	}

/**
 * Adaptive paypal setup
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function setup($gateway = 'adaptive_payments') {
		$this->provider = new AdaptivePayments;
		$this->provider = PayPal::setProvider('adaptive_payments');

	}

/**
 * Display the Earning chart details in Driver
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function earning_chart(Request $request) {

		$driver_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'user_type' => 'required|in:Driver,driver',
			'start_date' => 'required',
			'end_date' => 'required',
		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			$user = User::where('id', $driver_details->id)->where('user_type', $request->user_type)->first();
			$company_id = $user->company_id;

			$last_trip = Trips::where('driver_id', $driver_details->id)->where('status', 'Completed')->orderBy('id', 'DESC')->first();
			
			$last_trip = ($last_trip != null) ? $last_trip->company_driver_amount : "0";

			if (count($user)) {

				$symbol = Currency::where('code', $user->currency_code)->first()->symbol;

				$total_weekly_fare = 0;

				for ($i = 0; $i < 7; $i++) {

					$created_at = date("Y-m-d", strtotime($request->start_date . '+' . $i . 'day'));
					$created_day = date("l", strtotime($created_at));

					$trips = Trips::
						whereRaw("DATE_FORMAT(created_at,'%Y-%m-%d') = '" . $created_at . "'")
						->where('driver_id', $driver_details->id)
						->where('status', 'Completed')
						->get();

					$fare_amount = '0.00';

					if ($trips->count()) {
						$fare_amount = ($company_id == 1) ? $trips->sum('driver_payout') : $trips->sum('company_driver_amount');
					}

					$total_weekly_fare += $fare_amount;
					$trips_array[] = ["created_at" => $created_at, "day" => $created_day, "daily_fare" => strval($fare_amount)];
				}

				return response()->json([

					'trip_details' => $trips_array,

					'last_trip' => $last_trip,

					'recent_payout' => '',

					'status_message' => "Success",

					'total_week_amount' => (string) number_format(($total_weekly_fare), 2),

					'status_code' => '1',

					'currency_code' => @$user->currency_code,

					'currency_symbol' => $symbol,

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
 * Add payout for PayPal in Driver
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function add_payout(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'user_type' => 'required|in:Driver,driver,Rider,rider',
			'email_id' => 'required',
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
				User::where('id', $user_details->id)->update(['payout_id' => $request->email_id]);

				return response()->json([

					'status_message' => "Updated Successfully",

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
 * After Payment and  update the trip status
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function after_payment(Request $request) {

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

			$trip = Trips::find($request->trip_id); 

			if (count($user)) {
				if($trip->payment_mode=="Gladepay" || $trip->payment_mode=="Gladepay & Wallet")
				{
					$transaction_id = 0;

					if($trip->total_fare!=0)
					{
						$customer_token = PaymentMethod::where('user_id', $user_details->id)->first()->token;
						$payment_helper = new PaymantHelper();
						$nigireyan_currency = $payment_helper->currency_convert($trip->currency_code,'NGN',$trip->total_fare);
						$pay_data  = [
						  "action"=>"charge",
						  "paymentType"=>"token",
						  "token"=>$customer_token,
						  "user"=> [
						      "firstname"=>$user_details->first_name,
						      "lastname"=>$user_details->last_name,
						      "email"=>$user_details->email,
						  ],
						  "amount"=>$nigireyan_currency,
						  // "currency"=> "NGN",
						];
					$payment_result = $payment_helper->glade_way_payment($pay_data);
					
						if($payment_result['status_code']==1){
							$transaction_id = $payment_result['data']['txnRef'];
						}
						else
						{
							$messages = $payment_result['status_message']?$payment_result['status_message']:trans('messages.payment_token_error');
							return response()->json([ 'status_message' => $messages,
								'status_code' => '0', ]
							);
						}
					}
					
				}

			    // add card details
			   /* $default_payment = PaymentMethod::where('user_id', $user_details->id)->first();
				if(!$default_payment)
					$default_payment = new PaymentMethod;
				$default_payment->user_id = $user_details->id;
				$default_payment->token = $request->cardToken;
				$default_payment->brand = $request->brand;
				$default_payment->last4 = $request->mask;
				$default_payment->save();*/

				Trips::where('id', $request->trip_id)->update(['status' => 'Completed', 'paykey' => @$transaction_id, 'payment_status' => 'Completed']);

				$trip = Trips::where('id', $request->trip_id)->first();

				$data = [
				
					'trip_id' => $request->trip_id,

					'correlation_id' => @$transaction_id,

					'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',

				];

				Payment::updateOrCreate(['trip_id' => $request->trip_id], $data);

				$driver = User::where('id', $trip->driver_id)->first();

				$rider_thumb_image = $trip->rider_profile_picture;

				$push_data = array('trip_payment' => array('status' => 'Paid','trip_id' => $request->trip_id,'rider_thumb_image' => $rider_thumb_image));

				$user_type = $driver->user_type;

				$device_id = $driver->device_id;

				$push_tittle = "Payment Completed";

				// hided - reason for driver goes to online status when end trip. so after payment status update is
				//DriverLocation::where('user_id',$trip->driver_id)->update(['status' => 'Online']);

				if ($driver->device_type == 1) {
					$this->request_helper->push_notification_ios($push_tittle, $push_data, $user_type, $device_id);
				} else {
					$this->request_helper->push_notification_android($push_tittle, $push_data, $user_type, $device_id);
				}

				return response()->json([

					'status_message' => "Paid Successfully",

					'status_code' => '1',

					'currency_code' => @$trip->currency_code != '' ? $trip->currency_code : '',

					'total_time' => @$trip->total_time != '' ? $trip->total_time : '0.00',

					'total_km' => @$trip->total_km != '' ? $trip->total_km : '0.00',

					'total_time_fare' => (string) @$trip->time_fare != '' ? $trip->time_fare : '0.00',

					'total_km_fare' => @$trip->distance_fare != '' ? $trip->distance_fare : '0.00',

					'base_fare' => @$trip->base_fare != '' ? $trip->base_fare : '0.00',

					'total_fare' => @$trip->total_fare != '' ? $trip->total_fare : '0.00',

					'access_fee' => @$trip->access_fee != '' ? $trip->access_fee : '0.00',

					'pickup_location' => @$trip->pickup_location != '' ? $trip->pickup_location : '0.00',

					'drop_location' => @$trip->drop_location != '' ? $trip->drop_location : '0.00',

					'driver_payout' => @$trip->driver_payout != '' ? $trip->driver_payout : '0.00',

					'trip_status' => $trip->status,

				]);
			} else {
				return response()->json([

					'status_message' => "Invalid credentials",

					'status_code' => '0',

				]);

			}

		}

	}

	public function add_wallet(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'amount' => 'required',
		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			 $wallet_amount = $request->amount;

			$wallet = Wallet::whereUserId($user_details->id)->first();

			$default_payment = PaymentMethod::where('user_id', $user_details->id)->first();
			if($request->cardToken!=''){
				if(!$default_payment)
					$default_payment = new PaymentMethod;
				$default_payment->user_id = $user_details->id;
				$default_payment->token = $request->cardToken;
				$default_payment->brand = $request->brand;
				$default_payment->last4 = $request->mask;
				$default_payment->save();
			}
		

			if($wallet) {
			$prev_wallet_amount = (@$wallet->original_amount) ? $wallet->original_amount : 0;
			$wallet_amount = $prev_wallet_amount+@$wallet_amount;
			} else {
			$wallet = new Wallet;
			}

			$wallet->amount = $wallet_amount;
			$wallet->paykey = @$request->txnRef;
			$wallet->currency_code = $user_details->currency->code;
			$wallet->user_id = $user_details->id;

			$wallet->save();

			return response()->json([

				'status_message' => "Amount Added Successfully",

				'status_code' => '1',

				'wallet_amount' => $wallet->original_amount,

			]);
		}

	}

	public function add_promo_code(Request $request) {
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'code' => 'required',
		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			$promo_code_check = PromoCode::whereCode($request->code)->where('status', 'Active')->first();
			$promo_code_date_check = PromoCode::whereCode($request->code)->where('expire_date', '>=', date('Y-m-d'))->first();

			if (@$promo_code_check) {
				if (@$promo_code_date_check) {
					$users_promo_code_check = UsersPromoCode::whereUserId($user_details->id)->wherePromoCodeId($promo_code_date_check->id)->first();
					if (@$users_promo_code_check) {
						return ['status_code' => '0', 'status_message' => trans('messages.promo_already_applied')];
					} else {
						$users_promo_code = new UsersPromoCode;

						$users_promo_code->user_id = $user_details->id;
						$users_promo_code->promo_code_id = $promo_code_date_check->id;

						$users_promo_code->save();

						$users_promo_codes = UsersPromoCode::whereUserId($user_details->id)->whereTripId(0)->with('promo_code')->get();

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

						return ['status_code' => '1', 'status_message' => trans('messages.promo_applied_success'), 'promo_details' => $final_promo_details];
					}
				} else {
					return ['status_code' => '0', 'status_message' => trans('messages.promo_expired')];
				}
			} else {
				return ['status_code' => '0', 'status_message' => trans('messages.promo_invalid')];
			}
		}

	}

}
