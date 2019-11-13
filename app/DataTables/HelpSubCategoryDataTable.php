<?php

/**
 * Help Subcategory DataTable
 *
 * @package     Makent
 * @subpackage  DataTable
 * @category    Help Subcategory
 * @author      Trioangle Product Team
 * @version     1.5.9
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\HelpSubCategory;
use Yajra\Datatables\Services\DataTable;

class HelpSubCategoryDataTable extends DataTable
{
    // protected $printPreview  = 'path.to.print.preview.view';

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $help_subcategory = $this->query();

        return $this->datatables
            ->of($help_subcategory)
            ->addColumn('action', function ($help_subcategory) {   
                return '<a href="'.url('admin/edit_help_subcategory/'.$help_subcategory->subcategory_id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;<a data-href="'.url('admin/delete_help_subcategory/'.$help_subcategory->subcategory_id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>';
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
        $help_subcategory = HelpSubCategory::join('help_category', function($join) {
                                $join->on('help_category.id', '=', 'help_subcategory.category_id');
                            })
                            ->select(['help_subcategory.id as subcategory_id', 'help_subcategory.name as subcategory_name', 'help_subcategory.description as subcategory_description', 'help_subcategory.status as subcategory_status', 'help_subcategory.category_id', 'help_category.*']);

        return $this->applyScopes($help_subcategory);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    // ->columns($this->getColumns())
                    ->addColumn(['data' => 'subcategory_id', 'name' => 'help_subcategory.id', 'title' => 'Id'])
                    ->addColumn(['data' => 'category_name', 'name' => 'help_category.name', 'title' => 'Category Name'])
                    ->addColumn(['data' => 'subcategory_name', 'name' => 'help_subcategory.name', 'title' => 'Name'])
                    ->addColumn(['data' => 'subcategory_description', 'name' => 'help_subcategory.description', 'title' => 'Description'])
                    ->addColumn(['data' => 'subcategory_status', 'name' => 'help_subcategory.status', 'title' => 'Status'])
                    ->ajax('')
                    ->addAction(['width' => '80px'])
                    ->parameters($this->getBuilderParameters());
    }

    /**
     * Get columns.
     *
     * @return array
     */
    private function getColumns()
    {
        return [
            'id',
            'category_name',
            'name',
            'description',
            'status',
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'help_subcategory';
    }
}
