<?php
namespace App\Http\Controllers;

use App\Http\Controllers\EmailController;
use App\Http\Helper\FacebookHelper;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\CarType;
use App\Models\DriverAddress;
use App\Models\DriverDocuments;
use App\Models\PasswordResets;
use App\Models\ProfilePicture;
use App\Models\RiderLocation;
use App\Models\PayoutPreference;
use App\Models\PayoutCredentials;
use App\Models\PaymentGateway;
use App\Models\Country;
use App\Models\Currency;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\BankDetail;
use Auth;
use App;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Session;
use Validator;
use Input;
use Google_Client;

class UserController extends BaseController {
	protected $request_helper; // Global variable for Helpers instance

	private $fb; // Global variable for FacebookHelper instance

	public function __construct(RequestHelper $request, FacebookHelper $fb) {
		$this->request_helper = $request;
		$this->helper = new Helpers;
		$this->fb = $fb;
	}
	public function signin() {
		return view('user.signin');
	}
	public function signup() {
		return view('user.signup');
	}

	/**
	 * Redirect the user to the Facebook authentication page.
	 *
	 * @return Response
	 */
	public function facebook_login() {
		// $this->helper->flash_message('danger', trans('messages.facebook_https_error')); // Call flash message function
		// return redirect('signin_rider'); // Redirect to contact page

		return redirect(FB_URL);
	}

	//login functionality Rider
	public function login(Request $request) {
		$data = $request;

		$data = json_decode($data['data']); // AngularJS data decoding
		foreach ($data as $key => $credential) {
			if ($key == 'email_phone') {
				if (is_numeric($credential)) {

					if (strlen($credential) < 6) {
						return ['status' => 'false', 'error' => trans('messages.home.invalid_mobile_no'), 'success' => 'false'];
					}
					if ($data->user_type=='Company') {
						$company = Company::where('mobile_number', $credential)->first();
						if ($company) {
							if ($company->status == "Inactive") {
								Auth::guard('company')->logout();
								return ['status' => 'false', 'error' => trans('messages.user.disabled_company_account'), 'success' => 'true'];
							}
							Session::put('login_type', 'mobile_number');
							return ['status' => 'true', 'error' => '', 'success' => 'false', 'user_detail' => '+' . $company->country_code . ' ' . $company->mobile_number];
						} else {
							return ['status' => 'false', 'error' => trans('messages.user.no_recognize') . SITE_NAME, 'success' => 'false'];
						}
					}else{
						$user = User::where('mobile_number', $credential)->where('user_type', $data->user_type)->first();
						if ($user) {
							if ($user->status == "Inactive") {
								Auth::guard('web')->logout();
								return ['status' => 'false', 'error' => trans('messages.user.disabled_account'), 'success' => 'true'];
							}
							Session::put('login_type', 'mobile_number');
							return ['status' => 'true', 'error' => '', 'success' => 'false', 'user_detail' => '+' . $user->country_code . ' ' . $user->mobile_number];
						} else {
							return ['status' => 'false', 'error' => trans('messages.user.no_recognize') . SITE_NAME, 'success' => 'false'];
						}
					}

				} elseif (filter_var($credential, FILTER_VALIDATE_EMAIL)) {
					if ($data->user_type=='Company') {
						$company = Company::where('email', $credential)->first();
						if ($company) {
							if ($company->status == "Inactive") {
								Auth::guard('company')->logout();
								return ['status' => 'false', 'error' => trans('messages.user.disabled_company_account'), 'success' => 'true'];
							}
							Session::put('login_type', 'email');
							Session::put('email', $company->email);
							return ['status' => 'true', 'error' => '', 'success' => 'false', 'user_detail' => $company->email];
						} else {
							return ['status' => 'false', 'error' => trans('messages.user.no_recognize_email',['site'=>SITE_NAME]), 'success' => 'false'];
						}
					}else{
						$user = User::where('email', $credential)->where('user_type', $data->user_type)->first();
						if ($user) {
							if ($user->status == "Inactive") {
								Auth::guard('web')->logout();
								return ['status' => 'false', 'error' => trans('messages.user.disabled_account'), 'success' => 'true'];
							}
							Session::put('login_type', 'email');
							Session::put('email', $user->email);
							return ['status' => 'true', 'error' => '', 'success' => 'false', 'user_detail' => $user->email];
						} else {
							return ['status' => 'false', 'error' => trans('messages.user.no_recognize_email',['site'=>SITE_NAME]), 'success' => 'false'];
						}
					}

				} else {
					return ['error' => trans('messages.account.valid_email'), 'status' => 'false', 'success' => 'false'];
				}

			} elseif ($key == 'password') {

				if (Session::get('login_type') == 'email' || Session::get('login_type') == 'mobile_number') {
					if (is_numeric($data->email)) {

						if ($data->user_type=='Company') {
							$guard = Auth::guard('company')->attempt(['mobile_number' => $data->email, 'password' => $data->password]);
						}else{
							$guard = Auth::guard('web')->attempt(['mobile_number' => $data->email, 'password' => $data->password, 'user_type' => $data->user_type]);
						}

						if ($guard) {
							if ($data->user_type=='Company') {
								if (Auth::guard('company')->user()->status=='Pending') {
									$this->helper->flash_message('success', 'Your profile status is in  pending.If your are not submit your profile detail please provide it.Otherwise please wait until admin verify your account.');
								}elseif (Auth::guard('company')->user()->status=='Inactive') {
									$this->helper->flash_message('danger', 'Admin deactivate your account..');
								}
							}
							return ['status' => 'true', 'error' => '', 'success' => 'true'];
						} else {
							return ['error' => trans('messages.user.no_paswrd') , 'status' => 'false', 'success' => 'false'];
						}

					} else {

						if ($data->user_type=='Company') {
							$guard = Auth::guard('company')->attempt(['email' => $data->email, 'password' => $data->password]);
						}else{
							$guard = Auth::guard('web')->attempt(['email' => $data->email, 'password' => $data->password, 'user_type' => $data->user_type]);
						}

						if ($guard) {
							if ($data->user_type=='Company') {
								if (Auth::guard('company')->user()->status=='Pending') {
									$this->helper->flash_message('success', 'Your profile status is in  pending.If your are not submit your profile detail please provide it.Otherwise please wait until admin verify your account.');
								}elseif (Auth::guard('company')->user()->status=='Inactive') {
									$this->helper->flash_message('danger', 'Admin deactivate your account..');
								}
							}
							return ['status' => 'true', 'error' => '', 'success' => 'true'];
						} else {
							return ['error' =>  trans('messages.user.no_paswrd'), 'status' => 'false', 'success' => 'false'];
						}
					}

				}
			}
		}

	}
	//login functionality Driver
	public function login_driver(Request $request) {
		$rules = array(
			'email' => 'required|email',
			'password' => 'required|min:6',
		);

		$messages = array(
			'required'                => ':attribute '.trans('messages.home.field_is_required').'',
		);


		$niceNames = array(
			'email' => trans('messages.user.email'),
			'password' => trans('messages.user.paswrd'),
		);

		$validator = Validator::make($request->all(), $rules, $messages);
		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput(); // Form calling with
		} else {
			if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password, 'user_type' => 'Driver'])) {

				// $this->helper->flash_message('success', trans('messages.reg_successfully')); // Call flash message function
				return redirect()->intended('driver_profile'); // Redirect to dashboard page

			} else {
				// $this->helper->flash_message('danger', trans('messages.login_failed')); // Call flash message function
				// return redirect('signin_driver'); // Redirect to login page
				return back()->withErrors(['password' => 'Invalid credentials'])->withInput();
			}
		}
	}
	public function signin_driver() {
		return view('user.signin_driver');
	}

	public function signin_company() {
		if (Auth::guard('company')->user()!=null) {
			return redirect('company/dashboard'); // Redirect to dashboard page
		}
		return view('user.signin_company');
	}

	public function signin_rider() {
		return view('user.signin_rider');
	}

	public function forgot_password() {
		return view('user.forgot_password');
	}

	public function forgotpassword(Request $request, EmailController $email_controller) {
		if ($request->user_type == 'Company') {
			$rules = array(
				'email' => 'required|email|exists:companies,email',
			);
		}else{
			$rules = array(
				'email' => 'required|email|exists:users,email,user_type,' . $request->user_type,
			);
		}

		// Email validation custom messages
		$messages = array(

			'required'                => ':attribute '.trans('messages.home.field_is_required').'',

			'exists' => trans('messages.user.email_exists'),
		);

		// Email validation custom Fields name
		$niceNames = array(
			'email' => trans('messages.user.email'),
		);

		$validator = Validator::make($request->all(), $rules, $messages);
		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput()->with('error_code', 4); // Form calling with Errors and Input values
		} else {

			if ($request->user_type == 'Company') {
				$company = Company::whereEmail($request->email)->first();
				$email_controller->company_forgot_password_link($company);
				$this->helper->flash_message('success', trans('messages.user.link') . $company->email);
				return redirect('signin_company');
			}else{
				$user = User::whereEmail($request->email)->first();
				$email_controller->forgot_password_link($user);
				$this->helper->flash_message('success', trans('messages.user.link') . $user->email); // Call flash message function
				if ($user->user_type == 'Rider') {
					return redirect('signin_rider');
				} else {
					return redirect('signin_driver');
				}
			}

		}
	}
	// public function signup_rider() 
	// {
	//     $request = request();
	//     $data = array();


	// 	if($request->code) {
		  
	// 		$token_exchange_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/access_token?'.
	// 		'grant_type=authorization_code'.
	// 		'&code='.$request->code.
	// 		"&access_token=AA|".ACCOUNTKIT_APP_ID."|".ACCOUNTKIT_APP_SECRET;
	// 		$data = $this->doCurl($token_exchange_url);

	// 		if(isset($data['error'])) {
	// 			return view('user.signup_driver');
	// 		}

	// 		$user_id = $data['id'];
	// 		$user_access_token = $data['access_token'];
	// 		$refresh_interval = $data['token_refresh_interval_sec'];

	// 		// Get Account Kit information
	// 		$me_endpoint_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/me?'.
	// 		'access_token='.$user_access_token;
	// 		$data = $this->doCurl($me_endpoint_url);

	// 		$country_code = $data['phone']['country_prefix'];
	// 		$mobile_number = $data['phone']['national_number'];
	// 		$type ='Rider';

	// 		$user = User::where('mobile_number', $mobile_number)->where('user_type', $type)->get();

	// 		if(count($user)) {
	// 	        $this->helper->flash_message('success', trans('messages.mobile_number_exist')); // Call flash message function
	// 	        return redirect('signup_rider');
	// 		}

	// 		$data['country_code'] = $country_code;
	// 		$data['phone_number'] = $mobile_number;
	// 		$data['kit_id'] 	  = $user_id;
	// 	}
    
	// 	$fb_user_data = Session::get('fb_user_data');
		
	// 	if ($fb_user_data) {
	// 		$data['user'] = $fb_user_data;
	// 	}

	// 	return view('user.signup_rider', $data);
	// }

	public function signup_rider() {

		$fb_user_data = Session::get('fb_user_data');
		$data = array();
		if ($fb_user_data) {
			$data['user'] = $fb_user_data;
		}

		return view('user.signup_rider', $data);
	}



	public function doCurl($url) {
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  $data = json_decode(curl_exec($ch), true);
	  curl_close($ch);
	  return $data;
	}


	public function signup_driver(Request $request) {
		$data = array();

		if($request->code) {
			  
			$token_exchange_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/access_token?'.
			'grant_type=authorization_code'.
			'&code='.$request->code.
			"&access_token=AA|".ACCOUNTKIT_APP_ID."|".ACCOUNTKIT_APP_SECRET;
			$data = $this->doCurl($token_exchange_url);
			
			if(isset($data['error'])) {
				return view('user.signup_driver');
			}

			$user_id = $data['id'];
			$user_access_token = $data['access_token'];
			$refresh_interval = $data['token_refresh_interval_sec'];

			// Get Account Kit information
			$me_endpoint_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/me?'.
			'access_token='.$user_access_token;
			$data = $this->doCurl($me_endpoint_url);

			$country_code = $data['phone']['country_prefix'];
			$mobile_number = $data['phone']['national_number'];
			$type ='Driver';

			$user = User::where('mobile_number', $mobile_number)->where('user_type', $type)->get();

			if(count($user)) {

                $this->helper->flash_message('success', trans('messages.mobile_number_exist')); // Call flash message function
                return redirect('signup_driver');
			}

			$data['country_code'] = $country_code;
			$data['phone_number'] = $mobile_number;
			$data['kit_id'] 	  = $user_id;
		}

		if (@$request->step == 'car_details') {
			if ($request->user_id == Session::get('id')) {
				return view('user.driver_cardetails',$data);
			} else {
				return view('user.signup_driver',$data);
			}

		}
		return view('user.signup_driver',$data);
	}
	public function signup_company(Request $request) {
		if (Auth::guard('company')->user()!=null) {
			return redirect('company/dashboard'); // Redirect to dashboard page
		}
		return view('user.signup_company');
	}
	public function company_register(Request $request) {
		if (isset($request->code)) {
			$token_exchange_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/access_token?'.'grant_type=authorization_code'.'&code='.$request->code."&access_token=AA|".ACCOUNTKIT_APP_ID."|".ACCOUNTKIT_APP_SECRET;
				$data = $this->doCurl($token_exchange_url);
				if(isset($data['error'])) {
					return view('user.signup_company');
				}

				$user_id = $data['id'];
				$user_access_token = $data['access_token'];
				$refresh_interval = $data['token_refresh_interval_sec'];

				// Get Account Kit information
				$me_endpoint_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/me?'.
				'access_token='.$user_access_token;
				$data = $this->doCurl($me_endpoint_url);

				Input::merge(['mobile_number' => $data['phone']['national_number']]);
				Input::merge(['country_code' => $data['phone']['country_prefix']]);
		}
		$rules = array(
			'name' => 'required',
			'email' => 'required|email',
			'mobile_number' => 'required|numeric|regex:/[0-9]{6}/',
			'password' => 'required|min:6',
			'country_code' => 'required',
		);

		$messages = array(

			'required'                => ':attribute '.trans('messages.home.field_is_required').'',

			'mobile_number.regex' => trans('messages.user.mobile_no'),
		);

		$niceNames = array(
			'name' => trans('messages.profile.name'),
			'email' => trans('messages.user.email'),
			'password' => trans('messages.user.paswrd'),
			'country_code' => trans('messages.user.country_code'),
			'mobile_number' => trans('messages.user.mobile'),
		);

		$validator = Validator::make($request->all(), $rules, $messages);

		$validator->after(function ($validator) use($request) {
			$company = Company::where('mobile_number', $request->mobile_number)->get();

			$company_email = Company::where('email', $request->email)->get();

			if (count($company)) {
				$validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists')); // Form calling with Errors and Input values
			}

			if (count($company_email)) {
				$validator->errors()->add('email',trans('messages.user.email_exists')); // Form calling with Errors and Input values
			}
		});
		$validator->setAttributeNames($niceNames);

		if ($request->request_type == 'validation') {
			return json_encode($validator->errors());
		}else if ($validator->fails()) {
			return back()->withErrors($validator)->withInput(); // Form calling with
		}else {
			
			$company = new Company;
			$company->name = $request->name;
			$company->email = $request->email;
			$company->country_code = $request->country_code;
			$company->mobile_number = $request->mobile_number;
			$company->password = bcrypt($request->password);
			$company->save();

			if (Auth::guard('company')->attempt(['email' => $request->email, 'password' => $request->password])) {

				$this->helper->flash_message('success', trans('messages.user.register_successfully')); // Call flash message function
				return redirect('company/edit_company/'.$company->id); // Redirect to dashboard page

			} else {
				// $this->helper->flash_message('danger', trans('messages.login_failed')); // Call flash message function
				return redirect('signin_company'); // Redirect to login page
			}
		}

	}
	public function rider_register(Request $request) {
		if (isset($request->code)) {
			$token_exchange_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/access_token?'.'grant_type=authorization_code'.'&code='.$request->code."&access_token=AA|".ACCOUNTKIT_APP_ID."|".ACCOUNTKIT_APP_SECRET;
				$data = $this->doCurl($token_exchange_url);
				if(isset($data['error'])) {
					return view('user.signup_driver');
				}

				$user_id = $data['id'];
				$user_access_token = $data['access_token'];
				$refresh_interval = $data['token_refresh_interval_sec'];

				// Get Account Kit information
				$me_endpoint_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/me?'.
				'access_token='.$user_access_token;
				$data = $this->doCurl($me_endpoint_url);

				Input::merge(['mobile_number' => $data['phone']['national_number']]);
				Input::merge(['country_code' => $data['phone']['country_prefix']]);
		}
		$rules = array(
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email',
			'mobile_number' => 'required|numeric|regex:/[0-9]{6}/',
			'password' => 'required|min:6',
			'country_code' => 'required',
			'user_type' => 'required',
		);

		$messages = array(

			'required'                => ':attribute '.trans('messages.home.field_is_required').'',

			'mobile_number.regex' => trans('messages.user.mobile_no'),
		);

		$niceNames = array(
			'first_name' => trans('messages.user.firstname'),
			'last_name' => trans('messages.user.lastname'),
			'email' => trans('messages.user.email'),
			'password' => trans('messages.user.paswrd'),
			'country_code' => trans('messages.user.country_code'),
			'user_type' => trans('messages.user.user_type'),
			'mobile_number' => trans('messages.user.mobile'),
		);

		$validator = Validator::make($request->all(), $rules, $messages);
		$validator = Validator::make($request->all(), $rules, $messages);
			
		$validator->after(function ($validator) use($request) {
			$user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->get();

			$user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->get();

			if (count($user)) {
				$validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists')); // Form calling with Errors and Input values
			}

			if (count($user_email)) {
				$validator->errors()->add('email',trans('messages.user.email_exists')); // Form calling with Errors and Input values
			}
		});
		$validator->setAttributeNames($niceNames);

		if ($request->request_type == 'validation') {
			return json_encode($validator->errors());
		}else if ($validator->fails()) {
			return back()->withErrors($validator)->withInput(); // Form calling with
		} else {
			$user = new User;
			$user->first_name = $request->first_name;
			$user->last_name = $request->last_name;
			$user->email = $request->email;
			$user->country_code = $request->country_code;
			$user->mobile_number = $request->mobile_number;
			$user->password = bcrypt($request->password);
			$user->user_type = $request->user_type;

			if ($request->fb_id != null && $request->fb_id != "") {
				$user->fb_id = $request->fb_id;
			}

			$user->save();

			$user_pic = new ProfilePicture;

			$user_pic->user_id = $user->id;
			if ($request->fb_id != null && $request->fb_id != "") {
				$user_pic->src = "https://graph.facebook.com/" . $request->fb_id . "/picture?type=large";
				$user_pic->photo_source = 'Facebook';
				Session::forget('fb_user_data');
			} else {
				$user_pic->src = "";
				$user_pic->photo_source = 'Local';
			}

			$user_pic->save();

			$location = new RiderLocation;

			$location->user_id = $user->id;
			$location->home = '';
			$location->work = '';
			$location->home_latitude = '';
			$location->home_longitude = '';
			$location->work_latitude = '';
			$location->work_longitude = '';

			$location->save();

			if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password, 'user_type' => 'Rider'])) {

				$this->helper->flash_message('success', trans('messages.user.register_successfully')); // Call flash message function
				return redirect()->intended('trip'); // Redirect to dashboard page

			} else {
				// $this->helper->flash_message('danger', trans('messages.login_failed')); // Call flash message function
				return redirect('signin_rider'); // Redirect to login page
			}
		}

	}
	public function driver_register(Request $request) {
		// dd(App::getLocale());
		if ($request->step == 'basics') {
			if (isset($request->code)) {
				$token_exchange_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/access_token?'.'grant_type=authorization_code'.'&code='.$request->code."&access_token=AA|".ACCOUNTKIT_APP_ID."|".ACCOUNTKIT_APP_SECRET;
					$data = $this->doCurl($token_exchange_url);
					if(isset($data['error'])) {
						return view('user.signup_driver');
					}

					$user_id = $data['id'];
					$user_access_token = $data['access_token'];
					$refresh_interval = $data['token_refresh_interval_sec'];

					// Get Account Kit information
					$me_endpoint_url = 'https://graph.accountkit.com/'.ACCOUNTKIT_VERSION.'/me?'.
					'access_token='.$user_access_token;
					$data = $this->doCurl($me_endpoint_url);

					Input::merge(['mobile_number' => $data['phone']['national_number']]);
					Input::merge(['country_code' => $data['phone']['country_prefix']]);
			}

			$rules = array(
				'first_name' => 'required',
				'last_name' => 'required',
				'email' => 'required|email',
				'mobile_number' => 'required|numeric|regex:/[0-9]{6}/',
				'password' => 'required|min:6',
				'home_address' => 'required',
				'user_type' => 'required',
				// 'payout_id'     => 'required',
				// 'status'        => 'required',
				// 'license_front' => 'required|mimes:jpg,jpeg,png,gif',
				// 'license_back'  => 'required|mimes:jpg,jpeg,png,gif',
				// 'insurance'     => 'required|mimes:jpg,jpeg,png,gif',
				// 'rc'            => 'required|mimes:jpg,jpeg,png,gif',
				// 'permit'        => 'required|mimes:jpg,jpeg,png,gif',
				// 'vehicle_id'    => 'required',
				// 'vehicle_name'  => 'required',
				// 'vehicle_number'=> 'required',
			);

			// Add Driver Validation Custom Names
			$niceNames = array(
				'first_name' => trans('messages.user.firstname'),
				'last_name' => trans('messages.user.lastname'),
				'email' => trans('messages.user.email'),
				'password' => trans('messages.user.paswrd'),
				'home_address' => trans('messages.account.city'),
				'user_type' => trans('messages.user.user_type'),
				'mobile_number' => trans('messages.user.mobile'),
				// 'payout_id'     => 'Paypal email id',
				// 'status'        => 'Status',
				// 'vehicle_id'    => 'Vehicle Type',
				// 'license_front' => 'Driver\'s License - (Front)',
				// 'license_back'  => 'Driver\'s License - ( Back/Reverse) ',
				// 'insurance'     => 'Motor insurance Certificate',
				// 'rc'            => 'Certificate of Registration',
				// 'permit'        => 'Contact Carriage Permit',
			);
			// Edit Rider Validation Custom Fields message
			$messages = array(

				'required'                => ':attribute '.trans('messages.home.field_is_required').'',

				'mobile_number.regex' => trans('messages.user.mobile_no'),
			);
			$validator = Validator::make($request->all(), $rules, $messages);
			
			$validator->after(function ($validator) use($request) {
				$user = User::where('mobile_number', $request->mobile_number)->where('user_type', $request->user_type)->get();

				$user_email = User::where('email', $request->email)->where('user_type', $request->user_type)->get();

				if (count($user)) {
					$validator->errors()->add('mobile_number',trans('messages.user.mobile_no_exists')); // Form calling with Errors and Input values
				}

				if (count($user_email)) {
					$validator->errors()->add('email',trans('messages.user.email_exists')); // Form calling with Errors and Input values
				}
			});
			$validator->setAttributeNames($niceNames);

			if ($request->request_type == 'validation') {
				return json_encode($validator->errors());
			}else if ($validator->fails()) {
				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {
				$user = new User;

				$user->first_name = $request->first_name;
				$user->last_name = $request->last_name;
				$user->email = $request->email;
				$user->country_code = $request->country_code;
				$user->mobile_number = $request->mobile_number;
				$user->password = bcrypt($request->password);
				$user->user_type = $request->user_type;
				$user->company_id = 1;

				$user->status = 'Car_details';

				$user->save();

				$user_pic = new ProfilePicture;

				$user_pic->user_id = $user->id;
				$user_pic->src = "";
				$user_pic->photo_source = 'Local';

				$user_pic->save();

				$user_address = new DriverAddress;

				$user_address->user_id = $user->id;
				$user_address->address_line1 = $request->address_line1 ? $request->address_line1 : '';
				$user_address->address_line2 = $request->address_line2 ? $request->address_line2 : '';
				$user_address->city = $request->city ? $request->city : '';
				$user_address->state = $request->state ? $request->state : '';
				$user_address->postal_code = $request->postal_code ? $request->postal_code : '';

				$user_address->save();
				//store info for login
				Session::put('id', $user->id);
				Session::put('password', $request->password);

				return redirect('signup_driver?step=car_details&user_id=' . $user->id);
			}

		} else if ($request->step == 'car_details') {
			$rules = array(
				'vehicle_name' => 'required',
				'vehicle_number' => 'required',
				'vehicle_type' => 'required',
			);

			// Add Driver Validation Custom Names
			$niceNames = array(
				'vehicle_name' => trans('messages.user.veh_name'),
				'vehicle_number' => trans('messages.user.veh_no'),
				'vehicle_type' => trans('messages.user.veh_type'),
			);
			// Edit Rider Validation Custom Fields message

			$messages = array(
				'required'=> ':attribute '.trans('messages.home.field_is_required').'',
			);
			$validator = Validator::make($request->all(), $rules, $messages);

			$validator = Validator::make($request->all(), $rules);

			$validator->setAttributeNames($niceNames);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {

				$user = User::find(Session::get('id'));
				$user->status = 'Document_details';
				$user->save();
				if ($user) {
					$vehicle = Vehicle::where('user_id', $user->id)->first();
					if ($vehicle == null) {
						$vehicle = new Vehicle;
						$vehicle->user_id = $user->id;
						$vehicle->company_id = $user->company_id;
					}
					$vehicle->vehicle_name = $request->vehicle_name;
					$vehicle->vehicle_number = $request->vehicle_number;
					$vehicle->vehicle_id = $request->vehicle_type;
					$vehicle->vehicle_type = CarType::find($request->vehicle_type)->car_name;
					$vehicle->status = 'Inactive';
					$vehicle->save();

					$driver_doc = DriverDocuments::where('user_id', $user->id)->first();
					if ($driver_doc == null) {
						$driver_doc = new DriverDocuments;
						$driver_doc->user_id = $user->id;
						$driver_doc->document_count = 0;
						$driver_doc->save();
					}

					if (Auth::guard('web')->attempt(['email' => $user->email, 'password' => Session::get('password'), 'user_type' => 'Driver'])) {

						$this->helper->flash_message('success', trans('messages.user.register_successfully')); // Call flash message function
						return redirect()->intended('driver_profile'); // Redirect to dashboard page

					} else {
						return redirect('signup_driver');
					}
					// return redirect('signup_driver?step=document_upload&token='.$request->_token);
				} else {
					return redirect('signup_driver');

				}
			}
		} elseif ($request->step == 'document_upload') {
			// dd('dsf');
		} else {
			return redirect('signup_driver');
		}

	}

	// Rider Facebook login
	public function facebookAuthenticate(Request $request, EmailController $email_controller) {
		if ($request->error_code == 200) {
			// $this->helper->flash_message('danger', $request->error_description); // Call flash message function
			return redirect('signin_rider'); // Redirect to login page
		}

		$this->fb->generateSessionFromRedirect(); // Generate Access Token Session After Redirect from Facebook

		$response = $this->fb->getData(); // Get Facebook Response

		$userNode = $response->getGraphUser(); // Get Authenticated User Data

		// $email = ($userNode->getProperty('email') == '') ? $userNode->getId().'@fb.com' : $userNode->getProperty('email');
		$email = $userNode->getProperty('email');
		$fb_id = $userNode->getId();

		$user = User::user_facebook_authenticate($email, $fb_id); // Check Facebook User Email Id is exists

		if ($user->count() > 0) // If there update Facebook Id
		{
			$user = User::user_facebook_authenticate($email, $fb_id)->first();

			$user->fb_id = $userNode->getId();

			$user->save(); // Update a Facebook id

			$user_id = $user->id; // Get Last Updated Id
		} else // If not create a new user without Password
		{
			$user = User::user_facebook_authenticate($email, $fb_id);

			if ($user->count() > 0) {
				/*$data['title'] = 'Disabled ';
                return view('users.disabled', $data);*/
				return redirect('user_disabled');
			}

			$user = new User;

			// New user data
			$user->first_name = $userNode->getFirstName();
			$user->last_name = $userNode->getLastName();
			$user->email = $email;
			$user->fb_id = $userNode->getId();

			if ($email == '') {
				$user = array(
					'first_name' => $userNode->getFirstName(),
					'last_name' => $userNode->getLastName(),
					'email' => $email,
					'fb_id' => $userNode->getId(),
				);
				Session::put('fb_user_data', $user);
				return redirect('signup_rider');
			}
			$user->status = 'Active'; //user activated
			$user->user_type = 'Rider';
			$user->save(); // Create a new user

			$user_id = $user->id; // Get Last Insert Id

			$user_pic = new ProfilePicture;

			$user_pic->user_id = $user_id;
			$user_pic->src = "https://graph.facebook.com/" . $userNode->getId() . "/picture?type=large";
			$user_pic->photo_source = 'Facebook';

			$user_pic->save(); // Save Facebook profile picture

			// $email_controller->welcome_email_confirmation($user);

		}

		$users = User::where('id', $user_id)->where('user_type', 'Rider')->first();

		if (@$users->status != 'Inactive') {
			if (Auth::loginUsingId($user_id)) // Login without using User Id instead of Email and Password
			{

				return redirect()->intended('trip'); // Redirect to dashboard page
			} else {
				$this->helper->flash_message('danger', trans('messages.user.login_failed')); // Call flash message function
				return redirect('signin_rider'); // Redirect to login page
			}
		} else // Call Disabled view file for Inactive user
		{
			/*$data['title'] = 'Disabled ';
            return view('users.disabled', $data);*/
			return redirect('user_disabled');
		}
	}

	/**
     * Google User Registration and Login
     *
     * @return redirect to dashboard page
     */
    public function googleAuthenticate(Request $request)
    {
    	try {
            $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);  
            // Specify the CLIENT_ID of the app that accesses the backend
            $payload = $client->verifyIdToken($request->idtoken);

	        if ($payload) {
	            $google_id = $payload['sub'];
	        } 
	        else {
	            $this->helper->flash_message('danger', trans('messages.user.login_failed'));
	            return redirect('signin_rider');
	        }
        }
        catch(\Exception $e) {
            $this->helper->flash_message('danger', $e->getMessage());
            return redirect('signin_rider');
        }

        // Get Details From Google
        $firstName 	= $payload['given_name'];
        $lastName 	= isset($payload['family_name']) ? $payload['family_name'] : '';
        $email = ($payload['email'] == '') ? $google_id.'@gmail.com' : $payload['email'];
        $prev_count = User::user_google_authenticate($email, $google_id)->count();

        if($prev_count > 0 ) {
        	$user = User::user_google_authenticate($email, $google_id)->first();
			$user->google_id = $google_id;
			$user->save();
			$user_id = $user->id;
        }
        else {
        	$this->helper->flash_message('danger', trans('messages.user.google_login_failed'));
			return redirect('signin_rider');
		}

		$user = User::where('id', $user_id)->where('user_type', 'Rider')->first();

		if ($user->status != 'Inactive') {
			if(Auth::loginUsingId($user_id)) {
				return redirect()->intended('trip');
			}
			else {
				$this->helper->flash_message('danger', trans('messages.user.login_failed'));
				return redirect('signin_rider');
			}
		}
		else {
			return redirect('user_disabled');
		}
    }

	// User Disabled Page
	public function user_disabled() {
		$data['title'] = 'Disabled ';
		return view('user.disabled', $data);
	}
	/**
     * Add a Payout Method and Load Payout Preferences File
     *
     * @param array $request Input values
     * @return redirect to Payout Preferences page and load payout preferences view file
     */
    public function payout_preferences(Request $request, EmailController $email_controller)
    {
        if($request->isMethod('get'))
        { 
            $data['bank_detail'] = BankDetail::where('user_id',Auth::user()->id)->get();
            return view('driver_dashboard/payout_preferences', $data);
        }
        else
        {
        	
    		$rules = array(
    			'account_holder_name' => 'required',
                'account_number' => 'required',
                'bank_name' => 'required',
                'bank_location' => 'required',
            );

            $niceNames = array(
                'account_holder_name'  => trans('messages.account.holder_name'),
                'account_number'  => trans('messages.account.account_number'),
                'bank_name'  => trans('messages.account.bank_name'),
                'bank_location'  => trans('messages.account.bank_location'),
            );

    		$messages   = array('required'=> ':attribute '.trans('messages.home.field_is_required').'',);
            $validator = Validator::make($request->all(), $rules,$messages);

            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
            	return json_encode($validator->errors());
                // return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
           
           	// $bank_detail = BankDetail::where('user_id',Auth::user()->id)->first();
    		// if($bank_detail==null){
        	$bank_detail = new BankDetail;
    		// }
            $bank_detail->user_id = Auth::user()->id;
            $bank_detail->holder_name = $request->account_holder_name;
            $bank_detail->account_number = $request->account_number;
            $bank_detail->bank_name = $request->bank_name;
            $bank_detail->bank_location = $request->bank_location;
            

            $payout_check = BankDetail::where('user_id', Auth::user()->id)->where('is_default','yes')->get();

            if($payout_check->count() == 0)
            {
                $bank_detail->is_default = 'yes';
            }
            $bank_detail->save();
            $email_controller->payout_preferences($bank_detail->id);
        	$this->helper->flash_message('success', trans('messages.account.payout_added')); // Call flash message function

            return json_encode(array());
            return redirect('payout_preferences/'.Auth::user()->id);
        }
    }

    // stripe account creation
    public function update_payout_preferences(Request $request, EmailController $email_controller)
    {        

        $country_data = Country::where('short_name', $request->country)->first();

        if (!$country_data) {
            $message = trans('messages.user.service_not_available_country');
            $this->helper->flash_message('error', $message); // Call flash message function
               return back();
        }

        /*** required field validation --start-- ***/        
        $country = $request->country;

        
        $rules = array(
            'country' =>    'required',
            'currency' =>    'required',            
            'account_number' =>    'required',
            'holder_name' =>    'required',            
            'stripe_token'  => 'required',
            'address1'  => 'required',
            'city'  => 'required',            
            'postal_code'  => 'required',
            'document' => 'required|mimes:png,jpeg,jpg',

        ); 

        $user_id = Auth::user()->id; 


        $user  = User::find($user_id);

        // custom required validation for Japan country       
        if($country == 'JP')
        {

            $rules['phone_number'] = 'required';
            $rules['bank_name'] = 'required';
            $rules['branch_name'] = 'required';
            $rules['address1'] = 'required';
            $rules['kanji_address1'] = 'required';
            $rules['kanji_address2'] = 'required';
            $rules['kanji_city'] = 'required';
            $rules['kanji_state'] = 'required';
            $rules['kanji_postal_code'] = 'required';

            if(!$user->gender)
            {
                $rules['gender'] = 'required|in:male,female';
            }
        
        }
        // custom required validation for US country      
        else if($country == 'US')
        {
            $rules['ssn_last_4'] = 'required|digits:4';
        }

        $nice_names = array(
            'payout_country' =>    trans('messages.account.country'),
            'currency' =>    trans('messages.account.currency'),
            'routing_number' =>    trans('messages.account.routing_number'),
            'account_number' =>    trans('messages.account.account_number'),
            'holder_name' =>    trans('messages.account.holder_name'),
            'additional_owners' => trans('messages.account.additional_owners'),
            'business_name' => trans('messages.account.business_name'),
            'business_tax_id' => trans('messages.account.business_tax_id'),
            'holder_type' =>    trans('messages.account.holder_type'),
            'stripe_token' => 'Stripe Token', 
            'address1'  => trans('messages.account.address'),
            'city'  => trans('messages.account.city'),
            'state'  => trans('messages.account.state'),
            'postal_code'  => trans('messages.account.postal_code'),
            'document'  => trans('messages.account.legal_document'),
            'ssn_last_4'  => trans('messages.account.ssn_last_4'),            
        );

        $messages   = array('required'=> ':attribute '.trans('messages.home.field_is_required').'',);


        $validator  = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($nice_names); 
         
        if($validator->fails()) 
        {
          return back()->withErrors($validator)->withInput();        
                            
        }      
        /*** required field validation --end-- ***/

        
        $stripe_data    = PaymentGateway::where('site', 'Stripe')->pluck('value','name');

        \Stripe\Stripe::setApiKey($stripe_data['secret']);    

        $account_holder_type = 'individual';       

        /*** create stripe account ***/
        try
        {
                $recipient = \Stripe\Account::create(array(
                  "country" => strtolower($request->country),
                   "payout_schedule" => array(
                            "interval" => "manual"
                        ), 
                  "tos_acceptance" => array(
                        "date" => time(),
                        "ip"    => $_SERVER['REMOTE_ADDR']
                    ),
                  "type"    => "custom",
                ));
        }
        catch(\Exception $e)
        {
            $this->helper->flash_message('error', $e->getMessage());
            return back();
        }

        $recipient->email = Auth::user()->email;

        // create external account using stripe token --start-- //

        try{
            $recipient->external_accounts->create(array(
                "external_account" => $request->stripe_token,
            ));
        }catch(\Exception $e){   
            $this->helper->flash_message('error', $e->getMessage());
            return back();
        }
        // create external account using stripe token --end-- //
        try
        {
            // insert stripe external account datas --start-- //
            if($request->country != 'JP')
            {
                // for other countries //
                $recipient->legal_entity->type = $account_holder_type;            
                $recipient->legal_entity->first_name = $user->first_name;
                $recipient->legal_entity->last_name= $user->last_name;
                $recipient->legal_entity->dob->day= 15;
                $recipient->legal_entity->dob->month= 04;
                $recipient->legal_entity->dob->year= 1996;
                $recipient->legal_entity->address->line1= @$request->address1;
                $recipient->legal_entity->address->line2= @$request->address2 ? @$request->address2  : null;
                $recipient->legal_entity->address->city= @$request->city;
                $recipient->legal_entity->address->country= @$request->country;
                $recipient->legal_entity->address->state= @$request->state ? @$request->state : null;
                $recipient->legal_entity->address->postal_code= @$request->postal_code;
                if($request->country == 'US')
                {              
                  $recipient->legal_entity->ssn_last_4 = $request->ssn_last_4;
                }
            }
            else
            {
                // for Japan country //
                $address = array(
                                    'line1'         => $request->address1,
                                    'line2'         => $request->address2,
                                    'city'          => $request->city,
                                    'state'         => $request->state,
                                    'postal_code'   => $request->postal_code,
                                    );
                $address_kana = array(
                                    'line1'         => $request->address1,
                                    'town'         => $request->address2,
                                    'city'          => $request->city,
                                    'state'         => $request->state,
                                    'postal_code'   => $request->postal_code,
                                     'country'       => $request->country,
                                    );
                $address_kanji = array(
                                    'line1'         => $request->kanji_address1,
                                    'town'         => $request->kanji_address2,
                                    'city'          => $request->kanji_city,
                                    'state'         => $request->kanji_state,
                                    'postal_code'   => $request->kanji_postal_code,
                                    'country'       => $request->country,
                                    );

                $recipient->legal_entity->type = $account_holder_type;            
                $recipient->legal_entity->first_name_kana = $user->first_name;
                $recipient->legal_entity->last_name_kana= $user->last_name;
                $recipient->legal_entity->first_name_kanji = $user->first_name;
                $recipient->legal_entity->last_name_kanji= $user->last_name;
                $recipient->legal_entity->dob->day= 15;
                $recipient->legal_entity->dob->month= 04;
                $recipient->legal_entity->dob->year= 1996;
                $recipient->legal_entity->address_kana = $address_kana;
                $recipient->legal_entity->address_kanji = $address_kanji;                          
                $recipient->legal_entity->gender = $request->gender ? $request->gender : strtolower(Auth::user()->gender);                          
                
                $recipient->legal_entity->phone_number = @$request->phone_number ? $request->phone_number : null;

            }
         
            $recipient->save();
            // insert stripe external account datas --end-- //
        }
        catch(\Exception $e)
        {   
            try
            {
                $recipient->delete();
            }
            catch(\Exception $e)
            {
            }
            
            $this->helper->flash_message('error', $e->getMessage());
            return back();
        }

        // verification document upload for stripe account --start-- //
        $document = $request->file('document');
        

        if($request->document) {
            $extension =   $document->getClientOriginalExtension();
            $filename  =   $user_id.'_user_document_'.time().'.'.$extension;
            $filenamepath = dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user_id.'/uploads';
                                
            if(!file_exists($filenamepath))
            {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user_id.'/uploads', 0777, true);
            }
            $success   =   $document->move('images/users/'.$user_id.'/uploads/', $filename);
            if($success)
            {
                $document_path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/users/'.$user_id.'/uploads/'.$filename;
                
                try
                {
                    $stripe_file_details = \Stripe\FileUpload::create(
                      array(
                        "purpose" => "identity_document",
                        "file" => fopen($document_path, 'r')
                      ),
                      array("stripe_account" => @$recipient->id)
                    );

                    $recipient->legal_entity->verification->document = $stripe_file_details->id ;
                    $recipient->save();

                    $stripe_document = $stripe_file_details->id;
                }
                catch(\Exception $e)
                {   
                    $this->helper->flash_message('error', $e->getMessage());
                    return back();
                }

            
            }
            
        }       
        
        // verification document upload for stripe account --end-- //

        // store payout preference data to payout_preference table --start-- //
        $payout_preference = new PayoutPreference;
        $payout_preference->user_id = $user_id;
        $payout_preference->country = $request->country;
        $payout_preference->currency_code = $request->currency;
        $payout_preference->routing_number = $request->routing_number;
        $payout_preference->account_number = $request->account_number;
        $payout_preference->holder_name = $request->holder_name;
        $payout_preference->holder_type = $account_holder_type;
        $payout_preference->paypal_email = @$recipient->id;

        $payout_preference->address1 = @$request->address1;
        $payout_preference->address2 = @$request->address2;
        $payout_preference->city = @$request->city;
        
        $payout_preference->state = @$request->state;
        $payout_preference->postal_code = @$request->postal_code;
        $payout_preference->document_id = $stripe_document;                    
        $payout_preference->document_image =@$filename; 
        $payout_preference->phone_number =@$request->phone_number ? $request->phone_number : ''; 
        $payout_preference->branch_code =@$request->branch_code ? $request->branch_code : ''; 
        $payout_preference->bank_name =@$request->bank_name ? $request->bank_name : ''; 
        $payout_preference->branch_name =@$request->branch_name ? $request->branch_name : ''; 

        $payout_preference->ssn_last_4 = @$request->country == 'US' ? $request->ssn_last_4 : '';
        $payout_preference->payout_method = 'Stripe';

        $payout_preference->address_kanji = @$address_kanji ? json_encode(@$address_kanji) : json_encode([]);

        $payout_preference->save(); 

        $payout_credentials = new PayoutCredentials;
        
        $payout_credentials->user_id = $user_id;

        $payout_credentials->preference_id = $payout_preference->id;
        $payout_credentials->payout_id = @$recipient->id;

        $payout_credentials->type = 'Stripe';

        

        if($request->gender)
        {
            $user->gender = $request->gender; 
            $user->save();
        }

        $payout_check = PayoutCredentials::where('user_id', Auth::user()->id)->where('default','yes')->get();

        if($payout_check->count() == 0)
        {
            $payout_credentials->default = 'yes'; // set default payout preference when no default
           
        }
        else
        {
        	$payout_credentials->default = 'no';

     
        }
        $payout_credentials->save();
        // store payout preference data to payout_preference table --end-- //

        $email_controller->payout_preferences($payout_credentials->id); // send payout preference updated email to host user.
        $this->helper->flash_message('success', trans('messages.account.payout_updated'));
        return back(); 

    }

    public function stripe_payout_preferences(Request $request) {
        $stripe_credentials = PaymentGateway::where('site', 'Stripe')->pluck('value','name');
        \Stripe\Stripe::setApiKey($stripe_credentials['secret']);
        \Stripe\Stripe::setClientId($stripe_credentials['client_id']);
        try {
            $response = \Stripe\OAuth::token([
                'client_secret' => $stripe_credentials['secret'],
                'code'          => $request->code,
                'grant_type'    => 'authorization_code'
            ]);
        }
        catch (\Exception $e)
        {
            $oauth_url = \Stripe\OAuth::authorizeUrl([
                'response_type'    => 'code',
                'scope'    => 'read_write',
                'redirect_uri'  => url('stripe_payout_preferences'),
            ]);
            return redirect($oauth_url);
        }
        $session_payout_data = Session::get('payout_preferences_data');
        if(!$session_payout_data || !@$response['stripe_user_id']) {
            return redirect('payout_preferences/'.Auth::user()->id);
        }
        $session_payout_data->paypal_email = @$response['stripe_user_id'];
        $session_payout_data->payout_method = "Stripe";
        $session_payout_data->save();

        $payout_check = PayoutCredentials::where('user_id', Auth::user()->id)->where('default','yes')->get();

        if($payout_check->count() == 0)
        {
            $session_payout_data->default = 'yes';
            $session_payout_data->save();
        }
        
        Session::forget('payout_preferences_data');
        $this->helper->flash_message('success', trans('messages.account.payout_updated')); // Call flash message function
        return redirect('payout_preferences/'.Auth::user()->id);
    }   

    /**
     * Delete Payouts Default Payout Method
     *
     * @param array $request Input values
     * @return redirect to Payout Preferences page
     */
    public function payout_delete(Request $request, EmailController $email_controller)
    {
        $payout = BankDetail::find($request->id);
        if ($payout==null) {
            return redirect('payout_preferences/'.Auth::user()->id);
        }
        if($payout->is_default == 'yes')
        {
            $this->helper->flash_message('error', trans('messages.account.payout_default')); // Call flash message function
            return redirect('payout_preferences/'.Auth::user()->id);
        }
        else
        {
            $payout->delete();

            $email_controller->payout_preferences($payout->id, 'delete');

            $this->helper->flash_message('success', trans('messages.account.payout_deleted')); // Call flash message function
            return redirect('payout_preferences/'.Auth::user()->id);
        }
    }

    /**
     * Update Payouts Default Payout Method
     *
     * @param array $request Input values
     * @return redirect to Payout Preferences page
     */
    public function payout_default(Request $request, EmailController $email_controller)
    {
        $payout = BankDetail::find($request->id);

        if($payout->is_default == 'yes')
        {
            $this->helper->flash_message('error', trans('messages.account.payout_already_defaulted')); // Call flash message function
        }
        else
        {
        	BankDetail::where('user_id',Auth::user()->id)->update(['is_default'=>'no']);
            $payout->is_default = 'yes';
            $payout->save();

            $email_controller->payout_preferences($payout->id, 'default_update');

            $this->helper->flash_message('success', trans('messages.account.payout_defaulted')); // Call flash message function
        }
        return redirect('payout_preferences/'.Auth::user()->id);
    }

	/**
	 * Set Password View and Update Password
	 *
	 * @param array $request Input values
	 * @return view set_password / redirect to Login
	 */
	public function reset_password(Request $request) {
		if (!$_POST) {

			$password_resets = PasswordResets::whereToken($request->secret)->first();
			$user = User::where('email', @$password_resets->email)->first();
			if ($password_resets) {
				$password_result = $password_resets;

				$datetime1 = new DateTime();
				$datetime2 = new DateTime($password_result->created_at);
				$interval = $datetime1->diff($datetime2);
				$hours = $interval->format('%h');

				if ($hours >= 1) {
					// Delete used token from password_resets table
					PasswordResets::whereToken($request->secret)->delete();

					$this->helper->flash_message('error', trans('messages.user.token')); // Call flash message function
					if ($user->user_type == 'Rider') {
						return redirect('signin_rider');
					} else {
						return redirect('signin_driver');
					}

				}

				$data['result'] = User::whereEmail($password_result->email)->first();
				$data['token'] = $request->secret;
				return view('user.reset_password', $data);
			} else {
				$this->helper->flash_message('error', trans('messages.user.invalid_token')); // Call flash message function
				if (@$user->user_type == 'Rider') {
					return redirect('signin_rider');
				} else {
					return redirect('signin_driver');
				}

			}
		} else {
			// Password validation rules
			$rules = array(
				'new_password' => 'required|min:6|max:30',
				'confirm_password' => 'required|same:new_password',
			);

			// Password validation custom Fields name
			$niceNames = array(
				'new_password' => trans('messages.user.new_paswrd'),
				'confirm_password' => trans('messages.user.cnfrm_paswrd'),
			);

			$validator = Validator::make($request->all(), $rules);
			$validator->setAttributeNames($niceNames);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {

				// Delete used token from password_resets table
				$password_resets = PasswordResets::whereToken($request->token)->delete();

				$user = User::find($request->id);

				$user->password = bcrypt($request->new_password);

				$user->save(); // Update Password in users table

				$this->helper->flash_message('success', trans('messages.user.pswrd_chnge')); // Call flash message function
				if ($user->user_type == 'Rider') {
					return redirect('signin_rider');
				} else {
					return redirect('signin_driver');
				}

			}
		}
	}

	/**
	 * Set Password View and Update Password for company
	 *
	 * @param array $request Input values
	 * @return view set_password / redirect to Login
	 */
	public function company_reset_password(Request $request) {
		if (!$_POST) {

			$password_resets = PasswordResets::whereToken($request->secret)->first();
			$company = Company::where('email', @$password_resets->email)->first();
			if ($password_resets) {
				$password_result = $password_resets;

				$datetime1 = new DateTime();
				$datetime2 = new DateTime($password_result->created_at);
				$interval = $datetime1->diff($datetime2);
				$hours = $interval->format('%h');

				if ($hours >= 1) {
					// Delete used token from password_resets table
					PasswordResets::whereToken($request->secret)->delete();

					$this->helper->flash_message('error', trans('messages.user.token')); // Call flash message function
					return redirect('signin_company');

				}

				$data['result'] = Company::whereEmail($password_result->email)->first();
				$data['token'] = $request->secret;
				return view('user.reset_password', $data);
			} else {
				$this->helper->flash_message('error', trans('messages.user.invalid_token')); // Call flash message function
				return redirect('signin_company');

			}
		} else {
			// Password validation rules
			$rules = array(
				'new_password' => 'required|min:6|max:30',
				'confirm_password' => 'required|same:new_password',
			);

			// Password validation custom Fields name
			$niceNames = array(
				'new_password' => trans('messages.user.new_paswrd'),
				'confirm_password' => trans('messages.user.cnfrm_paswrd'),
			);

			$validator = Validator::make($request->all(), $rules);
			$validator->setAttributeNames($niceNames);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {

				// Delete used token from password_resets table
				$password_resets = PasswordResets::whereToken($request->token)->delete();

				$company = company::find($request->id);

				$company->password = bcrypt($request->new_password);

				$company->save(); // Update Password in users table

				$this->helper->flash_message('success', trans('messages.user.pswrd_chnge')); // Call flash message function
				return redirect('signin_company');

			}
		}
	}

}
