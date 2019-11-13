<?php

/**
 * Owe DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Owe Amount
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\User;
use App\Models\Trips;
use Yajra\Datatables\Services\DataTable;
use Auth;
use DB;

class OweDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';

    //protected $exportColumns = [ 'id', 'first_name','trip_ids', 'owe_amount','applied_owe_amount','remaining_owe_amount','currency_code'];

    protected $filter_type;

    // Set the value for User Type 
    public function setFilterType($filter_type){
        $this->filter_type = $filter_type;
        return $this;
    }

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $owe = $this->query();

        return $this->datatables
            ->of($owe)
            ->addColumn('trip_ids', function ($owe) {
                if(LOGIN_USER_TYPE == 'admin')
                    $trips_ids = Trips::CompanyTripsOnly($owe->company_id)->whereIn('payment_mode',['Cash & Wallet','Cash'])->get()->pluck('id')->toArray();
                else
                    $trips_ids = $owe->driver_trips->whereIn('payment_mode',['Cash & Wallet','Cash'])->whereIn('status',['Payment','Completed'])->pluck('id')->toArray();

                return '<div class="min_width">'.implode(',', $trips_ids).'</div>';
            })
            ->addColumn('owe_amount', function ($owe) {
                if(LOGIN_USER_TYPE == 'admin')
                    $owe_amount = Trips::CompanyTripsOnly($owe->company_id)->whereIn('payment_mode',['Cash & Wallet','Cash'])->get()->sum('owe_amount');
                else
                    $owe_amount = $owe->driver_trips->sum('owe_amount');

                return number_format($owe_amount,2,'.','');
            })
            ->addColumn('applied_owe_amount', function ($owe) {
                if(LOGIN_USER_TYPE == 'admin')
                    $applied_owe_amount = Trips::CompanyTripsOnly($owe->company_id)->get()->sum('applied_owe_amount');
                else
                    $applied_owe_amount = $owe->driver_trips->sum('applied_owe_amount');
                return number_format($applied_owe_amount,2,'.','');
            })
            ->addColumn('remaining_owe_amount', function ($owe) {
                if(LOGIN_USER_TYPE == 'admin') {
                    $company_trips = Trips::CompanyTripsOnly($owe->company_id)->get();
                    $remaining_owe_amount = $company_trips->sum('owe_amount') - $company_trips->sum('applied_owe_amount');
                }
                else {
                    $remaining_owe_amount = $owe->driver_trips->sum('owe_amount') - $owe->driver_trips->sum('applied_owe_amount');
                }
                return number_format($remaining_owe_amount,2,'.','');
            })
            ->addColumn('currency_code', function ($owe) {
                return @$owe->driver_trips->first()->currency_code;
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
        $owe = User::
                where(function($query)  {
                    if(LOGIN_USER_TYPE=='company') {
                        //If login user is company then get that company drivers only
                        $query->where('company_id',Auth::guard('company')->user()->id);
                    }
                })
                ->join('trips', function($join) {
                    $join->on('users.id', '=', 'trips.driver_id');
                })
                ->leftJoin('companies', function($join) {
                    $join->on('users.company_id', '=', 'companies.id');
                })
                ->select('trips.id as trip_id','users.id As id', 'users.first_name', 'users.last_name','users.email','trips.currency_code as currency_code',DB::raw("GROUP_CONCAT(trips.id) as trip_ids"),DB::raw('SUM(trips.owe_amount) as owe_amount'),DB::raw('SUM(trips.remaining_owe_amount) as remaining_owe_amount'),DB::raw('SUM(trips.applied_owe_amount) as applied_owe_amount'),'companies.name as driver_company_name','companies.id as company_id');
        if($this->filter_type == 'applied') {
            $owe = $owe->where('applied_owe_amount','>','0');
        }
        else {
            $owe = $owe->where('owe_amount','>','0');
        }

        if(LOGIN_USER_TYPE=='company') {
            $owe = $owe->groupBy('id');
        }
        else {
            $owe = $owe->groupBy('company_id');
        }

        return $this->applyScopes($owe);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        $owe_columns = array();

        if(LOGIN_USER_TYPE == 'admin') {
            $columns = array(
                ['data' => 'company_id', 'name' => 'companies.id', 'title' => 'Company Id'],
                ['data' => 'driver_company_name', 'name' => 'companies.name', 'title' => 'Company Name'],
                ['data' => 'trip_ids', 'name' => 'trip_ids', 'title' => 'Trip Ids','orderable' => false, 'searchable' => false],
                ['data' => 'owe_amount', 'name' => 'owe_amount', 'title' => 'Owe Amount'],
            );

            $owe_columns = array(
                ['data' => 'applied_owe_amount', 'name' => 'applied_owe_amount', 'title' => 'Applied Owe Amount'],
                ['data' => 'remaining_owe_amount', 'name' => 'remaining_owe_amount', 'title' => 'Remaining Owe Amount']
            );
        }
        else {
            $columns = array(
                ['data' => 'id', 'name' => 'users.id', 'title' => 'Driver Id'],
                ['data' => 'first_name', 'name' => 'users.first_name', 'title' => 'First Name'],
                ['data' => 'trip_ids', 'name' => 'trip_ids', 'title' => 'Trip Ids','orderable' => false, 'searchable' => false],
            );
            if($this->filter_type == 'applied') {
                $owe_columns = array(['data' => 'applied_owe_amount', 'name' => 'applied_owe_amount', 'title' => 'Applied Owe Amount']);
            }
            else {
                $owe_columns = array(['data' => 'owe_amount', 'name' => 'owe_amount', 'title' => 'Owe Amount']);
            }
        }
        $owe_columns = array_merge($columns, $owe_columns);
        return $this->builder()
            ->columns($owe_columns)
            ->addColumn(['data' => 'currency_code', 'name' => 'trips.currency_code', 'title' => 'Currency Code','orderable' => false])
            ->parameters([
                'dom' => 'lBfrtip',
                // 'dom' => 'Bfrtip',
                'buttons' => ['csv', 'excel', 'print', 'reset'],
                'order' => [0, 'desc'],
            ]);
    }
}
