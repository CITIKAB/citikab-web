<?php

/**
 * Payout Type DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Payouts 
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Trips;
use Yajra\Datatables\Services\DataTable;
use App\Http\Start\Helpers;
use DB;

class PayoutReportsDataTable extends DataTable
{

	protected $from,$to,$date;

	protected $filter_type;

    // Set the Type of Filter applied to Payout
    public function setFilter($filter_type)
    {
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
        $trips = $this->query();

        return $this->datatables
            ->of($trips)
            ->addColumn('day', function ($trips) {
                return date('l', strtotime($trips->created_at));
            })
            ->addColumn('driver_payout', function ($trips) {
                $payment_pending_trips = Trips::DriverPayoutTripsOnly()->where('driver_id',$trips->driver_id)->whereDate('created_at', date('Y-m-d',strtotime($trips->created_at)));
                if($this->filter_type == 'day_report') {
                	$payment_pending_trips = $payment_pending_trips->where('created_at', $trips->created_at);
                }
                $total_payout = $payment_pending_trips->get()->sum('driver_payout');
                return @$trips->currency->symbol.$total_payout;
            })
            ->addColumn('action', function ($trips) {
            	if($this->filter_type == 'day_report') {
            		$action_url = url(LOGIN_USER_TYPE.'/view_trips/'.$trips->id).'?source=reports';
            		$payment_action = '<form action="'.url(LOGIN_USER_TYPE.'/make_payout').'" method="post" name="payout_form" style="display:inline-block">
		                <input type="hidden" name="type" value="driver_trip">
		                <input type="hidden" name="_token" value="'.csrf_token().'">
		                <input type="hidden" name="trip_id" value="'.$trips->id.'">
		                <input type="hidden" name="driver_id" value="'.$trips->driver_id.'">
		                <input type="hidden" name="redirect_url" value="'.LOGIN_USER_TYPE.'/per_day_report/'.$trips->driver_id.'/'.request()->date.'">
		                <button type="submit" class="btn btn-primary make-pay-btn" name="submit" value="submit"> Make Payout </button>
		                
		                </form>';
            	}
            	else {
                	$action_url = url(LOGIN_USER_TYPE.'/per_day_report/'.$trips->driver_id).'/'.date('Y-m-d',strtotime($trips->created_at));
                	$payment_action = '<form action="'.url(LOGIN_USER_TYPE.'/make_payout').'" method="post" name="payout_form" style="display:inline-block">
		                <input type="hidden" name="type" value="driver_day">
		                <input type="hidden" name="_token" value="'.csrf_token().'">
		                <input type="hidden" name="driver_id" value="'.$trips->driver_id.'">
		                <input type="hidden" name="day" value="'.date('Y-m-d',strtotime($trips->created_at)).'">
		                <input type="hidden" name="redirect_url" value="'.LOGIN_USER_TYPE.'/per_week_report/'.$trips->driver_id.'/'.request()->start_date.'/'.request()->end_date.'">
		                <button type="submit" class="btn btn-primary make-pay-btn" name="submit" value="submit"> Make Payout </button>
		                
		                </form>';
                }
                return '<div>'.'<a href="'.$action_url.'" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a> '.$payment_action.'<div>';
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
        $this->from = date('Y-m-d' . ' 00:00:00', strtotime(request()->start_date));
		$this->to = date('Y-m-d' . ' 23:59:59', strtotime(request()->end_date));
		$this->date = date('Y-m-d', strtotime(request()->date));

		$driver_id = request()->driver_id;

		$trips = Trips::with(['currency','driver'])->DriverPayoutTripsOnly()->Where('driver_id',$driver_id);

		if($this->filter_type == 'day_report') {
			$trips->whereDate('created_at', $this->date);
		}
		else {
			$trips->whereBetween('created_at', [$this->from, $this->to])->groupBy(DB::raw('DATE(created_at)'));
		}

		$trips = $trips->get();

		return $this->applyScopes($trips);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
    	if($this->filter_type == 'day_report') {
    		$report_columns = array(
    			['data' => 'id', 'name' => 'id', 'title' => 'Trip Id'],
    			['data' => 'total_fare', 'name' => 'total_fare', 'title' => 'Total Fare'],
    			['data' => 'driver_payout', 'name' => 'driver_payout', 'title' => 'Payout Amount'],
    			['data' => 'payment_status', 'name' => 'payment_status', 'title' => 'Payment Status']
    		);
    	}
    	else {
    		$report_columns = array(
    			['data' => 'day', 'name' => 'created_at', 'title' => 'Day'],
    			['data' => 'driver_payout', 'name' => 'driver_payout', 'title' => 'Payout Amount'],
    		);
    	}

        return $this->builder()
        ->columns($report_columns)
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);
    }

    /**
     * Build excel file and prepare for export.
     *
     * @return \Maatwebsite\Excel\Writers\LaravelExcelWriter
     */
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
