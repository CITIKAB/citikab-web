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

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use Excel;
use App\Models\User;
use App\Models\Trips;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\SiteSettings;
use App\Models\Currency;
use App\Models\PayoutCredentials;
use Validator;
use DB;
use App\DataTables\CancelTripsDataTable;
use App\DataTables\TripsDataTable;
use Auth;

class TripsController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Trips
     *
     * @return view file
     */
     public function index(TripsDataTable $dataTable)
    {
        return $dataTable->render('admin.trips.index');
    }

    

  /**
     * Load particular trip data 
     *
     * @return view file
     */
    public function view(Request $request)
    {
        $data['result']=Trips::
                where('id',$request->id)
                ->where(function($query)  {
                    if(LOGIN_USER_TYPE=='company') {  //If login user is company then get that company drivers only
                        $query->whereHas('driver',function($q1){
                            $q1->where('company_id',Auth::guard('company')->user()->id);
                        });
                    }
                })
                ->first();
        if($data['result'])
        {
            $data['back_url'] = url(LOGIN_USER_TYPE.'/trips');  //redirect depends on login user is admin or company
            if($request->s == 'overall')
            {
                $data['back_url'] = url(LOGIN_USER_TYPE.'/statements/overall');  //redirect depends on login user is admin or company
            }
            elseif($request->s == 'driver')
            {
                $data['back_url'] = url(LOGIN_USER_TYPE.'/view_driver_statement/'.$data['result']->driver_id);   //redirect depends on login user is admin or company
            }
            return view('admin.trips.view', $data);
        }
        else
        {
            $this->helper->flash_message('danger', 'Invalid ID'); // Call flash message function
            return redirect(LOGIN_USER_TYPE.'/trips');  //redirect depends on login user is admin or company
        }
          
    }


  /**
     * Export trip data to excel 
     *
     * @return view file
     */
    public function export(Request $request)
    {
      

           $from = date('Y-m-d H:i:s', strtotime($request->from));
            $to = date('Y-m-d H:i:s', strtotime($request->to));
            $category = $request->category;

           
                $result = Trips::where('trips.created_at', '>=', $from)->where('trips.created_at', '<=', $to) 
                        ->join('users', function($join) {
                                $join->on('users.id', '=', 'trips.user_id');
                            })
                        ->join('currency', function($join) {
                                $join->on('currency.code', '=', 'trips.currency_code');
                            })
                        ->join('car_type', function($join) {
                                $join->on('car_type.id', '=', 'trips.car_id');
                            })
                        ->leftJoin('users as u', function($join) {
                                $join->on('u.id', '=', 'trips.driver_id');
                            })
                        ->select(['trips.id as id','trips.begin_trip as begin_trip','trips.pickup_location as pickup_location','trips.drop_location as drop_location', 'u.first_name as driver_name', 'users.first_name as rider_name',  DB::raw('CONCAT(currency.symbol, trips.total_fare) AS total_amount'), 'trips.status','car_type.car_name as car_name', 'trips.created_at as created_at', 'trips.updated_at as updated_at', 'trips.*'])->get();
        

        Excel::create('Trips-report', function($excel) use($result) {
            $excel->sheet('sheet1', function($sheet) use($result) {

                 $data[0]=['Id','From Location','To Location','Date','Driver Name','Rider Name','Fare','Vehicle Details','Status','Created At'];

                foreach ($result as $key => $value) {
                    $data[]=array($value->id,$value->pickup_location,$value->drop_location,date('d-m-y h:m a',strtotime($value->date)),$value->rider_name,$value->driver_name, html_entity_decode($value->total_amount),$value->car_name,$value->status,$value->created_at);
                }
                $data = array_values($data);
                 $sheet->with($data);
            });
        })->export('csv');
    }

    /**
     * Load Datatable for Cancel trips
     *
     * @param array $dataTable  Instance of Cancel tripsDataTable
     * @return datatable
     */
    public function cancel_trips(CancelTripsDataTable $dataTable)
    {
        return $dataTable->render('admin.trips.cancel');
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
        return number_format($usd_amount * $session_rate, 2, '.', '');
    }

    /**
     * Load Datatable for Cancel trips
     *
     * @param array $dataTable  Instance of Cancel tripsDataTable
     * @return datatable
     */
    public function payout(Request $request)
    {
        $trip_id            = $request->trip_id;
        $trip_details       = Trips::find($trip_id);

        $trip_currency      = $trip_details->currency_code;
        $trip_amount        = $trip_details->driver_payout;
        $payout_details     = $trip_details->driver->default_payout_credentials;
        $payout_id          = $payout_details->payout_id;
        $payment_type       = $payout_details->type;

        if($payment_type == 'stripe') {
            $payout_currency    = $payout_details->payout_preference->currency_code;
            $amount = $this->currency_convert($trip_currency, $payout_currency, $trip_amount);

            $stripe_credentials = PaymentGateway::where('site','Stripe')->pluck('value', 'name');
            $response = $this->stripe_payout($amount, $payout_currency, $payout_id);
        }
        else {
            $payout_currency = SiteSettings::where('name', 'paypal_currency')->first()->value;
            $amount = $this->currency_convert($trip_currency, $payout_currency, $trip_amount);

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
                        'sender_item_id' => "$request->trip_id"
                    ],
                ],
            ];
            $data=json_encode($data);
            $response = $this->paypal_payouts($data);
        }

        if($response['success']) {
            $payment_data['correlation_id']         = $response['transaction_id'];
            $payment_data['driver_payout_status']   = 'Paid';
            $payouts_data['payment_status']         = 'Completed';
            Trips::where('id',$request->trip_id)->update($payouts_data);
            Payment::where('trip_id',$request->trip_id)->update($payment_data);

            $this->helper->flash_message('success', ' Payout amount has transferred successfully');
        }
        else {
            $this->helper->flash_message('danger', 'Payout Failed : '.$response['message']);
        }

        return redirect(LOGIN_USER_TYPE.'/view_trips/'.$request->trip_id);  //redirect depends on login user is admin or company
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
