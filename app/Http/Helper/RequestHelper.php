<?php

/**
 * Request Helper
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Request
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */
namespace App\Http\Helper;

use App\Models\DriverLocation;
use App\Models\PaymentGateway;
use App\Models\Request;
use App\Models\Request as RideRequest;
use App\Models\ScheduleRide;
use App\Models\User;
use App\Models\Trips;
use App\Models\PeakFareDetail;
use Auth;
use DB;
use FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Carbon\Carbon;	
use DateTime;
Use Storage;

class RequestHelper {



	public function find_driver($array) {

		/*
			     *query for get nearest drivers with in 5 kilomerters and getting currently online status drivers
			     *driver_group_id first time goes null so getting all drivers details.- important
			     *driver_group_id creates for one riderid with multiple nearest locations drivers - important
		*/
		$this->clearPending(); 
		$data_array = [
            'pickup_latitude' => $array['pickup_latitude'],
            'pickup_longitude' => $array['pickup_longitude'],
            'drop_latitude' => $array['drop_latitude'],
            'drop_longitude' => $array['drop_longitude'],
            'car_id' => $array['car_id'],
            'schedule_id' => $array['schedule_id'],
        ];
		
		date_default_timezone_set($array['timezone']);
		$ignore_drivers = $this->ignoreAssigned($data_array);
		$nearest_car = DriverLocation::select(DB::raw('*, ( 6371 * acos( cos( radians(' . $array['pickup_latitude'] . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $array['pickup_longitude'] . ') ) + sin( radians(' . $array['pickup_latitude'] . ') ) * sin( radians( latitude ) ) ) ) as distance'))
			->having('distance', '<=', Driver_Km)->where('driver_location.status', 'Online')->where('driver_location.car_id', $array['car_id'])->whereNotIn('driver_location.user_id',$ignore_drivers)
			->whereHas('users', function ($q2) {
				$q2->where('status', 'Active')
					->whereHas('vehicle',function($q3){
                        $q3->where('status', 'Active');
                    })
                    ->whereHas('company',function($q3){
                        $q3->where('status', 'Active');
                    });
			});


		if ($array['driver_group_id'] == null || $array['driver_group_id'] == "") {
		} else {
			$nearest_car = $nearest_car->whereHas('request', function ($subQuery) use ($array) {
				$subQuery->where('group_id', $array['driver_group_id'])->whereIn('status', ['Cancelled', 'Pending'])->whereNotIn('status', ['Accepted']);
			}, '<', 1);
		}
		$nearest_car = $nearest_car->orderBy('distance', 'ASC')->get();

		if ($array['is_wallet'] == 'Yes') {
			if ($array['payment_method'] == '') {
				$payment_method_store == 'Wallet';
			} else {
				$payment_method_store = $array['payment_method'] . ' & Wallet';
			}

		} else {
			$payment_method_store = $array['payment_method'];
		}

		$i = 0;
		if ($i < count($nearest_car)) {

			$nearest_car = $nearest_car[$i];
			$driver_details = @User::where('id', $nearest_car->user_id)->first();
			//check the request are accepted or not
			$request_accepted = RideRequest::where('group_id', $array['driver_group_id'])->where('status', 'Accepted')->get()->count();

			//some times request inserts duplicates so we check already insert or not for same rider id and driver id with this group
			$request_already = RideRequest::where('user_id', $array['rider_id'])->where('driver_id', $nearest_car->user_id)->where('group_id', $array['driver_group_id'])->get()->count();
			if (!$request_accepted) {
				if (!$request_already) {
					$last_second = RideRequest::where('driver_id', $nearest_car->user_id)->where('status', 'Pending')->get()->count();
					if (!$last_second) {
						$request = new Request;
						$request->user_id = $array['rider_id'];
						$request->group_id = null;
						$request->pickup_latitude = $array['pickup_latitude'];
						$request->pickup_longitude = $array['pickup_longitude'];
						$request->drop_latitude = $array['drop_latitude'];
						$request->drop_longitude = $array['drop_longitude'];
						$request->driver_id = $nearest_car->user_id;
						$request->car_id = $array['car_id'];
						$request->pickup_location = $array['pickup_location'];
						$request->drop_location = $array['drop_location'];
						$request->payment_mode = $payment_method_store;
						$request->status = 'Pending';
						$request->timezone = $array['timezone'];
						$request->schedule_id = $array['schedule_id'];
						$request->location_id = $array['location_id'];
						$request->additional_fare = $array['additional_fare'];
						$request->peak_fare = $array['peak_price'];
						$request->save();

						$group_id = @RideRequest::select('group_id')->orderBy('group_id', 'DESC')->first()->group_id;
						if ($group_id == null) {
							$group_id = 1;
						} else {
							if ($array['driver_group_id']) {
								$group_id = $array['driver_group_id'];
							} else {
								$group_id = $request->id;
							}
						}
						$array['driver_group_id'] = $group_id;
						$last_id = $request->id;
						$last_created_at = $request->created_at;
						RideRequest::where('id', $request->id)->update(['group_id' => $group_id]);
						$last_created_count = @RideRequest::where('driver_id', $nearest_car->user_id)->where('created_at', $last_created_at)->get();
						if ($last_created_count->count() > 1) {
							$result_req = RideRequest::find($request->id);
							RideRequest::where('id', '<>', $last_created_count[0]->id)->where('driver_id', $last_created_count[0]->driver_id)->where('created_at', $last_created_at)->forceDelete();
						}
						$check = RideRequest::find(@$request->id);
						if (@$check) {
							$min_time = @$this->GetDrivingDistance($array['pickup_latitude'], $driver_details->driver_location->latitude, $array['pickup_longitude'], $driver_details->driver_location->longitude);
							$get_near_car_time = round(floor(round($min_time['time'] / 60)));
							if ($get_near_car_time == 0) {
								$get_near_car_time = 1;
							}
							$device_type = $driver_details->device_type;
							$device_id = $driver_details->device_id;
							$user_type = $driver_details->user_type;
							$push_tittle = "Trip Request";
							$data = array('ride_request' => array('request_id' => $request->id, 'pickup_location' => $array['pickup_location'], 'min_time' => $get_near_car_time, 'pickup_latitude' => @$array['pickup_latitude'], 'pickup_longitude' => @$array['pickup_longitude']));
							if ($device_type == 1) {
								$this->push_notification_ios($push_tittle, $data, $user_type, $device_id);
							} else {
								$this->push_notification_android($push_tittle, $data, $user_type, $device_id);
							}
						}
						//sleep 15 seconds for every drivers request time
						$nexttick = time() + 15;
						$active = true;
						while ($active) {
							if (time() >= $nexttick) {
                                
                                $array['request'] = $request;

								$active = $this->delay_calling($array);

							}
						}
					} else {
						$this->find_driver($array);
					}
				}
			}
		} else {

			$check_group_finish = RideRequest::where('group_id', $array['driver_group_id'])->where('status', 'Pending')->get()->count();

			if (!$check_group_finish) {

				$rider_details = @User::where('id', $array['rider_id'])->first();
				$device_type = $rider_details->device_type;
				$device_id = $rider_details->device_id;
				$user_type = $rider_details->user_type;
				$push_tittle = "No Cars Available";
				$data = array('no_cars' => array('status' => 0));
				
				if (!isset($array['booking_type']) || $array['booking_type'] == 'Schedule Booking') {
					if ($device_type == 1) {
						$this->push_notification_ios($push_tittle, $data, $user_type, $device_id);
					} else {
						$this->push_notification_android($push_tittle, $data, $user_type, $device_id);
					}
				}

				if ($array['schedule_id'] != '') {
					$data = array('schedule_cars' => array('status' => 0));
					ScheduleRide::where('id', $array['schedule_id'])->update(['status' => 'Car Not Found']);
				}
				
			} else {
				$this->find_driver($array);
			}
		}
	}


	//If manual booking then directly assign trip to driver
	public function trip_assign($array) {

        $additional_fare = "";
        $peak_price = 0;
        //change ScheduleRide status to completed
        $schedule = ScheduleRide::find($array['schedule_id']);
        $schedule->status = 'Completed';
        $schedule->save();
        if(isset($schedule->peak_id)!='')
        {
           $fare = PeakFareDetail::find($schedule->peak_id);
            if($fare){
                $peak_price = $fare->price; 
                $additional_fare = "Peak";
            }
        }

        //Insert record in RideRequest table
        $ride_request = new RideRequest;
        $ride_request->user_id = $schedule->user_id;
        $ride_request->group_id = null;
        $ride_request->pickup_latitude = $schedule->pickup_latitude;
        $ride_request->pickup_longitude = $schedule->pickup_longitude;
        $ride_request->drop_latitude = $schedule->drop_latitude;
        $ride_request->drop_longitude = $schedule->drop_longitude;
        $ride_request->driver_id = $schedule->driver_id;
        $ride_request->car_id = $schedule->car_id;
        $ride_request->pickup_location = $schedule->pickup_location;
        $ride_request->drop_location = $schedule->drop_location;
        $ride_request->payment_mode = $schedule->payment_method;
        $ride_request->status = 'Accepted';
        $ride_request->timezone = $schedule->timezone;
        $ride_request->schedule_id = $schedule->id;
        $ride_request->location_id = $schedule->location_id;
        $ride_request->additional_fare = $additional_fare;
        $ride_request->peak_fare = $peak_price;
        $ride_request->save();

        $group_id = @RideRequest::select('group_id')->orderBy('group_id', 'DESC')->first()->group_id;
        if ($group_id == null) {
            $group_id = 1;
        } else {
            $group_id = $ride_request->id;
        }

        $ride_request->group_id = $group_id;
        $ride_request->save();

        $polyline = @$this->GetPolyline($ride_request->pickup_latitude, $ride_request->drop_latitude, $ride_request->pickup_longitude, $ride_request->drop_longitude);

        $url = \App::runningInConsole() ? SITE_URL : url('/');
         $src = $url.'/images/user.jpeg';
        //Insert record in Trips table
        $trip = new Trips;
        $trip->user_id = $schedule->user_id;
        $trip->pickup_latitude = $schedule->pickup_latitude;
        $trip->pickup_longitude = $schedule->pickup_longitude;
        $trip->drop_latitude = $schedule->drop_latitude;
        $trip->drop_longitude = $schedule->drop_longitude;
        $trip->driver_id = $schedule->driver_id;
        $trip->car_id = $schedule->car_id;
        $trip->pickup_location = $schedule->pickup_location;
        $trip->drop_location = $schedule->drop_location;
        $trip->request_id = $ride_request->id;
        $trip->trip_path = @$polyline;
        $trip->payment_mode = $schedule->payment_method;
        $trip->status = 'Scheduled';
        $trip->currency_code = $schedule->users->currency->code;
        $trip->peak_fare = $ride_request->peak_fare;
        $trip->save();

        DriverLocation::where('user_id', $ride_request->driver_id)->update(['status' => 'Trip']);
		
		$driver_details = @User::where('id', $ride_request->driver_id)->first();
		$min_time = @$this->GetDrivingDistance($array['pickup_latitude'], $driver_details->driver_location->latitude, $array['pickup_longitude'], $driver_details->driver_location->longitude);
		$get_near_car_time = round(floor(round($min_time['time'] / 60)));
		if ($get_near_car_time == 0) {
			$get_near_car_time = 1;
		}
		$device_type = $driver_details->device_type;
		$device_id = $driver_details->device_id;
		$user_type = $driver_details->user_type;
		$push_tittle = "Trip Cancelled by Driver";
		$data = array('manual_booking_trip_assigned' => array(
			'status' => 'Arrive Now',
			'request_id' => $ride_request->id, 
			'pickup_location' => $array['pickup_location'], 
			'min_time' => $get_near_car_time, 
			'pickup_latitude' => @$array['pickup_latitude'], 
			'pickup_longitude' => @$array['pickup_longitude'],
			'pickup_location' => @$array['pickup_location'], 
			'drop_longitude' => @$array['drop_longitude'],
			'drop_latitude' => @$array['drop_latitude'], 
			'drop_location' => @$array['drop_location'],
			'trip_id' => $trip->id,
			'rider_name' => $ride_request->users->first_name,
			'mobile_number' => '+' . $ride_request->users->country_code . $ride_request->users->mobile_number,
			'rider_thumb_image' => (@$ride_request->users->profile_picture==null)? $src : $ride_request->users->profile_picture->src,
			'rating_value' => '',
			'car_type' => $ride_request->car_type->car_name,
			'car_active_image' =>$ride_request->car_type->active_image,
			'payment_method' => $ride_request->payment_mode,
			'booking_type' => (@$trip->ride_request->schedule_ride->booking_type==null)?"":@$trip->ride_request->schedule_ride->booking_type,
		));


		if ($device_type == 1) {
			$this->push_notification_ios($push_tittle, $data, $user_type, $device_id);
		} else {
			$this->push_notification_android($push_tittle, $data, $user_type, $device_id);
		}
	}

	public function delay_calling($array) {

		$request_status = RideRequest::where('id', $array['request']->id)->get();
		
		if ($request_status->count()) {

			if ($request_status[0]->status == 'Pending') {

				RideRequest::where('id', $array['request']->id)->update(['status' => 'Cancelled']);
				$this->find_driver($array);
			}
		} else {
			$this->find_driver($array);
		}
		return false;
	}

	public function check_push($rider_id) {

		$sametime_requests = RideRequest::onlyTrashed()->get();
		$i = 0;
		$nexttick2 = time() + 20;
		$active2 = true;

		while ($active2) {
			if (time() >= $nexttick2) {
				$active2 = false;
			}
		}
		foreach ($sametime_requests as $requests_delay) {
			$last_second_delay = RideRequest::where('driver_id', $requests_delay->driver_id)->where('status', 'Pending')->get()->count();
			if (!$last_second_delay) {
				$driver_details = @User::where('id', $requests_delay->driver_id)->first();
				$min_time = @$this->GetDrivingDistance(@$pickup_latitude, $driver_details->driver_location->latitude, @$pickup_longitude, $driver_details->driver_location->longitude);

				$get_near_car_time = round(floor(round($min_time['time'] / 60)));

				if ($get_near_car_time == 0) {
					$get_near_car_time = 1;
				}

				$device_type = $driver_details->device_type;

				$device_id = $driver_details->device_id;

				$user_type = $driver_details->user_type;

				$push_tittle = "Trip Request";

				$data = array('ride_request' => array('request_id' => $requests_delay->id, 'pickup_location' => @$pickup_location, 'min_time' => $get_near_car_time, 'pickup_latitude' => @$pickup_latitude, 'pickup_longitude' => @$pickup_longitude));

				if ($device_type == 1) {
					$this->push_notification_ios($push_tittle, $data, $user_type, $device_id);
				} else {
					$this->push_notification_android($push_tittle, $data, $user_type, $device_id);
				}
				RideRequest::withTrashed()->find($requests_delay->id)->restore();
				RideRequest::where('id', $requests_delay->id)->update(['created_at' => date('Y-m-d H:i:s'), 'status' => 'Cancelled']);
				$nexttick1 = time() + 15;
				$active1 = true;

				while ($active1) {
					if (time() >= $nexttick1) {
						$active1 = false;
						exit;
					}
				}

				$i++;
				if ($i == count($sametime_requests)) {
					$driver_details = @User::select('device_type', 'device_id', 'user_type')->where('id', $requests_delay->user_id)->first();
					$device_type = $driver_details->device_type;
					$device_id = $driver_details->device_id;
					$user_type = $driver_details->user_type;
					$push_tittle = "No Cars Available";
					$data = array('no_cars' => array('status' => 0));
					if ($device_type == 1) {
						$this->push_notification_ios($push_tittle, $data, $user_type, $device_id);
					} else {
						$this->push_notification_android($push_tittle, $data, $user_type, $device_id);
					}
					return response()->json(['status_message' => "No Cars Available", 'status_code' => '1']);
				}
			} else {
			}

		}
	}
	public function check_push_ios() {
		$optionBuilder = new OptionsBuilder();
		$optionBuilder->setTimeToLive(60 * 20);
		$optionBuilder->setContentAvailable(true);

		$notificationBuilder = new PayloadNotificationBuilder('my title');
		$notificationBuilder->setBody('Hello world bhhh')
			->setSound('default');

		$dataBuilder = new PayloadDataBuilder();
		$dataBuilder->addData(['custom' => 'my_data']);

		$option = $optionBuilder->build();
		$notification = $notificationBuilder->build();
		$data = $dataBuilder->build();

		$token = "es4vEgJY7QQ:APA91bHKMQ13Ul9nI4UUv0pIAZFGa4dJp6Es3MDIVd5AwMWt7CuaQQfPxaQ70jlCZmE3AqcSkJ0L8K1XsjZatU15_nWTOnsyGHcQCsKpQpN944jZ-knmqKIaYWEsS9kwqLVnY823jNk1";

		$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
	}

	public function push_notification_ios($push_tittle, $data, $user_type, $device_id) {
		try{
			// $message = PushNotification::Message($push_tittle,array('custom' => $data,'content-available'=> 1));

			// $collection = PushNotification::app($user_type)->to($device_id)->send($message);
			$notificationBuilder = new PayloadNotificationBuilder($user_type);
			$notificationBuilder->setBody($push_tittle)->setSound('default');

			$dataBuilder = new PayloadDataBuilder();
			$dataBuilder->addData(['custom' => $data]);

			$optionBuilder = new OptionsBuilder();
			$optionBuilder->setTimeToLive(15);
			//$optionBuilder->setContentAvailable(true);

			$notification = $notificationBuilder->build();
			$data = $dataBuilder->build();
			$option = $optionBuilder->build();

			$downstreamResponse = FCM::sendTo($device_id, $option, $notification, $data);
		} catch (\Exception $e) {
            \Log::error('Could not send push notification');
        }

	}

	public function push_notification_android($push_tittle, $data, $user_type, $device_id) {
		try{
			$notificationBuilder = new PayloadNotificationBuilder($user_type);
			$notificationBuilder->setBody($push_tittle)->setSound('default');

			$dataBuilder = new PayloadDataBuilder();
			$dataBuilder->addData(['custom' => $data]);

			$optionBuilder = new OptionsBuilder();
			$optionBuilder->setTimeToLive(15);

			$notification = $notificationBuilder->build();
			$data = $dataBuilder->build();
			$option = $optionBuilder->build();
			$downstreamResponse = FCM::sendTo($device_id, $option, null, $data);
		} catch (\Exception $e) {
            \Log::error('Could not send push notification');
        }

	}

	public function GetDrivingDistance($lat1, $lat2, $long1, $long2) {
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&language=pl-PL&key=" . MAP_KEY;

		$geocode = @file_get_contents($url);
		$response_a = json_decode($geocode);
		if ($response_a->status == "REQUEST_DENIED" || $response_a->status == "OVER_QUERY_LIMIT") {
			return array('status' => "fail", 'msg' => $response_a->error_message, 'time' => '0', 'distance' => "0");
		} elseif ($response_a->status == "OK") {
			$dist_find = $response_a->rows[0]->elements[0]->distance->value;
			$time_find = $response_a->rows[0]->elements[0]->duration->value;

			$dist = @$dist_find != '' ? $dist_find : '';
			$time = @$time_find != '' ? $time_find : '';
			return array('status' => 'success', 'distance' => $dist, 'time' => (int) $time);
		} else {
			return array('status' => 'success', 'distance' => "1", 'time' => "1");
		}
	}

	public function GetPolyline($lat1, $lat2, $long1, $long2) {
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $lat1 . "," . $long1 . "&destination=" . $lat2 . "," . $long2 . "&mode=driving&units=metric&sensor=true&&language=pl-PL&key=" . MAP_KEY;

		$geocode = @file_get_contents($url);
		$response_a = json_decode($geocode);

		$polyline_find = $response_a->routes[0]->overview_polyline->points;

		$polyline = @$polyline_find != '' ? $polyline_find : '';

		return $polyline;
	}

	public function GetCountry($lat1, $lat2) {

		$pickup_geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat1 . ',' . $lat2 . '&key=' . MAP_KEY);

		$pickup_check = json_decode($pickup_geocode);

		$country = '';

		if (@$pickup_check->results) {
			foreach ($pickup_check->results as $result) {
				foreach ($result->address_components as $addressPart) {

					if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))) {
						$country = $addressPart->long_name;

					}
				}
			}
		}

		return $country;
	}

	/**
	 * Get location
	 **/
	public function GetLocation($lat1, $lat2) {

		$drop_geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat1 . ',' . $lat2 . '&key=' . MAP_KEY);

		$drop_check = json_decode($drop_geocode);

		$location = '';

		if (@$drop_check->results) {
			$location = @$drop_check->results[0]->formatted_address;
		}

		return $location;
	}

	public function get_transaction_id_from_payment_id($payment_id = '') {
		$paypal_credentials = PaymentGateway::where('site', 'PayPal')->pluck('value', 'name');

		$gateway = \Omnipay\Omnipay::create('PayPal_Rest');

		// Initialise the gateway
		$gateway->initialize(array(
			'clientId' => $paypal_credentials['client'],
			'secret' => $paypal_credentials['secret'],
			'testMode' => ($paypal_credentials['mode'] == 'sandbox'), // Or false when you are ready for live transactions
		));

		$transaction_id = $payment_id ?: '';

		try
		{
			$purchase_response = $gateway->fetchPurchase(['transactionReference' => $payment_id])->send();
			$transaction_id = $purchase_response->getTransactionReference() ?: '';
		} catch (\Exception $e) {
			\Log::info($e->getMessage());
			$transaction_id = $payment_id ?: '';
		}

		return $transaction_id;
	}

	/**
	 * custom sms
	 *
	 * @return success or fail
	 */
	/*public function send_nexmo_message($to, $message) {
		$url = 'https://rest.nexmo.com/sms/json?' . http_build_query(
			[
				'api_key' => NEXMO_KEY,
				'api_secret' => NEXMO_SECRET,
				'to' => $to,
				'from' => NEXMO_FROM,
				'text' => $message,
			]
		);
		$response = @file_get_contents($url);
		$response_data = json_decode($response, true);
		$status = 'Failed';
		$status_message = trans('messages.errors.internal_server_error');
		if (@$response_data['messages']) {
			foreach ($response_data['messages'] as $message) {
				if ($message['status'] == 0) {
					$status = 'Success';
				} else {
					$status = 'Failed';
					$status_message = $message['error-text'];
				}
			}
		}
		return array('status' => $status, 'message' => $status_message);
	}*/

	public function send_nexmo_message($to,$text) {

		$twillo = DB::table('api_credentials')->where('site', 'Twillo')->get();

		$sid    = $twillo[0]->value;
		$token  = $twillo[1]->value;
		$from 	= $twillo[2]->value;

		$url = "https://api.twilio.com/2010-04-01/Accounts/$sid/SMS/Messages.json";

		$data =array(

			"Body" => $text,
			"From" => $from,
			"To"=> $to
		);

		$post = http_build_query($data);
		$ch = curl_init($url );
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);

		$result = json_decode($result,true);

		if ($result['status']!='queued') {
			$response = [
				'status_code' => 0,
				'message' => $result['message']
			];
		}else{
			$response = [
				'status_code' => 1,
				'message' => 'Success'
			];
		}

		\Log::info(json_encode($response));

		return $response;
	}


	public function clearPending(){
	
		$request = RideRequest::where('created_at', '<', Carbon::now()->subMinutes(2)->toDateTimeString())->where('status','Pending')->get();

        if($request)
        {
			foreach($request as $request_val)
			{

                 RideRequest::where('id', $request_val->id)->update(['status' => 'Cancelled']);

			}

	    }
  
	}

	public function ignoreAssigned($arr=[])
	{
		if (isset($arr['start_date']) && isset($arr['start_time'])) {
			$current_date = $arr['start_date'];				
			$current_time = $arr['start_time'];
		}else{
			$current_date = date('Y-m-d');				
			$current_time = date('H:i');
		}

		$pickup_latitude = $arr['pickup_latitude'];
		$pickup_longitude = $arr['pickup_longitude'];
		$drop_latitude = $arr['drop_latitude'];
		$drop_longitude = $arr['drop_longitude'];
		$car_id = $arr['car_id'];


		  $data = User::with('driver_documents.vehicle')->whereHas('driver_documents',function($q)use($car_id){	
                  $q->whereHas('vehicle',function($q1)use($car_id){
                  	$q1->where('vehicle_id',$car_id);
                  });
		  })->get()->map(function ($user) {
			    return collect($user->driver_documents->toArray())
			        ->only(['user_id'])
			        ->all();
          })->toArray();

		$driver_list = array_flatten($data);

		  $dt =  ScheduleRide::where('driver_id','!=',"")->where('schedule_date',$current_date)->where('status','Pending')->whereIn('driver_id',$driver_list)->where('id','!=',$arr['schedule_id'])->get();
			/* current distance time */

		  $get_min_time = 
		$this->GetDrivingDistance($pickup_latitude,$drop_latitude,$pickup_longitude,$drop_longitude);
          $get_current_trip_time = round(floor(round($get_min_time['time'] / 60)));

               $drivers_list = [];
		 foreach ($dt as $key => $value) 
		 {

			/* previous distance time */

			  $get_pre_time = 
			$this->GetDrivingDistance($drop_latitude,$value->pickup_latitude,$drop_longitude,$value->pickup_longitude);
			        $get_pre_time = round(floor(round($get_pre_time['time'] / 60)));

				$total_time  =$get_current_trip_time + $get_pre_time;

				$time = strtotime($current_time);
				$startTime = date("H:i", strtotime('-10 minutes', $time));
				$endTime = date("H:i", strtotime('+'.$total_time.' minutes', $time));


				$date1 = DateTime::createFromFormat('H:i', $value->schedule_time);
				$date2 = DateTime::createFromFormat('H:i', $value->schedule_end_time);
				$date3 = DateTime::createFromFormat('H:i', $startTime);
				$date4 = DateTime::createFromFormat('H:i', $endTime);
				
				if (($date3<$date1&&$date1<$date4)||($date3<$date2&&$date2<$date4)||($date1<$date3&&$date4<$date2))
				{ 

				    $drivers_list[] = $value->driver_id;
				}

		 }
          return $drivers_list;
  
	}


}