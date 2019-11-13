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

use App\Models\User;
use Yajra\Datatables\Services\DataTable;
use Auth;
use DB;
use App\Http\Start\Helpers;

class DriverDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';

    // protected $exportColumns = [ 'id', 'first_name', 'last_name', 'email','country_code' , 'mobile_number' , 'created_at' ];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $users = $this->query();

        return $this->datatables
            ->of($users)
            ->editColumn('company_name', function($users) {
                return $users->company_name;
            })
            ->addColumn('action', function ($users) {
                $edit = (LOGIN_USER_TYPE=='company' || auth('admin')->user()->can('edit_driver')) ? '<a href="'.url(LOGIN_USER_TYPE.'/edit_driver/'.$users->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                $delete = (Auth::guard('company')->user()!=null || auth('admin')->user()->can('delete_driver')) ? '<a href="'.url(LOGIN_USER_TYPE.'/delete_driver/'.$users->id).'" class="btn btn-xs btn-primary" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>&nbsp;':'';
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
        $users = DB::Table('users')->select('users.id as id', 'users.first_name', 'users.last_name','users.email','users.country_code','users.mobile_number', 'users.status','companies.name as company_name','users.created_at',DB::raw('CONCAT("+",users.country_code," ",users.mobile_number) AS mobile'))
            ->leftJoin('companies', function($join) {
                $join->on('users.company_id', '=', 'companies.id');
            })->where('user_type','Driver')->groupBy('id');

        // $users = DB::Table('users')->select('users.id as id', 'users.first_name', 'users.last_name','users.email','users.country_code','users.mobile_number', 'users.status','companies.name as company_name','users.created_at',DB::raw('CONCAT("XXXXXX",Right(users.mobile_number,4)) AS mobile'))
        //     ->leftJoin('companies', function($join) {
        //         $join->on('users.company_id', '=', 'companies.id');
        //     })->where('user_type','Driver')->groupBy('id');

        //If login user is company then get that company drivers only
        if (LOGIN_USER_TYPE=='company') {
            $users = $users->where('company_id',Auth::guard('company')->user()->id);
        }

        return $this->applyScopes($users);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        /*\DB::listen(function($sql) {
            \Log::info($sql->sql);
        });*/
        
        $re =  $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'users.id', 'title' => 'Id'])
        ->addColumn(['data' => 'first_name', 'name' => 'users.first_name', 'title' => 'First Name'])
        ->addColumn(['data' => 'last_name', 'name' => 'users.last_name', 'title' => 'Last Name']);
        if (LOGIN_USER_TYPE!='company') {
            $re->addColumn(['data' => 'company_name', 'name' => 'companies.name', 'title' => 'Company Name']);
        }
        $re->addColumn(['data' => 'email', 'name' => 'users.email', 'title' => 'Email'])
        ->addColumn(['data' => 'status', 'name' => 'users.status', 'title' => 'Status'])
        ->addColumn(['data' => 'mobile', 'name' => 'users.mobile_number', 'title' => 'Mobile Number'])
        ->addColumn(['data' => 'created_at', 'name' => 'users.created_at', 'title' => 'Created At'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false, 'exportable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            // 'dom' => 'Bfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);

        return $re;
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
                        'G' => '2',
                        'H' => '3',
                        'I' => '3',
                    );
        return Helpers::buildExcelFile($this->getFilename(), $this->getDataForExport(), $width);
    }
}
