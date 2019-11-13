<?php

/**
 * Wallet DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Wallet
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\User;
use Yajra\Datatables\Services\DataTable;
use Auth;
use DB;

class WalletDataTable extends DataTable
{
    // protected $printPreview = 'path-to-print-preview-view';

    protected $exportColumns = [ 'id', 'first_name', 'last_name', 'amount', 'currency_code'];

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $wallet = $this->query();
        return $this->datatables
            ->of($wallet)
            ->addColumn('amount', function ($wallet) { 
                return ($wallet->amount)?$wallet->amount:"0";
            }) 
            ->addColumn('action', function ($wallet) {   
                return '<a href="'.url('admin/edit_wallet/'.$wallet->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>
                <a href="'.url('admin/delete_wallet/'.$wallet->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-trash"></i></a>';
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
        $wallet = User::join('wallet', function($join) {
                                $join->on('users.id', '=', 'wallet.user_id');
                            })->select('users.id as id', 'users.first_name', 'users.last_name','users.email','wallet.currency_code as currency_code','wallet.amount as amount')->groupBy('id');       
        return $this->applyScopes($wallet);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'users.id', 'title' => 'User Id'])
        ->addColumn(['data' => 'first_name', 'name' => 'users.first_name', 'title' => 'First Name'])
        ->addColumn(['data' => 'last_name', 'name' => 'users.last_name', 'title' => 'Last Name'])
        ->addColumn(['data' => 'amount', 'name' => 'amount', 'title' => 'Wallet Amount'])
        ->addColumn(['data' => 'currency_code', 'name' => 'wallet.currency_code', 'title' => 'Currency Code','orderable' => false])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters([
            'dom' => 'lBfrtip',
            // 'dom' => 'Bfrtip',
            'buttons' => ['csv', 'excel', 'print', 'reset'],
            'order' => [0, 'desc'],
        ]);
    }
}
