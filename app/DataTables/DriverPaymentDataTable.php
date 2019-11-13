<?php

/**
 * Payment From Driver DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Payment From Driver
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\User;
use App\Models\Trips;
use App\Models\DriverPayment;
use Yajra\Datatables\Services\DataTable;
use Auth;
use DB;

class DriverPaymentDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';

    //protected $exportColumns = [ ];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $trips = $this->query();

        return $this->datatables
            ->of($trips)
            ->addColumn('trip_ids', function ($trips) {
                return '<div class="min_width">'.$trips->trip_ids.'</div>';
            })
            ->addColumn('user_id', function ($trips) {
                return $trips->driver_id;
            })
            ->addColumn('first_name', function ($trips) {
                return $trips->first_name;
            })
            ->addColumn('cash_trip_amount', function ($trips) {
                $total_fare = $trips->driver_trips->whereIn('payment_mode',['Cash & Wallet','Cash'])
                ->whereIn('status',['Payment','Completed'])->sum('total_fare');
                $driver_payments = DriverPayment::where('driver_id', $trips->driver_id)->first();
                $cash_trip_amount = $total_fare - @$driver_payments->paid_amount;
                $cash_trip_amount = $cash_trip_amount <= 0 ? 0:$cash_trip_amount;

                return number_format($cash_trip_amount,2,'.','');
            })            
            ->addColumn('currency_code', function ($trips) {
                return @$trips->driver_trips->first()->currency_code;
            })
            ->addColumn('paid_amount', function ($trips) {
                $driver_payments = DriverPayment::where('driver_id', $trips->driver_id)->first();
                return isset($driver_payments)? $driver_payments->paid_amount : '-';
            })
            ->addColumn('action', function ($trips) {
                $cash_trip_amount = $trips->driver_trips->whereIn('payment_mode',['Cash & Wallet','Cash'])
                ->whereIn('status',['Payment','Completed'])->sum('total_fare');
                $driver_payments = DriverPayment::where('driver_id', $trips->driver_id)->first();
                $payable_amount = $cash_trip_amount - @$driver_payments->paid_amount;
                $payable_amount = $payable_amount <= 0 ? 0 : number_format($payable_amount,2,'.','');

                $paid_btn = '<form action="'.route('update_payment').'" method="GET">
                            <input type="hidden" name="driver_id" value="'.$trips->id.'">
                            <input type="hidden" name="currency_code" value="'.$trips->driver_trips->first()->currency_code.'">
                            <input type="hidden" name="payable_amount" value="'.$payable_amount.'">
                            <button type="submit" class="btn btn-xs btn-primary"> Paid </button>
                            </form>';

                return $payable_amount == 0 ? '' : $paid_btn;
            })
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $owe = User::join('trips', function($join) {
                    $join->on('users.id', '=', 'trips.driver_id');
                })
                ->where('company_id',Auth::guard('company')->user()->id)
                ->whereIn('trips.payment_mode',['Cash & Wallet','Cash'])
                ->whereIn('trips.status',['Payment','Completed'])
                ->select('trips.id as trip_id','users.id As id', 'trips.driver_id as driver_id', 'users.first_name', 'trips.currency_code as currency_code',DB::raw("GROUP_CONCAT(trips.id) as trip_ids"),DB::raw('SUM(trips.total_fare) as total_fare'))
                ->groupBy('driver_id')
                ->get();

        return $this->applyScopes($owe);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'users.id', 'title' => 'Driver Id'])
        ->addColumn(['data' => 'first_name', 'name' => 'first_name', 'title' => 'First Name'])
        ->addColumn(['data' => 'trip_ids', 'name' => 'trip_ids', 'title' => 'Trip Ids','orderable' => false, 'searchable' => false])
        ->addColumn(['data' => 'cash_trip_amount', 'name' => 'cash_trip_amount', 'title' => 'Amount To Company'])
        ->addColumn(['data' => 'paid_amount', 'name' => 'paid_amount', 'title' => 'Amount Paid'])
        ->addColumn(['data' => 'currency_code', 'name' => 'trips.currency_code', 'title' => 'Currency Code','orderable' => false])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false, 'exportable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);
    }
}
