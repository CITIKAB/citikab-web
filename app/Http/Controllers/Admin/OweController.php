<?php

/**
 * Owe Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Owe Ammount
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
use App\Models\Wallet;
use App\Models\Currency;
use App\Models\Trips;
use App\Models\DriverPayment;
use Validator;
use DB;
use App\DataTables\OweDataTable;
use App\DataTables\DriverPaymentDataTable;

class OweController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
        $this->view_data = array();
    }

    /**
     * Load Datatable for Wallet
     *
     * @return view file
     */
    public function index(DriverPaymentDataTable $driver_payment, OweDataTable $owe_amount)
    {
        $this->view_data['main_title'] = 'Owe Amount';
        if(LOGIN_USER_TYPE == 'company') {
            $company_owe = Trips::CompanyTripsOnly(auth()->guard('company')->user()->id)->get();
            $this->view_data['sub_title'] = 'Manage Payment To Comapany';
            $this->view_data['total_owe_amount']       = $company_owe->sum('owe_amount');
            $this->view_data['applied_owe_amount']     = $company_owe->sum('applied_owe_amount');
            $this->view_data['remaining_owe_amount']   = $this->view_data['total_owe_amount'] - $this->view_data['applied_owe_amount'];
            return $driver_payment->render('admin.owe.index',$this->view_data);
        }

        return $owe_amount->setFilterType('overall')->render('admin.owe.index',$this->view_data);
    }

    public function owe_details(OweDataTable $dataTable,Request $request)
    {
        $type = $request->type;
        $this->view_data['main_title']  = ucfirst($type).' Owe Amount';
        return $dataTable->setFilterType($type)->render('admin.owe.index',$this->view_data);
    }

    public function update_payment(Request $request)
    {
        if(!auth()->guard('company')->check()) {
            abort(404);
        }

        $driver_id = $request->driver_id;
        $payable_amount = $request->payable_amount;
        $currency_code = $request->currency_code;

        if($payable_amount <= 0 ) {
            $this->helper->flash_message('danger', 'Driver Payment Failed.');
            return back();
        }

        $driver_payment = DriverPayment::firstOrNew(['driver_id' => $driver_id]);

        if($driver_payment->paid_amount > 0) {
            $payable_amount = $driver_payment->paid_amount + $payable_amount;
        }
        $driver_payment->driver_id = $driver_id;
        $driver_payment->currency_code = $currency_code;
        $driver_payment->paid_amount = $payable_amount;
        // $driver_payment->last_trip_id = $last_trip;
        $driver_payment->save();

        $this->helper->flash_message('success', 'Payment Details Updated.');
        return back();
    }

}
