<?php

/**
 * Locations DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Locations
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use Yajra\Datatables\Services\DataTable;
use App\Models\ManageFare;
use DB;

class ManageFareDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
    // protected $exportColumns = [];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $fare_details = $this->query();

        return $this->datatables
            ->of($fare_details)
            ->addColumn('action', function ($fare_details) {
                $edit = '<a href="'.url('admin/edit_manage_fare/'.$fare_details->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;';

                $delete = '<a data-href="'.url('admin/delete_manage_fare/'.$fare_details->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>&nbsp;';

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
        $fare_details = DB::Table('manage_fare')->join('locations', function($join) {
                            $join->on('manage_fare.location_id', '=', 'locations.id');
                        })
                        ->join('car_type', function($join) {
                            $join->on('manage_fare.vehicle_id', '=', 'car_type.id');
                        })
                        ->select('*','manage_fare.id as id','locations.name AS location_name','car_type.car_name AS vehicle_name');
        return $this->applyScopes($fare_details);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'manage_fare.id', 'title' => 'Id'])
        ->addColumn(['data' => 'location_name', 'name' => 'locations.name', 'title' => 'Location Name'])
        ->addColumn(['data' => 'vehicle_name', 'name' => 'car_type.car_name', 'title' => 'Vehicle Name'])
        ->addColumn(['data' => 'apply_peak', 'name' => 'apply_peak', 'title' => 'Apply Peak'])
        ->addColumn(['data' => 'apply_night', 'name' => 'apply_night', 'title' => 'Apply Night'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);
    }
}
