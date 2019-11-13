<?php

/**
 * Profile Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Profile
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use App\Models\Currency;
use App\Models\DriverAddress;
use App\Models\DriverDocuments;
use App\Models\ProfilePicture;
use App\Models\RiderLocation;
use App\Models\Trips;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Wallet;
use App\Models\Vehicle;
use Auth;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;
use Image;

class ProfileController extends Controller {

/**
 * User Profile photo upload
 *@param  Post method request inputs
 *
 * @return Response Json
 */

	public function upload_profile_image(Request $request) {

		$this->helper = new Helpers;

		$user_details =  JWTAuth::toUser($_POST['token']);

		$user_id = $user_details->id;

		//ceck uploaded image is set or not
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
			//$file_name = time().'_'.str_replace(" ", "", $_FILES['image']['name']);

			$type = pathinfo($file_name, PATHINFO_EXTENSION);

			$file_tmp = $_FILES['image']['tmp_name'];

			$dir_name = dirname($_SERVER['SCRIPT_FILENAME']) . '/images/users/' . $user_id;

			$f_name = dirname($_SERVER['SCRIPT_FILENAME']) . '/images/users/' . $user_id . '/' . $file_name;
			//check file directory is created or not

			if (!file_exists($dir_name)) {
				//create file directory
				mkdir(dirname($_SERVER['SCRIPT_FILENAME']) . '/images/users/' . $user_id, 0777, true);
			}
			//upload image from temp_file  to server file
			$img1 = Image::make($file_tmp);
            $img1->orientate();
            $img1->save($f_name);
			// if (move_uploaded_file($file_tmp, $f_name)) {

				//change compress image in 225*225
				$li = $this->helper->compress_image("images/users/" . $user_id . "/" . $file_name, "images/users/" . $user_id . "/" . $file_name, 80, 225, 225);


			// }

			$b_name = basename($file_name, '.' . $type);

			//return file based on image size.
			//$normal = url('/').'/images/users/'.$user_id.'/'.$file_name;

			$small = url('/') . '/images/users/' . $user_id . '/' . $b_name . '_225x225.' . $type;

			// $large  = url('/').'/images/users/'.$user_id.'/'.$b_name.'_510x510.'.$type;

			return response()->json([

				'status_message' => "Profile Image Upload Successfully",

				'status_code' => "1",

				'image_url' => $small,

			]);

		}

	}

/**
 * Driver Docuemnt upload
 *@param  Post method request inputs
 *
 * @return Response Json
 */

	public function document_upload(Request $request) {

		$this->helper = new Helpers;

		$user_details =  JWTAuth::toUser($_POST['token']);

		$user_id = $user_details->id;

		$rules = array(

			'document_type' => 'required|in:license_front,license_back,insurance,rc,permit',
			'image' => 'required',

		);

		$messages = [

			'document_type.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'image.required' => ':attribute ' . trans('messages.field_is_required') . '',

		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {

			$document_type = $request->document_type;

			//ceck uploaded image is set or not
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
				//$file_name = time().'_'.str_replace(" ", "", $_FILES['image']['name']);

				$file_tmp = $_FILES['image']['tmp_name'];

				$dir_name = dirname($_SERVER['SCRIPT_FILENAME']) . '/images/users/' . $user_id;

				$f_name = dirname($_SERVER['SCRIPT_FILENAME']) . '/images/users/' . $user_id . '/' . $file_name;
				//check file directory is created or not

				if (!file_exists($dir_name)) {
					//create file directory
					mkdir(dirname($_SERVER['SCRIPT_FILENAME']) . '/images/users/' . $user_id, 0777, true);
				}
				//upload image from temp_file  to server file
				// if (move_uploaded_file($file_tmp, $f_name)) {

					//change compress image in 225*225
					// $li=$this->helper->compress_image("images/users/".$user_id."/".$file_name, "images/users/".$user_id."/".$file_name, 80, 225, 225);

				// }

				$img1 = Image::make($file_tmp);
                $img1->orientate();
                $img1->save($f_name);

				$b_name = basename($file_name, '.' . $type);
				$normal = url('/') . '/images/users/' . $user_id . '/' . $file_name;
            	if ($document_type == 'insurance' || $document_type == 'rc' || $document_type == 'permit') {
					$count = @Vehicle::where('user_id', $user_id)->get();
					if (count($count)) {
						$document_count = @$count[0]['document_count'] != '' ? $count[0]['document_count'] : '0';

						$document = @$count[0][$document_type] != '' ? $count[0][$document_type] : '';
					} else {
						$document_count = '0';

						$document = '';
					}

					if ($document_count < 3 && $document == '') {
						$vehicle_document_count = $document_count + 1;
					} else {
						$vehicle_document_count = $document_count;
					}

					if ($vehicle_document_count >= 3) {
						$vehicle_document_count = 3;
					}

					$driver_document_count = @DriverDocuments::where('user_id',$user_id)->first()->document_count;
					//return file based on image size.
					$data = [
						'user_id' => $user_id,

						'company_id' => $user_details->company_id,

						$document_type => $normal,

						'document_count' => @$vehicle_document_count,

					];
					if ($driver_document_count==null) {
	                    DriverDocuments::updateOrCreate(['user_id' => $user_id],['document_count'=>0]);
	                }
					Vehicle::updateOrCreate(['user_id' => $user_id], $data);
				}else{
					$count = @DriverDocuments::where('user_id', $user_id)->get();

					if (count($count)) {
						$document_count = @$count[0]['document_count'] != '' ? $count[0]['document_count'] : '0';

						$document = @$count[0][$document_type] != '' ? $count[0][$document_type] : '';
					} else {
						$document_count = '0';

						$document = '';
					}

					if ($document_count < 2 && $document == '') {
						$driver_document_count = $document_count + 1;
					} else {
						$driver_document_count = $document_count;
					}

					if ($driver_document_count >= 2) {
						$driver_document_count = 2;
					}

					$vehicle_document_count = @Vehicle::where('user_id',$user_id)->first()->document_count;

					//return file based on image size.

					$data = [
						'user_id' => $user_id,

						$document_type => $normal,

						'document_count' => @$driver_document_count,

					];

					DriverDocuments::updateOrCreate(['user_id' => $user_id], $data);
				}

				if ($driver_document_count == 2 && $vehicle_document_count==3) {
					User::where('id', $user_id)->update(['status' => 'Pending']);
				}

				return response()->json([

					'status_message' => "Upload Successfully",

					'status_code' => "1",

					'document_url' => $normal,

					'driver_document_count' => $driver_document_count + $vehicle_document_count,

				]);
			}
		}

	}

/**
 * Display the vehicle details
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function vehicle_details(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$user_id = $user_details->id;

		$rules = array(

			'vehicle_id' => 'required',
			'vehicle_name' => 'required',
			'vehicle_type' => 'required',
			'vehicle_number' => 'required',

		);

		$messages = [

			'vehicle_id.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'vehicle_name.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'vehicle_type.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'vehicle_number.required' => ':attribute ' . trans('messages.field_is_required') . '',

		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			$data = [
				'user_id' => $user_id,
				'vehicle_id' => $request->vehicle_id,
				'vehicle_name' => urldecode($request->vehicle_name),
				'vehicle_type' => $request->vehicle_type,
				'vehicle_number' => urldecode($request->vehicle_number),

			];

			$driver_doc = DriverDocuments::where('user_id',$user_id)->first();
			if ($driver_doc==null) {
                DriverDocuments::updateOrCreate(['user_id' => $user_id],['document_count'=>0]);
            }
			Vehicle::updateOrCreate(['user_id' => $user_id], $data);

			User::where('id', $user_details->id)->update(['status' => 'Document_details']);

			return response()->json([

				'status_message' => trans('messages.update_success'),

				'status_code' => "1",

			]);

		}

	}

/**
 * Display the Rider profile details & get the trip information while app closed
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function get_rider_profile(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		if ($request->trip_id) {
			$rules = array(

				'trip_id' => 'required|exists:trips,id',

			);

		} else {

			$rules = array(

				'token' => 'required',

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
			$user = User::where('id', $user_details->id)->first();

			if ($request->trip_id) {

				$trip = Trips::where('id', $request->trip_id)->first();

				if (count($user)) {


			$symbol = html_entity_decode( $trip->currency->symbol);

			$total_trip_amount = $trip->subtotal_fare + $trip->peak_amount + $trip->access_fee;

	

	$invoice = [];

	if($trip->base_fare!=0)
	$invoice[] = array('key' => trans('messages.base_fare'), 'value' => $symbol . $trip->base_fare,'bar'=>0,'colour'=>'');

	if($trip->distance_fare!=0)
	$invoice[] = array('key' => trans('messages.distance_fare'), 'value' => $symbol  .(string) $trip->distance_fare,'bar'=>0,'colour'=>'');

   if($trip->time_fare!=0)
	$invoice[] = array('key' => trans('messages.time_fare'), 'value' => $symbol  .(string) $trip->time_fare,'bar'=>0,'colour'=>'');

   if($trip->schedule_fare!=0)
	$invoice[] = array('key' => trans('messages.schedule_fare'), 'value' => $symbol  .(string) $trip->schedule_fare,'bar'=>0,'colour'=>'');

	 if($trip->peak_fare!=0)
	 {
	    $invoice[] = array('key' => trans('messages.normal_fare'), 'value' =>  $symbol .(string) $trip->subtotal_fare,'bar'=>1,'colour'=>'black');

	    $invoice[] = array('key' => trans('messages.peak_time_fare').'  x'.$trip->peak_fare, 'value' =>  $trip->peak_amount,'bar'=>0,'colour'=>'');

	    $invoice[] = array('key' => trans('messages.peak_subtotal_fare'), 'value' =>   $symbol.(string) ($trip->peak_amount + $trip->subtotal_fare),'bar'=>1,'colour'=>'black');

	 }


	if($trip->owe_amount!=0)
	{ 
		
		if($trip->total_fare!=0)
		$invoice[] = array('key' => trans('messages.cash_collected'), 'value' => $symbol  .$trip->total_fare,'bar'=>0,'colour'=>'green');

	  $invoice[] = array('key' => trans('messages.owe_amount') , 'value' => '-'.$symbol  .$trip->owe_amount ,'bar'=>0,'colour'=>'');	


	    
	}

	  if($trip->applied_owe_amount!=0)
		$invoice[] = array('key' => trans('messages.applied_owe_amount'), 'value' => $symbol  .$trip->applied_owe_amount,'bar'=>0,'colour'=>'');

   
	$invoice[] = array('key' => trans('messages.driver_payout'), 'value' => $symbol  .$trip->driver_payout,'bar'=>0,'colour'=>'');

				
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

					return response()->json([

						'status_message' => trans('messages.success'),

						'status_code' => '1',

						'trip_id' => $trip->id,

						'booking_type' => (@$trip->ride_request->schedule_ride->booking_type==null)?"":@$trip->ride_request->schedule_ride->booking_type,
						'rider_name' => $trip->users->first_name,
						'payment_method' => @$trip->payment_mode,
						'mobile_number' => '+' . $trip->users->country_code . $trip->users->mobile_number,

						'rider_thumb_image' => @$trip->profile_picture->src != '' ? $trip->profile_picture->src : url('images/user.jpeg'),

						'rating_value' => '',

						'car_type' => $trip->car_type->car_name,
						
						'car_active_image' =>$trip->car_type->active_image,

						'pickup_location' => $trip->pickup_location,

						'drop_location' => $trip->drop_location,

						'pickup_latitude' => $trip->pickup_latitude,

						'pickup_longitude' => $trip->pickup_longitude,

						'drop_latitude' => $trip->drop_latitude,

						'drop_longitude' => $trip->drop_longitude,

						'trip_status' => $trip->status,

						'payment_details' => @$payment_details != '' ? $payment_details : [''],

						'invoice'    => $invoice,

						'contact'  => MANUAL_BOOK_CONTACT,

					]);

				} else {

					return response()->json([

						'status_message' => trans('messages.invalid_credentials'),

						'status_code' => '0',

					]);

				}

			} else {
				$trip_status = '';

				if (count($user)) {

					$wallet_amount = '0';

					$wallet = Wallet::whereUserId($user_details->id)->first();

					if ($wallet) 
					{
					    $wallet_amount = $wallet->original_amount;
					}


					$symbol = @Currency::where('code', $user->currency_code)->first()->symbol;

					return response()->json([

						'status_message' => trans('messages.success'),

						'status_code' => '1',

						'first_name' => $user->first_name,

						'last_name' => $user->last_name,

						'mobile_number' => $user->mobile_number,

						'country_code' => $user->country_code,

						'email_id' => $user->email,

						'profile_image' => ($user->profile_picture==null) ? url('/images/user.jpeg'): $user->profile_picture->src,

						'home' => @$user->rider_location->home != '' ? $user->rider_location->home : '',

						'work' => @$user->rider_location->work != '' ? $user->rider_location->work : '',

						'currency_code' => @$user->currency->code,

						'currency_symbol' => html_entity_decode(@$user->currency->original_symbol),

						'trip_status' => $trip_status,

						'wallet_amount' =>$wallet_amount,

						'contact'  => MANUAL_BOOK_CONTACT,


					]);

				} else {
					
					return response()->json([

						'status_message' => trans('messages.invalid_credentials'),

						'status_code' => '0',

					]);

				}
			}

		}

	}

/**
 * Update the location of Rider
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function update_rider_location(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		if ($request->home) {
			$rules = array(
				'home' => 'required',
				'latitude' => 'required',
				'longitude' => 'required',

			);

			$location_type = 'home';

		} else {
			$rules = array(
				'work' => 'required',
				'latitude' => 'required',
				'longitude' => 'required',

			);

			$location_type = 'work';

		}

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

				if ($location_type == 'work') {
					$data = [
						'user_id' => $user_details->id,
						'work' => $request->work,
						'work_latitude' => $request->latitude,
						'work_longitude' => $request->longitude,

					];
				} else {
					$data = [
						'user_id' => $user_details->id,
						'home' => $request->home,
						'home_latitude' => $request->latitude,
						'home_longitude' => $request->longitude,

					];
				}

				RiderLocation::updateOrCreate(['user_id' => $user_details->id], $data);

				return response()->json([

					'status_message' => trans('messages.update_success'),

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
 * Update Rider Profile
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function update_rider_profile(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'profile_image' => 'required',
			'first_name' => 'required',
			'last_name' => 'required',
			'country_code' => 'required',
			'mobile_number' => 'required',
			'email_id' => 'required',

		);

		$messages = [

			'first_name.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'last_name.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'mobile_number.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'country_code.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'email_id.required' => ':attribute ' . trans('messages.field_is_required') . '',
			'profile_image.required' => ':attribute ' . trans('messages.field_is_required') . '',

		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			$user_check = User::where('id', $user_details->id)->first();

			if (count($user_check)) {

				User::where('id', $user_details->id)->update(['first_name' => urldecode($request->first_name), 'last_name' => urldecode($request->last_name), 'mobile_number' => $request->mobile_number, 'email' => urldecode($request->email_id), 'country_code' => $request->country_code]);

				ProfilePicture::where('user_id', $user_details->id)->update(['src' => html_entity_decode($request->profile_image)]);

				$user = User::where('id', $user_details->id)->first();

				return response()->json([

					'status_message' => trans('messages.update_success'),

					'status_code' => '1',

					'first_name' => $user->first_name,

					'last_name' => $user->last_name,

					'mobile_number' => $user->mobile_number,

					'country_code' => $user->country_code,

					'email_id' => $user->email,

					'profile_image' => $user->profile_picture->src,

					'home' => @$user->rider_location->home != '' ? $user->rider_location->home : '',

					'work' => @$user->rider_location->work != '' ? $user->rider_location->work : '',

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
 * Display Driver  Profile
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function get_driver_profile(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$user = User::where('id', $user_details->id)->first();


		if (count($user)) {

			$symbol = @Currency::where('code', $user->currency_code)->first()->symbol;

			return response()->json([

				'status_message' => 'Success',

				'status_code' => '1',

				'first_name' => $user->first_name,

				'last_name' => $user->last_name,

				'mobile_number' => $user->mobile_number,

				'country_code' => $user->country_code,

				'email_id' => $user->email,
				'car_type' => $user->car_type,
				'car_id' => @$user->driver_documents->vehicle_id ?: '1',

				'profile_image' => @$user->profile_picture->src != '' ? $user->profile_picture->src : '',

				'address_line1' => @$user->driver_address->address_line1 != '' ? $user->driver_address->address_line1 : '',

				'address_line2' => @$user->driver_address->address_line2 != '' ? $user->driver_address->address_line2 : '',

				'city' => @$user->driver_address->city != '' ? $user->driver_address->city : '',

				'state' => @$user->driver_address->state != '' ? $user->driver_address->state : '',

				'postal_code' => @$user->driver_address->postal_code != '' ? $user->driver_address->postal_code : '',

				'vehicle_name' => @$user->driver_documents->vehicle_name != '' ? $user->driver_documents->vehicle_name : '',

				'vehicle_number' => @$user->driver_documents->vehicle_number != '' ? $user->driver_documents->vehicle_number : '',

				'license_front' => @$user->driver_documents->license_front != '' ? $user->driver_documents->license_front : '',

				'license_back' => @$user->driver_documents->license_back != '' ? $user->driver_documents->license_back : '',

				'insurance' => @$user->driver_documents->insurance != '' ? $user->driver_documents->insurance : '',

				'rc' => @$user->driver_documents->rc != '' ? $user->driver_documents->rc : '',

				'permit' => @$user->driver_documents->permit != '' ? $user->driver_documents->permit : '',

				'currency_code' => @$user->currency->code,

				'currency_symbol' => html_entity_decode(@$user->currency->original_symbol),

				'car_image' =>  @$user->driver_documents->car_type->vehicle_image,

				'car_active_image' =>@$user->driver_documents->car_type->active_image,

				'company_id' =>@$user->company_id,
				'company_name' =>@$user->company->name,

				'bank_details' => isset($user->bank_detail) ? $user->bank_detail : (object)[],


			]);
		} else {
			return response()->json([

				'status_message' => trans('messages.invalid_credentials'),

				'status_code' => '0',

			]);

		}

	}

/**
 * Update Driver  Profile
 *@param  Get method request inputs
 *
 * @return Response Json
 */

	public function update_driver_profile(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(

			'first_name' => 'required',

			'last_name' => 'required',

			'mobile_number' => 'required',

			'country_code' => 'required',

			'email_id' => 'required',

			'profile_image' => 'required',

			'address_line1' => 'required',

			'address_line2' => 'required',

			'city' => 'required',

			'state' => 'required',

			'postal_code' => 'required',

		);

		$messages = [

			'first_name.required' => trans('messages.first_name_required'),
			'last_name.required' => trans('messages.last_name_required'),
			'mobile_number.required' => trans('messages.mobile_num_required'),
			'country_code.required' => trans('messages.country_code_required'),
			'email_id.required' => trans('messages.email_id_required'),
			'profile_image.required' => trans('messages.profile_image_required'),
			'address_line1.required' =>  trans('messages.address_line1_required'),
			'address_line2.required' =>  trans('messages.address_line2_required'),
			'city.required' => trans('messages.city_required'),
			'state.required' => trans('messages.state_required'),
			'postal_code.required' => trans('messages.postal_code_required'),
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			$user_check = User::where('id', $user_details->id)->first();

			if (count($user_check)) {

				User::where('id', $user_details->id)->update
					([

					'first_name' => html_entity_decode($request->first_name),
					'last_name' => html_entity_decode($request->last_name),
					'mobile_number' => $request->mobile_number,
					'country_code' => $request->country_code,
					'email' => html_entity_decode($request->email_id),

				]);

				DriverAddress::where('user_id', $user_details->id)->update
					([

					'address_line1' => html_entity_decode($request->address_line1),
					'address_line2' => html_entity_decode($request->address_line2),
					'city' => html_entity_decode($request->city),
					'state' => html_entity_decode($request->state),
					'postal_code' => html_entity_decode($request->postal_code),

				]);

				ProfilePicture::where('user_id', $user_details->id)->update(['src' => html_entity_decode($request->profile_image)]);

				$user = User::where('id', $user_details->id)->first();

				return response()->json([

					'status_message' => trans('messages.update_success'),

					'status_code' => '1',

					'first_name' => $user->first_name,

					'last_name' => $user->last_name,

					'mobile_number' => $user->mobile_number,

					'country_code' => $user->country_code,

					'email_id' => $user->email,

					'profile_image' => $user->profile_picture->src,

					'address_line1' => $user->driver_address->address_line1,

					'address_line2' => $user->driver_address->address_line2,

					'city' => $user->driver_address->city,
					'car_type' => $user->car_type,

					'state' => $user->driver_address->state,

					'postal_code' => $user->driver_address->postal_code,

					'vehicle_name' => $user->driver_documents->vehicle_name,

					'vehicle_number' => $user->driver_documents->vehicle_number,

					'license_front' => $user->driver_documents->license_front,

					'license_back' => $user->driver_documents->license_back,

					'insurance' => $user->driver_documents->insurance,

					'rc' => $user->driver_documents->rc,

					'permit' => $user->driver_documents->permit,

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
	 * To update the currency code for the user
	 * @param  Request $request Get values
	 * @return Response Json
	 */
	public function update_user_currency(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'currency_code' => 'required|exists:currency,code',
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
				User::where('id', $user_details->id)->update(['currency_code' => $request->currency_code]);
				auth()->user()->currency_code = $request->currency_code;

				$wallet = Wallet::whereUserId(auth()->user()->id)->first();

				$wallet_amount = (@$wallet->original_amount) ? $wallet->original_amount : "0";

				return response()->json([
					'status_message' => trans('messages.update_success'),
					'status_code' => '1',
					'wallet_amount' => $wallet_amount,
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
	 * API for create a customer id  based on card details using stripe payment gateway
	 *
	 * @return Response Json response with status
	 */

	public function add_card_details(Request $request) {

		$user_details = JWTAuth::parseToken()->authenticate();


		try {

			\Stripe\Stripe::setApiKey(STRIPE_SECRET);

			$payment_details = PaymentMethod::where('user_id', $user_details->id)->first();

			if ($payment_details) {
				
				$customer = \Stripe\Customer::create(
					array(
						"description" => "Customer for daniel.jones@example.com",
						"source" => $request->stripe_id, // obtained with Stripe.js
					)
				);

				$customer_details = \Stripe\Customer::retrieve($customer->id);

				$payment_details->stripe_customer_id = $customer->id;
				$payment_details->save();

			} else
			 {
                 
				// $stripe = \Stripe\Token::create(array(
				// 	"card" => array(
				// 		"number" => "4242424242424242",
				// 		"exp_month" => 6,
				// 		"exp_year" => 2019,
				// 		"cvc" => "314",
				// 	),
				// ));


				// $id = $stripe->id;

				$id = $request->stripe_id;

				$customer = \Stripe\Customer::create(
					array(
						"description" => "Customer for daniel.jones@example.com",
						"source" => $id, // obtained with Stripe.js //
					)
				);

				$customer_details = \Stripe\Customer::retrieve($customer->id);

				$payment_details = new PaymentMethod;
				$payment_details->user_id = $user_details->id;
				$payment_details->stripe_customer_id = $customer->id;
				$payment_details->save();
			}

			$customer_details = \Stripe\Customer::retrieve($customer->id);

			$result = $customer_details->sources->data;

			// dd($result);

			return response()->json(
				[

					'status_message' => 'Successfully',

					'status_code' => '1',

					'brand' => $result[0]['brand'],

					'last4' => $result[0]['last4'],

					// 'payment_details' => $payment_details,

				]
			);
		} catch (\Exception $e) {
			return response()->json(
				[

					'status_message' => $e->getMessage(),

					'status_code' => '0',

				]
			);
		}
	}

	
}
