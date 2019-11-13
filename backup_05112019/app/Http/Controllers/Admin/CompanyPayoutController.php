<?php

/**
 * Trips Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Trips
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Start\Helpers;
use App\DataTables\CompanyPayoutDataTable;
use App\DataTables\CompanyPayoutReportsDataTable;
use App\Models\Payout;
use App\Models\User;
use App\Models\SiteSettings;
use App\Models\Trips;
use App\Models\Payment;
use App\Models\Currency;
use App\Models\PaymentGateway;
use DB;
class CompanyPayoutController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }   
    
    /**
    * View Over All Payout Details of All Drivers
    *
    * @param array $dataTable  Instance of PayoutDataTable DataTable
    * @return datatable
    */
    public function overall_payout(CompanyPayoutDataTable $dataTable)
    {
        $data['payout_title'] = 'Payouts';
        $data['sub_title'] = 'Payouts';
        return $dataTable->setFilter('OverAll')->render('admin.company_payouts.view',$data);
    }

    /**
    * View Weekly Payout Details of Drivers
    *
    * @param array $dataTable  Instance of PayoutDataTable DataTable
    * @return datatable
    */
    public function weekly_payout(CompanyPayoutDataTable $dataTable)
    {
        $company_id = request()->company_id;
        $data['payout_title'] = 'Weekly Payout for : '.$company_id;
        $data['sub_title'] = 'Payouts';

        return $dataTable->setFilter('Weekly')->render('admin.company_payouts.view',$data);
    }

    /**
    * View Week Day Payout Details of Drivers
    *
    * @param array $dataTable  Instance of CompanyPayoutReportsDataTable DataTable
    * @return datatable
    */
    public function payout_per_week_report(CompanyPayoutReportsDataTable $dataTable)
    {       
        $from = date('Y-m-d' . ' 00:00:00', strtotime(request()->start_date));
        $to = date('Y-m-d' . ' 23:59:59', strtotime(request()->end_date));
        $data['payout_title'] = 'Payout Details : '.request()->start_date.' to '.request()->end_date;
        $data['sub_title'] = 'Payout Details';
        return $dataTable->setFilter('week_report')->render('admin.company_payouts.view',$data);
    }

    /**
    * View Daily Payout Details of Drivers
    *
    * @param array $dataTable  Instance of CompanyPayoutReportsDataTable DataTable
    * @return datatable
    */
    public function payout_per_day_report(CompanyPayoutReportsDataTable $dataTable)
    {       
        $date = date('Y-m-d' . ' 00:00:00', strtotime(request()->date));
        $data['payout_title'] = 'Payout Details : '.request()->date;
        $data['sub_title'] = 'Payout Details';

        return $dataTable->setFilter('day_report')->render('admin.company_payouts.view',$data);
    }

    /**
    * Make Payout to driver based on the type of payout
    *
    * @param  \Illuminate\Http\Request  $request
    * 
    */
    public function payout_to_company(Request $request)
    {
        $type           = $request->type;
        $redirect_url   = $request->redirect_url;
        $trip_currency  = view()->shared('default_currency'); 
        $trip_currency  = $trip_currency->code;

        if($type == 'company_trip') {
            $trip_id            = $request->trip_id;
            $trip_details       = Trips::CompanyPayoutTripsOnly()->select('trips.*')->find($trip_id);
            $trip_currency      = $trip_details->currency_code;
            $trip_amount        = $trip_details->driver_payout;
            $payout_details     = $trip_details->driver->company->default_payout_credentials;
            $trip_ids           = array($trip_id);
            
        }
        else if($type == 'company_day') {
            $trip_details       = Trips::CompanyPayoutTripsOnly()->select('trips.*')
            ->whereHas('driver',function($q) use ($request){
                $q->where('company_id',$request->company_id);
            })
            ->whereDate('trips.created_at',$request->day)->get();

            $trip_amount        = $trip_details->sum('driver_payout');
            $trip_ids           = $trip_details->pluck('id')->toArray();

            $payout_details     = $trip_details[0]->driver->company->default_payout_credentials;
            
        }
        else if($type == 'company_weekly') {
            $start_date = date('Y-m-d '.'00:00:00',strtotime($request->start_date));
            $end_date = date('Y-m-d '.'23:59:59',strtotime($request->end_date));

            $trip_details       = Trips::CompanyPayoutTripsOnly()->select('trips.*')
            ->whereHas('driver',function($q) use ($request){
                $q->where('company_id',$request->company_id);
            })
            ->whereBetween('trips.created_at', [$start_date, $end_date])->get();
            
            $trip_amount        = $trip_details->sum('driver_payout');
            $trip_ids           = $trip_details->pluck('id')->toArray();

            $payout_details     = $trip_details[0]->driver->company->default_payout_credentials;

        }
        else if($type == 'company_overall') {
            $trip_details       = Trips::CompanyPayoutTripsOnly()
            ->select('trips.*')
            ->whereHas('driver',function($q) use ($request){
                $q->where('company_id',$request->company_id);
            })->get();

            $trip_amount        = $trip_details->sum('driver_payout');
            $trip_ids           = $trip_details->pluck('id')->toArray();

            $payout_details     = $trip_details[0]->driver->company->default_payout_credentials;
        }
        else {
            $this->helper->flash_message('danger', 'Invalid Request.Please Try Again.');
            return back();
        }

        if(count($trip_ids) == 0 || $trip_amount <= 0) {
            $this->helper->flash_message('danger', 'Invalid Request.Please Try Again.');
            return back();
        }

        if($payout_details == null) {
            $this->helper->flash_message('danger', 'Yet, Company doesn\'t enter his Payout details. Cannot Make Payout.');
            return back();
        }

        $this->make_company_payout($type, $payout_details, $trip_currency, $trip_amount, $trip_ids);
        
        return redirect($redirect_url);
    }

    /**
    * Make Payout to the company
    *
    * @param String $payout_type   Type
    * @param Collection $payout_details instance of Company Payout
    * @param String $trip_currency  Currency Code
    * @param Float $amount  Amount
    * @param Array $trip_ids  Payout Trip Id's
    */
    public function make_company_payout($payout_type, $payout_details, $trip_currency, $amount,$trip_ids = '')
    {

        $payout_id          = $payout_details->payout_id;
        $payment_type       = $payout_details->type;

        if($payment_type == 'stripe') {
            $payout_currency    = $payout_details->company_payout_preference->currency_code;
            $amount = $this->currency_convert($trip_currency, $payout_currency, $amount);

            $response = $this->stripe_payout($amount, $payout_currency, $payout_id);
        }
        else {
            $payout_currency = SiteSettings::where('name', 'paypal_currency')->first()->value;
            $amount = $this->currency_convert($trip_currency, $payout_currency, $amount);

            // Set request-specific fields.
            $vEmailSubject = 'PayPal Payment';
            $emailSubject  = urlencode($vEmailSubject);
            $receiverType  = urlencode($payout_id);
            $currency      = $payout_currency; // or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')

            $data = [
                'sender_batch_header' => [
                    'email_subject' => "$emailSubject",    
                ],
                'items' => [
                    [
                        'recipient_type' => "EMAIL",
                        'amount' => [
                            'value' => "$amount",
                            'currency' => "$payout_currency"
                        ],
                        'receiver' => "$payout_id",
                        'note' => 'payment of commissions',
                        'sender_item_id' => $trip_ids[0],
                    ],
                ],
            ];
            $data=json_encode($data);
            $response = $this->paypal_payouts($data);
        }

        if($response['success']) {
            $payment_data['admin_payout_status']   = 'Paid';

            Payment::whereIn('trip_id',$trip_ids)->update($payment_data);

            $this->helper->flash_message('success', ' Payout amount has transferred successfully');
        }
        else {
            $this->helper->flash_message('danger', 'Payout Failed : '.$response['message']);
        }
        
    }

    /**
    * Currency Convert
    *
    * @param int $from   Currency Code From
    * @param int $to     Currency Code To
    * @param int $price  Price Amount
    * @return int Converted amount
    */
    public function currency_convert($from = '', $to = '', $price = 0)
    {
        if($from == '') {
            $from = $this->getSessionOrDefaultCode();
        }
        if($to == '') {
            $to = $this->getSessionOrDefaultCode();
        }

        $rate = Currency::whereCode($from)->first()->rate;
        $session_rate = Currency::whereCode($to)->first()->rate;

        $usd_amount = $price / $rate;
        return number_format($usd_amount * $session_rate, 2, '.', '');
    }

    // Single payout using paypal 
    public function paypal_payouts($data=false)
    {
        global $environment;
        $paypal_credentials = PaymentGateway::where('site','PayPal')->get();
        $api_user = $paypal_credentials[1]->value;
        $api_pwd  = $paypal_credentials[2]->value;
        $api_key  = $paypal_credentials[3]->value;
        $paymode  = $paypal_credentials[4]->value;
        
        $client  = $paypal_credentials[6]->value;
        $secret  = $paypal_credentials[7]->value;
        
        if($paymode == 'sandbox')
            $environment = 'sandbox';
        else
            $environment = '';

         $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.$environment.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, $client.":".$secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $result = curl_exec($ch);
        $json = json_decode($result);
        if(!isset($json->error))
        {
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
            curl_setopt($ch, CURLOPT_URL, "https://api.$environment.paypal.com/v1/payments/payouts?sync_mode=true");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer ".$json->access_token,""));

            $result = curl_exec($ch);

            if(empty($result))
            {
                $json ="error";
            }
            else
            {
                $json = json_decode($result);
            }
            curl_close($ch);
              
        }
        else
        {
            $json ="error";
            
        }

        $payout_response = $json;
        $data = array();

        if($payout_response != "error") {
            if($payout_response->batch_header->batch_status=="SUCCESS") {
                if($payout_response->items[0]->transaction_status == 'SUCCESS') {
                    $correlation_id         = $payout_response->items[0]->transaction_id;
                    $data['success']        = true;
                    $data['transaction_id'] = $correlation_id;
                } 
                else  {
                    $data['success'] = false;
                    $data['message'] = $payout_response->items[0]->errors->name;
                }
                
            }
            else {
                $data['success'] = false;
                $data['message'] = $payout_response->name;
            }
        }
        else {
            $data['success'] = false;
            $data['message'] = 'Unknown error';
        }

        return $data;
    }

    public function stripe_payout($amount, $currency, $payout_user_id)
    {
        $stripe_credentials = PaymentGateway::where('site','Stripe')->pluck('value', 'name');
        $data = array();
        $stripe_key = $stripe_credentials['secret'];
        \Stripe\Stripe::setApiKey($stripe_key);
        try
        {
            // $bal = \Stripe\Balance::retrieve();

            /*$response = \Stripe\Payout::create(array(
                "amount" => round($amount),
                "currency" => $currency,
                "source_type" => 'bank_account',
            ), array("destination" => $payout_user_id));
            */
            $response = \Stripe\Transfer::create(array(
              "amount" => $amount * 100,
              "currency" => $currency,
              "destination" => $payout_user_id,
              "source_type" => "card"
            ));


        } catch (\Exception $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
            return $data;
        }
        $response = $response->__toArray(true);

        $data['success'] = true;
        $data['transaction_id'] = $response['id'];
        return $data;
        
        /*if ($response->isSuccessful()) {
            $response_data = $response->getData();

            $correlation_id = @$response_data['id'];
            $data['success'] = true;
            $data['transaction_id'] = $correlation_id;
            return $data;
        } else {
            $data['success'] = false;
            $data['message'] = $response->getMessage();
            return $data;
        }*/
    }

}