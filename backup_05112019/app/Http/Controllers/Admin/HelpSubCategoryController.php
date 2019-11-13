<?php

/**
 * Help Subcategory Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Help Subcategory
 * @author      Trioangle Product Team
 * @version     1.5.9
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\HelpSubCategoryDataTable;
use App\Models\HelpSubCategory;
use App\Models\Help;
use App\Models\HelpCategory;
use App\Models\HelpSubCategoryLang;
use App\Models\Language;
use App\Http\Start\Helpers;
use Validator;

class HelpSubCategoryController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Help Subcategory
     *
     * @param array $dataTable  Instance of HelpSubCategoryDataTable
     * @return datatable
     */
    public function index(HelpSubCategoryDataTable $dataTable)
    {
        return $dataTable->render('admin.help_subcategory.view');
    }

    /**
     * Add a New Help Subcategory
     *
     * @param array $request  Input values
     * @return redirect     to Help Subcategory view
     */
    public function add(Request $request)
    {
        if(!$_POST)
        {
            $data['category'] = HelpCategory::active_all();
            $data['languages'] = Language::where('status', '=', 'Active')->pluck('name', 'value');
            return view('admin.help_subcategory.add', $data);
        }
        else if($request->submit)
        {
            // Add Help Subcategory Validation Rules
            $rules = array(
                    'name'    => 'required|unique:help_subcategory',
                    'category_id'  => 'required',
                    'status'  => 'required'
                    );

            // Add Help Subcategory Validation Custom Names
            $niceNames = array(
                        'name'    => 'Name',
                        'category_id'  => 'Category',
                        'status'  => 'Status'
                        );

             foreach($request->translations ?: array() as $k => $translation)
            {
                $rules['translations.'.$k.'.locale'] = 'required';
                $rules['translations.'.$k.'.name'] = 'required';

                $niceNames['translations.'.$k.'.locale'] = 'Language';
                $niceNames['translations.'.$k.'.name'] = 'Name';
            }
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                $help_subcategory = new HelpSubCategory;

                $help_subcategory->name        = $request->name;
                $help_subcategory->category_id = $request->category_id;
                $help_subcategory->description = isset($request->description) ? $request->description :'';
                $help_subcategory->status      = $request->status;

                $help_subcategory->save();

                 foreach($request->translations ?: array() as $translation_data) {  
                    if($translation_data){
                        $help_category_lang = new HelpSubCategoryLang;
                        $help_category_lang->name        = $translation_data['name'];
                        $help_category_lang->description = isset($translation_data['description']) ? $translation_data['description'] : '';
                        $help_category_lang->locale      = $translation_data['locale'];
                        $help_category_lang->sub_category_id = $help_subcategory->id;
                        $help_category_lang->save();
                    }
                }               


                $this->helper->flash_message('success', 'Added Successfully'); // Call flash message function

                return redirect('admin/help_subcategory');
            }
        }
        else
        {
            return redirect('admin/help_subcategory');
        }
    }

    /**
     * Update Help Subcategory Details
     *
     * @param array $request    Input values
     * @return redirect     to Help Subcategory View
     */
    public function update(Request $request)
    {
        if(!$_POST)
        {
            $data['category'] = HelpCategory::active_all();
            $data['languages'] = Language::where('status', '=', 'Active')->pluck('name', 'value');
            $data['result'] = HelpSubCategory::find($request->id);

            return view('admin.help_subcategory.edit', $data);
        }
        else if($request->submit)
        {
            // Edit Help Subcategory Validation Rules
            $rules = array(
                    'name'    => 'required|unique:help_subcategory,name,'.$request->id,
                    'category_id'  => 'required',
                    'status'  => 'required'
                    );

            // Edit Help Subcategory Validation Custom Fields Name
            $niceNames = array(
                        'name'    => 'Name',
                        'category_id'  => 'Category',
                        'status'  => 'Status'
                        );

            foreach($request->translations ?: array() as $k => $translation)
            {
                $rules['translations.'.$k.'.locale'] = 'required';
                $rules['translations.'.$k.'.name'] = 'required';

                $niceNames['translations.'.$k.'.locale'] = 'Language';
                $niceNames['translations.'.$k.'.name'] = 'Name';
            }
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                $help_subcategory = HelpSubCategory::find($request->id);

                $help_subcategory->name        = $request->name;
                $help_subcategory->category_id = $request->category_id;
                $help_subcategory->description = isset($request->description) ? $request->description :'';
                $help_subcategory->status      = $request->status;

                $help_subcategory->save();

               
                 $data['locale'][0] = 'en';
                foreach($request->translations ?: array() as $translation_data) {  
                    if($translation_data){
                         $get_val = HelpSubCategoryLang::where('sub_category_id',$help_subcategory->id)->where('locale',$translation_data['locale'])->first();
                            if($get_val)
                                $help_category_lang = $get_val;
                            else
                                $help_category_lang = new HelpSubCategoryLang;
                        $help_category_lang->name        = $translation_data['name'];
                        $help_category_lang->description = isset($translation_data['description']) ? $translation_data['description'] : '';
                        $help_category_lang->locale      = $translation_data['locale'];
                        $help_category_lang->sub_category_id     = $help_subcategory->id;
                        $help_category_lang->save();
                        $data['locale'][] = $translation_data['locale'];
                    }
                }
                if(@$data['locale'])
                HelpSubCategoryLang::where('sub_category_id',$help_subcategory->id)->whereNotIn('locale',$data['locale'])->delete();


                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function

                return redirect('admin/help_subcategory');
            }
        }
        else
        {
            return redirect('admin/help_subcategory');
        }
    }

    /**
     * Delete Help Subcategory
     *
     * @param array $request    Input values
     * @return redirect     to Help Subcategory View
     */
    public function delete(Request $request)
    {
        $count = Help::where('subcategory_id', $request->id)->count();

        if($count > 0)
            $this->helper->flash_message('error', 'Help have this Help Subcategory. So, Delete that Help or Change that Help Help Subcategory.'); // Call flash message function
        else {
            HelpSubCategory::find($request->id)->delete();
            HelpSubCategoryLang::where('sub_category_id',$request->id)->delete();
            $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function
        }
        return redirect('admin/help_subcategory');
    }
}
