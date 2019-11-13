<?php

/**
 * Users DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Users
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\User;
use Yajra\Datatables\Services\DataTable;
use Auth;
use DB;
use App\Http\Start\Helpers;

class VehicleDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';

    // protected $exportColumns = [ 'id', 'first_name', 'last_name', 'email','country_code' , 'mobile_number' , 'created_at' ];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $vehicles = $this->query();

        return $this->datatables
            ->of($vehicles)
            ->addColumn('action', function ($vehicle) {
                //URL depends on login user is admin or company
                $edit = '<a href="'.url(LOGIN_USER_TYPE.'/edit_vehicle/'.$vehicle->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' ;
                $delete = '<a href="'.url(LOGIN_USER_TYPE.'/delete_vehicle/'.$vehicle->id).'" class="btn btn-xs btn-primary" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>&nbsp;';

                return $edit.$delete;
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
        $vehicles = DB::Table('vehicle')
            ->select('vehicle.id as id','vehicle.status as status','vehicle.vehicle_name as vehicle_name','vehicle.vehicle_number as vehicle_number','vehicle.vehicle_type as vehicle_type', 'users.first_name as driver_name','companies.name as company_name')
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'vehicle.user_id');
            })
            ->leftJoin('companies', function ($join) {
                $join->on('companies.id', '=', 'vehicle.company_id');
            });

        if (LOGIN_USER_TYPE=='company') {  //If login user is company then get that company vehicles only
            $vehicles = $vehicles->where('vehicle.company_id',Auth::guard('company')->user()->id);
        }

        return $this->applyScopes($vehicles);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'vehicle.id', 'title' => 'Id'])
        ->addColumn(['data' => 'company_name', 'name' => 'companies.name', 'title' => 'Company Name'])
        ->addColumn(['data' => 'driver_name', 'name' => 'users.first_name', 'title' => 'Driver Name'])
        ->addColumn(['data' => 'vehicle_type', 'name' => 'vehicle.vehicle_type', 'title' => 'Vehicle Type'])
        ->addColumn(['data' => 'vehicle_name', 'name' => 'vehicle.vehicle_name', 'title' => 'Vehicle Name'])
        ->addColumn(['data' => 'vehicle_number', 'name' => 'vehicle.vehicle_number', 'title' => 'Vehicle Number'])
        ->addColumn(['data' => 'status', 'name' => 'vehicle.status', 'title' => 'Status'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false, 'exportable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            // 'dom' => 'Bfrtip',
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
                    );
        return Helpers::buildExcelFile($this->getFilename(), $this->getDataForExport(), $width);
    }
}
