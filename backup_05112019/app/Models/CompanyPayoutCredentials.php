<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DateTimeZone;
use Config;

class CompanyPayoutCredentials extends Model
{
	

    // Return the companies default payout_preference details
    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    // Return the company default payout_preference details
    public function company_payout_preference()
    {
        return $this->belongsTo('App\Models\CompanyPayoutPreference','preference_id','id');
    }

}
