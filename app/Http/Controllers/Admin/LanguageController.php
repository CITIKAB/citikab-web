<?php

/**
 * Language Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Language
 * @author      Trioangle Product Team
 * @version     1.5.9
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\LanguageDataTable;
use App\Models\Language;
use App\Models\SiteSettings;
use App\Http\Start\Helpers;
use Validator;

class LanguageController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Language
     *
     * @param array $dataTable  Instance of LanguageDataTable
     * @return datatable
     */
    public function index(LanguageDataTable $dataTable)
    {
        return $dataTable->render('admin.language.view');

    }

    /**
     * Add a New Language
     *
     * @param array $request  Input values
     * @return redirect     to Language view
     */
    public function add(Request $request)
    {
        if(!$_POST)
        {
            return view('admin.language.add');
        }
        else if($request->submit)
        {
            // Add Language Validation Rules
            $rules = array(
                    'name'   => 'required|unique:language',
                    'value'  => 'required|unique:language',
                    'is_translatable'  => 'required',
                    'status' => 'required'
                    );

            // Add Language Validation Custom Names
            $niceNames = array(
                        'name'    => 'Name',
                        'value'   => 'Value',
                        'is_translatable'  => 'Is Translatable',
                        'status'  => 'Status'
                        );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                $language = new Language;

                $language->name   = $request->name;
                $language->value  = $request->value;
                $language->is_translatable  = $request->is_translatable;
                $language->status = $request->status;
                $language->default_language = '0';

                $language->save();

                $this->helper->flash_message('success', 'Added Successfully'); // Call flash message function

                return redirect('admin/language');
            }
        }
        else
        {
            return redirect('admin/language');
        }
    }

    /**
     * Update Language Details
     *
     * @param array $request    Input values
     * @return redirect     to Language View
     */
    public function update(Request $request)
    {
        if(!$_POST)
        {
			$data['result'] = Language::find($request->id);

            return view('admin.language.edit', $data);
        }
        else if($request->submit)
        {
            // Edit Language Validation Rules
            $rules = array(
                    'name'   => 'required|unique:language,name,'.$request->id,
                    'value'  => 'required|unique:language,value,'.$request->id,
                    'is_translatable'  => 'required',
                    'status' => 'required'
                    );

            // Edit Language Validation Custom Fields Name
            $niceNames = array(
                        'name'    => 'Name',
                        'value'   => 'Value',
                        'is_translatable'  => 'Is Translatable',
                        'status'  => 'Status'
                        );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                $language = Language::find($request->id);
                if($language->value == 'en'){
                    $this->helper->flash_message('error','Cannot Edit English Language');
                    return back();
                }
                if($request->status == 'Inactive' || $request->value != $language->value || $request->is_translatable == '0')
                {
                    $result= $this->canDestroy($language);
                    if($result['status'] == 0)
                    {
                        $this->helper->flash_message('error',$result['message']);
                        return back();
                    }
                }

			    $language->name   = $request->name;
                $language->value  = $request->value;
                $language->is_translatable  = $request->is_translatable;
                $language->status = $request->status;

                $language->save();

                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function
                
                return redirect('admin/language');
            }
        }
        else
        {
            return redirect('admin/language');
        }
    }

    /**
     * Delete Language
     *
     * @param array $request    Input values
     * @return redirect     to Language View
     */
    public function delete(Request $request)
    {
        // $already_used_count = $this->get_already_used_count($request->id);
        $language = Language::where('id', $request->id)->first();
        if($language->value == 'en'){
            $this->helper->flash_message('error','Cannot delete English Language');
            return back();
        }
        $result = $this->canDestroy($language);
        if($result['status'] == 0)
        {
            $this->helper->flash_message('error',$result['message']);
            return back();
        }
        $language->delete();

        $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function

        return redirect('admin/language');
    }

    public function canDestroy($language)
    {
        $active_language_count = Language::where('status', 'Active')->count();
        $is_default_language  = $language->default_language == 1;

        $return  = ['status' => '1', 'message' => ''];
        if($active_language_count < 1)
        {
            $return = ['status' => 0, 'message' => 'Sorry, Minimum one Active language is required.'];
        }
        else if($is_default_language)
        {
            $return = ['status' => 0, 'message' => 'Sorry, This language is Default Language. So, change the Default Language.'];
        }
        return $return;
    }

}
