<?php

/**
 * car Type Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    car Type
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\CarTypeDataTable;
use App\Models\CarType;
use App\Models\Currency;
use App\Models\DriverDocuments;
use App\Models\DriverLocation;
use App\Models\Vehicle;
use App\Http\Start\Helpers;
use Validator;

class CarTypeController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for car Type
     *
     * @param array $dataTable  Instance of CarTypeDataTable
     * @return datatable
     */
    public function index(CarTypeDataTable $dataTable)
    {
        return $dataTable->render('admin.car_type.view');
    }

    /**
     * Add a New car Type
     *
     * @param array $request  Input values
     * @return redirect     to car Type view
     */
    public function add(Request $request)
    {
        if(!$_POST)
        {
            $data['currency']   = Currency::codeSelect();
            return view('admin.car_type.add', $data);
        }
        else if($request->submit)
        {
            // add car Type Validation Rules
            $rules = array(

                    'car_name'      => 'required|unique:car_type,car_name,'.$request->id,
                    'status'   => 'required',
                    'vehicle_image'   => 'required|mimes:jpg,jpeg,png,gif',                
                    'active_image'        => 'required|mimes:jpg,jpeg,png,gif'

                    );

            // add car Type Validation Custom Fields Name
            $niceNames = array(

                        'car_name'      => 'Name',                      
                        'status'        => 'Status',
                        'active_image'  =>'Active image',
                        'vehicle_image'  =>'Vehicle image',
                       
                        );


            $validator = Validator::make($request->all(), $rules);
            $validator->after(function ($validator) use($request) {
                $active_car = CarType::where('status','active')->count();
                if($active_car<=0 && $request->status=='Inactive')
                {
                   $validator->errors()->add('status',"Atleast one vehicle type should be in active status");
                }
            });
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {

                 $icon = $request->file('active_image');
                 $icon2 = $request->file('vehicle_image');

                $car_type = new  CarType;
                $car_type->car_name     = $request->car_name;
                $car_type->description  = $request->description;
                
                $car_type->status       = $request->status; 

                                    //Active Image
                    if($icon)
                    { 
                    $icon_extension      =   $icon->getClientOriginalExtension();
                    $icon_filename       =   'active_image' . time() . '.' . $icon_extension;

                    $success = $icon->move('images/car_image/', $icon_filename);

                    if(!$success)
                    return back()->withError('Could not upload icon Image');
                    $car_type->active_image     = $icon_filename;
                    }     


                   //Imctive Image

                    if($icon2)
                    { 
                    $icon2_extension      =   $icon2->getClientOriginalExtension();
                    $icon2_filename       =   'vehicle_image' . time() . '.' . $icon2_extension;

                    $success = $icon2->move('images/car_image/', $icon2_filename);

                    if(!$success)
                    return back()->withError('Could not upload icon2 Image');
                    $car_type->vehicle_image     = $icon2_filename;
                    }          

            
                $car_type->save();

                $this->helper->flash_message('success', 'Added Successfully'); // Call flash message function

                return redirect('admin/car_type');
            }
        }
        else
        {
            return redirect('admin/car_type');
        }
    }

    /**
     * Update car Type Details
     *
     * @param array $request    Input values
     * @return redirect     to car Type View
     */
    public function update(Request $request)
    {
        if(!$_POST)
        {
            $data['result'] = CarType::find($request->id);
            if($data['result'])
            {
                $data['currency']   = Currency::codeSelect();
                return view('admin.car_type.edit', $data);  
            }
            else
            {
                $this->helper->flash_message('danger', 'Invalid ID'); // Call flash message function
                return redirect('admin/car_type');
            }
            
        }
        else if($request->submit)
        {
            // Edit car Type Validation Rules
               // add car Type Validation Rules
            $rules = array(
                    'car_name'      => 'required|unique:car_type,car_name,'.$request->id,
                    'status'        => 'required',
                    'active_image'   => 'mimes:jpg,jpeg,png,gif',
                    'vehicle_image'   => 'mimes:jpg,jpeg,png,gif',    
                    );


 

            // add car Type Validation Custom Fields Name
            $niceNames = array(
                        'car_name'      => 'Name',
                        'status'        => 'Status',                       
                        );


            $validator = Validator::make($request->all(), $rules);
            $validator->after(function ($validator) use($request) {
                $active_car = CarType::where('status','active')->where('id','!=',$request->id)->count();
                if($active_car<=0 && $request->status=='Inactive')
                {
                   $validator->errors()->add('status',"Atleast one vehicle type should be in active status");
                }
            });
            $validator->setAttributeNames($niceNames); 
            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {

                $icon = $request->file('active_image');
                $icon2 = $request->file('vehicle_image');

                $car_type = CarType::find($request->id);              
                $car_type->car_name     = $request->car_name;
                $car_type->description  = $request->description;                
                $car_type->status       = $request->status; 

                //Active Image
                if($icon)
                { 
                $icon_extension      =   $icon->getClientOriginalExtension();
                $icon_filename       =   'active_image' . time() . '.' . $icon_extension;

                $success = $icon->move('images/car_image/', $icon_filename);

                if(!$success)
                return back()->withError('Could not upload icon Image');
                $car_type->active_image     = $icon_filename;
                }     


               //Inctive Image

                if($icon2)
                { 
                $icon2_extension      =   $icon2->getClientOriginalExtension();
                $icon2_filename       =   'vehicle_image' . time() . '.' . $icon2_extension;

                $success = $icon2->move('images/car_image/', $icon2_filename);

                if(!$success)
                return back()->withError('Could not upload icon2 Image');
                $car_type->vehicle_image     = $icon2_filename;
                } 

                $car_type->save(); 

                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function

                return redirect('admin/car_type');
            }
        }
        else
        {
            return redirect('admin/car_type');
        }
    }




    /**
     * Delete car Type
     *
     * @param array $request    Input values
     * @return redirect     to car Type View
     */
    public function delete(Request $request)
    {
        $driver_location_id = DriverLocation::where('car_id',$request->id)->count();
        $find_vehicle_id = Vehicle::where('vehicle_id',$request->id)->count();
        $active_car = CarType::where('status','active')->where('id','!=',$request->id)->count();
        if($driver_location_id)
            $this->helper->flash_message('danger', "Driver using this Vehicle  type, So can't delete this"); // Call flash message function
        elseif($find_vehicle_id)
            $this->helper->flash_message('danger', "vehicle using this Vehicle type, So can't delete this"); // Call flash message function
        elseif($active_car<=0){
            $this->helper->flash_message('danger', "Atleast one vehicle type should be in active status, So can't delete this");
        }
        else
        { 
            CarType::find($request->id)->delete();
            $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function
        }
        return redirect('admin/car_type');
    }
}
