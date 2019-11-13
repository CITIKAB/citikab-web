<?php

/**
 * Trips Type DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Trips 
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

class TripsDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
    protected $exportColumns = ['id', 'driver_name', 'rider_name','pickup_location','drop_location','trip_date','total','car_name','status'];
      // $data[0]=['Id','From Location','To Location','Date','Driver Name','Rider Name','Fare','Vehicle Details','Status','Created At'];
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
            ->addColumn('total_amount', function ($trips) {
                if (LOGIN_USER_TYPE=='company') {
                    return $trips->currency->symbol.@($trips->subtotal_fare + $trips->driver_peak_amount - $trips->driver_or_company_commission);
                }
                else{
                    return $trips->currency->symbol.@($trips->subtotal_fare + $trips->peak_amount + $trips->access_fee + $trips->schedule_fare);
                }
            })
            ->addColumn('action', function ($trips) {   
                return '<a href="'.url(LOGIN_USER_TYPE.'/view_trips/'.$trips->id).'" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>';
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
        $trips =  $result = Trips::
                        join('users as rider', function($join) {
                                $join->on('rider.id', '=', 'trips.user_id');
                            })
                        ->join('currency', function($join) {
                                $join->on('currency.code', '=', 'trips.currency_code');
                            })
                        ->join('car_type', function($join) {
                                $join->on('car_type.id', '=', 'trips.car_id');
                            })
                        ->leftJoin('users as driver', function($join) {
                                $join->on('driver.id', '=', 'trips.driver_id');
                            })
                        ->leftJoin('companies', function($join) {
                                $join->on('driver.company_id', '=', 'companies.id');
                            })
                        ->select(['trips.id as id','trips.begin_trip as begin_trip','trips.pickup_location as pickup_location','trips.drop_location as drop_location', 'driver.first_name as driver_name', 'rider.first_name as rider_name',  DB::raw('CONCAT(currency.symbol, trips.total_fare) AS total_amount'), 'trips.total_fare as total','trips.status as status','car_type.car_name as car_name', 'trips.created_at as trip_date', 'trips.updated_at as updated_at', 'trips.*', 'companies.name as company_name']);

        if (LOGIN_USER_TYPE=='company') {  //If login user is company then get that company drivers only
            $trips = $trips->whereHas('driver',function($q){
                        $q->where('company_id',Auth::guard('company')->user()->id);
                    });
        }

        return $this->applyScopes($trips);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {

        if(LOGIN_USER_TYPE == 'company') {
            $payout_columns = array(
                ['data' => 'total_amount', 'name' => 'total_fare', 'title' => 'Earned'],
            );              
            $company_columns = array();                
        }else {
            $payout_columns = array(
                ['data' => 'total_amount', 'name' => 'total_fare', 'title' => 'Earned'],
            );
            $company_columns = array(
                ['data' => 'company_name', 'name' => 'companies.name', 'title' => 'Company Name']
            );
        }
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'trips.id', 'title' => 'Id'])
        ->addColumn(['data' => 'driver_name', 'name' => 'driver.first_name', 'title' => 'Driver Name'])
        ->addColumn(['data' => 'rider_name', 'name' => 'rider.first_name', 'title' => 'Rider Name'])
        ->columns($company_columns)
        ->addColumn(['data' => 'pickup_location', 'name' => 'trips.pickup_location', 'title' => 'Pickup Location'])
        ->addColumn(['data' => 'drop_location', 'name' => 'trips.drop_location', 'title' => 'Drop Location'])
        ->addColumn(['data' => 'trip_date', 'name' => 'trips.created_at', 'title' => 'Trip Date'])
        ->columns($payout_columns)
        ->addColumn(['data' => 'car_name', 'name' => 'car_type.car_name', 'title' => 'Vehicle Details'])
        ->addColumn(['data' => 'status', 'name' => 'trips.status', 'title' => 'Status'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
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
                        'F' => '1',
                        'G' => '2',
                        'H' => '3',
                    );
        return Helpers::buildExcelFile($this->getFilename(), $this->getDataForExport(), $width);
    }
}
