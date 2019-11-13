<?php

/**
 * Room Type DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Room Type
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Cancel;
use Yajra\Datatables\Services\DataTable;
use Auth;

class CancelTripsDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
    protected $exportColumns = ['id','trips_created_at','rider_name','driver_name','pickup_location','drop_location','cancel_reason','cancel_comments','cancelled_by','created_at'];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $rating = $this->query();

        return $this->datatables
            ->of($rating)
           
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $cancel = Cancel::
                        where(function($query)  {
                            if(LOGIN_USER_TYPE=='company') {  //If login user is company then get that company driver trips only
                                $query->whereHas('trip.driver',function($q1){
                                    $q1->where('company_id',Auth::guard('company')->user()->id);
                                });
                            }
                        })
                        ->join('trips', function($join) {
                                $join->on('trips.id', '=', 'cancel.trip_id');
                            })
                        ->join('users', function($join) {
                                $join->on('users.id', '=', 'trips.user_id');
                            })
                        ->leftJoin('users as u', function($join) {
                            $join->on('u.id', '=', 'trips.driver_id');
                        })
                        ->leftJoin('companies', function($join) {
                            $join->on('u.company_id', '=', 'companies.id');
                        })
                        ->select(['cancel.id as id', 'cancel.created_at', 'u.first_name as driver_name', 'users.first_name as rider_name','cancel.cancel_reason as cancel_reason', 'cancel.cancel_comments as cancel_comments ','cancel.cancelled_by as cancelled_by', 'cancel.created_at as cancel_created_at','cancel.*','trips.pickup_location as pickup_location','trips.drop_location as drop_location','trips.created_at as trips_created_at','companies.name as company_name']);

        return $this->applyScopes($cancel);
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
        ->addColumn(['data' => 'id', 'name' => 'cancel.id', 'title' => 'Id'])
        ->addColumn(['data' => 'trips_created_at', 'name' => 'trips.created_at', 'title' => 'Trip Date'])
        ->columns($company_columns)
        ->addColumn(['data' => 'driver_name', 'name' => 'u.first_name', 'title' => 'Driver Name'])
        ->addColumn(['data' => 'rider_name', 'name' => 'users.first_name', 'title' => 'Rider Name'])
        ->addColumn(['data' => 'pickup_location', 'name' => 'pickup_location', 'title' => 'Pickup Location'])
        ->addColumn(['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location'])
        ->addColumn(['data' => 'cancel_reason', 'name' => 'cancel_reason', 'title' => 'Reason'])
        ->addColumn(['data' => 'cancel_comments', 'name' => 'cancel_comments', 'title' => 'Comments'])
        ->addColumn(['data' => 'cancelled_by', 'name' => 'cancelled_by', 'title' => 'Canceled By'])
        ->addColumn(['data' => 'cancel_created_at', 'name' => 'cancel.created_at', 'title' => 'Cancel At'])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => [],
            'order' => [0, 'desc'],
        ]);
    }
}
