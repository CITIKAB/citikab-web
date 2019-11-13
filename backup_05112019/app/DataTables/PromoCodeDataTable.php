<?php

/**
 * Promo Code DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Promo Code
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\PromoCode;
use Yajra\Datatables\Services\DataTable;
use Helpers;
class PromoCodeDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';
    
    protected $exportColumns = ['id', 'code', 'amount', 'currency_code', 'expire_date','status'];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $promo_code = $this->query();

        return $this->datatables
            ->of($promo_code)
            ->addColumn('action', function ($promo_code) {   
                return '<a href="'.url('admin/edit_promo_code/'.$promo_code->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;<a data-href="'.url('admin/delete_promo_code/'.$promo_code->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>';
            })
            ->addColumn('expire_date', function ($promo_code) { 
                return $promo_code->expire_date_mdy;  
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
        $promo_code = PromoCode::get();

        return $this->applyScopes($promo_code);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->columns(['id', 'code', 'original_amount', 'currency_code', 'expire_date','status'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc']
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
