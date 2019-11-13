<?php

/**
 * Users DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Users
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Company;
use Yajra\Datatables\Services\DataTable;
use Auth;
use DB;
use App\Http\Start\Helpers;

class CompanyDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';

    // protected $exportColumns = [ 'id', 'name', 'email','country_code' , 'mobile_number'];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $companies = $this->query();

        return $this->datatables
            ->of($companies)
            ->addColumn('drivers', function ($companies) {
                return count($companies->drivers);
            })
            ->addColumn('action', function ($companies) {

                $edit = (auth('admin')->user()->can('edit_company')) ? '<a href="'.url('admin/edit_company/'.$companies->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                /*$delete = (auth('admin')->user()->can('delete_company')) ? '<a href="'.url('admin/delete_company/'.$companies->id).'" class="btn btn-xs btn-primary" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>&nbsp;':'';*/
                $delete = (auth('admin')->user()->can('delete_company')) ? '<a data-href="'.url('admin/delete_company/'.$companies->id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>':'';

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
        /* only for Package */
        $companies = Company::select('companies.id', 'companies.name','companies.email','companies.country_code','companies.mobile_number', 'companies.status',DB::raw('CONCAT("+",companies.country_code," ",companies.mobile_number) AS mobile'))->with('drivers');

        // $companies = Company::select('companies.id', 'companies.name','companies.email','companies.country_code','companies.mobile_number', 'companies.status',DB::raw('CONCAT("XXXXXX",Right(mobile_number,4)) AS mobile'))->with('drivers');

        return $this->applyScopes($companies);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'companies.id', 'title' => 'Id'])
        ->addColumn(['data' => 'name', 'name' => 'companies.name', 'title' => 'Name'])
        ->addColumn(['data' => 'drivers', 'name' => 'companies.id', 'title' => 'Drivers'])
        ->addColumn(['data' => 'email', 'name' => 'companies.email', 'title' => 'Email'])
        ->addColumn(['data' => 'mobile', 'name' => 'companies.mobile_number', 'title' => 'Mobile Number'])
        ->addColumn(['data' => 'status', 'name' => 'companies.status', 'title' => 'Status'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false, 'exportable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            // 'dom' => 'Bfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);
    }

    /**
     * Build excel file and prepare for export.
     *
     * @return \Maatwebsite\Excel\Writers\LaravelExcelWriter
     */
    protected function buildExcelFile()
    {

        $width = array(
                        'A' => '1',
                        'B' => '2',
                        'C' => '2',
                        'D' => '2',
                        'E' => '2',
                        'F' => '1',
                    );
        return Helpers::buildExcelFile($this->getFilename(), $this->getDataForExport(), $width);
    }
}
