<?php

/**
 * Driver Address Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Driver Address
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */

    // Join with users table
	public function users() {
		return $this->belongsTo('App\Models\User', 'user_id', 'id');
	}
}
