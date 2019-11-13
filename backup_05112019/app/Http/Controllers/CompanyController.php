<?php
namespace App\Http\Controllers;

use App\Http\Controllers\EmailController;
use App\Http\Helper\FacebookHelper;
use App\Http\Helper\RequestHelper;
use App\Http\Start\Helpers;
use App\Models\CompanyPayoutPreference;
use App\Models\CompanyPayoutCredentials;
use App\Models\PaymentGateway;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Company;
use Auth;
use App;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Session;
use Validator;
use Input;

class CompanyController extends BaseController {
	protected $request_helper; // Global variable for Helpers instance

	private $fb; // Global variable for FacebookHelper instance

	public function __construct(RequestHelper $request, FacebookHelper $fb) {
		$this->request_helper = $request;
		$this->helper = new Helpers;
		$this->fb = $fb;
	}

	/**
     * Add a Payout Method and Load Payout Preferences File
     *
     * @param array $request Input values
     * @return redirect to Payout Preferences page and load payout preferences view file
     */
    public function payout_preferences(Request $request, EmailController $email_controller)
    {
    	// dd('test');
        if(!$request->address1)
        { 
            $data['payouts'] = CompanyPayoutCredentials::with(['company_payout_preference'])->where('company_id', Auth::guard('company')->user()->id)->orderBy('id','desc')->get();
            $data['country']   = Country::all()->pluck('long_name','short_name');
            $data['currency']   = Currency::all()->pluck('name','id');
            $data['stripe_data'] = PaymentGateway::where('site', 'Stripe')->get();             
            $data['country_list'] = Country::getPayoutCoutries();
            $data['iban_supported_countries'] = Country::getIbanRequiredCountries();
            $data['country_currency'] = $this->helper->getStripeCurrency();
            $data['mandatory']         = CompanyPayoutPreference::getAllMandatory();
            $data['branch_code_required'] = Country::getBranchCodeRequiredCountries();

            return view('company_payout', $data);
        }
        else
        {
            $country_data = Country::where('short_name', $request->country)->first();

            if (!$country_data) {
                $message = trans('messages.user.service_not_available_country');
               $this->helper->flash_message('error', $message); // Call flash message function
               return back();
            }
            $payout     =   new CompanyPayoutPreference;

            $payout->company_id       = Auth::guard('company')->user()->id;
            $payout->address1      = $request->address1;
            $payout->address2      = $request->address2;
            $payout->city          = $request->city;
            $payout->state         = $request->state;
            $payout->postal_code   = $request->postal_code;
            $payout->country       = $request->country;
            $payout->payout_method = $request->payout_method;
            $payout->paypal_email  = $request->paypal_email;
            $payout->currency_code = PAYPAL_CURRENCY_CODE;
            
            if($request->payout_method == 'Stripe') {
                $stripe_credentials = PaymentGateway::where('site', 'Stripe')->pluck('value','name');
                \Stripe\Stripe::setApiKey($stripe_credentials['secret']);
                \Stripe\Stripe::setClientId($stripe_credentials['client_id']);
                $oauth_url = \Stripe\OAuth::authorizeUrl([
                    'response_type'    => 'code',
                    'scope'    => 'read_write',
                    'redirect_uri'  => url('company/stripe_payout_preferences'),
                ]);

                Session::put('payout_preferences_data', $payout);
                return redirect($oauth_url);
            }

            $payout->save();

            // Save Paypal Details to payout credentials
            $payout_credentials = new CompanyPayoutCredentials;
	        $payout_credentials->company_id = Auth::guard('company')->user()->id;
	        $payout_credentials->preference_id = $payout->id;
	        $payout_credentials->payout_id = $payout->paypal_email;
	        $payout_credentials->type = 'Paypal';
	        $payout_credentials->save();

            $payout_check = CompanyPayoutCredentials::where('company_id', Auth::guard('company')->user()->id)->where('default','yes')->get();

            if($payout_check->count() == 0)
            {
                $payout_credentials->default = 'yes';
                $payout_credentials->save();
            }

            // $email_controller->payout_preferences($payout_credentials->id);

            $this->helper->flash_message('success', trans('messages.account.payout_updated')); // Call flash message function
            return redirect('company/payout_preferences');
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

        $company_id = Auth::guard('company')->user()->id; 


        $company  = Company::find($company_id);

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
            $rules['gender'] = 'required|in:male,female';
        
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

        $recipient->email = Auth::guard('company')->user()->email;

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
                $recipient->legal_entity->first_name = $company->name;
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
                $recipient->legal_entity->first_name_kana = $company->name;
                $recipient->legal_entity->first_name_kanji = $company->name;
                $recipient->legal_entity->dob->day= 15;
                $recipient->legal_entity->dob->month= 04;
                $recipient->legal_entity->dob->year= 1996;
                $recipient->legal_entity->address_kana = $address_kana;
                $recipient->legal_entity->address_kanji = $address_kanji;                          
                $recipient->legal_entity->gender = $request->gender;                          
                
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
            $filename  =   $company_id.'_company_document_'.time().'.'.$extension;
            $filenamepath = dirname($_SERVER['SCRIPT_FILENAME']).'/images/company/'.$company_id.'/uploads';
                                
            if(!file_exists($filenamepath))
            {
                mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/company/'.$company_id.'/uploads', 0777, true);
            }
            $success   =   $document->move('images/company/'.$company_id.'/uploads/', $filename);
            if($success)
            {
                $document_path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/company/'.$company_id.'/uploads/'.$filename;
                
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
        $payout_preference = new CompanyPayoutPreference;
        $payout_preference->company_id = $company_id;
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

        $payout_credentials = new CompanyPayoutCredentials;
        
        $payout_credentials->company_id = $company_id;

        $payout_credentials->preference_id = $payout_preference->id;
        $payout_credentials->payout_id = @$recipient->id;

        $payout_credentials->type = 'Stripe';

        

        /*if($request->gender)
        {
            $user->gender = $request->gender; 
            $user->save();
        }*/

        $payout_check = CompanyPayoutCredentials::where('company_id', Auth::guard('company')->user()->id)->where('default','yes')->get();

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

        // $email_controller->payout_preferences($payout_credentials->id); // send payout preference updated email to host user.
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
                'redirect_uri'  => url('company/stripe_payout_preferences'),
            ]);
            return redirect($oauth_url);
        }
        $session_payout_data = Session::get('payout_preferences_data');
        if(!$session_payout_data || !@$response['stripe_user_id']) {
            return redirect('company/payout_preferences');
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
        return redirect('company/payout_preferences');
    }   

    /**
     * Delete Payouts Default Payout Method
     *
     * @param array $request Input values
     * @return redirect to Payout Preferences page
     */
    public function payout_delete(Request $request, EmailController $email_controller)
    {
        $payout = CompanyPayoutCredentials::find($request->id);
        if ($payout==null) {
            return redirect('company/payout_preferences');
        }
        if($payout->default == 'yes')
        {
            $this->helper->flash_message('error', trans('messages.account.payout_default')); // Call flash message function
            return redirect('company/payout_preferences');
        }
        else
        {
        	CompanyPayoutPreference::find($payout->preference_id)->delete();
            $payout->delete();

            // $email_controller->payout_preferences($payout->id, 'delete');

            $this->helper->flash_message('success', 'Selected Payout Method has Removed Successfully'); // Call flash message function
            return redirect('company/payout_preferences');
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
        $payout = CompanyPayoutCredentials::find($request->id);

        if($payout->default == 'yes')
        {
            $this->helper->flash_message('error', trans('messages.account.payout_already_defaulted')); // Call flash message function
        }
        else
        {
            CompanyPayoutCredentials::where('company_id',Auth::guard('company')->user()->id)->update(['default'=>'no']);

            $payout->default = 'yes';
            $payout->save();

            // $email_controller->payout_preferences($payout->id, 'default_update');

            $this->helper->flash_message('success', 'Default Payout Method has updated Successfully'); // Call flash message function
        }
        return redirect('company/payout_preferences');
    }

}
