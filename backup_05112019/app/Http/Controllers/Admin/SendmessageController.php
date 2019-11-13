<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\Admin;
use App\Models\User;
use App\Models\Company;
use App\Http\Start\Helpers;
use Validator;
use PushNotification;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use DB;

class SendmessageController extends Controller
{
    protected $helper; // Global variable for instance of Helpers
    
    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Index View for Dashboard
     *
     * @return view index
     */
    public function index(Request $request)
    {
        if(!$_POST)
        {
            return view('admin.send_message');
        }
        else if($request->submit)
        {
            // Send Email Validation Rules
            $rules = array(
                    'txtEditor' => 'required',
                    );

            if($request->to != 'to_all')
                $rules['users'] = 'required';

            // Send Email Validation Custom Names
            $niceNames = array(
                'txtEditor' => 'Message',
                'users'     => (LOGIN_USER_TYPE == 'company') ? 'Drivers' : 'Users',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                
                $to=$request->to;
                $user_type=$request->user_type;
                $message_type=$request->message_type;
                $users_id = [];
                $companies_id = [];

                if($to=="to_specific")
                {
                    $explode_users=explode(',',$request->users);
                    foreach ($explode_users as $explode_user) {
                        $email=explode('-',$explode_user);
                        if ($email[0] == 'Company') {
                            $companies_id[] = $email[1];
                        }else{
                            $users_id[] = $email[1];
                        }
                    }
                }


                if($to=="to_specific" && $user_type!="Company")
                {
                    $users_result=User::wherein('id',$users_id)->where('status',"Active");
                }
                else if($to=="to_all" && $user_type!="Company")
                {
                    $users_result=User::where('status',"Active");
                    if($user_type!="all")
                    {
                        $users_result=$users_result
                        ->where(function($query)  {
                            //For company user login, get only that company's drivers
                            if(LOGIN_USER_TYPE=='company') {
                                $query->where('company_id',Auth::guard('company')->user()->id);
                            }
                        })
                        ->where('user_type',$user_type);
                    }
                }

                if (isset($users_result)) {
                    $users_result = $users_result->select('id','country_code','mobile_number','device_id','user_type')->get();
                }

                if(LOGIN_USER_TYPE!='company'){
                    if (($user_type=="all" || $user_type=="Company") && $to=="to_all") {
                       $companies = Company::select('id','name','country_code','mobile_number','device_id',DB::raw('"Company" as user_type'))->where('status','Active')->where('id','!=',1)->get();
                    }elseif ($to=="to_specific") {
                        $companies = Company::select('id','name','country_code','mobile_number','device_id',DB::raw('"Company" as user_type'))->where('status','Active')->where('id','!=',1)->wherein('id',$companies_id)->get();
                    }

                    if ($user_type=="all") {
                        $collection = collect([$users_result,$companies]);
                        $users_result = $collection->collapse();
                        $users_result->all();
                    }elseif ($user_type=="Company") {
                        $users_result = $companies;
                    }
                }

                if($users_result->count())
                {
                    foreach($users_result as $row_user)
                    {
                        if($message_type=="sms")
                        {
                            $to='+'.$row_user->country_code.$row_user->mobile_number;
                            $this->send_nexmo_message($to,$request->txtEditor);
                        }
                        else
                        {
                            if($row_user->device_id!="")
                            {
                                $this->send_custom_pushnotification($row_user->device_id,$row_user->user_type,$request->txtEditor);    
                            }
                            
                        }
                    }
                }
                $this->helper->flash_message('success', 'Send Successfully'); // Call flash message function
                return redirect(LOGIN_USER_TYPE.'/send_message');  //redirect depends on login user is admin or company
            }
        }
    }

    /**
     * Get user function by type -rider or driver or all
     *
     * @return users list
     */
    public function get_send_users(Request $request)
    {
        $type=$request->type;
        if($type == "Company") {
            $company_details=Company::select('id','name','mobile_number', DB::raw('"Company" as user_type'))->where('status','Active')->where('id','!=',1)->get();
            return $company_details->toJson();
        }

        $user_details=User::select('id','first_name','mobile_number','user_type')->where('status','Active');

        if($type != "all") {
            $user_details=$user_details->where('user_type',$type);
        }

        if (LOGIN_USER_TYPE == 'company') {
            $user_details = $user_details->where('company_id',Auth::guard('company')->user()->id);
        }

        if ($type == 'all' && LOGIN_USER_TYPE != 'company') {
            $user_details = $user_details->get();
            $company_details=Company::select('id','name','mobile_number', DB::raw('"Company" as user_type'))->where('status','Active')->where('id','!=',1)->get();
            $collection = collect([$user_details,$company_details]);
            $user_details = $collection->collapse();
            $user_details->all();
            return $user_details;
        }
        return $user_details->get()->toJson();
    }


    /**
     * custom push notification android
     *
     * @return success or fail
     */
    public function send_custom_pushnotification($device_id,$user_type,$message)
    {   
        if (LOGIN_USER_TYPE=='company') {
            $push_title = "Message from ".Auth::guard('company')->user()->name;    
        }else{
            $push_title = "Message from Gofer";    
        }
        $data       = array('custom_message' => array('message_data' => $message,'title' => $push_title ));
        $notificationBuilder = new PayloadNotificationBuilder($user_type);
        $notificationBuilder->setBody($push_title)->setSound('default');
                          
        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['custom' => $data]);

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $optionBuilder->setContentAvailable(true);

        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        $option = $optionBuilder->build();

        $downstreamResponse = FCM::sendTo($device_id,$option, $notification, $data);
    }

    /**
     * custom sms
     *
     * @return success or fail
     */
    public function send_nexmo_message($to, $message)
    {

        $url = 'https://rest.nexmo.com/sms/json?' . http_build_query(
            [
            'api_key'     => NEXMO_KEY,
            'api_secret'  => NEXMO_SECRET,
            'to'          => $to,
            'from'        => NEXMO_FROM,
            'text'        => $message
            ]
            );

        $response = @file_get_contents($url);

        $response_data = json_decode($response, true);

        $status = 'Failed';
        $status_message = trans('messages.errors.internal_server_error');

        if(@$response_data['messages']){
            foreach ( $response_data['messages'] as $message ) {
                if ($message['status'] == 0) {
                    $status = 'Success';
                } else {
                    $status = 'Failed'; 
                    $status_message = $message['error-text'];
                }
            }
        }
        return array('status' => $status, 'message' => $status_message);
    }


}
