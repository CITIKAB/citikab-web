<?php

/**
 * Currency DataTable
 *
 * @package     Makent
 * @subpackage  DataTable
 * @category    Currency
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Currency;
use Yajra\Datatables\Services\DataTable;
use Helpers;
class CurrencyDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
  /*  protected $exportColumns = ['id', 'name', 'code', 'original_symbol', 'rate', 'status'];*/

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $currency = $this->query();

        return $this->datatables
            ->of($currency)
            ->addColumn('action', function ($currency) {   
                return '<a href="'.url('admin/edit_currency/'.$currency->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;<a data-href="'.url('admin/delete_currency/'.$currency->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>';
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
        $currency = Currency::get();

        return $this->applyScopes($currency);
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
            'code'])
        ->addColumn(['data' => 'original_symbol', 'name' => 'symbol', 'title' => 'Symbol'])
        ->columns([
            'rate',
            'status'
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
                        'F' => '2',
                    );
        return Helpers::buildExcelFile($this->getFilename(), $this->getDataForExport(), $width);
    }
}
