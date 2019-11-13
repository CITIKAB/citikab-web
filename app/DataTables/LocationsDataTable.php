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
use DB;

class LocationsDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
    // protected $exportColumns = [ 'id', 'username', 'email', 'status', 'created_at', 'updated_at' ];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $locations = $this->query();

        return $this->datatables
            ->of($locations)
            ->addColumn('action', function ($locations) {
                $edit = '<a href="'.url('admin/edit_location/'.$locations->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;';

                $delete = '<a data-href="'.url('admin/delete_location/'.$locations->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>&nbsp;';

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
        $locations = DB::table('locations')->select(['id','name','status','coordinates']);
        return $this->applyScopes($locations);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'id', 'title' => 'Id'])
        ->addColumn(['data' => 'name', 'name' => 'name', 'title' => 'Location Name'])
        ->addColumn(['data' => 'status', 'name' => 'status', 'title' => 'Status'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);
    }
}
