<?php

/**
 * Request DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Request
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Request as RideRequest;
use Yajra\Datatables\Services\DataTable;
use DB;
use DateTime;
use Auth;

class RequestDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
    protected $exportColumns = [''];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $get_request = $this->query();
        
        return $this->datatables
            ->of($get_request)
            ->addColumn('date_time',function($get_request) {
                $now = new DateTime;
                $ago = new DateTime($get_request->updated_at);
                $diff = $now->diff($ago);

                $diff->w = floor($diff->d / 7);
                $diff->d -= $diff->w * 7;

                $string = array('y' => 'year','m' => 'month','w' => 'week','d' => 'day','h' => 'hour','i' => 'minute','s' => 'second');
                foreach ($string as $k => &$v) {
                    if ($diff->$k) {
                        $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                    } else {
                        unset($string[$k]);
                    }
                }
                $string = array_slice($string, 0, 1);

                return $string ? implode(', ', $string) . ' ago' : 'just now';
            })
            ->addColumn('request_status', function ($get_request) {
                $request_status=DB::table('request')->where('group_id',$get_request->group_id)->where('status','Accepted');
                $pending_request_status=DB::table('request')->where('group_id',$get_request->group_id)->where('status','Pending');
                if($request_status->count())
                {
                    $req_id=$request_status->get()->first()->id;
                    $trip_status=@DB::table('trips')->where('request_id',$req_id)->get()->first()->status;
                    return $trip_status;
                }
                elseif($pending_request_status->count())
                {
                    return "Searching";
                }
                else
                {
                    return "No one accepted";
                }
            })
            ->addColumn('payment_status', function ($get_request) {
                return ($get_request->payment_status != null ) ? $get_request->payment_status : "Not Paid";
            })
            ->addColumn('total_amount', function ($get_request) {
                return ($get_request->total_fare!= null ) ? $get_request->currency_symbol." ".$get_request->total_fare : "N/A";
            })
            ->addColumn('action', function ($get_request) {
                return '<a href="'.url(LOGIN_USER_TYPE.'/detail_request/'.$get_request->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye-open"></i></a>&nbsp;';
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
        $get_request = DB::Table('request')
                        ->where(function($query)  {
                            if(LOGIN_USER_TYPE=='company') {  //If login user is company then get that company requests only
                                $query->join('users as drivers', function($join) {
                                    $join->on('drivers.id', '=', 'request.driver_id')
                                        ->where('drivers.company_id',Auth::guard('company')->user()->id);
                                });
                            }
                        })
                        ->Leftjoin('trips', function($join) {
                                $join->on('trips.request_id', '=', 'request.id');
                        })
                        ->Leftjoin('currency', function($join) {
                                    $join->on('currency.code', '=', 'trips.currency_code');
                        })
                        ->join('users', function($join) {
                                $join->on('users.id', '=', 'request.user_id');
                        })
                        ->join('car_type', function($join) {
                                $join->on('car_type.id', '=', 'request.car_id');
                        })                        
                        ->groupBy('group_id')
                        ->select(['request.id as id', 'users.first_name',DB::raw('CONCAT(currency.symbol, trips.total_fare) AS total_amount'),'request.group_id','request.payment_mode','trips.payment_status','request.updated_at','trips.total_fare','currency.symbol AS currency_symbol']);

        if(LOGIN_USER_TYPE=='company') {  //If login user is company then get that company drivers only
            $get_request = $get_request
                ->join('users as drivers', function($join) {
                    $join->on('drivers.id', '=', 'request.driver_id')
                        ->where('drivers.company_id',Auth::guard('company')->user()->id);
                });
        }

        // $get_request = RideRequest::with(['trips','users','car_type'])->groupBy('group_id');

        return $this->applyScopes($get_request);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        if(LOGIN_USER_TYPE == 'company') {
            $payout_columns = array();                
        }else {
            $payout_columns = array(
                ['data' => 'total_amount', 'name' => 'trips.total_fare', 'title' => 'Amount']
            );
        }
        
        return $this->builder()
     
         ->addColumn(['data' => 'id', 'name' => 'request.id', 'title' => 'Request id'])
         ->addColumn(['data' => 'first_name', 'name' => 'users.first_name', 'title' => 'Rider Name'])
         ->addColumn(['data' => 'date_time', 'name' => 'date_time', 'title' => 'Date and Time ' ,'orderable' => false, 'searchable' => false])
         ->addColumn(['data' => 'request_status', 'name' => 'request_status', 'title' => 'Status','orderable' => false, 'searchable' => false])
         ->columns($payout_columns)
         ->addColumn(['data' => 'payment_mode', 'name' => 'request.payment_mode', 'title' => 'Payment mode'])
         ->addColumn(['data' => 'payment_status', 'name' => 'payment_status', 'title' => 'Payment Status'])
         ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => [],
            'order' => [0, 'desc'],
        ]);
    }
}
