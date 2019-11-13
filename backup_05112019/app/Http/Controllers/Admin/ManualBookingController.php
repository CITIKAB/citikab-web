<?php

/**
 * Metas Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Metas
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Http\Start\Helpers;
use App\Models\DriverLocation;
use App\Models\Location;
use App\Models\ManageFare;
use App\Models\CarType;
use App\Models\ScheduleRide;
use App\Models\PeakFareDetail;
use App\Models\Trips;
use App\Models\Currency;
use App\Models\Request as RideRequest;
use App\Http\Helper\RequestHelper;
use Validator;
use DateTime;
use DateTimeZone;
use Input;
use DB;
use Auth;

class ManualBookingController extends Controller
{
    protected $request_helper; // Global variable for Helpers instance
    public function __construct(RequestHelper $request) {
        $this->request_helper = $request;
        $this->helper = new Helpers;
    }
    public function index($id=null)
    {
        $data['schedule_ride'] = ScheduleRide::with('users')->where('id',$id)->where('status','Pending')->where('booking_type','Manual Booking')->first();
        if ($id!=null&&$data['schedule_ride']==null) {
           abort("404");
        }

        //Company can edit it's driver booking and Company's booking
        if (LOGIN_USER_TYPE=='company' && $id!=null && Auth::guard('company')->user()->id != $data['schedule_ride']->company_id && Auth::guard('company')->user()->id != @$data['schedule_ride']->driver->company_id) {
            abort("404");
        }

        $data['country_code_option']=Country::select('long_name','phone_code')->get();
        $data['vehicle_types'] = CarType::select('id','car_name')->get();

        //For company login get session currency
        if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {
            $data['default_currency'] = Currency::whereCode(session()->get('currency'))->first();
        }
        return view('admin.manual_booking.index',$data);
    }
    public function store(Request $request)
    {
        
        $rider = User::
            where('mobile_number',$request->mobile_number)
            ->where('user_type','Rider')
            ->where(function ($query) use ($request) {
                $query->where('country_code', '')
                ->orWhere('country_code', $request->country_code);
            })->first();
        if ($rider==null) {
            $rider = new User;
            $rider->first_name = $request->first_name;
            $rider->last_name = $request->last_name;
            $rider->email = $request->email;
            $rider->country_code = $request->country_code;
            $rider->mobile_number = $request->mobile_number;
            $rider->user_type = 'Rider';
            $rider->save();
        }

        $timezone = $this->getTimeZone($request->pickup_latitude, $request->pickup_longitude);

        $polyline = @$this->request_helper->GetPolyline($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);

        $get_fare_estimation = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);
        $travel_minutes = round(floor(round($get_fare_estimation['time'] / 60)));

        $end_time = strtotime("+".$travel_minutes." minutes", strtotime($request->date_time));

        if ($request->manual_booking_id!=null) {
            $schedule=ScheduleRide::where('status','Pending')->where('id',$request->manual_booking_id)->first();
            if ($schedule==null) {
                $this->helper->flash_message('danger', 'Invalid ID');
                return redirect(LOGIN_USER_TYPE.'/later_booking');  //redirect depends on login user is admin or company
            }
        }else{
            $schedule = new ScheduleRide;
            
            //For company user login, company id is logined company id
            if (LOGIN_USER_TYPE=='company') {
                $schedule->company_id = Auth::guard('company')->user()->id;
            }
        }
        $schedule->user_id = $rider->id;
        $schedule->schedule_date = date('Y-m-d', strtotime($request->date_time));
        $schedule->schedule_time = date('H:i', strtotime($request->date_time));
        $schedule->schedule_end_date = date('Y-m-d', $end_time);
        $schedule->schedule_end_time = date('H:i', $end_time);
        $schedule->pickup_latitude = $request->pickup_latitude;
        $schedule->pickup_longitude = $request->pickup_longitude;
        $schedule->drop_latitude = $request->drop_latitude;
        $schedule->drop_longitude = $request->drop_longitude;
        $schedule->car_id = $request->vehicle_type;
        $schedule->pickup_location = $request->pickup_location;
        $schedule->drop_location = $request->drop_location;
        $schedule->status = 'Pending';
        $schedule->trip_path = @$polyline;
        $schedule->timezone = ($timezone=='')?'Asia/Kolkata':$timezone;
        $schedule->payment_method ='Cash';
        $schedule->is_wallet = 'No';
        $schedule->location_id = $request->location_id;
        $schedule->peak_id = $request->peak_id;
        $schedule->booking_type = 'Manual Booking';
        $schedule->driver_id = (!isset($request->auto_assign_status))?$request->auto_assign_id:0;
        $schedule->save();

        //booking notification to driver
            if (!isset($request->auto_assign_status)) {
                $driver_details = @User::where('id', $schedule->driver_id)->first();
                $device_type = $driver_details->device_type;
                $device_id = $driver_details->device_id;
                $user_type = $driver_details->user_type;
                $push_tittle = "Trip Scheduled";
                $data = array(
                    'manual_booking_trip_booked_info' => array('date' => $schedule->schedule_date,'time'=>$schedule->schedule_time,'pickup_location' => $schedule->pickup_location,'pickup_latitude' => $schedule->pickup_latitude, 'pickup_longitude' => $schedule->pickup_longitude,'rider_first_name'=>$rider->first_name,'rider_last_name'=>$rider->last_name,'rider_mobile_number'=>$rider->mobile_number,'rider_country_code'=>$rider->country_code));

                if ($device_type == 1) {
                    try{
                        $this->request_helper->push_notification_ios($push_tittle, $data, $user_type, $device_id);
                    } catch (\Exception $e) {
                        \Log::error('Could not send push notification');
                    }
                } else {
                    try {
                        $this->request_helper->push_notification_android($push_tittle, $data, $user_type, $device_id);
                    } catch (\Exception $e) {
                        \Log::error('Could not send push notification');
                    }
                }

                //message notification to driver
                    $text = trans('messages.trip_booked_driver_message',['date'=>$schedule->schedule_date.' ' .$schedule->schedule_time,'pickup_location'=>$schedule->pickup_location,'drop_location'=>$schedule->drop_location]);
                    $to=$driver_details->country_code.$driver_details->mobile_number;
                    $this->request_helper->send_nexmo_message($to,$text);
            }
        
        //booking message to user
            $text = trans('messages.trip_booked_user_message',['date'=>$schedule->schedule_date.' ' .$schedule->schedule_time]);
            if (!isset($request->auto_assign_status)) {
                $driver = User::find($request->auto_assign_id);
                $text = $text.trans('messages.trip_booked_driver_detail',['first_name'=>$driver->first_name,'phone_number'=>$driver->mobile_number]);
            }
            $to=$rider->country_code.$rider->mobile_number;
            $this->request_helper->send_nexmo_message($to,$text);
        
        if ($request->manual_booking_id!=null) {
            $this->helper->flash_message('success', 'Updated Successfully');
        }else{
            $this->helper->flash_message('success', 'Added Successfully');
        }
        return redirect(LOGIN_USER_TYPE.'/later_booking');  //redirect depends on login user is admin or company
    }

    //get user by phone number
    public function search_phone(Request $request)
    {
        $user_details=User::where('user_type',$request->type)->where(function ($query) use ($request) {
                $query->where('country_code', '')
                ->orWhere('country_code', $request->country_code);
            });
        if($request->text!="")
        {
            $user_details=$user_details->where('mobile_number', 'like', '%'.$request->text.'%');
        }
        return $user_details->get()->toJson();
    }

    //get car type list
    public function search_cars(Request $request) {
        $day = date("N", strtotime($request->date_time));
        $current_time = date('H:i:00', strtotime($request->date_time));
        $pickup_country = $this->request_helper->GetCountry($request->pickup_latitude, $request->pickup_longitude);
        $drop_country = $this->request_helper->GetCountry($request->drop_latitude, $request->drop_longitude);
        if ($pickup_country == $drop_country) {
            $match_location = Location::select(DB::raw("id,status,(ST_WITHIN( GeomFromText(
                    'POINT(".$request->pickup_latitude.' '.$request->pickup_longitude.")'),ST_GeomFromText(coordinates))) as available "))->having('available','1')->where('status','Active')->first();
            if($match_location == null){
                return response()->json([
                    'trans_message' => trans('messages.location_unavailable'),
                    'status_message' => 'location_unavailable',
                    'status_code' => '0',
                ]);
            }elseif($request->pickup_location == $request->drop_location){
                return response()->json([
                    'trans_message' => 'Pickup location and Drop location should not be same',
                    'status_message' => 'location_unavailable',
                    'status_code' => '0',
                ]);
            }else{
                $location_cars =    ManageFare::where('location_id',$match_location->id)->get()->toArray();
                $vehicles =   array_column($location_cars,'vehicle_id');
                $location_id =  $match_location->id;
                $nearest_car = DriverLocation::select(DB::raw('*, ( 6371 * acos( cos( radians(' . $request->pickup_latitude . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $request->pickup_longitude . ') ) + sin( radians(' . $request->pickup_latitude . ') ) * sin( radians( latitude ) ) ) ) as distance'))
                    ->having('distance', '<=', Driver_Km)
                    ->with(['car_type', 'users','manage_fare'])
                    ->with(['users.driver_trips' => function ($query) {
                        $query->whereIn('status',['Scheduled','Begin trip','End trip','Rating']);
                    }])
                    ->whereHas('users', function ($q2) use($vehicles,$location_id) {
                        $q2->where('status', 'Active')
                        ->whereHas('vehicle',function($q3){
                            $q3->where('status', 'Active');
                        })
                        ->whereHas('company',function($q3){
                            $q3->where('status', 'Active');
                        })
                        ->where(function($query)  {
                            //For company user login, only get that company's driver
                            if(LOGIN_USER_TYPE=='company') {
                                $query->where('company_id',Auth::guard('company')->user()->id);
                            }
                         });
                    })->whereHas('car_type', function ($q2) use($vehicles,$location_id) {
                        $q2->where('status', 'Active')->whereIn('car_id',$vehicles);
                    })->whereHas('manage_fare', function ($q2) use($location_id) {
                        $q2->where('location_id', $location_id);
                    })->orderBy('distance', 'ASC')->get();
                    $nearest_car = collect($nearest_car)->groupBy('car_id')->values();
                if (isset($nearest_car) && !$nearest_car->isEmpty()) {

                    $get_fare_estimation = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);

                    if ($get_fare_estimation['status'] == "success") {
                         $data = ManageFare::
                         with(['peak_fare' => function ($query)use($day,$current_time) {
                                $query->where(function($q) use($day) {
                                    $q->where('day', $day)->orWhere('day', null);
                                })
                                ->where('start_time','<=',$current_time)
                                ->where('end_time','>=',$current_time);
                            }])
                         ->whereHas('peak_fare',function($q)use($day,$current_time){
                                $q->where(function($q) use($day) {
                                    $q->where('day', $day)->orWhere('day', null);
                                })->where('start_time','<=',$current_time)->where('end_time','>=',$current_time);
                         })->where('location_id',$location_id)->groupBy('vehicle_id')->get();
                        $fare_details= [];
                        if($data){
                            foreach($data as $fare){
                                $fare_details[$fare->vehicle_id]= array('id' =>$fare->peak_fare[0]->id ,'car_id' => $fare->vehicle_id ,'price' => $fare->peak_fare[0]->price,'type' => $fare->peak_fare[0]->type);
                            }
                        }
                        $minutes = round(floor(round($get_fare_estimation['time'] / 60)));
                        if ($get_fare_estimation['distance'] == '') {
                            $get_fare_estimation['distance'] = 0;
                        }
                        $km = round(floor($get_fare_estimation['distance'] / 1000) . '.' . floor($get_fare_estimation['distance'] % 1000));
                        $location = [];
                        $i = 0;
                        foreach ($nearest_car as $key => $list_car) {
                            $location = $list_car->map(function ($item) use ($km, $minutes) {
                                return array('latitude' => $item->latitude, 'longitude' => $item->longitude);
                            })->toArray();
                            if (count($location) > 0) {
                                $get_min_time = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $location[0]['latitude'], $request->pickup_longitude,$location[0]['longitude']);
                                $base_fare = round($list_car[$i]->manage_fare->base_fare + ($list_car[$i]->manage_fare->per_km * $km));
                                $fare_estimation = number_format(($base_fare + round($list_car[$i]->manage_fare->per_min * $minutes)), 2, '.', '');
                                if($fare_estimation < $list_car[$i]->manage_fare->min_fare){     
                                    $fare_estimation = $list_car[$i]->manage_fare->min_fare;
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
                            $peak_fare=0;

                            if(!empty($fare_details)){
                                if(array_key_exists($list_car[$i]->car_id,$fare_details)){ 
                                    $peak_price = $fare_details[$list_car[$i]->car_id]['price'];
                                    $peak_id = $fare_details[$list_car[$i]->car_id]['id'];
                                    $apply_peak = "Yes";
                                    $peak_fare = $fare_estimation * ($peak_price-1);
                                    $fare_estimation = $fare_estimation * $peak_price;
                                }
                            }

                            $currency = Currency::defaultCurrency()->first()->original_symbol;

                            $car_array[$list_car[0]->car_id] = [ 
                                'id'=>$list_car[0]->car_id,
                                'car_id' => $list_car[$i]->car_id,
                                'car_name' => $list_car[$i]->car_type->car_name,
                                'driver_id' => $list_car[$i]->user_id,
                                'capacity' => $list_car[$i]->manage_fare->capacity,
                                'base_fare' => $list_car[$i]->manage_fare->base_fare,
                                'per_min' => $list_car[$i]->manage_fare->per_min,
                                'per_km' => $list_car[$i]->manage_fare->per_km,
                                'min_fare' => $list_car[$i]->manage_fare->min_fare,
                                'schedule_fare' => $list_car[$i]->manage_fare->schedule_fare,
                                'schedule_cancel_fare' => $list_car[$i]->manage_fare->schedule_cancel_fare,
                                'location' => $location,
                                'peak_fare' => (string) $peak_fare ,
                                'fare_estimation' => (string) $fare_estimation ,
                                'min_time' => (string) $get_near_car_time,
                                'minutes' => (string) $minutes,
                                'km' => (string) $km,
                                'apply_peak' =>  $apply_peak,
                                'peak_price' =>  $peak_price,
                                'location_id' => $location_id,
                                'peak_id' =>  $peak_id,
                                'car_image' =>  $list_car[$i]->car_type->vehicle_image,
                                'car_active_image' =>$list_car[$i]->car_type->active_image,
                                'currency' =>$currency,
                            ];
                        }
                    }
                    return response()->json([
                        'vehicle_type' =>$car_array,
                        'status_message' => trans('messages.cars_found'),
                        'status_code' => '1',
                    ]);
                } else {
                    return response()->json([
                        'status_message' => 'no_cars_found',
                        'trans_message' => trans('messages.no_cars_found'),
                        'status_code' => '0',
                    ]);
                }
            }
        }
        return response()->json([
            'status_message' => 'location_country',
            'trans_message' => trans('messages.location_country'),
            'status_code' => '0',
        ]);
    }

    //get drivers list
    public function driver_list(Request $request) {
        $get_fare_estimation = $this->request_helper->GetDrivingDistance($request->pickup_latitude, $request->drop_latitude, $request->pickup_longitude, $request->drop_longitude);
        $travel_minutes = round(floor(round($get_fare_estimation['time'] / 60)));

        $end_time = strtotime("+".$travel_minutes." minutes", strtotime($request->date_time));

        $data = [
            'pickup_latitude' => $request->pickup_latitude,
            'pickup_longitude' => $request->pickup_longitude,
            'drop_latitude' => $request->drop_latitude,
            'drop_longitude' => $request->drop_longitude,
            'car_id' => $request->car_id,
            'start_date' => date('Y-m-d', strtotime($request->date_time)),
            'start_time' => date('H:i', strtotime($request->date_time)),
            'schedule_id' => $request->schedule_id,
        ];

        $ignore_assigned = $this->request_helper->ignoreAssigned($data);
        $match_location = Location::select(DB::raw("id,status,(ST_WITHIN( GeomFromText(
                'POINT(".$request->pickup_latitude.' '.$request->pickup_longitude.")'),ST_GeomFromText(coordinates))) as available "))->having('available','1')->where('status','Active')->first();
        if($match_location == null){
            return response()->json([
                'trans_message' => trans('messages.location_unavailable'),
                'status_message' => 'location_unavailable',
                'status_code' => '0',
            ]);
        }else{
            $location_cars =    ManageFare::where('location_id',$match_location->id)->get()->toArray();
            $location_id =  $match_location->id;
            $list_car = DriverLocation::select(DB::raw('*, ( 6371 * acos( cos( radians(' . $request->pickup_latitude . ') ) * cos( radians( latitude ) ) * cos(radians( longitude ) - radians(' . $request->pickup_longitude . ') ) + sin( radians(' . $request->pickup_latitude . ') ) * sin( radians( latitude ) ) ) ) as distance'))
                ->having('distance', '<=', Driver_Km)
                ->where('car_id',$request->car_id)
                ->whereNotIn('user_id',$ignore_assigned)
                ->with(['car_type', 'users','manage_fare'])
                ->with(['users.driver_trips' => function ($query) {
                    $query->whereIn('status',['Scheduled','Begin trip','End trip','Rating']);
                }])
                ->whereHas('users', function ($q2) use($location_id,$ignore_assigned) {
                    $q2->where('status', 'Active')
                    ->whereHas('vehicle',function($q3){
                        $q3->where('status', 'Active');
                    })
                    ->whereHas('company',function($q3){
                        $q3->where('status', 'Active');
                    })
                    ->where(function($query)  {
                        //For company user login, only get that company's driver
                        if(LOGIN_USER_TYPE=='company') {
                            $query->where('company_id',Auth::guard('company')->user()->id);
                        }
                     });
                })->whereHas('car_type', function ($q2) use($request,$location_id) {
                    $q2->where('status', 'Active');
                })->whereHas('manage_fare', function ($q2) use($location_id) {
                    $q2->where('location_id', $location_id);
                })->orderBy('distance', 'ASC')->get();

                $user=[];
                foreach ($list_car as $key => $car) {
                    $user[] = [
                        'id'=>$car->users->id,
                        'hidden_mobile_number'=>$car->users->mobile_number,
                        'mobile_number'=>$car->users->mobile_number,
                        'email'=>$car->users->email,
                        'first_name'=>$car->users->first_name,
                        'last_name'=>$car->users->last_name,
                        'company'=>$car->users->company->name,
                        'company_id'=>$car->users->company_id,
                        'src'=>$car->users->profile_picture->header_src,
                        'latitude'=>$car->latitude,
                        'longitude'=>$car->longitude,
                        'driver_current_status'=>($car->status=='Trip')?@$car->users->driver_trips->last()->status:$car->status,
                    ];
                }
        }
        return json_encode($user);
    }

    //get driver detail by id
    public function get_driver(Request $request)
    {
        $book_time = $request->date_time;
        $current_time = date("Y-m-d H:i", strtotime("+".$request->utc_offset." minutes"));
        if ($book_time<date("Y-m-d H:i", strtotime($current_time . "+15 minutes"))) {
            return response()->json([
                'status_message' => 'select_ahead_time',
                'trans_message' => 'Please make sure that the booking time is 15 minutes ahead from current time. So if your current time is 3:00 PM then please select 3:15 PM as booking time. This gives a room to auto assign drivers properly.',
                'status_code' => '0',
            ]);
        }
        $driver = User::where('id',$request->driver_id)->select('id','first_name','last_name','mobile_number','email')->first();
        return response()->json([
            'driver' => $driver,
            'status_code' => '1',
        ]);
    }

    //get timezone of latlang
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
}