<?php

/**
 * Metas DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Metas
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Metas;
use Yajra\Datatables\Services\DataTable;
use Helpers;
class MetasDataTable extends DataTable
{
    // protected $printPreview  = 'path.to.print.preview.view';

    protected $exportColumns = [ 'id', 'url', 'title', 'description', 'keywords' ];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $meta = $this->query();

        return $this->datatables
            ->of($meta)
            ->addColumn('action', function ($meta) {   
                return '<a href="'.url('admin/edit_meta/'.$meta->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>';
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
        $metas = Metas::query();

        return $this->applyScopes($metas);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->columns([
            'id',
            'url',
            'title',
            'description',
            'keywords'
        ])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
        ]);
    }

              //column alignment 
      protected function buildExcelFile()
    {

        $width = array(
                        'A' => '1',
                        'B' => '2',
                        'C' => '2',
                        'D' => '2',
                        'E' => '2',
                    );
        return Helpers::buildExcelFile($this->getFilename(), $this->getDataForExport(), $width);
    }
}
