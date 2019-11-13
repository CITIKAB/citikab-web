<?php

/**
 * Request Helper
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Request
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */
namespace App\Http\Helper;
use App\Models\PaymentMethod;
use App\Models\Currency;
use JWTAuth;
use DB;


class PaymantHelper {



	public function glade_way_payment($data) {
    $payment = DB::table('payment_gateway')->where('site', 'Gladepay')->get();
    $key = array(
        "key: ".$payment[1]->value,
        "mid: ".$payment[0]->value
      );
    $curl = curl_init();
      curl_setopt_array($curl, array(
      CURLOPT_URL => $payment[2]->value,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "PUT",
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => $key,
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      $return['status_code'] = "0";
      if ($err) {
        $return['status_message'] = "cURL Error #:" . $err;
      } else {

        $return['data'] = json_decode($response,true);
        if($return['data']['status']==200){
          $return['status_message'] = 'Success';
          $return['status_code'] = "1";
        }
        else
          $return['status_message'] = $return['data']['message'];
      }   
      return $return; 
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
    

    $rate = Currency::whereCode($from)->first()->rate;
    $session_rate = Currency::whereCode($to)->first();

    if($session_rate)
    {
    $session_rate = $session_rate->rate;
    }
    else
    {
    $session_rate = '1';
    }
   
      if($rate!="0.0")
      {    if($price)
          $usd_amount = $price / $rate;
          else
            $usd_amount = 0;
      }
      else
      {
         echo "Error Message : Currency value '0' (". $from . ')';
         die;
      }
    

    return number_format($usd_amount * $session_rate, 2, '.', '');
  }



}