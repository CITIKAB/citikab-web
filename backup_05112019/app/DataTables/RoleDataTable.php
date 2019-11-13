<?php

/**
 * Role DataTable
 *
 * @package     Makent
 * @subpackage  DataTable
 * @category    Role
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Role;
use Yajra\Datatables\Services\DataTable;

class RoleDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    // protected $exportColumns = ['id', 'name'];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $role = $this->query();

        return $this->datatables
            ->of($role)
            ->addColumn('action', function ($role) {
                return '<a href="'.url('admin/edit_role/'.$role->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;<a data-href="'.url('admin/delete_role/'.$role->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>';
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
        $role = Role::select();

        return $this->applyScopes($role);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns([
                'id',
                'name',
                'display_name',
                'description',
            ])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]);
    }
}
