<?php

/**
 * Help DataTable
 *
 * @package     Makent
 * @subpackage  DataTable
 * @category    Help
 * @author      Trioangle Product Team
 * @version     1.5.9
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Help;
use Yajra\Datatables\Services\DataTable;
use DB;

class HelpDataTable extends DataTable
{
    // protected $printPreview  = 'path.to.print.preview.view';

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $help = $this->query();

        return $this->datatables
            ->of($help)
            ->addColumn('action', function ($help) {   
                return '<a href="'.url('admin/edit_help/'.$help->help_id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;<a data-href="'.url('admin/delete_help/'.$help->help_id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>';
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
        $help = Help::join('help_category', function($join) {
                                $join->on('help_category.id', '=', 'help.category_id');
                            })
                    ->leftJoin('help_subcategory', function($join) {
                                $join->on('help_subcategory.id', '=', 'help.subcategory_id');
                            })
                    ->select(['help_category.*', 'help_subcategory.*','help.id as help_id','help.category_id as category_id','help.subcategory_id as subcategory_id','question',DB::raw("concat(substring(answer, 1, 50),'...') as answer"),'help.status as help_status'])
                    ->where('help_category.status','Active');

        return $this->applyScopes($help);
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
                    ->addColumn(['data' => 'help_id', 'name' => 'help.id', 'title' => 'Id'])
                    ->addColumn(['data' => 'category_name', 'name' => 'help_category.name', 'title' => 'Category Name'])
                    ->addColumn(['data' => 'subcategory_name', 'name' => 'help_subcategory.name', 'title' => 'Subcategory Name'])
                    ->addColumn(['data' => 'question', 'name' => 'help.question', 'title' => 'Question'])
                    ->addColumn(['data' => 'answer', 'name' => 'help.answer', 'title' => 'Answer'])
                    ->addColumn(['data' => 'help_status', 'name' => 'help.status', 'title' => 'Status'])
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
            'subcategory_name',
            'question',
            'answer',
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
        return 'help';
    }
}
