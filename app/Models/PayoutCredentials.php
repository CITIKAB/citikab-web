<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DateTimeZone;
use Config;

class PayoutCredentials extends Model
{
	

    // Return the drivers default payout_preference details
    public function payout_preference()
    {
        return $this->belongsTo('App\Models\PayoutPreference','preference_id','id');
    }

    // Join with users table
	public function users() {
		return $this->belongsTo('App\Models\User', 'user_id', 'id');
	}
}
