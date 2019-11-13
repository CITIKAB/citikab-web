<?php

/**
 * Company Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Company
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use DB;

class Company extends Authenticatable
{
    use Notifiable;

    protected $guard = 'company';

    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'country_code','mobile_number','mobile_number','vat_number','password','status','device_type','device_id','currency_code','language','address','city','state','country','postal_code'
    ];

    protected $appends = ['first_name'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Update Company Role
    public static function update_role($user_id, $role_id)
    {
        return DB::table('role_user')->where('user_id', $user_id)->update(['role_id' => $role_id]);
    }

    public function company_document(){
        return $this->hasOne('App\Models\CompanyDocuments','company_id','id');
    }

    public function default_payout_credentials(){
        return $this->hasOne('App\Models\CompanyPayoutCredentials','company_id','id')->where('is_default','Yes');
    }

    public function drivers(){
        return $this->hasMany('App\Models\User','company_id','id');
    }

    public function getFirstNameAttribute()
    {
        return $this->name;
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

        return $protected_number;
    }
}
