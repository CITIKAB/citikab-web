<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\CompanyDataTable;
use App\Http\Start\Helpers;
use App\Models\Country;
use App\Models\Company;
use App\Models\CompanyDocuments;
use App\Models\Vehicle;
use App\Models\CompanyPayoutPreference;
use App\Models\CompanyPayoutCredentials;
use App\Models\ScheduleRide;
use Validator;
use DB;
use Image;
use App\Models\User;
use Auth;

class CompanyController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Company
     *
     * @param array $dataTable  Instance of Company DataTable
     * @return datatable
     */
    public function index(CompanyDataTable $dataTable)
    {
        return $dataTable->render('admin.company.view');
    }

    /**
     * Add a New Company
     *
     * @param array $request  Input values
     * @return redirect     to Company view
     */
    public function add(Request $request)
    {
        if(!$_POST)
        {
            $data['country_code_option']=Country::select('long_name','phone_code')->get();
            $data['country_name_option']=Country::pluck('long_name', 'short_name');
            return view('admin.company.add',$data);
        }
        else{
            $rules = array(
                        'name'    => 'required|unique:companies,name,'.$request->id,
                        'email'         => 'required|email',
                        'country_code'  => 'required',
                        'mobile_number' => 'required|regex:/[0-9]{6}/',
                        'status'        => 'required',
                        'password'      => 'required|min:6',
                        'profile' => 'mimes:jpg,jpeg,png',
                        'license'  => 'mimes:jpg,jpeg,png',
                        'insurance'     => 'mimes:jpg,jpeg,png',
                        'license_exp_date'    => 'nullable|after_or_equal:tomorrow',
                        'insurance_exp_date'  => 'nullable|after_or_equal:tomorrow',
                        'address_line'  => 'required',
                        'postal_code'  => 'required',
                        'company_commission' => 'required|numeric|max:100',
                    );

            $niceNames = array(
                        'name'    => 'Name',
                        'email'         => 'Email',
                        'country_code'  => 'Country Code',
                        'mobile_number' => 'Mobile Number',
                        'status'        => 'Status',
                        'password'      => 'Password',
                        'profile' => 'Profile',
                        'license'  => 'License',
                        'insurance'     => 'Insurance',
                        'license_exp_date'    => 'License Expiry Date',
                        'insurance_exp_date'  => 'Insurance Expiry Date',
                        'address_line'  => 'Address Line',
                        'postal_code'  => 'Postal Code',
                        'company_commission' => 'Company Commission',
                    );
            
            $messages =array(
                        'required'            => ':attribute is required.',
                        'mobile_number.regex' => trans('messages.user.mobile_no'),
                        );

            $validator = Validator::make($request->all(), $rules);

            $validator->after(function ($validator) use($request) {
                $company = Company::where('mobile_number', $request->mobile_number)->count();

                $company_email = Company::where('email', $request->email)->count();

                if($company)
                {
                   $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                }

                if($company_email)
                {
                   $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
            });
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }

            $company = new Company;
            $company->name         = $request->name;
            $company->vat_number   = $request->vat_number;
            $company->email        = $request->email;
            $company->country_code = $request->country_code;
            $company->mobile_number= $request->mobile_number;
            $company->password     = bcrypt($request->password);
            $company->status       = $request->status;
            $company->address       = $request->address_line;
            $company->city       = $request->city;
            $company->state       = $request->state;
            $company->country       = $request->country_code;
            $company->postal_code       = $request->postal_code;
            $company->company_commission  = $request->company_commission;
            $company->save();

            $license                =   $request->file('license');
            $insurance              =   $request->file('insurance');
            $profile              =   $request->file('profile');

            $company_doc = new CompanyDocuments;
            $company_doc->company_id=$company->id;
            $company_doc->license_exp_date=$request->license_exp_date;
            $company_doc->insurance_exp_date=$request->insurance_exp_date;

            $path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/companies/'.$company->id;
                            
            if(!file_exists($path)) {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/companies/'.$company->id, 0777, true);
            }

            if($profile)
            { 
                $profile_extension      =   $profile->getClientOriginalExtension();
                $profile_filename       =   'profile' . time() .  '.' . $profile_extension;

                $success = $profile->move('images/companies/'.$company->id, $profile_filename);
                if(!$success)
                    return back()->withError(trans('messages.user.profile_image'));
                $company->profile   =url('images/companies').'/'.$company->id.'/'.$profile_filename;
                $company->save();
            }

            if($license)
            { 
                $license_extension      =   $license->getClientOriginalExtension();
                $license_filename       =   'license' . time() .  '.' . $license_extension;

                $success = $license->move('images/companies/'.$company->id, $license_filename);
                if(!$success)
                    return back()->withError(trans('messages.user.license_image'));
                $company_doc->license_photo   =url('images/companies').'/'.$company->id.'/'.$license_filename;
            }

            //insurance
            if($insurance)
            { 
                $insurance_extension      =   $insurance->getClientOriginalExtension();
                $insurance_filename       =   'insurance' . time() . '.' . $insurance_extension;

                 $success = $insurance->move('images/companies/'.$company->id, $insurance_filename);

                if(!$success)
                    return back()->withError(trans('messages.user.insurance_image'));
                 $company_doc->insurance_photo       =url('images/companies').'/'.$company->id.'/'.$insurance_filename;
            }

            $company_doc->save();
           
            $this->helper->flash_message('success', trans('messages.user.add_success')); // Call flash message function

            return redirect('admin/company');
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
            $data['result']             = Company::find($request->id);

            if (LOGIN_USER_TYPE=='company' && $request->id != Auth::guard('company')->user()->id) {
                abort(404);
            }

            if($data['result'])
            {
                $data['documents']   = CompanyDocuments::where('company_id',$request->id)->first();
                $data['country_code_option']=Country::select('long_name','phone_code')->get();
                $data['path']               = url('images/users/'.$request->id);
                return view('admin.company.edit', $data);
            }
            else
            {
                $this->helper->flash_message('danger', 'Invalid ID'); // Call flash message function
                return redirect(LOGIN_USER_TYPE.'/company');    //redirect depends on company or admin user
            }

        }
        else{

            $rules = array(
                        'name'    => 'required|unique:companies,name,'.$request->id,
                        'email'         => 'required|email',
                        'country_code'  => 'required',
                        'password'      => 'nullable|min:6',
                        'profile' => 'mimes:jpg,jpeg,png',
                        'license'  => 'mimes:jpg,jpeg,png',
                        'insurance'     => 'mimes:jpg,jpeg,png',
                        'license_exp_date'    => 'nullable|after_or_equal:tomorrow',
                        'insurance_exp_date'  => 'nullable|after_or_equal:tomorrow',
                        'address_line'  => 'required',
                        'postal_code'  => 'required',
                    );

            if (LOGIN_USER_TYPE != 'company') {  //Admin only can update status and company commission.Company could not update
                $rules['status'] = 'required';
                if ($request->id != 1) {
                    $rules['company_commission'] = 'required|numeric|max:100';
                }
                $rules['mobile_number'] = 'nullable|regex:/[0-9]{6}/';
            }else{
                $rules['mobile_number'] = 'required|regex:/[0-9]{6}/';
            }

            $niceNames = array(
                        'name'    => 'Name',
                        'email'         => 'Email',
                        'country_code'  => 'Country Code',
                        'mobile_number' => 'Mobile Number',
                        'status'        => 'Status',
                        'password'      => 'Password',
                        'profile' => 'Profile',
                        'license'  => 'License',
                        'insurance'     => 'Insurance',
                        'license_exp_date'    => 'License Expiry Date',
                        'insurance_exp_date'  => 'Insurance Expiry Date',
                        'address_line'  => 'Address Line',
                        'postal_code'  => 'Postal Code',
                        'company_commission' => 'Company Commission',
                    );
            
            $messages =array(
                        'required'            => ':attribute is required.',
                        'mobile_number.regex' => trans('messages.user.mobile_no'),
                        );

            $validator = Validator::make($request->all(), $rules);


            $validator->after(function ($validator) use($request) {

                if ($request->mobile_number != '') {
                    $company = Company::where('mobile_number', $request->mobile_number)->where('id','!=',$request->id)->count();

                    if($company)
                    {
                       $validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists'));
                    }
                }

                $company_email = Company::where('email', $request->email)->where('id','!=',$request->id)->count();

                if($company_email)
                {
                   $validator->errors()->add('email',trans('messages.user.email_exists'));
                }
            });
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }

            $company = Company::find($request->id);
            $company->name         = $request->name;
            $company->vat_number   = $request->vat_number;
            $company->email        = $request->email;
            $company->country_code = $request->country_code;
            if($request->mobile_number!="")
                $company->mobile_number= $request->mobile_number;
            if (isset($request->password)) {
                $company->password     = bcrypt($request->password);
            }
            if (LOGIN_USER_TYPE != 'company') {  //Admin only can update status and company commission.Company could not update
                $company->status       = $request->status;
                $company->company_commission  = $request->company_commission;
            }
            $company->address      = $request->address_line;
            $company->city         = $request->city;
            $company->state        = $request->state;
            $company->country      = $request->country_code;
            $company->postal_code  = $request->postal_code;
            $company->save();

            $license                =   $request->file('license');
            $insurance              =   $request->file('insurance');
            $profile              =   $request->file('profile');

            $company_doc = CompanyDocuments::where('company_id',$company->id)->first();
            if ($company_doc == null) {
                $company_doc = new CompanyDocuments();
            }
            $company_doc->company_id=$company->id;
            $company_doc->license_exp_date=$request->license_exp_date;
            $company_doc->insurance_exp_date=$request->insurance_exp_date;

            $path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/companies/'.$company->id;
                            
            if(!file_exists($path)) {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/companies/'.$company->id, 0777, true);
            }

            if($profile)
            { 
                $profile_extension      =   $profile->getClientOriginalExtension();
                $profile_filename       =   'profile' . time() .  '.' . $profile_extension;

                $success = $profile->move('images/companies/'.$company->id, $profile_filename);
                if(!$success)
                    return back()->withError(trans('messages.user.profile_image'));
                $company->profile   =url('images/companies').'/'.$company->id.'/'.$profile_filename;
                $company->save();
            }

            if($license)
            { 
                $license_extension      =   $license->getClientOriginalExtension();
                $license_filename       =   'license' . time() .  '.' . $license_extension;

                $success = $license->move('images/companies/'.$company->id, $license_filename);
                if(!$success)
                    return back()->withError('Could not upload license Image');
                $company_doc->license_photo   =url('images/companies').'/'.$company->id.'/'.$license_filename;
            }

            //insurance
            if($insurance)
            { 
                $insurance_extension      =   $insurance->getClientOriginalExtension();
                $insurance_filename       =   'insurance' . time() . '.' . $insurance_extension;

                 $success = $insurance->move('images/companies/'.$company->id, $insurance_filename);

                if(!$success)
                    return back()->withError(trans('messages.user.insurance_image'));
                 $company_doc->insurance_photo       =url('images/companies').'/'.$company->id.'/'.$insurance_filename;
            }

            $company_doc->save();

            $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function
            
            if (LOGIN_USER_TYPE == 'company') {
                return redirect('company/edit_company/'.Auth::guard('company')->user()->id);
            }
            return redirect('admin/company');
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
        if($request->id == 1)
        {
            $this->helper->flash_message('danger', 'Could not delete default company'); // Call flash message function
            return redirect(LOGIN_USER_TYPE.'/company');  //redirect depends on login user is admin or company
        }
        
        $company_drivers = User::where('user_type','Driver')->where('company_id',$request->id)->count();
        if($company_drivers>=1)
        {
            $this->helper->flash_message('danger', 'Company have some drivers, So can\'t delete this company'); // Call flash message function
            return redirect(LOGIN_USER_TYPE.'/company');  //redirect depends on login user is admin or company
        }

        $company_schedule=ScheduleRide::where('company_id',$request->id)->count();
        if($company_schedule)
        {
            $this->helper->flash_message('danger', 'Company have some schedule rides, So can\'t delete this company'); // Call flash message function
            return redirect(LOGIN_USER_TYPE.'/company');  //redirect depends on login user is admin or company
        }
        
        Vehicle::where('company_id',$request->id)->delete();
        CompanyDocuments::where('company_id',$request->id)->delete();
        CompanyPayoutPreference::where('company_id',$request->id)->delete();
        CompanyPayoutCredentials::where('company_id',$request->id)->delete();
        Company::find($request->id)->delete();
        $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function
        return redirect('admin/company');
    }


}
