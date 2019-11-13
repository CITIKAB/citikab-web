<?php

/**
 * Api Credentials Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Api Credentials
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ApiCredentials;
use App\Http\Start\Helpers;
use Validator;

class ApiCredentialsController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load View and Update Api Credentials
     *
     * @return redirect     to api_credentials
     */
    public function index(Request $request)
    {
        if(!$_POST)
        {
            $data['result'] = ApiCredentials::get();

            return view('admin.api_credentials', $data);
        }
        else if($request->submit)
        {
            // Api Credentials Validation Rules
            $rules = array(
                    'google_map_key'        => 'required',
                    'google_map_server_key' => 'required',
                    'twillo_sid'             => 'required',
                    'twillo_token'          => 'required',
                    'twillo_from'            => 'required',
                    'fcm_server_key'        => 'required',
                    'fcm_sender_id'         => 'required',
                    'facebook_client_id'    => 'required',
                    'facebook_client_secret'=> 'required',
                    'google_client'         =>'required',
                );




            $validator = Validator::make($request->all(), $rules);
          

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {


                ApiCredentials::where(['name' => 'key', 'site' => 'GoogleMap'])->update(['value' => $request->google_map_key]);

                ApiCredentials::where(['name' => 'server_key', 'site' => 'GoogleMap'])->update(['value' => $request->google_map_server_key]);

                ApiCredentials::where(['name' => 'server_key', 'site' => 'FCM'])->update(['value' => $request->fcm_server_key]);

                ApiCredentials::where(['name' => 'sender_id', 'site' => 'FCM'])->update(['value' => $request->fcm_sender_id]);

                ApiCredentials::where(['name' => 'sid', 'site' => 'Twillo'])->update(['value' => $request->twillo_sid]);

                ApiCredentials::where(['name' => 'token', 'site' => 'Twillo'])->update(['value' => $request->twillo_token]);

                ApiCredentials::where(['name' => 'from', 'site' => 'Twillo'])->update(['value' => $request->twillo_from]);

                ApiCredentials::where(['name' => 'client_id','site' => 'Facebook'])->update(['value' => $request->facebook_client_id]);

                ApiCredentials::where(['name' => 'client_secret','site' => 'Facebook'])->update(['value' => $request->facebook_client_secret]);

                 ApiCredentials::where(['name' => 'stripe_publish_key','site' => 'Stripe'])->update(['value' => $request->stripe_publish_key]); 
                 
                 ApiCredentials::where(['name' => 'stripe_secret_key','site' => 'Stripe'])->update(['value' => $request->stripe_secret_key]);
                 ApiCredentials::where(['name' => 'client_id','site' => 'Google'])->update(['value' => $request->google_client]);
                
                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function

                return redirect('admin/api_credentials');
            }
        }
        else
        {
            return redirect('admin/api_credentials');
        }
    }
}
