<?php

/**
 * Payment DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Payment
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Trips;
use Yajra\Datatables\Services\DataTable;
use DB;
use App\Http\Start\Helpers;
use Auth;

class PaymentsDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
     // protected $exportColumns = ['id', 'driver_name', 'rider_name', 'time_amount','distance_amount','base_amount','access_amount','total_amount','driver_amount','status','created_at'];

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
            ->addColumn('time_fare', function ($trips) {   
                return @$trips->currency->symbol.$trips->time_fare;
            })->addColumn('distance_fare', function ($trips) {   
                return @$trips->currency->symbol.$trips->distance_fare;
            })->addColumn('base_fare', function ($trips) {   
                return @$trips->currency->symbol.$trips->base_fare;
            })->addColumn('driver_payout', function ($trips) {   
                return @$trips->currency->symbol.$trips->driver_payout;
            })->addColumn('driver_or_company_commission', function ($trips) {   
                return @$trips->currency->symbol.$trips->driver_or_company_commission;
            })->addColumn('total_fare', function ($trips) {   
                return @$trips->currency->symbol.$trips->total_fare;
            })->addColumn('access_fee', function ($trips) {   
                return @$trips->currency->symbol.$trips->access_fee;
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
        $trips = Trips::
                        where(function($query)  {
                            if(LOGIN_USER_TYPE=='company') {  //If login user is company then get that company driver trips only
                                $query->whereHas('driver',function($q1){
                                    $q1->where('company_id',Auth::guard('company')->user()->id);
                                });
                            }
                        })
                        ->join('currency', function($join) {
                                $join->on('currency.code', '=', 'trips.currency_code');
                            })
                        ->leftJoin('users as u', function($join) {
                                $join->on('u.id', '=', 'trips.driver_id');
                            })
                        ->leftJoin('users as rider', function($join) {
                            $join->on('rider.id', '=', 'trips.user_id');
                        })
                        ->leftJoin('companies', function($join) {
                            $join->on('u.company_id', '=', 'companies.id');
                        })
                        ->select(['trips.id as id','trips.begin_trip as begin_trip', 'u.first_name as driver_name','rider.first_name as rider_name','trips.time_fare AS time_fare', 'trips.distance_fare AS distance_fare','trips.base_fare AS base_fare','trips.access_fee AS access_fee','trips.total_fare AS total_fare','trips.driver_payout AS driver_amount','trips.payment_status','trips.*','trips.created_at as trip_date','companies.name as company_name']);
        return $this->applyScopes($trips);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
   public function html()
    {
        $company_columns = array();
        if(LOGIN_USER_TYPE == 'company') {
            $payout_columns = array(
                 ['data' => 'total_fare', 'name' => 'total_fare', 'title' => 'Total Fare'],
                ['data' => 'driver_or_company_commission', 'name' => 'driver_or_company_commission', 'title' => 'Admin Commission'],
               
            );                
        }else {
            $payout_columns = array(
                ['data' => 'access_fee', 'name' => 'access_fee', 'title' => 'Access Fare'],
                ['data' => 'driver_or_company_commission', 'name' => 'driver_or_company_commission', 'title' => 'Admin Commission'],
                ['data' => 'total_fare', 'name' => 'total_fare', 'title' => 'Total Fare']
            );
            $company_columns = array(
                ['data' => 'company_name', 'name' => 'companies.name', 'title' => 'Company Name']
            );
        }


        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'trips.id', 'title' => 'Id'])
        ->addColumn(['data' => 'trip_date', 'name' => 'trips.created_at', 'title' => 'Trip Date'])
        ->columns($company_columns)
        ->addColumn(['data' => 'driver_name', 'name' => 'u.first_name', 'title' => 'Driver Name'])
        ->addColumn(['data' => 'rider_name', 'name' => 'rider.first_name', 'title' => 'Rider Name'])
        ->addColumn(['data' => 'time_fare', 'name' => 'time_fare', 'title' => 'Time Fare'])
        ->addColumn(['data' => 'distance_fare', 'name' => 'distance_fare', 'title' => 'Distance Fare'])
        ->addColumn(['data' => 'base_fare', 'name' => 'base_fare', 'title' => 'Base Fare'])
        ->columns($payout_columns)
        ->addColumn(['data' => 'driver_payout', 'name' => 'driver_payout', 'title' => 'Earnings'])
        ->addColumn(['data' => 'status', 'name' => 'trips.status', 'title' => 'Status'])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);
    }

    protected function buildExcelFile()
    {

        $width = array(
                        'A' => '1',
                        'B' => '2',
                        'C' => '2',
                        'D' => '2',
                        'E' => '2',
                        'F' => '2',
                        'G' => '2',
                        'H' => '2',
                        'I' => '2',
                        'J' => '2',
                        'K' => '3',
                    );
        return Helpers::buildExcelFile($this->getFilename(), $this->getDataForExport(), $width);
    }
}
