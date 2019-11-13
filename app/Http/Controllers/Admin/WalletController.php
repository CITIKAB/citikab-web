<?php

/**
 * Wallet Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Wallet
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use Excel;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Currency;
use Validator;
use DB;
use App\DataTables\WalletDataTable;

class WalletController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Wallet
     *
     * @return view file
     */
     public function index(WalletDataTable $dataTable)
    {        
        return $dataTable->render('admin.wallet.index');
    }

    

    /**
     * Add a New wallet 
     *
     * @param array $request  Input values
     * @return redirect     to wallet view
     */
    public function add(Request $request)
    {    
        $data['users_list']    = User::leftJoin('wallet', 'users.id', '=', 'wallet.user_id')
        ->whereNull('wallet.user_id')->where('user_type','Rider')->whereStatus('Active')->pluck('first_name','id');

        if(!$_POST)
        {
            $data['currency']   = Currency::codeSelect();
            return view('admin.wallet.add',$data);
        }
        else if($request->submit)
        {

            $rules = array(
                    'user_id'       => 'required|unique:wallet,user_id',
                    'amount'        => 'required|numeric|digits_between:1,4',
                    'currency_code' => 'required',
                    );
            
            $niceNames = array(
                        'user_id'       => 'User Id',
                        'amount'        => 'Amount',
                        'currency_code' => 'Currency code',
                        );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {   

                $wallet = new Wallet;

                $wallet->user_id        = $request->user_id;
                $wallet->amount         = $request->amount;
                $wallet->currency_code  = $request->currency_code;

                $wallet->save();

                $this->helper->flash_message('success', 'Added Successfully'); // Call flash message function

                return redirect('admin/wallet');
            }
        }
        else
        {
            return redirect('admin/wallet');
        }
    }

    /**
     * Update Wallet Details
     *
     * @param array $request    Input values
     * @return redirect     to wallet View
     */
    public function update(Request $request)
    {   
        $data['result'] = Wallet::where('user_id',$request->id)->first();

        $data['users_list']    = User::where('user_type','Rider')->whereStatus('Active')->pluck('first_name','id');


        if(!$_POST)
        {
            $data['currency']   = Currency::codeSelect();
            return view('admin.wallet.edit', $data);
        }
        else if($request->submit)
        {            
            if($request->prev_user_id==$request->user_id)
            {
                $rules = array(
                    'amount'        => 'required|numeric|digits_between:1,4',
                    'currency_code' => 'required',
                );                
            }
            else
            {
                $rules = array(
                    'amount'        => 'required|numeric',
                    'currency_code' => 'required',
                );
            }

            $niceNames = array(
                        'amount'        => 'Amount',
                        'currency_code' => 'Currency code',
                        );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {   
                $wallet = array(
                    'user_id'        => $request->prev_user_id,
                    'amount'         => $request->amount,
                    'currency_code'  => $request->currency_code
                    );

                Wallet::where('user_id',$request->prev_user_id)->update($wallet);

                $this->helper->flash_message('success', 'Update Successfully'); // Call flash message function
                return redirect('admin/wallet');

            }
        }
        else
        {
            return redirect('admin/wallet');
        }
    }
    public function delete(Request $request)
    {
        $check_wallet=Wallet::where('user_id',$request->id)->first();
        if($check_wallet)
        {
            Wallet::where('user_id',$request->id)->delete();
            $this->helper->flash_message('success', 'Wallet Deleted Successfully'); // Call flash message function
            return redirect('admin/wallet');
        }
        else
        {
            $this->helper->flash_message('error', 'Invalid Wallet ID'); // Call flash message function
            return redirect('admin/wallet');    
        }
        
    }
}
