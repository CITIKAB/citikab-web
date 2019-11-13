<?php

/**
 * Admin Users DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Admin Users
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Admin;
use Yajra\Datatables\Services\DataTable;

class AdminusersDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
    protected $exportColumns = [ 'id', 'username', 'email', 'status', 'created_at', 'updated_at' ];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $admin = $this->query();

        return $this->datatables
            ->of($admin)
            ->addColumn('action', function ($admin) {
                return '<a href="'.url('admin/edit_admin_users/'.$admin->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;';
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
        $admin = Admin::get();

        return $this->applyScopes($admin);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            /*->columns([
                'id',
                'username',
                'email',
                'role_name',
                'status',
            ])*/
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => 'Id'])
            ->addColumn(['data' => 'username', 'name' => 'username', 'title' => 'Username'])
            ->addColumn(['data' => 'email', 'name' => 'email', 'title' => 'Email'])
            ->addColumn(['data' => 'status', 'name' => 'status', 'title' => 'Status'])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
            ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            ]);
    }
}
