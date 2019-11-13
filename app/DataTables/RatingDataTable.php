<?php

/**
 * Rating DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Rating
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Rating;
use Yajra\Datatables\Services\DataTable;
use Auth;

class RatingDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
    protected $exportColumns = ['id','rider_name','driver_name','car_name','rider_rading','driver_rading','driver_name','rider_comments','driver_comments', 'status'];

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
        $rating = Rating::
                        where(function($query)  {  //If login user is company then get that company driver ratings only
                            if(LOGIN_USER_TYPE=='company') {
                                $query->whereHas('driver',function($q1){
                                    $q1->where('company_id',Auth::guard('company')->user()->id);
                                });
                            }
                        })
                        ->join('users', function($join) {
                                $join->on('users.id', '=', 'rating.user_id');
                            })
                        ->join('trips', function($join) {
                                $join->on('trips.id', '=', 'rating.trip_id');
                            })
                        ->join('car_type', function($join) {
                                $join->on('car_type.id', '=', 'trips.car_id');
                            })
                        ->leftJoin('users as u', function($join) {
                                $join->on('u.id', '=', 'rating.driver_id');
                            })
                         ->leftJoin('companies', function($join) {
                            $join->on('u.company_id', '=', 'companies.id');
                        })
                        ->select(['rating.id as id', 'u.first_name as driver_name', 'users.first_name as rider_name', 'car_type.car_name as car_name','rating.rider_rating as rider_rating', 'rating.driver_rating as driver_rading','rating.rider_comments as rider_comments', 'rating.driver_comments as driver_comments','trips.created_at as date','rating.*','companies.name as driver_company_name']);

        return $this->applyScopes($rating);
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
                ['data' => 'driver_company_name', 'name' => 'companies.name', 'title' => 'Company Name']
            );
        }
        return $this->builder()
        ->addColumn(['data' => 'trip_id', 'name' => 'trip_id', 'title' => 'Trip Number'])
        ->addColumn(['data' => 'date', 'name' => 'trips.created_at', 'title' => 'Trip Date'])
        ->addColumn(['data' => 'driver_name', 'name' => 'u.first_name', 'title' => 'Driver Name'])
        ->addColumn(['data' => 'rider_name', 'name' => 'users.first_name', 'title' => 'Rider Name'])
        ->Columns($company_columns)
        ->addColumn(['data' => 'car_name', 'name' => 'car_name', 'title' => 'Car name'])
        ->addColumn(['data' => 'driver_rating', 'name' => 'driver_rating', 'title' => 'Driver Rating'])
        ->addColumn(['data' => 'rider_rating', 'name' => 'rider_rating', 'title' => 'Rider Rating'])
        ->addColumn(['data' => 'rider_comments', 'name' => 'rider_comments', 'title' => 'Rider Comments'])
        ->addColumn(['data' => 'driver_comments', 'name' => 'driver_comments', 'title' => 'Driver Comments'])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => [],
            'order' => [0, 'desc'],
        ]);
    }
}
