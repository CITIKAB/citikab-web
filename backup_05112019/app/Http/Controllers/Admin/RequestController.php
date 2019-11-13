<?php

/**
 * Request Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Request
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\RequestDataTable;
use App\Models\Request as RideRequest;
use App\Models\Currency;
use App\Http\Start\Helpers;
use Validator;
use DB;
use Auth;

class RequestController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Request
     *
     * @param array $dataTable  Instance of RequestDataTable
     * @return datatable
     */
    public function index(RequestDataTable $dataTable)
    {
        return $dataTable->render('admin.request.view');
    }

    public function detail_request(Request $request)
    {
        $request_id = $request->id;

        $data['request_details'] = RideRequest::with([
            'users' => function($query)  {},
            'driver' => function($query){},
            'trips' => function($query){},            
        ])
        ->where(function($query)  {
            //For company user login, only get that company's driver requests
            if(LOGIN_USER_TYPE=='company') {
                $query->whereHas('driver',function($q1){
                    $q1->where('company_id',Auth::guard('company')->user()->id);
                });
            }
        })
        ->where('id',$request_id)
        ->first();
        if($data['request_details'])
        {
           $request_status=RideRequest::where('group_id',$data['request_details']->group_id)->where('status','Accepted');
           if($dt = $request_status->first())
           {
              $data['driver_name']  = $dt->driver->first_name.' '.$dt->driver->last_name;
              $data['company_name'] = $dt->driver->company->name;
           }

            $pending_request_status=DB::table('request')->where('group_id',$data['request_details']->group_id)->where('status','Pending');
            if($request_status->count())
            {
                $req_id=$request_status->get()->first()->id;
                $trip_status=@DB::table('trips')->where('request_id',$req_id)->get()->first()->status;

                $data['is_tripped']=true;
                $data['request_status']=@$trip_status;
            }
            elseif($pending_request_status->count())
            {
                $data['is_tripped']=false;
                $data['request_status']="Searching";
            }
            else
            {
                $data['is_tripped']=false;
                $data['request_status']="No one accepted";
            }

            //For company user login, get session currency
            if (LOGIN_USER_TYPE=='company' && session()->get('currency') != null) {
                $data['default_currency'] = Currency::whereCode(session()->get('currency'))->first();
            }
            return view('admin.request.details', $data);
        }
        else
        {
            $this->helper->flash_message('danger', 'Invalid ID'); // Call flash message function
            return redirect(LOGIN_USER_TYPE.'/request');  //redirect depends on login user is admin or company
        }
    }

}
