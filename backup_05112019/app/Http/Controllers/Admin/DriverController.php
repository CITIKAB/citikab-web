<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\DriverDataTable;
use App\Models\User;
use App\Models\Trips;
use App\Models\DriverAddress;
use App\Models\DriverDocuments;
use App\Models\Country;
use App\Models\CarType;
use App\Models\ProfilePicture;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\BankDetail;
use App\Http\Start\Helpers;
use Validator;
use DB;
use Image;
use Auth;

class DriverController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Driver
     *
     * @param array $dataTable  Instance of Driver DataTable
     * @return datatable
     */
    public function index(DriverDataTable $dataTable)
    {
        return $dataTable->render('admin.driver.view');
    }

    /**
     * Add a New Driver
     *
     * @param array $request  Input values
     * @return redirect     to Driver view
     */
    public function add(Request $request)
    {
        if(!$_POST)
        {
            //Inactive Company could not add driver
            if (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->status!='Active') {
                abort(404);
            }
            $data['country_code_option']=Country::select('long_name','phone_code')->get();
            $data['country_name_option']=Country::pluck('long_name', 'short_name');
            $data['company']=Company::where('status','Active')->pluck('name','id');
            return view('admin.driver.add',$data);

        }
        else if($request->submit)
        {
            // Add Driver Validation Rules
            $rules = array(
                    'first_name'    => 'required',
                    'last_name'     => 'required',
                    'email'         => 'required|email',
                    'mobile_number' => 'required|regex:/[0-9]{6}/',
                    'password'      => 'required',
                    'country_code'  => 'required',
                    'user_type'     => 'required',
                
                    'status'        => 'required',
                    'license_front' => 'required|mimes:jpg,jpeg,png,gif',
                    'license_back'  => 'required|mimes:jpg,jpeg,png,gif',
                    );
            
            //Bank details are required only for company drivers & Not required for Admin drivers
            if ((LOGIN_USER_TYPE!='company' && $request->company_name != 1) || (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->id!=1)) {
                $rules['account_holder_name'] = 'required';
                $rules['account_number'] = 'required';
                $rules['bank_name'] = 'required';
                $rules['bank_location'] = 'required';
                $rules['bank_code'] = 'required';
            }

                if (LOGIN_USER_TYPE!='company') {
                    $rules['company_name'] = 'required';
                }

            // Add Driver Validation Custom Names
            $niceNames = array(
                        'first_name'    => trans('messages.user.firstname'),
                        'last_name'     => trans('messages.user.lastname'),
                        'email'         => trans('messages.user.email'),
                        'password'      => trans('messages.user.paswrd'),
                        'country_code'  => trans('messages.user.country_code'),
                        'user_type'     => trans('messages.user.user_type'),
                        'status'        => trans('messages.driver_dashboard.status'),
                        'license_front' => trans('messages.driver_dashboard.driver_license_front'),
                        'license_back'  => trans('messages.driver_dashboard.driver_license_back'),
                        'account_holder_name'  => 'Account Holder Name',
                        'account_number'  => 'Account Number',
                        'bank_name'  => 'Name of Bank',
                        'bank_location'  => 'Bank Location',
                        'bank_code'  => 'BIC/SWIFT Code',
                        );
                // Edit Rider Validation Custom Fields message
            $messages =array(
                        'required'            => ':attribute is required.',
                        'mobile_number.regex' => trans('messages.user.mobile_no'),
                        );
            $validator = Validator::make($request->all(), $rules,$messages);

            $validator->after(function ($validator) use($request) {
                $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->get();

                $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->get();

                if(count($user))
                {
                   $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                }

                if(count($user_email))
                {
                   $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
            });
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                
                $user = new User;

                $user->first_name   = $request->first_name;
                $user->last_name    = $request->last_name;
                $user->email        = $request->email;
                $user->country_code = $request->country_code;
                $user->mobile_number= $request->mobile_number;
                $user->password     = bcrypt($request->password);
                $user->status       = $request->status;
                $user->user_type    = $request->user_type;
                $user->status       = $request->status;
                if (LOGIN_USER_TYPE=='company') {
                    $user->company_id       = Auth::guard('company')->user()->id;
                }else {
                    $user->company_id       = $request->company_name;
                }
                $user->save();

                $user_pic = new ProfilePicture;

                $user_pic->user_id      =   $user->id;
                $user_pic->src          =   "";
                $user_pic->photo_source =   'Local';

                $user_pic->save();

                $user_address = new DriverAddress;

                $user_address->user_id       =   $user->id;
                $user_address->address_line1 =   $request->address_line1 ? $request->address_line1 :'';
                $user_address->address_line2 =   $request->address_line2 ? $request->address_line2:'';
                $user_address->city          =   $request->city ? $request->city:'';
                $user_address->state         =   $request->state ? $request->state:'';
                $user_address->postal_code   =   $request->postal_code ? $request->postal_code:'';

                $user_address->save();

                if ($user->company_id != null && $user->company_id != 1) {
                    $bank_detail = new BankDetail;
                    $bank_detail->user_id = $user->id;
                    $bank_detail->holder_name = $request->account_holder_name;
                    $bank_detail->account_number = $request->account_number;
                    $bank_detail->bank_name = $request->bank_name;
                    $bank_detail->bank_location = $request->bank_location;
                    $bank_detail->code = $request->bank_code;
                    $bank_detail->save();
                }



             //Documents
                $license_front          =   $request->file('license_front');
                $license_back           =   $request->file('license_back');


                $path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user->id;
                                
            if(!file_exists($path)) {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user->id, 0777, true);
            }
                 $user_doc = new DriverDocuments;
                 $user_doc->user_id       =   $user->id;

             //licence front
            if($license_front)
                { 
                    $license_front_extension      =   $license_front->getClientOriginalExtension();
                    $license_front_filename       =   'license_front' . time() .  '.' . $license_front_extension;

                    $success = $license_front->move('images/users/'.$user->id, $license_front_filename);
                    if(!$success)
                        return back()->withError(trans('messages.user.license_image'));
                    $user_doc->license_front   =url('images/users').'/'.$user->id.'/'.$license_front_filename;
                }

                //license back
                 if($license_back)
                { 
                    $license_back_extension      =   $license_back->getClientOriginalExtension();
                    $license_back_filename       =   'license_back' . time() .  '.' . $license_back_extension;

                    $success = $license_back->move('images/users/'.$user->id, $license_back_filename);
        
                    if(!$success)
                        return back()->withError(trans('messages.user.license_image'));
                    $user_doc->license_back    =url('images/users').'/'.$user->id.'/'.$license_back_filename;
                }
             
                $user_doc->save();
               
                $this->helper->flash_message('success', trans('messages.user.add_success')); // Call flash message function

                return redirect(LOGIN_USER_TYPE.'/driver');  //redirect depends on login user is admin or company
            }
        }
        else
        {
            return redirect(LOGIN_USER_TYPE.'/driver'); //redirect depends on login user is admin or company
        }
    }

    /**
     * Update Driver Details
     *
     * @param array $request    Input values
     * @return redirect     to Driver View
     */
    public function update(Request $request)
    {
        if(!$_POST)
        {
            $data['result']             = User::find($request->id);

            //If login user is company then company can edit only that company's driver details

            if($data['result'] && (LOGIN_USER_TYPE!='company' || Auth::guard('company')->user()->id == $data['result']->company_id))
            {
                $data['address']            = DriverAddress::where('user_id',$request->id)->first();
                $data['driver_documents']   = DriverDocuments::where('user_id',$request->id)->first();
                $data['country_code_option']=Country::select('long_name','phone_code')->get();
                $data['company']=Company::where('status','Active')->pluck('name','id');
                $data['path']               = url('images/users/'.$request->id);
                return view('admin.driver.edit', $data);
            }
            else
            {
                $this->helper->flash_message('danger', 'Invalid ID'); // Call flash message function
                return redirect(LOGIN_USER_TYPE.'/driver');  //redirect depends on login user is admin or company
            }

        }
        else if($request->submit)
        {

            // Edit Driver Validation Rules
            $rules = array(
                    'first_name'    => 'required',
                    'last_name'     => 'required',
                    'email'         => 'required|email',
                    'status'        => 'required',
                    // 'mobile_number' => 'required|regex:/[0-9]{6}/',
                    'country_code'  => 'required',
                    'license_front' => 'mimes:jpg,jpeg,png,gif',
                    'license_back'  => 'mimes:jpg,jpeg,png,gif',
                    );

            //Bank details are updated only for company's drivers.
            if ((LOGIN_USER_TYPE!='company' && $request->company_name != 1) || (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->id!=1)) {
                $rules['account_holder_name'] = 'required';
                $rules['account_number'] = 'required';
                $rules['bank_name'] = 'required';
                $rules['bank_location'] = 'required';
                $rules['bank_code'] = 'required';
            }

                if (LOGIN_USER_TYPE!='company') {
                    $rules['company_name'] = 'required';
                }


            // Edit Driver Validation Custom Fields Name
            $niceNames = array(
                        'first_name'    => trans('messages.user.firstname'),
                        'last_name'     => trans('messages.user.lastname'),
                        'email'         => trans('messages.user.email'),
                        'status'        => trans('messages.driver_dashboard.status'),
                        'mobile_number' => trans('messages.profile.phone'),
                        'country_ode'   => trans('messages.user.country_code'),
                        'license_front' => trans('messages.signup.license_front'),
                        'license_back'  => trans('messages.signup.license_back'),
                        'license_front' => trans('messages.user.driver_license_front'),
                        'license_back'  => trans('messages.user.driver_license_back'),
                        'account_holder_name'  => 'Account Holder Name',
                        'account_number'  => 'Account Number',
                        'bank_name'  => 'Name of Bank',
                        'bank_location'  => 'Bank Location',
                        'bank_code'  => 'BIC/SWIFT Code',
                        );
             // Edit Rider Validation Custom Fields message
            $messages =array(
                        'required'            => ':attribute is required.',
                        'mobile_number.regex' => trans('messages.user.mobile_no'),
                        );

            $validator = Validator::make($request->all(), $rules,$messages);
              if($request->mobile_number!="")
              {
                 $validator->after(function ($validator) use($request) {
                $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->where('id','!=', $request->id)->get();


                if(count($user))
                {
                   $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                }

                 });


              }
           
                $validator->after(function ($validator) use($request) {
                $user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->where('id','!=', $request->id)->get();


                if(count($user_email))
                {
                $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
                });

            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {

                $country_code = $request->country_code;

                $user = User::find($request->id);

                $user->first_name   = $request->first_name;
                $user->last_name    = $request->last_name;
                $user->email        = $request->email;
                $user->status       = $request->status;
                $user->country_code = $country_code;
                 if($request->mobile_number!="")
                $user->mobile_number= $request->mobile_number;
                $user->user_type    = $request->user_type;
             

                if($request->password != '')
                    $user->password = bcrypt($request->password);

                if (LOGIN_USER_TYPE=='company') {
                    $user->company_id       = Auth::guard('company')->user()->id;
                }else{
                    $user->company_id       = $request->company_name;
                }

                Vehicle::where('user_id',$user->id)->update(['company_id'=>$user->company_id]);

                $user->save();

                $user_address = DriverAddress::where('user_id',  $user->id)->first();
                if(count($user_address)==0)
                $user_address                =   new DriverAddress;
                $user_address->user_id       =   $user->id;
                $user_address->address_line1 =   $request->address_line1;
                $user_address->address_line2 =   $request->address_line2;
                $user_address->city          =   $request->city;
                $user_address->state         =   $request->state;
                $user_address->postal_code   =   $request->postal_code;
                $user_address->save();

                if ($user->company_id != null && $user->company_id != 1) {
                    $bank_detail = BankDetail::where('user_id',$user->id)->first();
                    if ($bank_detail==null) {
                        $bank_detail = new BankDetail;
                    }
                    $bank_detail->user_id = $user->id;
                    $bank_detail->holder_name = $request->account_holder_name;
                    $bank_detail->account_number = $request->account_number;
                    $bank_detail->bank_name = $request->bank_name;
                    $bank_detail->bank_location = $request->bank_location;
                    $bank_detail->code = $request->bank_code;
                    $bank_detail->save();
                }else{
                    BankDetail::where('user_id',$user->id)->delete();
                }

                //Documents
                $license_front          =   $request->file('license_front');
                $license_back           =   $request->file('license_back');
                $insurance              =   $request->file('insurance');
                $rc                     =   $request->file('rc');
                $permit                 =   $request->file('permit');

       
                    
            
            $path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$request->id;
                                
            if(!file_exists($path)) {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$request->id, 0777, true);
            }
                 $user_doc = DriverDocuments::where('user_id',  $user->id)->first();
                  if(count($user_doc)==0)
                    $user_doc                   =   new DriverDocuments;
             //licence front
            if($license_front)
                { 
                    $license_front_extension      =   $license_front->getClientOriginalExtension();
                    $license_front_filename       =   'license_front' . time() .  '.' . $license_front_extension;

                    $success = $license_front->move('images/users/'.$request->id, $license_front_filename);
                    if(!$success)
                        return back()->withError('Could not upload license Image');
                    $user_doc->license_front   =url('images/users').'/'.$user->id.'/'.$license_front_filename;

                    
                }

                //license back
                 if($license_back)
                { 
                    $license_back_extension      =   $license_back->getClientOriginalExtension();
                    $license_back_filename       =   'license_back' . time() .  '.' . $license_back_extension;

                    $success = $license_back->move('images/users/'.$request->id, $license_back_filename);
        
                    if(!$success)
                        return back()->withError('Could not upload license Image');
                    $user_doc->license_back    =url('images/users').'/'.$user->id.'/'.$license_back_filename;
                }

                 
                $user_doc->user_id      = $user->id;                
                $user_doc->save();




                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function
                
                return redirect(LOGIN_USER_TYPE.'/driver');  //redirect depends on login user is admin or company
            }
        }
        else
        {
            return redirect(LOGIN_USER_TYPE.'/driver');  //redirect depends on login user is admin or company
        }
    }

    /**
     * Delete Driver
     *
     * @param array $request    Input values
     * @return redirect     to Driver View
     */
    public function delete(Request $request)
    {    
        //Company can delete only this company's drivers.
        if(LOGIN_USER_TYPE=='company'){
            $user = User::find($request->id);
            if ($user->company_id != Auth::guard('company')->user()->id) {
                $this->helper->flash_message('danger', 'Invalid ID'); // Call flash message function
                return redirect(LOGIN_USER_TYPE.'/driver');  //redirect depends on login user is admin or company
            }
        }

        $driver_trips=Trips::where('driver_id',$request->id)->get()->count();
        if($driver_trips)
        {
            $this->helper->flash_message('danger', 'Driver have some trips, So can\'t delete this driver'); // Call flash message function
            return redirect(LOGIN_USER_TYPE.'/driver');  //redirect depends on login user is admin or company
        }
        else
        {
            User::find($request->id)->delete();
            $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function
            return redirect(LOGIN_USER_TYPE.'/driver');  //redirect depends on login user is admin or company
        }
    }


}
