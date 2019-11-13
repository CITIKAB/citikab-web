<?php

/**
 * Datatable Base Controller - For custom search 
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Datatable Base
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */


namespace App\DataTables;
namespace App\Http\Controllers\Admin;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Query\Builder;
use Yajra\Datatables\Datatables;

use Yajra\Datatables\Engines\BaseEngine;
use Yajra\Datatables\Services\DataTable;
use App\Http\Start\Helpers;
class DataTableBase extends DataTable {

    /*
     * The reason why this class exists is that Yajra/Datatables requires this service class for export methods to work
     * properly.
     */

    /** @var Builder The query that will be used to get the data from the db. */
    private $mQuery;

    /** @var array An array of columns */
    private $mColumns;

    /** @var BaseEngine The DataTable */
    private $mDataTable;

    /**
     * @param            $query
     * @param BaseEngine $dataTable
     * @param array      $columns
     */
    public function __construct($query, BaseEngine $dataTable, $columns) {
        parent::__construct(app(Datatables::class), app(Factory::class));

        $this->mQuery = $query;
        $this->mColumns = $columns;
        $this->mDataTable = $dataTable;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax() {
        return $this->mDataTable->make(true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query() {
        return $this->mQuery;
    }

    public function html() {
        return $this->builder()->columns($this->mColumns);
    }

}