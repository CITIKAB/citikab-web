<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use App\Models\EmailSettings;
use App\Models\User;
use App\Models\Company;
use App\Mail\MailQueue;
use Validator;
use Mail;

class EmailController extends Controller
{
	protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load View and Update Email Settings Data
     *
     * @return redirect     to email_settings
     */
    public function index(Request $request)
    {
        if($request->method() != 'POST') {
            $data['result'] = EmailSettings::get();
            return view('admin.email.email_settings', $data);
        }
        else if($request->submit) {
            $user =($request->driver=='mailgun') ? 'domain' : 'username' ;
            $pass =($request->driver=='mailgun') ? 'secret' : 'password' ;
            $username =($request->driver=='mailgun') ? 'Domain' : 'Username' ;
            $password =($request->driver=='mailgun') ? 'Secret' : 'Password' ;
            // Email Settings Validation Rules
            $rules = array(
	                    'driver'       => 'required|in:smtp,sendmail,mailgun,mandrill,ses,sparkpost',
	                    'host'         => 'required',
	                    'port'         => 'required',
	                    'from_address' => 'required',
	                    'from_name'    => 'required',
	                    'encryption'   => 'required',
	                    $user         => 'required',
	                    $pass         => 'required'
	                );

            // Email Settings Validation Custom Names
            $niceNames = array(
	                        'driver'       => 'Driver',
	                        'host'         => 'Host',
	                        'port'         => 'Port',
	                        'from_address' => 'From Address',
	                        'from_name'    => 'From Name',
	                        'encryption'   => 'Encryption',
	                        $user          => $username,
	                        $pass          => $password
	                    );

            $messages = [ 'in' => 'Enter Valid :attribute.',];
            $validator = Validator::make($request->all(), $rules,$messages);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else {
                EmailSettings::where(['name' => 'driver'])->update(['value' => $request->driver]);
                EmailSettings::where(['name' => 'host'])->update(['value' => $request->host]);
                EmailSettings::where(['name' => 'port'])->update(['value' => $request->port]);
                EmailSettings::where(['name' => 'from_address'])->update(['value' => $request->from_address]);
                EmailSettings::where(['name' => 'from_name'])->update(['value' => $request->from_name]);
                EmailSettings::where(['name' => 'encryption'])->update(['value' => $request->encryption]);
                if($request->driver == 'mailgun') {
                	EmailSettings::where(['name' => 'domain'])->update(['value' => $request->domain]);
                	EmailSettings::where(['name' => 'secret'])->update(['value' => $request->secret]);
                }
                else {
                	EmailSettings::where(['name' => 'username'])->update(['value' => $request->username]);
                	EmailSettings::where(['name' => 'password'])->update(['value' => $request->password]);
                }

                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function
                return redirect('admin/email_settings');
            }
        }
        else {
            return redirect('admin/email_settings');
        }
    }

    /**
     * Send Email to users
     *
     * @return redirect     to send_email
     */
    public function send_email(Request $request)
    {
        if($request->method() != 'POST') {
        	$results = [];
            $result = User::select('email','user_type')->get();
            foreach ($result as $row) {
                $results[] = $row->user_type.' - '.$row->email;
            }

            $company = Company::select('email')->where('id','!=',1) ->get();
            foreach ($company as $row) {
                $results[] = 'Company - '.$row->email;
            }

            $data['email_address_list'] = json_encode($results);
            return view('admin.email.send_email', $data);
        }
        else if($request->submit) {
            // Send Email Validation Rules
            $rules = array(
	                    'subject' => 'required',
	                    'message' => 'required',
                    );

            if($request->to != 'to_all')
                $rules['email'] = 'required';

            // Send Email Validation Custom Names
            $niceNames = array(
	                        'subject' => 'Subject',
	                        'message' => 'Message',
	                        'email'   => 'Email',
                        );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) {
            	// Form calling with Errors and Input values
                return back()->withErrors($validator)->withInput();
            }
            else {
            	$result = array();
                if($request->to == 'to_all') {
                    $results = User::select('email','user_type')->get();
                    foreach ($results as $row)
                        $result[] = [$row->user_type,$row->email];

                    $results = Company::select('email')->where('id','!=',1) ->get();
                    foreach ($results as $row)
                        $result[] = ['Company',$row->email];
                }
                else{
                    $result = explode(',', $request->email);

                    $result 		= array_filter(array_map(function($email){
                        $email_value = explode(' - ', $email);
                        if (isset($email_value[1])) {
                            return [$email_value[0],trim($email_value[1])];
                        }
                    },$result));
                }

                $emails = $result;
                $data['url'] 	= url('/').'/';
                $data['locale'] = \App::getLocale();

                for($i=0; $i<count($emails); $i++) {
                    if ($emails[$i][0] == 'Company') {
                        $company 			= Company::where('email', $emails[$i][1])->get();
                        $email 				= isset($company[0]->email) ? $company[0]->email:$emails[$i][1];
                        $first_name 		= isset($company[0]->name) ? $company[0]->name:$emails[$i][1];
                    }else{
                        $user               = User::where('email', $emails[$i][1])->get();
                        $email              = isset($user[0]->email) ? $user[0]->email:$emails[$i][1];
                        $first_name         = isset($user[0]->first_name) ? $user[0]->first_name:$emails[$i][1];
                    }
                    $data['first_name'] = $first_name;
                    $data['content'] 	= $request->message;
                    $data['subject'] 	= $request->subject;
                    $data['view_file'] 	= 'emails.custom_email';
                    // return view($data['view_file'],$data);
                    Mail::to($email,$first_name)->queue(new MailQueue($data));
                }

                // Call flash message function
                $this->helper->flash_message('success', 'Email Sent Successfully');

                return redirect('admin/send_email');
            }
        }
    }
}
