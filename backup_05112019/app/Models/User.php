<?php

/**
 * User Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    User
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */


namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Models\Country;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DateTime;
use DB;
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password',];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $appends = ['car_type','latitude','longitude','date_time_join','phone_number','total_earnings','total_rides','total_commission','hidden_mobile_number','company_name'];
    // Join with profile_picture table
    public function profile_picture()
    {
        return $this->belongsTo('App\Models\ProfilePicture','id','user_id');
    }

    // Join with profile_picture table
    public function wallet()
    {
        return $this->belongsTo('App\Models\Wallet','id','user_id');
    }

    // Join with vehicle table
    public function vehicle()
    {
        return $this->hasOne('App\Models\Vehicle','user_id','id');
    }

    // Join with bank_detail table
    public function bank_detail()
    {
        return $this->hasOne('App\Models\BankDetail','user_id','id');
    }

    // Join with driver_documents table
    public function driver_documents()
    {
        return $this->belongsTo('App\Models\DriverDocuments','id','user_id');
    }

     // Join with driver_location table
    public function driver_location()
    {
        return $this->belongsTo('App\Models\DriverLocation','id','user_id');
    }

     // Join with driver_documents table
    public function driver_address()
    {
        return $this->belongsTo('App\Models\DriverAddress','id','user_id');
    }

     // Join with driver_documents table
    public function rider_location()
    {
        return $this->belongsTo('App\Models\RiderLocation','id','user_id');
    }

    // Join with trips table
    public function driver_trips()
    {
        return $this->hasMany('App\Models\Trips','driver_id','id');
    }

    // Return the drivers default payout credential details
    public function default_payout_credentials()
    {
        return $this->belongsTo('App\Models\PayoutCredentials','id','user_id')->where('default','yes');
    }

    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    // Get Driver payout currency
    public function getDriverPayoutCurrencyAttribute() {
        $payout = PayoutCredentials::with(['payout_preference'])->where('user_id', $this->attributes['id'])->where('default', 'yes')->first();
        return $payout->currency_code;
    }

    //Join with country
    public function country_name()
    {
        $data = Country::where('phone_code',@$this->attributes['country_code'])->first();
        if($data)
            return $data->long_name;    
    }

    // facebook authenticate 
    public static function user_facebook_authenticate($email, $fb_id){
        $user = User::where(function($query) use($email, $fb_id){
            $query->where('email', $email)->orWhere('fb_id', $fb_id);
        });
        return $user;
    }

    // Check Email and Google ID
    public static function user_google_authenticate($email, $google_id)
    {
        $user = User::where('user_type','Rider')->where(function($query) use($email, $google_id) {
            $query->where('email', $email)->orWhere('google_id', $google_id);
        });
        return $user;
    }

    // get latitude
    public function getLatitudeAttribute(){
        $user_type = @$this->attributes['user_type'];

        if($user_type == 'Driver')
        {
            $latitude = @DriverLocation::where('user_id',@$this->attributes['id'])->first()->latitude;
        }
        else
        {
            $latitude = @RiderLocation::where('user_id',@$this->attributes['id'])->first()->latitude;
        }

        return @$latitude;

    }

    // get longitude
    public function getLongitudeAttribute(){
        $user_type = @$this->attributes['user_type'];

        if($user_type == 'Driver')
        {
            $longitude = @DriverLocation::where('user_id',@$this->attributes['id'])->first()->longitude;
        }
        else
        {
            $longitude = @RiderLocation::where('user_id',@$this->attributes['id'])->first()->longitude;
        }

        return @$longitude;
    }
   

    // Get header picture source URL based on photo_source
    public function getCarTypeAttribute()
    {
       $user = Vehicle::with([
                    'car_type' => function($query){}
                 ])->where('user_id',$this->attributes['id'])->get();

     

        if($user->count())
        return (@$user[0]->car_type->car_name)? @$user[0]->car_type->car_name : '';    
        else
        return "";
    }

    public function getPhoneNumberAttribute()
    {
        return "+".@$this->attributes['country_code'].@$this->attributes['mobile_number'];
    }

    public function getDateTimeJoinAttribute()
    {
        $full = false;

        $now = new DateTime;
        $ago = new DateTime(@$this->attributes['created_at']);
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

    public function getTotalRidesAttribute()
    {
        $total_rides=DB::table('trips')->where('driver_id',$this->attributes['id']);
        return $total_rides->get()->count();
    }

    public function getTotalEarningsAttribute()
    {
        $total_rides=Trips::where('driver_id',$this->attributes['id']);
        if($total_rides->get()->count())
        {

            $total_amount = $total_rides->get()->sum(function ($trip) {
                return $trip->driver_payout;
            });

            return $total_amount;
        }
        else
        {
            return number_format(0,2);
        }
    }

    public function getTotalCommissionAttribute()
    {
        $total_rides=Trips::where('driver_id',$this->attributes['id']);
        if($total_rides->get()->count())
        {
            $total_amount = $total_rides->get()->sum(function ($trip) {
                return $trip->commission;
            });

            return $total_amount;
        }
        else
        {
            return number_format(0,2);
        }
    }

    public function getTotalCompanyAdminCommissionAttribute()
    {
        $total_rides=Trips::where('driver_id',$this->attributes['id']);
        if($total_rides->get()->count())
        {
            $total_amount = $total_rides->get()->sum(function ($trip) {
                return $trip->company_admin_commission;
            });

            return $total_amount;
        }
        else
        {
            return number_format(0,2);
        }
    }

    public function getCurrencyAttribute()
    {
        $currency_code = $this->attributes['currency_code'];
        $currency = Currency::where('code', $currency_code)->first();
        if(!$currency)
        {
            $currency = Currency::defaultCurrency()->first();
            User::where('id', $this->attributes['id'])->update(['currency_code' => $currency->code]);
        }
        return $currency;
    }
	// Get Translated Status Attribute
    public function getTransStatusAttribute()
    {
        return trans('messages.driver_dashboard.'.$this->attributes['status']);
    }

    // Get Payout Id of the driver
    public function getPayoutIdAttribute()
    {
        $payout_id = '';
        $payout_details = $this->default_payout_credentials()->first();
        if($payout_details != '')
            $payout_id = $payout_details->payout_id;

        return $payout_id;
    }

    // Get Mobile number with Protected format
    public function getHiddenMobileNumberAttribute()
    {
        // return $this->attributes['mobile_number'];

        $protected_number = '-';
        if(!isset($this->attributes['mobile_number'])){
            return $protected_number;
        }
        $mobile_number = $this->attributes['mobile_number'];
        if($mobile_number != '') {
            $protected_number = str_replace(range(0,9), "*", substr($mobile_number, 0, -4)) .  substr($mobile_number, -4);
        }

        return $mobile_number;
    }

    public function getCompanyNameAttribute()
    {
        $company_name = '';

        if(@$this->attributes['user_type'] == 'Driver') {
            $company_name = isset($this->company) ? $this->company->name : '';
        }

        return $company_name;
    }

}
