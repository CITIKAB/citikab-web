<?php

/**
 * Trips Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Trips
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DB;
use Auth;

class Trips extends Model
{
    use CurrencyConversion;

    public $convert_fields = ['time_fare', 'distance_fare', 'base_fare', 'total_fare', 'access_fee', 'driver_payout', 'owe_amount', 'remaining_owe_amount', 'applied_owe_amount', 'wallet_amount','promo_amount','payable_driver_payout','cash_collectable','commission','company_admin_commission','total_trip_fare','total_invoice','total_payout_frontend','cash_collect_frontend','driver_front_payout','rider_paid_amount','subtotal_fare','peak_amount','schedule_fare','driver_peak_amount','company_commission','driver_service_fee','driver_or_company_commission','company_earning'];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'trips';

    protected $appends = ['vehicle_name','driver_name','rider_name','rider_profile_picture','driver_thumb_image','rider_thumb_image','date','pickup_time','drop_time','pickup_date_time','trip_time','begin_date','payout_status','date_time_trip','driver_joined_at','payable_driver_payout','cash_collectable','commission','company_admin_commission','total_trip_fare','total_invoice','total_payout_frontend','cash_collect_frontend','driver_front_payout','rider_paid_amount','map_image','currency_symbol','status'];


  // Join with profile_picture table
    public function users()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }
    // Join with user table
    public function driver()
    {
        return $this->belongsTo('App\Models\User','driver_id','id');
    }
    // Join with cancel table
    public function cancel()
    {
        return $this->belongsTo('App\Models\Cancel','id','trip_id');
    }
    // Join with payment table
    public function payment()
    {
        return $this->belongsTo('App\Models\Payment','trip_id','id');
    }
    // Join with Currency table
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency','currency_code','code');
    }
    public function language()
    {
        return $this->belongsTo('App\Models\Language','language_code','value');
    } 
    // Join with profile_picture table
    public function profile_picture()
    {
        return $this->belongsTo('App\Models\ProfilePicture','user_id','user_id');
    }

    // Join with car_type table
    public function car_type()
    {
        return $this->belongsTo('App\Models\CarType','car_id','id');
    }

    // Join with driver_location table
    public function driver_location()
    {
        return $this->belongsTo('App\Models\DriverLocation','driver_id','user_id');
    }
    // Join with rating table
    public function rating()
    {
        return $this->belongsTo('App\Models\Rating','user_id','user_id');
    }
     public function trip_rating()
    {
        return $this->belongsTo('App\Models\Rating','id','trip_id');
    }

    // Join with request table
    public function ride_request()
    {
        return $this->belongsTo('App\Models\Request','request_id','id');
    }
    
    // Join with Driver Address table
    public function driver_address()
    {
        return $this->belongsTo('App\Models\DriverAddress','driver_id','user_id');
    }

    // Join with payment table
    public function driver_payment()
    {
        return $this->hasOne('App\Models\Payment','trip_id','id');
    }

    public function scopeDriverPayoutTripsOnly($query) {
        return $query->with(['payment'])
            ->whereHas('driver_payment', function ($query) {
                $query->where('driver_payout_status', 'Pending');
            })
            ->where(function($query)  {
                if(LOGIN_USER_TYPE=='company') {
                    $query->whereHas('driver',function($q1){
                        $q1->where('company_id',Auth::guard('company')->user()->id);
                    });
                }else{
                    $query->whereHas('driver',function($q1){
                        $q1->where('company_id',1);
                    });
                }
            })
            ->where('status','Completed')
            ->where('driver_payout','>',0)
            ->where('payment_mode','<>','Cash');
    }

    public function scopeCompanyPayoutTripsOnly($query) {
        return $query->with(['payment'])
            ->whereHas('driver_payment', function ($query) {
                if(LOGIN_USER_TYPE == 'admin') {
                    $query->where('admin_payout_status','Pending');
                }
                else {
                    $query->where('driver_payout_status', 'Pending');
                }
            })
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'trips.driver_id')
                    ->where('users.company_id', '!=', 1);
            })
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'users.company_id')
                    ->where('users.company_id', '!=', 1);
            })
            ->where('trips.status','Completed')
            ->where('trips.driver_payout','>',0)
            ->where('trips.payment_mode','<>','Cash');
    }

    // Get vehicle name
    public function getVehicleNameAttribute()
    {
        return CarType::find($this->attributes['car_id'])->car_name;
    } 

     // Get status
    public function getStatusAttribute()
    {
        if(LOGIN_USER_TYPE == 'company' || LOGIN_USER_TYPE == 'admin') {
            return $this->attributes['status'];
        }

        if($this->attributes['status']=="Completed") {
            if(@Auth::user()->user_type=="Rider") {
                if(@$this->trip_rating->rider_rating) {
                    return  $this->attributes['status'];
                }
            }
            else {
                if(@Auth::user()->user_type=="Driver") {
                    if(@$this->trip_rating->driver_rating) {
                       return  $this->attributes['status'];
                    }
                }
            }
            return "Rating";
        }

        return $this->attributes['status'];
    }

    public function getCommissionAttribute()
    {
        return $this->attributes['access_fee'] + ( $this->attributes['peak_amount'] - $this->attributes['driver_peak_amount'] ) + $this->attributes['schedule_fare'] + $this->attributes['driver_or_company_commission'];
    }
    public function getCompanyAdminCommissionAttribute()
    {
        return ( $this->attributes['peak_amount'] - $this->attributes['driver_peak_amount'] ) + $this->attributes['driver_or_company_commission'];
    }
    public function getCompanyEarningAttribute()
    {
        return ( $this->attributes['subtotal_fare'] + $this->attributes['driver_peak_amount'] ) - $this->attributes['driver_or_company_commission'];
    }
    public function getPayableDriverPayoutAttribute()
    {
        if($this->attributes['payment_mode']=="Cash" && $this->attributes['wallet_amount']==0 && $this->attributes['promo_amount']==0){
            return 0;
        }
        elseif(($this->attributes['payment_mode']=="Cash" || $this->attributes['payment_mode']=="Cash & Wallet") && ($this->attributes['wallet_amount']!=0 || $this->attributes['promo_amount']!=0))
        {
            $promo_wallet=$this->attributes['wallet_amount']+$this->attributes['promo_amount'];
            if($promo_wallet >$this->total_fare() ) 
            $cash_collectable= 0;
            else
            $cash_collectable=$this->total_fare()-$promo_wallet;

            return number_format(($this->attributes['driver_payout'] + $this->attributes['access_fee'] -$cash_collectable),2, '.', '');
        }
        else
        {
            return number_format((($this->total_fare()-$this->attributes['access_fee'])-$this->attributes['applied_owe_amount']),2, '.', '');
        }
    }
    public function getRiderPaidAmountAttribute()
    {
        return number_format(($this->attributes['total_fare'])-($this->attributes['wallet_amount']+$this->attributes['promo_amount']),2, '.', '');   
    }
    public function getCashCollectableAttribute()
    {
        $cashcollect=0;

        if($this->attributes['payment_mode']=="Cash" || $this->attributes['payment_mode']=="Cash & Wallet")
        {  
            if($this->attributes['promo_amount']+$this->attributes['wallet_amount'] > $this->total_fare())
            {
                $cashcollect = 0 ; 
            }
            else
            $cashcollect=$this->total_fare()-($this->attributes['promo_amount']+$this->attributes['wallet_amount']);
        }
        return number_format($cashcollect,2, '.', '');
    }

    public function total_fare()
    {
         return $total_fare = $this->attributes['base_fare'] + $this->attributes['time_fare'] + $this->attributes['distance_fare'] + $this->attributes['schedule_fare'] + $this->attributes['access_fee'] + $this->attributes['peak_amount'];
    }
    public function getDriverFrontPayoutAttribute()
    {
        return number_format((($this->attributes['wallet_amount']+$this->attributes['promo_amount'])-($this->attributes['access_fee']+$this->attributes['applied_owe_amount'])),2, '.', '');
    }
    public function getCashCollectFrontendAttribute()
    {
        $cashcollect=0;
        if($this->attributes['payment_mode']=="Cash" || $this->attributes['payment_mode']=="Cash & Wallet")
        {
            $cashcollect=$this->attributes['total_fare']-($this->attributes['promo_amount']+$this->attributes['wallet_amount']);
        }
        return number_format($cashcollect,2, '.', '');
    }

    public function getTotalPayoutFrontendAttribute()
    {
        return number_format($this->attributes['driver_payout'],2, '.', '');
    }

    public function getPayoutStatusAttribute()
    {
        $payout=Payment::where('trip_id',$this->attributes['id']);
        if($payout->count())
        {
            return Payment::where('trip_id',$this->attributes['id'])->first()->driver_payout_status;    
        }
        else
        {
            return "";
        }
        
    } 
    // get begin trip value
    public function getDateAttribute()
    {
        return strtotime($this->attributes['begin_trip']);
    }
    public function getMapImageAttribute()
    {   
        $map_image = @$this->attributes['map_image'];       

        if(@$map_image != ''){
         
            $map_image = url('images/map/'.$this->attributes['id'].'/'.@$map_image);

        }

        return @$map_image;
    }

        //get trip currency code
    public function getCurrencySymbolAttribute()
    {
        $trips= Trips::where('request_id',$this->attributes['id']);
        if($trips->count())
        {
            $code =  @$trips->get()->first()->currency_code;

           return Currency::where('code',$code)->first()->symbol;
        }
        else
        {
            return "$";
        }
    }

    // get begin trip value with the format: yyyy-mm-dd
    public function getBeginDateAttribute()
    {
        return date('Y-m-d',strtotime($this->attributes['created_at']));
    }
    // get pickup date with the format: Thursday, July 20, 2017 11:58 AM
    public function getPickupDateTimeAttribute()
    {
      return date('l, F d, Y h:i A',strtotime($this->attributes['created_at']));
    }
    // get pickup time with the format: 11:58 AM
    public function getPickupTimeAttribute()
    {
      return date('h:i A',strtotime($this->attributes['begin_trip']));
    }
    // get drop time with the format: 11:58 AM
    public function getDropTimeAttribute()
    {
      return date('h:i A',strtotime($this->attributes['end_trip']));
    }
    // get Driver name
    public function getDriverNameAttribute()
    {
      return User::find($this->attributes['driver_id'])->first_name;
    }
    // get Rider name
    public function getRiderNameAttribute()
    {
      return User::find($this->attributes['user_id'])->first_name;
    }
    // get Rider Profile Picture
    public function getRiderProfilePictureAttribute()
    {
      $profile_picture=ProfilePicture::where('user_id',$this->attributes['user_id'])->first();
      return isset($profile_picture)?$profile_picture->src:url('images/user.jpeg');
    }
    // get DriverThumb image
    public function getDriverThumbImageAttribute()
    {
      $profile_picture=ProfilePicture::find($this->attributes['driver_id']);
      return isset($profile_picture)?$profile_picture->src:url('images/user.jpeg');
    }
    // get DriverThumb image
    public function getRiderThumbImageAttribute()
    {
      $profile_picture=ProfilePicture::find($this->attributes['user_id']);
      return isset($profile_picture)?$profile_picture->src:url('images/user.jpeg');
    }
    // get total trip time
    public function getTripTimeAttribute()
    {      
      $begin_time = new DateTime($this->attributes['begin_trip']);
      $end_time   = new DateTime($this->attributes['end_trip']);
      $timeDiff   = date_diff($begin_time,$end_time);
      return $timeDiff->format('%H').':'.$timeDiff->format('%I').':'.$timeDiff->format('%S');
               
    }
    public function getTotalTripFareAttribute()
    {
        return number_format(($this->attributes['total_fare']-$this->attributes['access_fee']),2, '.', '');    
    }
    public function getTotalInvoiceAttribute()
    {
        if($this->attributes['driver_payout']>0)
        {
            return number_format($this->attributes['driver_payout'],2,'.','');
        }
        else
        {
            return number_format($this->attributes['total_fare']-$this->attributes['access_fee'],2, '.', '');
        }
    }
    
    public function getTotalFareAttribute()
    {
        return number_format(($this->attributes['total_fare']),2, '.', ''); 
    }
    public function getDriverPayoutAttribute()
    {
        return number_format(($this->attributes['driver_payout']),2, '.', ''); 
    }
    public function getAccessFeeAttribute()
    {
        return number_format(($this->attributes['access_fee']),2, '.', ''); 
    }
    public function getOweAmountAttribute()
    {
        return number_format(($this->attributes['owe_amount']),2, '.', ''); 
    }
    public function getWalletAmountAttribute()
    {
        return number_format(($this->attributes['wallet_amount']),2, '.', ''); 
    }
    public function getAppliedOweAmountAttribute()
    {
        return number_format(($this->attributes['applied_owe_amount']),2, '.', ''); 
    }
    public function getRemainingOweAmountAttribute()
    {
        return number_format(($this->attributes['remaining_owe_amount']),2, '.', ''); 
    }    
    public function getPromoAmountAttribute()
    {
        return number_format(($this->attributes['promo_amount']),2, '.', ''); 
    }
    public function getDateTimeTripAttribute()
    {
        $full = false;

        $now = new DateTime;
        $ago = new DateTime($this->attributes['created_at']);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    public function getDriverJoinedAtAttribute()
    {
        $full = false;
        $driver_created_at=DB::table('users')->where('id',$this->attributes['driver_id'])->get()->first()->created_at;
        $now = new DateTime;
        $ago = new DateTime($driver_created_at);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    // get Begin time with the format: 11:58 AM
    public function getFormattedBeginTripAttribute() {
        $trip_time = '-';
        $begin_time = strtotime($this->attributes['begin_trip']);
        if($begin_time > 0 ){
            $trip_time = date('g:i A',$begin_time);
        }

        return $trip_time;
    }

    // get Begin time with the format: 11:58 AM
    public function getFormattedEndTripAttribute() {
        $trip_time = '-';
        $end_time = strtotime($this->attributes['end_trip']);
        if($end_time > 0 ){
            $trip_time = date('g:i A',$end_time);
        }

        return $trip_time;
    }

    public function getPeakSubtotalFareAttribute()
    {
        return $this->peak_amount + $this->subtotal_fare;
    }

    public function getWeekDaysAttribute()
    {
        $week_no = 0;
        $year = date('Y', strtotime($this->attributes['created_at']));
        $week_no = date('W', strtotime($this->attributes['created_at']));
        $week_days = \App\Http\Start\Helpers::getWeekDates($year, $week_no);

        return $week_days;
    }

    public function scopeCompanyTripsOnly($query, $company_id)
    {
        $company_trips = $query->whereHas('driver', function ($query) use ($company_id) {
            $query->where('company_id',$company_id);
        });
        return $company_trips;
    }

    public function getCompanyDriverAmountAttribute()
    {
        if($this->driver->company_id == 1) {
           return  $this->driver_payout;
        }
        $payment_mode  = $this->attributes['payment_mode'];

        $subtotal_fare = ($payment_mode == 'Cash' || $payment_mode == 'Cash & Wallet') ? $this->total_fare : $this->subtotal_fare;
        return $subtotal_fare;
    }
}
