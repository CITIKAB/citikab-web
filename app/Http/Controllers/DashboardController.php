<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trips;
use App\Models\Rating;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\ProfilePicture;
use Validator;
use Auth;
use DateTime;
use Image;
use PDF;
use Session;
use Illuminate\Routing\Controller as BaseController;

class DashboardController extends BaseController
{
    protected $request_helper; // Global variable for Helpers instance
    
    public function __construct(RequestHelper $request)
    {
        $this->request_helper = $request;
        $this->helper = new Helpers;
    }
    /** 
    * Rider Trips page
    **/
	public function trip()
    {   
      
        Session::forget('Account_kit');
        
        $data['trips'] = Trips::with(['currency','rating'])->where('user_id',@Auth::user()->id);
        $data['trips']  = $data['trips']->paginate(4)->toJson();     
        return view('dashboard.trip',$data);
    }
    /** 
    * Get All Trips using ajax
    **/
    public function ajax_trips(Request $request)
    {
        $user = User::find($request->id);

        if(!$user)
            return ['status'=>false];

        if($request->month)
        {
            $data = explode('-', $request->month);
            if($user->user_type == 'Rider')
                $result = Trips::with(['currency','rating'])
                        ->where('user_id',$request->id)
                        ->whereYear('created_at', '=', $data[0])
                        ->whereMonth('created_at', '=', $data[1])->orderBy('created_at', 'desc');
            else
                $result = Trips::with(['currency','rating'])
                        ->where('driver_id',$request->id)
                        ->whereYear('created_at', '=', $data[0])
                        ->whereMonth('created_at', '=', $data[1])->orderBy('created_at', 'desc');

        }
        else
        {
            if($user->user_type == 'Rider')
                $result = Trips::with(['currency','rating'])->where('user_id',$request->id)->orderBy('created_at', 'desc');
            else
                $result = Trips::with(['currency','rating'])->where('driver_id',$request->id)->orderBy('created_at', 'desc');
            
        }
        $result =  $result->paginate(4)->toJson();
        return $result;
    }
    /** 
    * Rider Profile Page
    **/
    public function profile()
    {
        $data['result'] = User::find(Auth::user()->id);
        return view('dashboard.profile',$data);
    }
    /** 
    * Rider Payment Page
    **/
    public function payment()
    {
        return view('dashboard.payment');
    }
    /** 
    * Rider Trip Details Page
    **/
    public function trip_detail(Request $request)
    {
        $data['trip'] = $trip = Trips::find($request->id);

        if(count($data['trip']) > 0 ){
        return view('dashboard.trip_detail',$data);
        }
        else{

        abort("404");
    }
       
    }

    /**
    * Rider Rating
    **/
    public function rider_rating(Request $request)
    {
        $rating = @Rating::where('trip_id',$request->trip_id)->first();
        $trips = Trips::where('id',$request->trip_id)->first();
        if(@Auth::user()->user_type == 'Rider')
        {
            $data = [   

                    'trip_id'       => $request->trip_id,
                    'user_id'       => $trips->user_id,
                    'driver_id'     => $trips->driver_id,
                    'rider_rating'  => $request->rating,
                ];
            $rating = Rating::updateOrCreate(['trip_id' => $request->trip_id], $data);

            Trips::where('id',$request->trip_id)->update(['status'   => 'Payment']);
            return ['success' => 'true','user_rating' => $rating->rider_rating];
        }
        else
        {
            return ['success' => 'false'];
        }
    }
    /***
    * Rider invoice Page
    ***/
    public function trip_invoice(Request $request)
    {
        $trip_id = $request->id; 
        $data['trip'] = Trips::find($trip_id);
        if(!$data['trip']) {
            abort(404);
        }
        return view('dashboard.rider_invoice', $data);
    }

    /***
    * Driver Download invoice Page
    ***/
    public function download_rider_invoice(Request $request)
    {
        $trip_id = $request->id; 
        $data['trip'] = Trips::find($trip_id);
        
        $pdf = PDF::loadView('dashboard.download_rider_invoice', $data);
        return $pdf->download('invoice.pdf');

    }
    /***
    * Print Invoice Page
    ***/
    public function print_invoice(Request $request)
    {
        $trip_id = $request->id; 
        $data['trip'] = Trips::find($trip_id);

        if(count($data['trip']) > 0 ){
            return view('dashboard.print_invoice',$data);
        }
        else{

        abort("404");
    }
 
    }

    /***
    * Update Profile
    ***/
    public function update_profile(Request $request)
    {
        $rules = array(
        'first_name'    => 'required',
        'last_name'     => 'required',
        'email'         => 'required|email',
        'mobile_number' => 'required|numeric|regex:/[0-9]{6}/',
        // 'country_code'  => 'required',
        'profile_image' => 'mimes:jpg,jpeg,png,gif'
        );
       
        $messages = array(
        'required'                => ':attribute '.trans('messages.home.field_is_required').'',
        'mobile_number.regex'   => trans('messages.user.mobile_no'),
        );

       
        $niceNames = array(
                       'first_name' => trans('messages.user.firstname'),
                        'last_name' => trans('messages.user.lastname'),
                        'email' => trans('messages.user.email'),
                        // 'country_code'  => 'Country Code',
                        'mobile_number' => trans('messages.profile.mobile'),
                        'profile_image' => trans('messages.user.profile_image'),
                    );

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->setAttributeNames($niceNames); 
      
        if ($validator->fails()) 
        {
            return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values     
            
        }
        else
        { 
            $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->where('id','!=',$request->id)->get();

            if(count($user_email))
            {
                return back()->withErrors(['email' => trans('messages.user.email_exists')])->withInput(); // Form calling with Errors and Input values
            }

            $user = User::find($request->id);
            
            /*if($request->code) {

                $token_exchange_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/access_token?'.
                'grant_type=authorization_code'.
                '&code='.$request->code.
                "&access_token=AA|".ACCOUNTKIT_APP_ID."|".ACCOUNTKIT_APP_SECRET;
                $data = $this->helper->doCurl($token_exchange_url);

                if(isset($data['error'])) {                    
                    $this->helper->flash_message('danger', $data['error']['message']);
                    return redirect('driver_profile');
                }

                $user_id = $data['id'];
                $user_access_token = $data['access_token'];
                $refresh_interval = $data['token_refresh_interval_sec'];

                // Get Account Kit information
                $me_endpoint_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/me?'.
                'access_token='.$user_access_token;
                $data = $this->helper->doCurl($me_endpoint_url);

                $country_code = $data['phone']['country_prefix'];
                $mobile_number = $data['phone']['national_number'];
                $type ='Driver';

                $check_user = User::where('mobile_number', $mobile_number)->where('user_type', $request->user_type)->where('id','!=',$request->id)->get();

                if(count($check_user)) {
                    return back()->withErrors(['mobile_number' => trans('messages.user.mobile_no_exists')])->withInput();
                }

                $user->mobile_number    = $mobile_number;
                $user->country_code     = $country_code;
            }*/
            $user->mobile_number    = $request->mobile_number;
            $user->country_code     = $request->mobile_country;
            $user->first_name   = $request->first_name;
            $user->last_name    = $request->last_name;
            $user->email        = $request->email;
            $user->save();

            $user_profile_image = ProfilePicture::find($request->id);
            if(!$user_profile_image)
            {
                $user_profile_image = new ProfilePicture;
                $user_profile_image->user_id = $user->id;
            }

            $user_profile_image->photo_source = 'Local';
            $profile_image          =   $request->file('profile_image');
            $path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user->id;
                                
            if(!file_exists($path)) 
            {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user->id, 0777, true);
            }
            if($profile_image)
            { 
                    $profile_image_extension      =   $profile_image->getClientOriginalExtension();
                    $profile_image_filename       =   'profile_image' . time() .  '.' . $profile_image_extension;

                    $success = $profile_image->move('images/users/'.$user->id, $profile_image_filename);
                    if(!$success)
                        return back()->withError('Could not upload profile Image');
                    $user_profile_image->src   =url('images/users').'/'.$user->id.'/'.$profile_image_filename;
                    $user_profile_image->save();
            }
            $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function
            return redirect('profile');
            // return ['status' => 'success','status_message' => 'Updated Successfully','src'=>$user_profile_image->src];   
        }
    }
}