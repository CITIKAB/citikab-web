<?php

/**
 * Static Pages DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Static Pages
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Pages;
use Yajra\Datatables\Services\DataTable;

class PagesDataTable extends DataTable
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
        $pages = $this->query();

        return $this->datatables
            ->of($pages)
            ->addColumn('action', function ($page) {   
                return '<a href="'.url('admin/edit_page/'.$page->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;<a data-href="'.url('admin/delete_page/'.$page->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>&nbsp;<a href="'.url($page->url).'" target="_blank" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye-open"></i></a>';
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
        $pages = Pages::select();

        return $this->applyScopes($pages);
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
                'url',
                'status',
                'updated_at',
            ])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
            ->parameters([
                'buttons' => ['csv', 'excel', 'print'],
            ]);
    }
}
