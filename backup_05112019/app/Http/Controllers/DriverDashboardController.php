<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\User;
use App\Models\Trips;
use App\Models\DriverAddress;
use App\Models\Request as RideRequest;  
use App\Models\ProfilePicture;
use App\Models\DriverDocuments;
use App\Models\Vehicle;
use Auth;
use App;
use DB;
use Validator;
use PDF;
use App\Http\Start\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller as BaseController;
use session;

class DriverDashboardController extends BaseController
{
    protected $helper; 
    public function __construct()
    {
        $this->helper = new Helpers;
    }
    /**
    * Driver Profile
    **/
	public function driver_profile()
    {
         Session::forget('Account_kit');
        $data['result'] = User::find(@Auth::user()->id);
        return view('driver_dashboard.driver_profile',$data);
    }
    /***
    * Driver Download invoice Page
    ***/
    public function download_invoice(Request $request)
    {
        $trip_id = $request->id; 
        $data['trip'] = Trips::find($trip_id);
        
        $pdf = PDF::loadView('dashboard.download_invoice', $data);
        return $pdf->download('invoice.pdf');

    }
    /***
    * Driver print invoice Page
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
    /**
    *    Driver Profile update
    **/
    public function driver_update_profile(Request $request)
    {
       
        $rules = array(
        'email'             => 'required|email',
        'mobile_number'     => 'required|numeric|regex:/[0-9]{6}/',
        // 'address_line1'     => 'required',
        // 'city'              => 'required',
        // 'postal_code'       => 'required',
        'profile_image'     => 'mimes:jpg,jpeg,png,gif'

        );
       
        $messages = array(
        'required'                => ':attribute '.trans('messages.home.field_is_required').'',
        'mobile_number.regex'   => trans('messages.user.mobile_no'),
        );

       
        $niceNames = array(
                        'email'         => trans('messages.user.email'),
                        'mobile_number' => trans('messages.profile.phone'),
                        // 'address_line1' => 'Address',
                        // 'city'          => 'City',
                        // 'postal_code'   => 'Postal Code',
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

            $user       = User::find($request->id);

            if($request->code) {

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
            }

            $user->email            = $request->email;
            $user->save();

            $driver_address = DriverAddress::where('user_id',$user->id)->first();
            if(!$driver_address)
            {
                $driver_address = new DriverAddress;
                $driver_address->user_id = $user->id;
            }
            $driver_address->city = $request->city ? $request->city : '';
            $driver_address->address_line1 = $request->address_line1 ? $request->address_line1 : '';
            $driver_address->address_line2 = $request->address_line2 ? $request->address_line2 : '';
            $driver_address->state = $request->state ? $request->state : '';
            $driver_address->postal_code = $request->postal_code ? $request->postal_code : '';
            $driver_address->save();

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
                        return back()->withError(trans('messages.user.license_image'));
                    $user_profile_image->src   =url('images/users').'/'.$user->id.'/'.$profile_image_filename;
                    $user_profile_image->save();
            }

            $this->helper->flash_message('success', trans('messages.user.update_success')); // Call flash message function
            return redirect('driver_profile');

            // return ['status' => 'success','status_message' => 'Updated Successfully','src'=>$user_profile_image->src];   

        }
    }
    /**
    *    Profile upload
    **/
    public function profile_upload(Request $request)
    {

        $errors    = array();

            $acceptable = array(
                  'image/jpeg',
                  'image/jpg',
                  'image/gif',
                  'image/png'
                    );

            if((!in_array($_FILES['file']['type'], $acceptable)) && (!empty($_FILES['file']["type"]))) 
            {
                return ['success' => 'false','status_message' => 'Invalid file type. Only  JPG, GIF and PNG types are accepted.'];            

            }
        $user = User::find(@Auth::user()->id);
        $user_profile_image = ProfilePicture::find($user->id);
        if(!$user_profile_image)
        {
            $user_profile_image = new ProfilePicture;
            $user_profile_image->user_id = $user->id;
        }

            $user_profile_image->photo_source = 'Local';
            $profile_image          =   $request->file('file');
            $path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user->id;
                                
            if(!file_exists($path)) 
            {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user->id, 0777, true);
            }
            if($profile_image)
            { 
                    $result['success'] = 'true';
                    $profile_image_extension      =   $profile_image->getClientOriginalExtension();
                    $profile_image_filename       =   'profile_image' . time() .  '.' . $profile_image_extension;
                    $profile_image_save_filename       =   'profile_image' . time() .  '_450x250.' . $profile_image_extension;
                    $success = $profile_image->move('images/users/'.$user->id, $profile_image_filename);
                    if(!$success)
                    {
                        return back()->withError(trans('messages.user.update_fail'));
                    }
                    else
                    {
                        $this->helper->compress_image("images/users/".$user->id."/".$profile_image_filename, "images/users/".$user->id."/".$profile_image_filename, 80, 450, 250);
                    }
                    $user_profile_image->src   =url('images/users').'/'.$user->id.'/'.$profile_image_save_filename;
                    $user_profile_image->save();
            }

        return ['success' => 'true','profile_url' => $user_profile_image->src,'status_message'=>'Uploaded Successfully'];
    }
    public function documents(Request $request)
    {
        $data['user'] = User::find(@Auth::user()->id);
        return view('driver_dashboard.documents',$data);
    }
    /**
    *    Driver document upload
    **/
    public function document_upload(Request $request)
    {
        
            $errors    = array();

            $acceptable = array(
                  'image/jpeg',
                  'image/jpg',
                  'image/gif',
                  'image/png'
                    );

            if($_FILES[$request->document_type]['name'] == "") {
                return ['status' => 'false','status_message' => trans('validation.required', ['attribute' => 'File'])];               
            }
            
            if((!in_array($_FILES[$request->document_type]['type'], $acceptable)) && (!empty($_FILES[$request->document_type]["type"]))) 
            {
                return ['status' => 'false','status_message' => trans('messages.user.invalid_file_type')];            

            }

            $user_id = $request->id;
            $user_details =  User::find($user_id);
             
            $document_type = $request->document_type;
            $file_name = time().'_'.$_FILES[$request->document_type]['name'];
            $type      = pathinfo($file_name, PATHINFO_EXTENSION);

            $file_tmp  = $_FILES[$request->document_type]['tmp_name'];

                
            $dir_name = dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user_id;        
            $f_name   = dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user_id.'/'.$file_name;
            //check file directory is created or not

            if(!file_exists($dir_name))
            {   //create file directory
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user_id, 0777, true);
            }
            //upload image from temp_file  to server file
            if(move_uploaded_file($file_tmp,$f_name))
            {

            } 

            $b_name           = basename($file_name,'.'.$type);
            $normal           = url('/').'/images/users/'.$user_id.'/'.$file_name;

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
            Vehicle::where('id', $user_id)->update(['status' => 'Inactive']);
                             
            return ['status' => 'true'];
    }
    /**
    *    return add vehicle page
    **/
    public function add_vehicle(){
        return view('driver_dashboard.add_vehicle');
    }
    /**
    * Driver payment page
    **/
    public function driver_payment()
    {
        $data['total_earnings'] = Trips::where('driver_id',@Auth::user()->id)
                     ->where('status','Completed')
                     ->get()->sum('driver_payout');
        $total_count = RideRequest::where('driver_id',@Auth::user()->id)->count();
        $acceptance_count = RideRequest::where('driver_id',@Auth::user()->id)->where('status','Accepted')->count();
        if($acceptance_count != '0' || $total_count != '0')
            $data['acceptance_rate'] = round(($acceptance_count/$total_count)*100).'%';
        else
            $data['acceptance_rate'] = '0%';
        $data['completed_trips'] = Trips::where('driver_id',@Auth::user()->id)->where('status','Completed')->count();
        $data['cancelled_trips'] = Trips::where('driver_id',@Auth::user()->id)->where('status','Cancelled')->count();
        $data['all_trips'] = Trips::with(['currency'])->where('driver_id',@Auth::user()->id)->orderBy('created_at', 'desc');
        $data['all_trips'] = $data['all_trips']->paginate(4)->toJson();
        return view('driver_dashboard.driver_payment',$data);
    }
    /**
    * Driver invoice page
    **/
    public function driver_invoice(Request $request)
    {

        $data['trip'] = Trips::find($request->id);
        $data['all_invoice'] = 'false';
          if(count($data['trip']) > 0 ){
         return view('driver_dashboard.driver_invoice',$data);
    }
    else{

       abort("404");
    }

    }
    /**
    * Show all trips
    **/
    public function show_invoice(Request $request)
    {
        if($request->limit == 'undefined')
            return ['status' => false];

        if($request->limit)
        {
            $data = Trips::where('driver_id',@Auth::user()->id)->with(['currency'])->orderBy('created_at', 'desc')->paginate($request->limit);
            return $data;
        }
        $data['trips'] = Trips::where('driver_id',@Auth::user()->id)->with(['currency'])->orderBy('created_at', 'desc')->paginate(10)->toJson();
        $data['all_invoice'] = 'true';
        return view('driver_dashboard.driver_invoice',$data);
    }

    public function driver_banking()
    {
        return view('driver_dashboard.driver_banking');
    }
    /**
    * show All Driver Trips 
    **/
    public function driver_trip()
    {
        $trip = Trips::with(['currency','rating'])->where('driver_id',@Auth::user()->id)->orderBy('created_at', 'desc');

        $data['trips'] = $trip->paginate(4)->toJson();
        return view('driver_dashboard.driver_trip',$data);
    }
    /**
    * Driver Trip Details
    **/
    public function driver_trip_detail(Request $request)
    {
        $data['trip'] = $trip = Trips::find($request->id);
        if(count($data['trip']) > 0 ){
        return view('driver_dashboard.driver_trip_detail',$data); 
    }
    else{
        abort("404");
    }

    }
    /**
    * Get payment information
    **/
    public function ajax_payment(Request $request)    
    {
        if($request->data == 'all')
        {
            $data['completed_trips'] = Trips::where('driver_id',@Auth::user()->id)
                                ->where('status','Completed')
                                ->count();
            $data['cancelled_trips'] = Trips::where('driver_id',@Auth::user()->id)
                                ->where('status','Cancelled')
                                ->count();

            return $data;
        }
        elseif($request->data == 'current')
        {
            $from = date('Y-m-d');
            $to   = date('Y-m-d');
            $data['completed_trips'] = Trips::where('driver_id',@Auth::user()->id)
                                ->where('status','Completed')
                                ->where('created_at','>=',$from)
                                ->where('created_at','<=',$to)
                                ->count();
            $data['cancelled_trips'] = Trips::where('driver_id',@Auth::user()->id)
                                ->where('status','Cancelled')
                                ->where('created_at','>=',$from)
                                ->where('created_at','<=',$to)
                                ->count();
            return $data;
        }
        elseif($request->data == 'all_trips')
        {
            if($request->begin_trip != '' || $request->end_trip != '')
                $data = Trips::with(['currency'])->where('driver_id',@Auth::user()->id)
                        ->where('created_at','>=',$request->begin_trip)
                        ->where('created_at','<=',$request->end_trip)->orderBy('created_at', 'desc');
            else
                $data = Trips::with(['currency'])->where('driver_id',@Auth::user()->id)->orderBy('created_at', 'desc');

            $data =  $data->paginate(4)->toJson();
            return $data;
        }
        elseif($request->data == 'completed_trips')
        {
            if($request->begin_trip != '' || $request->end_trip != '')
            $data = Trips::with(['currency'])->where('driver_id',@Auth::user()->id)
                    ->where('created_at','>=',$request->begin_trip)
                    ->where('created_at','<=',$request->end_trip)
                    ->where('status','Completed')->orderBy('created_at', 'desc');
            else
                $data = Trips::with(['currency'])->where('driver_id',@Auth::user()->id)->where('status','Completed')->orderBy('created_at', 'desc');

            $data =  $data->paginate(4)->toJson();
            return $data;
        }
        elseif($request->data == 'cancelled_trips')
        {   
            if($request->begin_trip != '' || $request->end_trip != '')
                $data = Trips::with(['currency'])->where('driver_id',@Auth::user()->id)
                    ->where('created_at','>=',$request->begin_trip)
                    ->where('created_at','<=',$request->end_trip)
                    ->where('status','Cancelled')->orderBy('created_at', 'desc');
            else
                $data = Trips::with(['currency'])->where('driver_id',@Auth::user()->id)
                    ->where('status','Cancelled')->orderBy('created_at', 'desc');

            $data =  $data->paginate(4)->toJson();
            return $data;
        }
        else
        {
            $date = explode('/', $request->data);
            $from = date('Y-m-d',strtotime($date[0]));
            $to   = date('Y-m-d',strtotime($date[1]));
            $data['completed_trips'] = Trips::where('driver_id',@Auth::user()->id)
                                ->where('status','Completed')
                                ->where('created_at','>=',$from)
                                ->where('created_at','<=',$to)
                                ->count();
            $data['cancelled_trips'] = Trips::where('driver_id',@Auth::user()->id)
                                ->where('status','Cancelled')
                                ->where('created_at','>=',$from)
                                ->where('created_at','<=',$to)
                                ->count();
            return $data;
        }
                            
    }    
}