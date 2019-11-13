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

use App\Models\ScheduleRide;
use Yajra\Datatables\Services\DataTable;
use DB;
use App\Http\Start\Helpers;
use Auth;

class LaterBookingDataTables extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
   /* protected $exportColumns = ['id', 'driver_name', 'rider_name','pickup_location','drop_location','trip_date','total','car_name','status'];*/
      // $data[0]=['Id','From Location','To Location','Date','Driver Name','Rider Name','Fare','Vehicle Details','Status','Created At'];
    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $schedule_rides = $this->query();

        return $this->datatables
            ->of($schedule_rides)
            ->editColumn('driver_name', function($schedule_rides) {
                $button = '';
                if ($schedule_rides->status=='Pending' && $schedule_rides->booking_type=='Manual Booking') {
                    $button = '<br><a href="'.url(LOGIN_USER_TYPE.'/manual_booking/'.$schedule_rides->id).'" class="btn btn-primary edit_'.$schedule_rides->id.'"><i class="fa fa-edit"></i></a>';
                }

                if ($schedule_rides->status=='Completed' && $schedule_rides->driver_name==null && $schedule_rides->request->trips != null) {
                    $driver_name = $schedule_rides->request->trips->driver->first_name;
                }else{
                    $driver_name = ($schedule_rides->driver_name==null?'Auto Assign':$schedule_rides->driver_name);
                }
                return $driver_name.' '.$button;
            })
            ->editColumn('status', function($schedule_rides) {
                if ($schedule_rides->status=='Pending' && $schedule_rides->booking_type=='Manual Booking') {
                    $status = '<span class="cancel_'.$schedule_rides->id.'">'.$schedule_rides->status.'</span><br><span data-toggle="modal" data-target="#cancel_popup" class="btn btn-primary cancel_button" schedule_id="'.$schedule_rides->id.'">Cancel</span>';
                }elseif($schedule_rides->status=='Cancelled'){
                    if ($schedule_rides->schedule_cancel==null) {
                        $status = $schedule_rides->status;
                    }else{
                        $status = 'Cancelled by '.$schedule_rides->schedule_cancel->cancel_by.'<br><span data-toggle="modal" data-target="#cancel_reason_popup" class="btn btn-primary cancel_button" schedule_id="'.$schedule_rides->id.'" cancel_by="'.$schedule_rides->schedule_cancel->cancel_by.'" cancel_reason="'.$schedule_rides->schedule_cancel->cancel_reason.'">Cancel Reason</a>';
                    }
                }elseif($schedule_rides->status=='Car Not Found'){
                    $status = '<span class="immediate_request_'.$schedule_rides->id.'">'.$schedule_rides->status.'</span><br><span id="immediate_request" class="btn btn-primary" schedule_id="'.$schedule_rides->id.'">immediate Request</span>';
                }elseif($schedule_rides->status=='Completed' && $schedule_rides->booking_type=='Manual Booking' && $schedule_rides->request->trips != null && $schedule_rides->request->trips->status == 'Cancelled'){
                    $status = 'Trip cancelled by driver';
                }elseif($schedule_rides->status=='Completed' && $schedule_rides->request->trips != null ){
                    $status = $schedule_rides->request->trips->status;
                }else{
                    $status = $schedule_rides->status;
                }
                return $status;
            })
            ->addColumn('date_time', function ($schedule_rides) {
                return date("Y-m-d H:i a",strtotime($schedule_rides->schedule_date.' '.$schedule_rides->schedule_time));
            })
            ->addColumn('company_name', function ($schedule_rides) {
                return $schedule_rides->company_name != '' ? $schedule_rides->company_name : '-';
            })
            ->addColumn('trip_details', function ($schedule_rides) {
                if ($schedule_rides->request==null || $schedule_rides->request->trips==null) {
                    return '---';
                }else{
                    return '<a href="'.url(LOGIN_USER_TYPE.'/view_trips/'.$schedule_rides->request->trips->id).'" class="btn btn-primary"><i class="fa fa-eye"></i></a>';
                }
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
        $schedule_rides =  $result = ScheduleRide::
                        join('users as rider', function($join) {
                                $join->on('rider.id', '=', 'schedule_ride.user_id');
                            })
                        ->join('car_type', function($join) {
                                $join->on('car_type.id', '=', 'schedule_ride.car_id');
                            })
                        ->leftJoin('users as driver', function($join) {
                                $join->on('driver.id', '=', 'schedule_ride.driver_id');
                            })
                        ->leftJoin('companies', function($join) {
                            $join->on('driver.company_id', '=', 'companies.id');
                        })
                        ->with('schedule_cancel')
                        ->with('request.trips.driver')
                        ->select([
                            'schedule_ride.*',
                            'rider.first_name as rider_name',
                            'driver.first_name as driver_name',
                            DB::raw('DATE_FORMAT(CONCAT(schedule_ride.schedule_date," ", schedule_ride.schedule_time), "%d %M %Y %H:%i") as date_time'),
                            'companies.name as company_name'
                        ])
                        ->where(function($query)  {
                            //If login user is company then get that company booking only
                            if(LOGIN_USER_TYPE=='company') {
                                $query->where('schedule_ride.company_id',Auth::guard('company')->user()->id)
                                ->orWhereHas('driver',function($q1){
                                    $q1->where('driver.company_id',Auth::guard('company')->user()->id);
                                });
                            }
                        });

        return $this->applyScopes($schedule_rides);
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
        ->addColumn(['data' => 'id', 'name' => 'schedule_ride.id', 'title' => 'Id'])
        ->addColumn(['data' => 'booking_type', 'name' => 'schedule_ride.booking_type', 'title' => 'Booked By'])
        ->addColumn(['data' => 'date_time', 'name' => 'schedule_ride.schedule_date', 'title' => 'Date'])
        ->addColumn(['data' => 'driver_name', 'name' => 'driver.first_name', 'title' => 'Driver Name'])
        ->columns($company_columns)
        ->addColumn(['data' => 'rider_name', 'name' => 'rider.first_name', 'title' => 'Rider Name'])
        ->addColumn(['data' => 'pickup_location', 'name' => 'schedule_ride.pickup_location', 'title' => 'Pickup Location'])
        ->addColumn(['data' => 'drop_location', 'name' => 'schedule_ride.drop_location', 'title' => 'Drop Location'])
        ->addColumn(['data' => 'trip_details', 'name' => 'schedule_ride.id', 'title' => 'Trip Details'])
        ->addColumn(['data' => 'status', 'name' => 'schedule_ride.status', 'title' => 'Status'])
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
