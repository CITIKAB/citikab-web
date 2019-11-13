<?php

/**
 * Payments Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Payments
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\PaymentsDataTable;
use App\Http\Start\Helpers;
use Excel;
use App\Models\User;
use App\Models\Trips;
use Validator;
use DB;

class PaymentsController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

        /**
     * Load Datatable for Rating
     *
     * @param array $dataTable  Instance of PaymentsDataTable
     * @return datatable
     */
    public function index(PaymentsDataTable $dataTable)
    {
        return $dataTable->render('admin.payments.payments');
    }

     
}
