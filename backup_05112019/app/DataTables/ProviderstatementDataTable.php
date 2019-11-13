<?php

/**
 * Provider Statement DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Statements
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Trips;
use App\Models\User;
use Yajra\Datatables\Services\DataTable;
use Auth;
use DB;
use App\Http\Start\Helpers;

class ProviderstatementDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';

    //protected $exportColumns = [ 'id', 'first_name', 'last_name', 'email','country_code' , 'mobile_number', 'created_at' ];

 public function ajax()
    {
        $trips = $this->query();

        return $this->datatables
            ->of($trips)
            ->addColumn('provider_name', function ($trips) {   
                return @$trips->driver->first_name;
            })
            ->addColumn('phone_number', function ($trips) {   
                return @$trips->driver->hidden_mobile_number;
            })
            ->addColumn('total_rides_driver', function ($trips) {   
                return @$trips->driver->total_rides;
            })
            ->addColumn('total_earnings_driver', function ($trips) {   
                return @$trips->currency->symbol.$trips->driver->total_earnings;
            })
            ->addColumn('total_commission_driver', function ($trips) { 
                if (LOGIN_USER_TYPE=='company') {
                    return @$trips->currency->symbol.$trips->driver->total_company_admin_commission;
                }else{
                    return @$trips->currency->symbol.$trips->driver->total_commission;
                }
            })
            ->addColumn('driver_joined_at', function ($trips) {   
                return @$trips->driver->date_time_join;
            })
            ->addColumn('company_name', function ($trips) {   
                return @$trips->driver->company->name;
            })
            ->addColumn('action', function ($trips) {   
                return '<a href="'.url(LOGIN_USER_TYPE.'/view_driver_statement/'.$trips->driver_id).'" class="btn btn-xs btn-primary">View by Ride</a>';
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
        $trips = Trips::with(['driver' => function($q) {
            $q->with(['company']);
            }, 'currency'])
                ->where(function($query)  {
                    if(LOGIN_USER_TYPE=='company') {  //If login user is company then get that company drivers only
                        $query->whereHas('driver',function($q1){
                            $q1->where('company_id',Auth::guard('company')->user()->id);
                        });
                    }
                })
                ->groupBy('trips.driver_id')->get();
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
        if(LOGIN_USER_TYPE == 'admin') {
            $company_columns = array(
                ['data' => 'company_name', 'name' => 'companies.name', 'title' => 'Company Name']
            );
        }
        return $this->builder()
        ->addColumn(['data' => 'driver.id', 'name' => 'driver.id', 'title' => 'Driver id'])
        ->addColumn(['data' => 'provider_name', 'name' => 'driver_name', 'title' => 'Driver Name'])
        ->Columns($company_columns)
        ->addColumn(['data' => 'phone_number', 'name' => 'driver.mobile_number', 'title' => 'Mobile'])
        ->addColumn(['data' => 'driver.status', 'name' => 'driver.status', 'title' => 'Status'])
        ->addColumn(['data' => 'total_rides_driver', 'name' => 'total_rides_driver', 'title' => 'Total Rides'])
        ->addColumn(['data' => 'total_earnings_driver', 'name' => 'total_earnings_driver', 'title' => 'Earnings'])
        ->addColumn(['data' => 'total_commission_driver', 'name' => 'total_commission_driver', 'title' => 'Admin commission'])
        ->addColumn(['data' => 'driver_joined_at', 'name' => 'driver_joined_at', 'title' => 'Joined at'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Details', 'orderable' => false, 'searchable' => false])
        
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);
    }
      protected function buildExcelFile()
    {

        $width = array(
                        'A' => '10',
                        'B' => '20',
                        'C' => '20',
                        'D' => '20',
                        'E' => '20',
                        'F' => '10',
                        'G' => '20',
                        'H' => '30',
                    );
        return Helpers::buildExcelFile($this->getFilename(), $this->getDataForExport(), $width);
    }
}
