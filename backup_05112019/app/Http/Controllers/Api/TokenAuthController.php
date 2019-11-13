<?php


/**
 * TokenAuth Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    TokenAuth
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */


namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Auth;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ProfilePicture;
use App\Models\DriverLocation;
use App\Models\DriverAddress;
use App\Models\DriverDocuments;
use App\Models\CarType;
use App\Models\Currency;
use App\Models\Trips;
use App\Models\Wallet;
use App\Models\PromoCode;
use App\Models\UsersPromoCode;
use App\Models\SiteSettings;
use App\Models\Language;
use App\Models\Request as RideRequest;
use Validator;
use DateTime;
use Session;
use DB;
use App;

class TokenAuthController extends Controller
{

/**
     * User Authendicate
     *@param  Get method request inputs
     *
     * @return Response Json 
     */

    public function authenticate(Request $request)
    {   
        
        $credentials = $request->only('mobile_number', 'password');
 
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials']);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token']);
        }
 
        // if no errors are encountered we can return a JWT
        return response()->json(compact('token'));
    }
 
 /**
     * User Authendicate Error
     *@param  Get method request inputs
     *
     * @return Response Json 
     */

    public function getAuthenticatedUser()
    {
        try {
        
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'user_not_found']);
            }
 
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
 
            return response()->json(['error' => 'token_expired']);
 
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        
            return response()->json(['error' => 'token_invalid']);
 
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
 
            return response()->json(['error' => 'token_absent']);
 
        }
        
        return response()->json(compact('user'));
    }
 
  /**
     * User Resister
     *@param  Get method request inputs
     *
     * @return Response Json 
     */

    public function register(Request $request) 
    {   
            if(isset($request->language))
            {
                App::setLocale($request->language);
                $language = $request->language;
            }
            else
            {
                App::setLocale('en');
                $language = 'en';
            }

        if($request->user_type =='Rider' || $request->user_type =='rider')
        {
             $rules = array(
            'mobile_number'   => 'required|regex:/^[0-9]+$/|min:6',
            'user_type'       =>'required|in:Rider,Driver,rider,driver',
            'password'        =>'required|min:6',
            'first_name'      =>'required',
            'last_name'       =>'required',
            'email_id'        =>'required|max:255|email',            
            'country_code'    =>'required',
            'device_type'     =>'required',
            'device_id'       =>'required'

            );
        }
        else
        {
             $rules = array(
            'mobile_number'   =>'required|regex:/^[0-9]+$/|min:6',
            'user_type'       =>'required|in:Rider,Driver,rider,driver',
            'password'        =>'required|min:6',
            'first_name'      =>'required',
            'last_name'       =>'required',
            'email_id'        =>'required|max:255|email',
            'city'            =>'required',
            'country_code'    =>'required',
            'device_type'     =>'required',
            'device_id'       =>'required'

            );
        }

         if(isset($request->new_user)==1)
         {
            if($request->fb_id!='' && $request->google_id!='')
            {

                    return response()->json([
                        'status_message'   =>  'Invalid Request...',
                        'status_code'       =>  '0'
                    ]); 

            }
           
         }
       
            $niceNames = array(

                'mobile_number'   => 'Mobile Number',            
            );



        $validator = Validator::make($request->all(), $rules);
        
        $validator->setAttributeNames($niceNames); 



        if (!$validator->fails()) 
        { 

            

            $mobile_number = $request->mobile_number;

            $user = User::where('mobile_number', $mobile_number)->where('user_type', $request->user_type)->get();

        if(count($user))
        {
            return response()->json([

            'status_message' =>   trans('messages.already_have_account'),

            'status_code'     => '0'

                                   ]);
        }
        else
        {

        $user = new User;

        $user->mobile_number    =   $request->mobile_number;
        $user->first_name       =   urldecode($request->first_name);
        $user->last_name        =   urldecode($request->last_name); 
        $user->user_type        =   $request->user_type;
        $user->password         =   bcrypt($request->password);
        $user->country_code     =   $request->country_code;
        $user->device_type      =   $request->device_type;
        $user->device_id        =   $request->device_id;
        $user->language         =   $language;
        $user->email            =   urldecode($request->email_id);
        
        $currency_code          =   $this->get_currency_from_ip();
                
        $user->currency_code    =   $currency_code;

         $image ='';
         

         $photo_source = 'Local';

        if($request->user_type =='Rider' || $request->user_type =='rider')
        {
           
            $user->status           =   "Active";

            
           
            if(isset($request->new_user)==1)
            {

                if(isset($request->fb_id))
                {
                  $user->fb_id         = $request->fb_id;
                }
                else
                {
                 $user->google_id     = $request->google_id;  
                }

                if($request->fb_id)
                {
                    $user_new= @User::with('profile_picture')->where('fb_id',$request->fb_id)->first();

                    $photo_source = "Facebook";
                }
                else
                {
                    $user_new= @User::with('profile_picture')->where('google_id',$request->google_id)->first();

                    $photo_source = "Google";   
                }


                $image = @$request->user_image;

           }

            $user->save();  

            
        }
        else
        { 

            
            $user->company_id           =   1;
            $user->status           =   "Car_details";
            $user->save();

            $driver_address                    = new DriverAddress;

            $driver_address->user_id           = $user->id;

            $driver_address->address_line1     = '';

            $driver_address->address_line2     = '';

            $driver_address->city              = html_entity_decode($request->city);

            $driver_address->state             = '';

            $driver_address->postal_code       = '';

            $driver_address->save();

        }


          // Create a new user


        $profile                    = new ProfilePicture;

        $profile->user_id           = $user->id;

        $profile->src               = (string) $image;

        $profile->photo_source      =  $photo_source;

        $profile->save();


        $credentials = $request->only('mobile_number', 'password','user_type');
 
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials']);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token']);
        }
        
        $car_detais = CarType::where('status','Active')->get();

        $currency = Currency::where('default_currency',1)->first();

        $wallet = Wallet::whereUserId($user->id)->first();

        $wallet_amount = (@$wallet->original_amount) ? $wallet->original_amount : 0;

        // if no errors are encountered we can return a JWT

        $register = array(

                     'status_message'    =>  'Register Success',

                     'status_code'       =>  '1',

                     'access_token'      =>  $token,

                     'car_detais'        => $car_detais,

                     'user_id'           =>  $user->id,

                     'first_name'        =>  html_entity_decode($request->first_name),

                     'last_name'         =>  html_entity_decode($request->last_name),

                     'mobile_number'     =>  $request->mobile_number,

                     'email_id'          =>  @html_entity_decode($request->email_id)!=''? html_entity_decode($request->email_id):'',

                     'user_status'       =>  $user->status,

                     'city'              =>  @html_entity_decode($request->city)!=''? html_entity_decode($request->city):'',

                     'country_code'      =>  @$request->country_code!=''? $request->country_code:'',

                     'address_line1'     =>  '',

                     'address_line2'     =>  '',

                     'state'             =>  '',

                     'postal_code'       =>  '',

                     'user_thumb_image'  =>  url('images/user.jpeg'),

                     'home'              =>  '',

                     'work'              =>  '',

                     'home_latitude'     =>  '',

                     'home_longitude'    =>  '',

                     'work_latitude'     =>  '',

                     'work_longitude'    =>  '',

                     'currency_symbol'   => $user->currency->symbol,

                     'currency_code'     => $user->currency->code,

                     'payout_id'         => '',

                     'wallet_amount'     => $wallet_amount,

                     'admin_paypal_id'   =>  PAYPAL_ID,

                     'paypal_mode'       =>  PAYPAL_MODE,

                     'paypal_app_id'     =>  PAYPAL_CLIENT_ID,

                     'google_map_key'    => MAP_KEY,

                     'fb_id'             => FB_CLIENT_ID, 

                    );
        
          return response()->json($register);
        }
        }
        else
        {


             $error=$validator->messages()->toArray();

                    foreach($error as $er)
                    {
                         $error_msg[]=array($er);
                    } 
          
                    return response()->json([

                                               'status_message'=>$error_msg['0']['0']['0'],

                                               'status_code'=>'0'
                                           ]);  

        }
    }


  /**
     * User Socail media Resister & Login 
     *@param  Get method request inputs
     *
     * @return Response Json 
     */

    public function socialsignup(Request $request) 
    {   
          //validation for signup and login

          if($request->fb_id!='' && $request->google_id!='')
           {

                   return response()->json([
                                            'status_message'   =>  'Invalid Request...',

                                            'status_code'       =>  '0'
                                         ]); 

           }
           elseif($request->fb_id!='' && $request->google_id=='') 
           {  

             $rules     = array('fb_id'      => 'required|exists:users,fb_id');

             $messages  = array('required'  =>':required.');

             $validator = Validator::make($request->all(), $rules, $messages);
           }

           elseif($request->google_id!='' && $request->fb_id=='')
           { 

             if($request->google_id!='')
             {

              $rules     =  array('google_id'      => 'required|exists:users,google_id');

              $messages  =  array('required'  => ':required.');

              $validator =  Validator::make($request->all(), $rules, $messages);
             }
           }
           else
           {
            if($request->google_id=='' && $request->fb_id=='')
             {

              $rules     =  array('google_id'      => 'required|exists:users,google_id');

              $niceNames = array('google_id'   => 'Google id or Facebook',);

              $validator = Validator::make($request->all(), $rules);
              
              $validator->setAttributeNames($niceNames);


                if ($validator->fails()) 
                {
                     $error=$validator->messages()->toArray();

                    foreach($error as $er)
                    {
                         $error_msg[]=array($er);
                    } 
                    return ['status_code' => '0' , 'status_message' => $error_msg['0']['0']['0']];
                }

             }

           }

       if($validator->fails()) 
        {       
            

            if($request->new_user == 1)
            {


              if($request->fb_id!='' )
                {     

                $rules =  array(
                                'fb_id'        => 'required|unique:users,fb_id',

                                'email_id'     => 'required|max:255|email',

                                'first_name'   => 'required',

                                'last_name'    => 'required',

                                'mobile_number'=>'required',

                                'user_type'    =>'required|in:Rider,rider',

                                'user_image'   =>'required',

                                'password'     =>'required|min:6',

                                'country_code' =>'required',

                                'device_type'  =>'required',

                                'device_id'    =>'required'

                               );
                

                }
            else
                {   
              
                    $rules = array(

                                'google_id'    => 'required|unique:users,google_id',

                                'email_id'     => 'required|max:255|email',

                                'first_name'   => 'required',

                                'last_name'    => 'required',

                                'mobile_number'=>'required',

                                'user_type'    =>'required|in:Rider,rider',

                                'user_image'   =>'required',

                                'password'     =>'required|min:6',

                                'country_code' =>'required',

                                'device_type'  =>'required',

                                'device_id'    =>'required'

                                 );
                

                }

                   $messages = array('required'=>':attribute is required.');

                   $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) 
                { 
          
                    $error=$validator->messages()->toArray();

                    foreach($error as $er)
                    {
                         $error_msg[]=array($er);
                    } 
          
                    return response()->json([

                                               'status_message'=>$error_msg['0']['0']['0'],

                                               'status_code'=>'0'
                                           ]);   
                }
            else
                {

                    $user = new User;
                    $user->mobile_number    =   $request->mobile_number;
                    $user->country_code     =   $request->country_code;
                    $user->first_name       =   html_entity_decode($request->first_name);
                    $user->last_name        =   html_entity_decode($request->last_name);
                    $user->user_type        =   $request->user_type;
                    $user->status           =   "Active";
                    $user->password         =   bcrypt($request->password);
                    $user->email            =   html_entity_decode($request->email_id);
                    $user->device_type      =   $request->device_type;
                    $user->device_id        =   $request->device_id;

                    $currency_code          =   $this->get_currency_from_ip();
                    $user->currency_code    =   $currency_code;
                    $user->language         =   $request->language;

                    if($request->fb_id)
                    {
                    $user->fb_id         = $request->fb_id;
                    }
                    else
                    {
                    $user->google_id     = $request->google_id;  
                    }

                    $user->save();  

                    

                    if($request->fb_id)
                    {
                    $user_new= @User::with('profile_picture')->where('fb_id',$request->fb_id)->first();

                    $photo_source = "Facebook";
                    }
                    else
                    {
                    $user_new= @User::with('profile_picture')->where('google_id',$request->google_id)->first();

                    $photo_source = "Google";   
                    }

                    $profile                    = new ProfilePicture;

                    $profile->user_id           = $user_new->id;

                    $profile->src               = html_entity_decode($request->user_image);

                    $profile->photo_source      = $photo_source;

                    $profile->save();


                    $currency = Currency::where('default_currency',1)->first();

                    $wallet = Wallet::whereUserId($user->id)->first();

                    $wallet_amount = (@$wallet->original_amount) ? $wallet->original_amount : 0;


                    $token = JWTAuth::fromUser($user_new);

                    $new_register = array(

                     'status_message'   =>  'Signup Success',

                     'status_code'       =>  '1',

                     'access_token'      =>  $token,

                     'user_id'           =>  $user->id,

                     'first_name'        =>  $user_new->first_name,

                     'last_name'         =>  $user_new->last_name,

                     'mobile_number'     =>  $user_new->mobile_number,

                     'country_code'      =>  @$user_new->country_code!=''? $user_new->country_code:'',

                     'email_id'          =>  @$user_new->email!=''? $user_new->email:'',

                     'user_status'       =>  "Active",

                     'user_thumb_image'  =>  @html_entity_decode($request->user_image)!='' ? html_entity_decode($request->user_image):url('images/user.jpeg'),

                     'home'              =>  '',

                     'work'              =>  '',

                     'home_latitude'     =>  '',

                     'home_longitude'    =>  '',

                     'work_latitude'     =>  '',

                     'work_longitude'    =>  '',

                     'payout_id'         =>  '',
                     
                     'wallet_amount'     => $wallet_amount,

                     'admin_paypal_id'   =>  PAYPAL_ID,

                     'paypal_mode'       =>  PAYPAL_MODE,

                     'paypal_app_id'     =>  PAYPAL_CLIENT_ID,

                     'currency_symbol'   => $user->currency->symbol,

                     'currency_code'     => $user->currency->code,

                     'google_map_key'    => MAP_KEY,

                     'fb_id'             => FB_CLIENT_ID, 


                    );
        
                 return response()->json($new_register);

                }
        }
        else
        {
               return response()->json([

                                               'status_message'=>"New User",

                                               'status_code'=>'0'
                                           ]);  
        }

    }
        else
        {   
            
            if($request->fb_id!='' )
                {     

                    $rules =  array(

                                'fb_id'         => 'required',

                                'device_type'  =>'required',

                                'device_id'    =>'required'

                               );
                

                }
            else
                {   
              
                    $rules = array(

                                'google_id'  => 'required',

                                'device_type'  =>'required',

                                'device_id'    =>'required'

                                 );
                

                }

                   $messages = array('required'=>':attribute is required.');

                   $validator = Validator::make($request->all(), $rules, $messages);

            if (!$validator->fails()) 
                {

                    if($request->fb_id)
                        {
                            $user= @User::where('fb_id',$request->fb_id)->first();
                           
                        }
                    else
                        {
                            $user= @User::where('google_id',$request->google_id)->first();
                        }

                    $currency_code          =   $this->get_currency_from_ip();
                    
                    User::whereId($user->id)->update(['device_id'=>$request->device_id,'device_type'=>$request->device_type, 'currency_code' => $currency_code,'language' => $request->language]);

                    $user = User::where('id', $user->id)->first();
                
                    $token = JWTAuth::fromUser($user);

                    $users_promo_codes = UsersPromoCode::whereUserId($user->id)->whereTripId(0)->with('promo_code')->get();

                    $final_promo_details = [];

                    foreach($users_promo_codes as $row)
                    {
                        if(@$row->promo_code)
                        {
                            $promo_details['id'] = $row->promo_code->id;
                            $promo_details['amount'] = $row->promo_code->amount;
                            $promo_details['code'] = $row->promo_code->code;
                            $promo_details['expire_date'] = $row->promo_code->expire_date_dmy;
                            $final_promo_details[] = $promo_details;
                        }
                    }

                    $currency = Currency::where('code', $user->currency_code)->first();
                    if(!$currency)
                        $currency = Currency::where('default_currency',1)->first();

                    $wallet = Wallet::whereUserId($user->id)->first();

                    $wallet_amount = (@$wallet->original_amount) ? $wallet->original_amount : 0;

                    
                    $register = array(

                     'status_message'   =>  'Login Success',

                     'status_code'       =>  '1',

                     'access_token'      =>  $token,

                     'user_id'           =>  $user->id,

                     'first_name'        =>  $user->first_name,

                     'last_name'         =>  $user->last_name,

                     'mobile_number'     =>  $user->mobile_number,

                     'country_code'      =>  $user->country_code,

                     'email_id'          =>  @$user->email!=''? $user->email:'',

                     'user_status'       =>  @$user->status,

                     'user_thumb_image'  =>  @$user->profile_picture->src!='' ? $user->profile_picture->src:url('images/user.jpeg'),

                     'home'              =>  @$user->rider_location->home!=''? $user->rider_location->home:'',

                     'work'              =>  @$user->rider_location->work!=''? $user->rider_location->work:'',

                     'home_latitude'     =>  @$user->rider_location->home_latitude!=''? $user->rider_location->home_latitude:'',

                     'home_longitude'    =>  @$user->rider_location->home_longitude!=''? $user->rider_location->home_longitude:'',

                     'work_latitude'     =>  @$user->rider_location->work_latitude!=''? $user->rider_location->work_latitude:'',

                     'work_longitude'    =>  @$user->rider_location->work_longitude!=''? $user->rider_location->work_longitude:'',

                     'currency_symbol'   => $user->currency->symbol,

                     'currency_code'     => $user->currency->code,

                     'payout_id'         =>  '',
                     
                     'wallet_amount'     => $wallet_amount,

                     'admin_paypal_id'   =>  PAYPAL_ID,

                     'paypal_mode'       =>  PAYPAL_MODE,

                     'paypal_app_id'     =>  PAYPAL_CLIENT_ID,

                    'promo_details'     => $final_promo_details,

                    'google_map_key'    => MAP_KEY,

                     'fb_id'             => FB_CLIENT_ID, 

                    );
        
                    return response()->json($register);

                }
             else
                {
                 $error=$validator->messages()->toArray();

                    foreach($error as $er)
                    {
                         $error_msg[]=array($er);
                    } 
          
                    return response()->json([

                                               'status_message'=>$error_msg['0']['0']['0'],

                                               'status_code'=>'0'
                                           ]); 
             }
        }



    }

  /**
     * User Token
     *@param  Get method request inputs
     *
     * @return Response Json 
     */

    public function token(Request $request)
    {

        $token = JWTAuth::refresh($request->token);
        
        return response()->json(['token' => $token], 200);
    }
    
    
  /**
     * User Login
     *@param  Get method request inputs
     *
     * @return Response Json 
     */

    public function login(Request $request)
    {   

        $user_id = $request->mobile_number;
        $db_id   = 'mobile_number';

        $rules = array(
        'mobile_number'   =>'required|regex:/^[0-9]+$/|min:6',
        'user_type'       =>'required|in:Rider,Driver,rider,driver',
        'password'        =>'required',
        'country_code'    =>'required',
        'device_type'     =>'required',
        'device_id'       =>'required',
       // 'language'        =>'required',
        );


        $validator = Validator::make($request->all(), $rules); 

        if ($validator->fails()) 
        {
                 $error=$validator->messages()->toArray();

                foreach($error as $er)
                {
                     $error_msg[]=array($er);
                } 
                return ['status_code' => '0' , 'status_message' => $error_msg['0']['0']['0']];
        }
        else
        {
            if(isset($request->language))
                App::setLocale($request->language);
            else
                App::setLocale('en');

            if(Auth::attempt([$db_id => $user_id, 'password' => $request->password,'user_type' =>$request->user_type,'country_code' => $request->country_code]))
            {
                $credentials = $request->only($db_id, 'password','user_type','country_code');
         
                try{

                     if (! $token = JWTAuth::attempt($credentials))
                      {

                        return response()->json([

                          'status_message' => @trans('messages.credentials'),

                          'status_code'     => '0'

                          ]);

                      }

                    } 
                  catch (JWTException $e) 
                  {

                    return response()->json([

                                            'status_message' => 'could_not_create_token',

                                            'status_code'     => '0'

                                           ]);

                  }

                $user = User::with('company')->where($db_id, $user_id)->where('user_type',$request->user_type)->first();

                if($user->status == 'Inactive') {
                    return response()->json([
                    'status_message' =>trans('messages.inactive_admin'),
                    'status_code'     => '0'
                   ]);
                }

                if(isset($user->company) && $user->company->status == 'Inactive') {
                    return response()->json([
                    'status_message' =>trans('messages.inactive_company'),
                    'status_code'     => '0'
                   ]);
                }

                $currency_code          =   $this->get_currency_from_ip();
                
                User::whereId($user->id)->update(['device_id'=>$request->device_id,'device_type'=>$request->device_type, 'currency_code' => $currency_code,'language'=>$request->language]);

                $user = User::where('id', $user->id)->first();
                auth()->setUser($user);

                $car_detais = CarType::where('status','Active')->get();

                $wallet = Wallet::whereUserId($user->id)->first();

                $wallet_amount = (@$wallet->original_amount) ? $wallet->original_amount : '0.00';

                if($request->user_type != 'Rider' || $request->user_type != 'rider') {
                    $data = [   
                        'user_id'  => $user->id,
                        'status'   => 'Offline',
                        'car_id'   => @$user->driver_documents->vehicle_id!=''? $user->driver_documents->vehicle_id:@$car_detais[0]->id
                    ];

                    DriverLocation::updateOrCreate(['user_id' => $user->id], $data);
                    @RideRequest::where('driver_id',$user->id)->where('status','Pending')->update(['status'=>'Cancelled']);

                }

                $users_promo_codes = UsersPromoCode::whereUserId($user->id)->whereTripId(0)->with('promo_code')->get();

                $final_promo_details = [];

                foreach($users_promo_codes as $row) {
                    if(@$row->promo_code) {
                        $promo_details['id'] = $row->promo_code->id;
                        $promo_details['amount'] = $row->promo_code->amount;
                        $promo_details['code'] = $row->promo_code->code;
                        $promo_details['expire_date'] = $row->promo_code->expire_date_dmy;
                        $final_promo_details[] = $promo_details;
                    }
                }

                $currency = Currency::where('code', $user->currency_code)->first();
                if(!$currency)
                    $currency = Currency::where('default_currency',1)->first();


                if(isset($user->language))
                    App::setLocale($user->language);
                else
                    App::setLocale('en');
               
                $user=array(

                        'status_message'    => @trans('messages.login_success'),

                        'status_code'       =>  '1',

                        'access_token'      =>  $token,

                        'car_detais'        =>  $car_detais,

                        'user_id'           =>  $user->id,

                        'first_name'        =>  $user->first_name,

                        'last_name'         =>  $user->last_name,

                        'mobile_number'     =>  $user->mobile_number,

                        'country_code'      =>  $user->country_code,

                        'email_id'          =>  $user->email,

                        'user_status'       =>  $user->status,

                        'home'              =>  @$user->rider_location->home!=''? $user->rider_location->home:'',

                        'work'              =>  @$user->rider_location->work!=''? $user->rider_location->work:'',

                        'home_latitude'     =>  @$user->rider_location->home_latitude!=''? $user->rider_location->home_latitude:'',

                        'home_longitude'    =>  @$user->rider_location->home_longitude!=''? $user->rider_location->home_longitude:'',

                        'work_latitude'     =>  @$user->rider_location->work_latitude!=''? $user->rider_location->work_latitude:'',

                        'work_longitude'    =>  @$user->rider_location->work_longitude!=''? $user->rider_location->work_longitude:'',

                        'user_thumb_image'  =>  @$user->profile_picture->src!=''?$user->profile_picture->src:url('images/user.jpeg'),

                        'license_front'     =>  @$user->driver_documents->license_front!=''? $user->driver_documents->license_front:'',

                        'license_back'      =>  @$user->driver_documents->license_back!=''? $user->driver_documents->license_back:'',

                        'insurance'         =>  @$user->driver_documents->insurance!=''? $user->driver_documents->insurance:'',

                        'rc'                =>  @$user->driver_documents->rc!=''? $user->driver_documents->rc:'',

                        'permit'            =>  @$user->driver_documents->permit!=''? $user->driver_documents->permit:'',

                        'vehicle_id'        =>  @$user->driver_documents->vehicle_id!=''? $user->driver_documents->vehicle_id:'',

                        'vehicle_type'      =>  @$user->driver_documents->vehicle_type!=''? $user->driver_documents->vehicle_type:'',

                        'vehicle_number'    =>  @$user->driver_documents->vehicle_number!=''? $user->driver_documents->vehicle_number:'',

                        'address_line1'     =>  @$user->driver_address->address_line1!=''? $user->driver_address->address_line1:'',

                        'address_line2'     =>  @$user->driver_address->address_line2!=''? $user->driver_address->address_line2:'',

                        'state'             =>  @$user->driver_address->state!=''? $user->driver_address->state:'',

                        'postal_code'       =>  @$user->driver_address->postal_code!=''? $user->driver_address->postal_code:'',

                        'currency_symbol'   => $user->currency->symbol,

                        'currency_code'     => $user->currency->code,

                        'payout_id'         => @$user->payout_id!=''?$user->payout_id:'',

                        'wallet_amount'     => $wallet_amount,

                        'admin_paypal_id'   =>  PAYPAL_ID,

                        'paypal_mode'       =>  PAYPAL_MODE,

                        'paypal_app_id'     =>  PAYPAL_CLIENT_ID,

                        'promo_details'     => $final_promo_details,

                        'google_map_key'    => MAP_KEY,

                        'fb_id'             => FB_CLIENT_ID, 

                        'company_name'      =>(string)@$user->company->name,

                        'company_id'      =>(string)@$user->company->id,
                    );
                
                  return response()->json($user);
                
                  }
                  else
                  {
                       return response()->json([

                        'status_message' =>@trans('messages.credentials'),

                        'status_code'     => '0'

                                               ]);

                  }
    }
       
    }




    public function language(Request $request)
    {
        
        $user_details = JWTAuth::parseToken()->authenticate();


           $user= User::find($user_details->id);
           $user->language =$request->language;
           $user->save();

            if(isset($user->language))
                App::setLocale($user->language);
                else
                App::setLocale('en');

            
            if(count($user))
            {
                    return response()->json([

                    'status_message'    => trans('messages.update_success'),

                    'status_code'       =>  '1'

                    ]);
            }
            else
            {       return response()->json([

                    'status_message' => trans('messages.credentials'),

                    'status_code'     => '0'

                    ]);

            } 


        
    }
    
     /**
     * User Email Validation
     *
     * @return Response in Json
     */ 

    public function emailvalidation(Request $request)
    {
        $rules = array('email'=> 'required|max:255|email_id|unique:users');

        // Email signup validation custom messages
        $messages = array('required'=>':attribute is required.');

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()) 
        {
         $user=array('status_message'=>'Email Already exist','status_code'=>'0');

         return response()->json($user);
        }
        else
        {
        $user=array('status_message'=>'Emailvalidation Success','status_code'=>'1');
        return response()->json($user);
        }
    }
     /**
     * Forgot Password
     * 
     * @return Response in Json
     */ 
    public function forgotpassword(Request $request)
      {
         $rules = array(
            'mobile_number'   => 'required|regex:/^[0-9]+$/|min:6',
            'user_type'       =>'required|in:Rider,Driver,rider,driver',
            'password'        =>'required|min:6',
            'country_code'    =>'required',
            'device_type'     =>'required',
            'device_id'       =>'required'

        );
        $niceNames = array(
            'mobile_number'   => 'Mobile Number',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($niceNames); 

        if ($validator->fails()) 
        {
             $error=$validator->messages()->toArray();

            foreach($error as $er)
            {
                 $error_msg[]=array($er);
            } 
            return ['status_code' => '0' , 'status_message' => $error_msg['0']['0']['0']];
        }
        else
        {  
            $user_check = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->first();
            
            if(count($user_check))
            {
                $password  =  bcrypt($request->password);

                $currency_code          =   $this->get_currency_from_ip();

                User::whereId($user_check->id)->update(['password'=>$password,'device_id'=>$request->device_id,'device_type'=>$request->device_type, 'currency_code'=> $currency_code]);
                     

                $user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->first();

                $token = JWTAuth::fromUser($user);

                auth()->setUser($user);

                $car_detais = CarType::where('status','Active')->get();

                $wallet = Wallet::whereUserId($user->id)->first();

                $wallet_amount = (@$wallet->original_amount) ? $wallet->original_amount : '0.00';

                if($request->user_type != 'Rider' || $request->user_type != 'rider')
                {

                $data = [   
                                'user_id'  => $user->id,

                                'status'   => 'Offline',

                                'car_id'   => @$user->driver_documents->vehicle_id!=''? $user->driver_documents->vehicle_id:@$car_detais[0]->id

                              ];

                DriverLocation::updateOrCreate(['user_id' => $user->id], $data);

                @RideRequest::where('driver_id',$user->id)->where('status','Pending')->update(['status'=>'Cancelled']);  

                }

                $users_promo_codes = UsersPromoCode::whereUserId($user->id)->whereTripId(0)->with('promo_code')->get();

                $final_promo_details = [];

                foreach($users_promo_codes as $row)
                {
                    if(@$row->promo_code)
                    {
                        $promo_details['id'] = $row->promo_code->id;
                        $promo_details['amount'] = $row->promo_code->amount;
                        $promo_details['code'] = $row->promo_code->code;
                        $promo_details['expire_date'] = $row->promo_code->expire_date_dmy;
                        $final_promo_details[] = $promo_details;
                    }
                }
            
                $currency = Currency::where('code', $user->currency_code)->first();
                if(!$currency)
                    $currency = Currency::where('default_currency',1)->first();

                $user=array(

                             'status_message'    =>  'Login Success',

                             'status_code'       =>  '1',

                             'access_token'      =>  $token,

                             'car_detais'        =>  $car_detais,

                             'user_id'           =>  $user->id,

                             'first_name'        =>  $user->first_name,

                             'last_name'         =>  $user->last_name,

                             'mobile_number'     =>  $user->mobile_number,

                             'country_code'      =>  $user->country_code,

                             'email_id'          =>  $user->email,

                             'user_status'       =>  $user->status,

                             'user_thumb_image'  =>  @$user->profile_picture->src!=''?$user->profile_picture->src:url('images/user.jpeg'),

                             'license_front'     =>  @$user->driver_documents->license_front!=''? $user->driver_documents->license_front:'',

                             'license_back'      =>  @$user->driver_documents->license_back!=''? $user->driver_documents->license_back:'',

                             'insurance'         =>  @$user->driver_documents->insurance!=''? $user->driver_documents->insurance:'',

                             'rc'                =>  @$user->driver_documents->rc!=''? $user->driver_documents->rc:'',

                             'permit'            =>  @$user->driver_documents->permit!=''? $user->driver_documents->permit:'',

                             'vehicle_id'        =>  @$user->driver_documents->vehicle_id!=''? $user->driver_documents->vehicle_id:'',

                             'vehicle_type'      =>  @$user->driver_documents->vehicle_type!=''? $user->driver_documents->vehicle_type:'',

                             'vehicle_number'    =>  @$user->driver_documents->vehicle_number!=''? $user->driver_documents->vehicle_number:'',

                             'currency_symbol'   => $user->currency->symbol,

                             'currency_code'     => $user->currency->code,

                             'payout_id'         => @$user->payout_id!=''?$user->payout_id:'',

                             'wallet_amount'     => $wallet_amount,

                             'admin_paypal_id'   =>  PAYPAL_ID,

                             'paypal_mode'       =>  PAYPAL_MODE,

                             'paypal_app_id'     =>  PAYPAL_CLIENT_ID,
                             'paypal_app_id'     =>  PAYPAL_CLIENT_ID,
                             'company_name'      =>(string)@$user->company->name,
                             'company_id'      =>(string)@$user->company->id,

                            );
                
                  return response()->json($user);

            }
            else
            {
                return response()->json([

                        'status_message'  => "Invalid credentials",

                        'status_code'     => '0',


                                               ]);

            }

        }
      }
     /**
     * Mobile number verification
     * 
     * @return Response in Json
     */ 
    public function numbervalidation(Request $request)
    {
        if($request->forgotpassword)
        {

             $rules = array(
            'mobile_number'   =>'required|regex:/^[0-9]+$/|min:6|exists:users,mobile_number',
            'user_type'       =>'required|in:Rider,Driver,rider,driver',
            'country_code'    =>'required',

            );

        $messages = array(
            'mobile_number.exists'   => trans('messages.enter_registered_number'),
        );

        }
        else
        {
            $rules = array(
            'mobile_number'   => 'required|regex:/^[0-9]+$/|min:6',
            'user_type'       =>'required|in:Rider,Driver,rider,driver',
            'country_code'    =>'required',

            );

             $messages = array(
            'mobile_number.required'   => trans('messages.mobile_num_required'),
        );
        }
       

        $validator = Validator::make($request->all(), $rules,$messages);
      
        

        if ($validator->fails()) 
        {
             $error=$validator->messages()->toArray();

            foreach($error as $er)
            {
                 $error_msg[]=array($er);
            } 
            return ['status_code' => '0' , 'status_message' => $error_msg['0']['0']['0']];
        }


        $mobile_number = $request->mobile_number;


        $user = User::where('mobile_number', $mobile_number)->where('user_type', $request->user_type)->get();


        if(count($user))
        {
               
                return response()->json([

                        'status_message'  => trans('messages.mobile_number_exist'),

                        'status_code'     => '1',


                ]);
                    
         }

         
            else
            {
                      
                        if(isset($request->language))
                        {
                            App::setLocale($request->language);
                            $language = $request->language;
                        }
                        else
                        {
                            App::setLocale('en');
                            $language = 'en';
                        }

                    return response()->json([

                    'status_message' =>  trans('messages.number_does_not_exists'),

                    'status_code'     => '0'
                    

                    ]);

            }

            
   
       

      }

 /**
     * Send OTP
     *@param  Get method request inputs
     *
     * @return Response Json 
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

        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $response = curl_exec($ch);

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

 /**
     * Updat Device ID and Device Type
     *@param  Get method request inputs
     *
     * @return Response Json 
     */

    public function update_device(Request $request)
    {

        $user_details = JWTAuth::parseToken()->authenticate();

        $rules = array(
            'user_type'    =>'required|in:Rider,Driver,rider,driver',
            'device_type'  =>'required',
            'device_id'    =>'required'

        );

        $niceNames = array(
            'mobile_number'   => 'Mobile Number',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($niceNames); 

        if ($validator->fails()) 
        {
             $error=$validator->messages()->toArray();

            foreach($error as $er)
            {
                 $error_msg[]=array($er);
            } 
            return ['status_code' => '0' , 'status_message' => $error_msg['0']['0']['0']];
        }
        else
        { 
          $user = User::where('id', $user_details->id)->first();

            if(count($user))
            {
                User::whereId($user_details->id)->update(['device_id'=>$request->device_id,'device_type'=>$request->device_type]);

               /* if($request->user_type == 'Driver' || $request->user_type == 'driver')
                {
                  RideRequest::where('driver_id',$user_details->id)->where('status','Pending')->update(['status'=>'Cancelled']);  
                }*/
                

                 return response()->json([

                        'status_message'  => "Updated Successfully",

                        'status_code'     => '1'

                                   ]);
            }
            else
            {
                return response()->json([

                'status_message' =>  "Invalid credentials",

                'status_code'     => '0'

                                       ]);

            }
          

        }

        }
   public function logout(Request $request)
    {   

        $user_details = JWTAuth::parseToken()->authenticate();

        $rules = array(
            'user_type'    =>'required|in:Rider,Driver,rider,driver'
        );



        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) 
        {
             $error=$validator->messages()->toArray();

            foreach($error as $er)
            {
                 $error_msg[]=array($er);
            } 
            return ['status_code' => '0' , 'status_message' => $error_msg['0']['0']['0']];
        }
        else
        { 
          $user = User::where('id', $user_details->id)->first();

            if(count($user))
            {
                if($request->user_type == 'Driver' || $request->user_type == 'driver')
                {

                        $driver_location = DriverLocation::where('user_id',$user_details->id)->first();

                        if(@$driver_location->status == 'Trip')
                        {  

                          
                                 return response()->json([

                                  'status_message' =>  trans('messages.complete_your_trips'),

                                  'status_code'     => '2'

                                               ]); 
                            
                        }
                        else
                        {
                            DriverLocation::where('user_id',$user_details->id)->update(['status'=>'Offline']);
                                       //Deactive the Access Token
                            JWTAuth::invalidate($request->token);

                            Session::flush();

                            $user->device_type = Null;
                            $user->device_id = '';
                            $user->save();
                            
                            return response()->json([

                                    'status_message'  => "Logout Successfully",

                                    'status_code'     => '1'

                                               ]); 
                        }

                }
                else
                {
                $trips = Trips::where('user_id',$user_details->id)->where('status','!=','Completed')->where('status','!=','Cancelled')->get();

                    if(count($trips))
                    {
                        return response()->json([

                                  'status_message' => trans('messages.complete_your_trips'),

                                  'status_code'     => '2'

                                               ]);  
                    }
                    else
                    {
                        //Deactive the Access Token
                        JWTAuth::invalidate($request->token);

                        Session::flush();

                        $user->device_type = Null;
                        $user->device_id = '';
                        $user->save();

                        return response()->json([

                                'status_message'  => "Logout Successfully",

                                'status_code'     => '1'

                                           ]);
                    }

                }
               
            }
            else
            {
                return response()->json([

                'status_message' =>  "Invalid credentials",

                'status_code'     => '0'

                                       ]);

            }
          

        }
      

    }
 

    public function get_currency_from_ip($ip_address = '')
    {
        $ip_address = $ip_address ?: request()->getClientIp();
        $default_currency = Currency::active()->defaultCurrency()->first();
        $currency_code    = @$default_currency->code;
        if(session()->get('currency_code'))
        {
            $currency_code = session()->get('currency_code');
        }
        else if($ip_address!='')
        {
            $result = array();
            try{
              $result = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip_address));
              // $result = json_decode(file_get_contents('https://ipapi.co/'.$ip_address.'/json/'), true);
              // \Log::info(' CC1 '.print_r($result, true).' '.$ip_address.' ');
            }
            catch(\Exception $e)
            {
              \Log::info(' CC2 '.print_r($result, true).' '.$ip_address.' '.$e->getMessage());
            }
            // Default Currency code for footer
            if(@$result['geoplugin_currencyCode'])
            {
                $currency_code =  $result['geoplugin_currencyCode'];

                 $session_rate = Currency::whereCode($currency_code)->first();
                 if($session_rate)
                 {
                    $currency_code =  $result['geoplugin_currencyCode'];
                 }
                else{

                $currency_code =  $default_currency->code;

                }

            }
            // if(@$result['currency'])
            // {
            //     $currency_code =  $result['currency'];
            // }
            session()->put('currency_code', $currency_code);
        }
        return $currency_code;
    }

    public function paypal_currency_conversion(Request $request)
    {
        $rules  = [
            'currency_code' => 'required|exists:currency,code',
            'amount' => 'required|numeric|min:0'
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails())
        {
            return ['status_code' => '0' , 'status_message' => $validator->messages()->first()];
        }

        $paypal_currency = @SiteSettings::where('name', 'paypal_currency')->first()->value;
        $from = $request->currency_code;
        $to = $paypal_currency;
        $price = $request->amount;

        if($from == '')
        {
          $from = $this->getSessionOrDefaultCode();
        }
        if($to == '')
        {
          $to = $this->getSessionOrDefaultCode();
        }

        $rate = Currency::whereCode($from)->first()->rate;
        $session_rate = Currency::whereCode($to)->first()->rate;

        $usd_amount = $price / $rate;
        $converted_amount = number_format($usd_amount * $session_rate, 2, '.', '');

        return ['status_code' => '1', 'status_message' => 'Amount converted successfully to PayPal currency', 'currency_code' => $to, 'amount' => $converted_amount,
           'paypal_mode'  =>  PAYPAL_MODE, 'paypal_app_id'  =>  PAYPAL_CLIENT_ID];
    }

    public function getSessionOrDefaultCode()
    {
        $currency_code = Currency::defaultCurrency()->first()->code;
    }

    public function currency_list() 
    {
        $currency_list = Currency::where('status', 'Active')->orderBy('code')->get();
        $curreny_list_keys = ['code', 'symbol'];

        $currency_list = $currency_list->map(function ($item, $key) use($curreny_list_keys) {
            return array_combine($curreny_list_keys, [$item->code, $item->symbol]);
        })->all();

        if(!empty($currency_list)) { 

            return response()->json([
                'status_message' => 'Currency Details Listed Successfully',
                'status_code'     => '1',
                'currency_list'   => $currency_list
            ]);
        }
        else {
            return response()->json([
              'status_message' => 'Currency Details Not Found',
              'status_code'     => '0'
            ]);      
        } 
    }

     public function language_list() 
    {
          $languages = @Language::where('status', '=', 'Active')->get();

            $languages = $languages->map(function ($item, $key)  {
                    return $item->value;
            })->all();

            if(!empty($languages)) { 

                return response()->json([
                'status_message' => 'Successfully',
                'language_list' => $languages,
                'status_code'     => '1'
                ]);     

            }
            return response()->json([
              'status_message' => 'language Details Not Found',
              'status_code'     => '0'
            ]);   

    }

}
 
