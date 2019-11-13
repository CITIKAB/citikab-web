<?php

/**
 * Metas Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Metas
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metas extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'metas';

    public $timestamps = false;
}
