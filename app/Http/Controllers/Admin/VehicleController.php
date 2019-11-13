<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\VehicleDataTable;
use App\Models\User;
use App\Models\Trips;
use App\Models\DriverAddress;
use App\Models\Vehicle;
use App\Models\Country;
use App\Models\CarType;
use App\Models\ProfilePicture;
use App\Models\Company;
use App\Models\DriverDocuments;
use App\Http\Start\Helpers;
use Validator;
use DB;
use Image;
use Auth;

class VehicleController extends Controller
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
    public function index(VehicleDataTable $dataTable)
    {
        return $dataTable->render('admin.vehicle.view');
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
            //Inactive company can not add any vehicle
            if (LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->status!='Active') {
                abort(404);
            }
            $data['country_code_option']=Country::select('long_name','phone_code')->get();
            $data['country_name_option']=Country::pluck('long_name', 'short_name');
            $data['company']=Company::where('status','Active')->pluck('name','id');
            $data['car_type']=CarType::where('status','Active')->pluck('car_name', 'id');
            return view('admin.vehicle.add',$data);

        }
        else if($request->submit)
        {


            // Add Driver Validation Rules
            $rules = array(
                    'driver_name'        => 'required',
                    'status'        => 'required',
                    'insurance'     => 'required|mimes:jpg,jpeg,png,gif',
                    'rc'            => 'required|mimes:jpg,jpeg,png,gif',
                    'permit'        => 'required|mimes:jpg,jpeg,png,gif',
                    'vehicle_id'    => 'required',
                    'vehicle_name'  => 'required',
                    'vehicle_number'=> 'required',
                    );

                if (LOGIN_USER_TYPE!='company') {
                    $rules['company_name'] = 'required';
                }

            // Add Driver Validation Custom Names
            $niceNames = array(
                        'status'        => trans('messages.driver_dashboard.status'),
                        'vehicle_id'    => trans('messages.user.veh_type'),
                        'insurance'     => trans('messages.driver_dashboard.motor_insurance'),
                        'rc'            => trans('messages.driver_dashboard.reg_certificate'),
                        'permit'        => trans('messages.driver_dashboard.carriage_permit'),
                        );
                // Edit Rider Validation Custom Fields message
            $messages =array(
                        'required'            => ':attribute is required.',
                        'mobile_number.regex' => trans('messages.user.mobile_no'),
                        );
            $validator = Validator::make($request->all(), $rules,$messages);

            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {

             //Documents
                $license_front          =   $request->file('license_front');
                $license_back           =   $request->file('license_back');
                $insurance              =   $request->file('insurance');
                $rc                     =   $request->file('rc');
                $permit                 =   $request->file('permit');

                $vehicle = new Vehicle;
                $vehicle->user_id       =   $request->driver_name;
                if (LOGIN_USER_TYPE!='company') {
                    $vehicle->company_id       =   $request->company_name;
                }else{
                    $vehicle->company_id       =   Auth::guard('company')->user()->id;
                }
                $vehicle->status       =   $request->status;
                $vehicle->save();

                $driver_doc = DriverDocuments::where('user_id', $vehicle->user_id)->first();
                if ($driver_doc == null) {
                    $driver_doc = new DriverDocuments;
                    $driver_doc->user_id = $vehicle->user_id;
                    $driver_doc->document_count = 0;
                    $driver_doc->save();
                }

                $path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/vehicle/'.$vehicle->id;
                                
                if(!file_exists($path)) {
                    mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/vehicle/'.$vehicle->id, 0777, true);
                }
                 

            //insurance
                if($insurance){ 
                    $insurance_extension      =   $insurance->getClientOriginalExtension();
                    $insurance_filename       =   'insurance' . time() . '.' . $insurance_extension;

                     $success = $insurance->move('images/vehicle/'.$vehicle->id, $insurance_filename);

                    if(!$success)
                        return back()->withError(trans('messages.user.insurance_image'));
                     $vehicle->insurance       =url('images/vehicle').'/'.$vehicle->id.'/'.$insurance_filename;
                }

            //Rc book
                  if($rc)
                { 
                    $rc_extension      =   $rc->getClientOriginalExtension();
                    $rc_filename       =   'rc' . time() .  '.' . $rc_extension;

                    $success = $rc->move('images/vehicle/'.$vehicle->id, $rc_filename);
        
                    if(!$success)
                        return back()->withError(trans('messages.user.rc_image'));
                    $vehicle->rc              =url('images/vehicle').'/'.$vehicle->id.'/'.$rc_filename;
                }
         //Permit
                 if($permit)
                { 
                    $permit_extension      =   $permit->getClientOriginalExtension();
                    $permit_filename       =   'permit' . time() .  '.' . $permit_extension;

                    $success = $permit->move('images/vehicle/'.$vehicle->id, $permit_filename);
        
                    if(!$success)
                        return back()->withError(trans('messages.user.permit_image'));
                 $vehicle->permit          =url('images/vehicle').'/'.$vehicle->id.'/'.$permit_filename;

                }

                 
                $vehicle->vehicle_id      = $request->vehicle_id;
                $vehicle->vehicle_name    = $request->vehicle_name;
                $vehicle->vehicle_number  = $request->vehicle_number;
                $vehicle->vehicle_type    = CarType::find($request->vehicle_id)->car_name;    
                $vehicle->save();
               
                $this->helper->flash_message('success', trans('messages.user.add_success')); // Call flash message function

                return redirect(LOGIN_USER_TYPE.'/vehicle');  //redirect depends on login user is admin or company
            }
        }
        else
        {
            return redirect(LOGIN_USER_TYPE.'/vehicle');  //redirect depends on login user is admin or company
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
            $data['result']             = Vehicle::find($request->id);

            if($data['result'] && (LOGIN_USER_TYPE!='company' || Auth::guard('company')->user()->id == $data['result']->company_id))
            {
                $data['country_code_option']=Country::select('long_name','phone_code')->get();
                $data['car_type']           = CarType::where('status','Active')->pluck('car_name', 'id');
                $data['company']=Company::where('status','Active')->pluck('name','id');
                $data['path']               = url('images/users/'.$request->id);
                return view('admin.vehicle.edit', $data);
            }
            else
            {
                $this->helper->flash_message('danger', 'Invalid ID'); // Call flash message function
                return redirect(LOGIN_USER_TYPE.'/vehicle');  //redirect depends on login user is admin or company
            }

        }
        else if($request->submit)
        {

            // Edit Driver Validation Rules
            $rules = array(
                    'status'        => 'required',
                    'insurance'     => 'mimes:jpg,jpeg,png,gif',
                    'rc'            => 'mimes:jpg,jpeg,png,gif',
                    'permit'        => 'mimes:jpg,jpeg,png,gif',
                    'vehicle_id'    => 'required',
                    'vehicle_name'  => 'required',
                    'vehicle_number'=> 'required',
                    );

                if (LOGIN_USER_TYPE!='company') {
                    $rules['company_name'] = 'required';
                }


            // Edit Driver Validation Custom Fields Name
            $niceNames = array(
                        'status'        => trans('messages.driver_dashboard.status'),
                        'insurance'     => trans('messages.user.insurance'),
                        'rc'            => trans('messages.user.rc_book'),
                        'permit'        => trans('messages.user.permit'),
                        'vehicle_id'    => trans('messages.user.veh_type'),
                        'vehicle_name'  => trans('messages.user.veh_name'),
                        'vehicle_number'=> trans('messages.user.veh_no'),
                        'insurance'     => trans('messages.user.motor_insurance'),
                        'rc'            => trans('messages.user.reg_certificate'),
                        'permit'        => trans('messages.user.carriage_permit'),
                        );
             // Edit Rider Validation Custom Fields message
            $messages =array(
                        'required'            => ':attribute is required.',
                        'mobile_number.regex' => trans('messages.user.mobile_no'),
                        );

            $validator = Validator::make($request->all(), $rules,$messages);
            $validator->setAttributeNames($niceNames); 
            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {

                $country_code = $request->country_code;

                $insurance              =   $request->file('insurance');
                $rc                     =   $request->file('rc');
                $permit                 =   $request->file('permit');

                $path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/vehicle/'.$request->id;
                                
                if(!file_exists($path)) {
                    mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/vehicle/'.$request->id, 0777, true);
                }
                $vehicle = Vehicle::find($request->id);
                $vehicle->user_id       =   $request->driver_name;
                

                //If login user is company then company id is logined company id
                if (LOGIN_USER_TYPE!='company') {
                    $vehicle->company_id       =   $request->company_name;
                }else{
                    $vehicle->company_id       =   Auth::guard('company')->user()->id;
                }
                $vehicle->status        =   $request->status;

                //insurance
                if($insurance){ 
                    $insurance_extension      =   $insurance->getClientOriginalExtension();
                    $insurance_filename       =   'insurance' . time() . '.' . $insurance_extension;

                     $success = $insurance->move('images/vehicle/'.$request->id, $insurance_filename);

                    if(!$success)
                        return back()->withError('Could not upload Insurance Image');
                     $vehicle->insurance       =url('images/vehicle').'/'.$vehicle->id.'/'.$insurance_filename;
                }

                //Rc book
                if($rc){ 
                    $rc_extension      =   $rc->getClientOriginalExtension();
                    $rc_filename       =   'rc' . time() .  '.' . $rc_extension;

                    $success = $rc->move('images/vehicle/'.$request->id, $rc_filename);
        
                    if(!$success)
                        return back()->withError('Could not upload Rc book Image');
                    $vehicle->rc              =url('images/vehicle').'/'.$vehicle->id.'/'.$rc_filename;
                }

                //Permit
                 if($permit){ 
                    $permit_extension      =   $permit->getClientOriginalExtension();
                    $permit_filename       =   'permit' . time() .  '.' . $permit_extension;

                    $success = $permit->move('images/vehicle/'.$vehicle->id, $permit_filename);
        
                    if(!$success)
                        return back()->withError('Could not upload Permit Image');
                    $vehicle->permit          =url('images/vehicle').'/'.$vehicle->id.'/'.$permit_filename;

                }

                 
                $vehicle->vehicle_id      = $request->vehicle_id;
                $vehicle->vehicle_name    = $request->vehicle_name;
                $vehicle->vehicle_number  = $request->vehicle_number;
                $vehicle->vehicle_type    = CarType::find($request->vehicle_id)->car_name;                
                $vehicle->save();
                
                $driver_doc = DriverDocuments::where('user_id', $vehicle->user_id)->first();
                if ($driver_doc == null) {
                    $driver_doc = new DriverDocuments;
                    $driver_doc->user_id = $vehicle->user_id;
                    $driver_doc->document_count = 0;
                    $driver_doc->save();
                }



                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function
                
                return redirect(LOGIN_USER_TYPE.'/vehicle');  //redirect depends on login user is admin or company
            }
        }
        else
        {
            return redirect(LOGIN_USER_TYPE.'/vehicle');  //redirect depends on login user is admin or company
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
        $vehicle = Vehicle::find($request->id);
        //If login user is company then it can edit it's vehicle only
        if($vehicle==null || (LOGIN_USER_TYPE=='company' && $vehicle->company_id != Auth::guard('company')->user()->id)) {
                $this->helper->flash_message('danger', 'Invalid ID'); // Call flash message function
                return redirect(LOGIN_USER_TYPE.'/vehicle'); //redirect depends on login user is admin or company
        }

        $vehicle->delete();
        $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function
        return redirect(LOGIN_USER_TYPE.'/vehicle');  //redirect depends on login user is admin or company
    }

    /**
     * get Driver
     *
     * @param array $request    Input values
     * @return redirect     to Driver View
     */
    public function get_driver(Request $request,$company_id)
    {  
        $drivers=User::select('id','first_name','last_name')->whereNotIn('status',['Inactive'])
            ->where('user_type','Driver')
            ->where('company_id',$company_id)
            ->where(function($query) use ($request)  {
                $query->whereDoesntHave('vehicle')
                ->orWhereHas('vehicle',function($q) use ($request){
                    $q->where('id',$request->vehicle_id);
                });
            })
            ->get();

        return response()->json([
                        'drivers' =>$drivers,
                        'status_code' => '1',
                    ]);
        
    }

}
