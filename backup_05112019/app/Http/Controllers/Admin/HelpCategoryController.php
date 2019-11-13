<?php

/**
 * Help Category Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Help Category
 * @author      Trioangle Product Team
 * @version     1.5.9
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\HelpCategoryDataTable;
use App\Models\HelpCategory;
use App\Models\HelpSubCategory;
use App\Models\Help;
use App\Models\HelpCategoryLang;
use App\Models\Language;
use App\Http\Start\Helpers;
use Validator;

class HelpCategoryController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Help Category
     *
     * @param array $dataTable  Instance of HelpCategoryDataTable
     * @return datatable
     */
    public function index(HelpCategoryDataTable $dataTable)
    {
        return $dataTable->render('admin.help_category.view');
    }

    /**
     * Add a New Help Category
     *
     * @param array $request  Input values
     * @return redirect     to Help Category view
     */
    public function add(Request $request)
    {
        if(!$_POST)
        {
            $data['languages'] = Language::where('status', '=', 'Active')->pluck('name', 'value');
            return view('admin.help_category.add',$data);
        }
        else if($request->submit)
        {
            // Add Help Category Validation Rules
            $rules = array(
                    'name'    => 'required|unique:help_category',
                    'status'  => 'required'
                    );

            // Add Help Category Validation Custom Names
            $niceNames = array(
                        'name'    => 'Name',
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
                $help_category = new HelpCategory;

                $help_category->name        = $request->name;
                $help_category->description = isset($request->description) ? $request->description :'';
                $help_category->status      = $request->status;

                $help_category->save();

                foreach($request->translations ?: array() as $translation_data) {  
                    if($translation_data){
                        $help_category_lang = new HelpCategoryLang;
                        $help_category_lang->name        = $translation_data['name'];
                        $help_category_lang->description = isset($translation_data['description']) ? $translation_data['description'] : '';
                        $help_category_lang->locale      = $translation_data['locale'];
                        $help_category_lang->category_id = $help_category->id;
                        $help_category_lang->save();
                    }
                }

                $this->helper->flash_message('success', 'Added Successfully'); // Call flash message function
                return redirect('admin/help_category');
            }
        }
        else
        {
            return redirect('admin/help_category');
        }
    }

    /**
     * Update Help Category Details
     *
     * @param array $request    Input values
     * @return redirect     to Help Category View
     */
    public function update(Request $request)
    {
        if(!$_POST)
        {
            $data['result'] = HelpCategory::find($request->id);
            $data['languages'] = Language::where('status', '=', 'Active')->pluck('name', 'value');
            return view('admin.help_category.edit', $data);
        }
        else if($request->submit)
        {
            // Edit Help Category Validation Rules
            $rules = array(
                    'name'    => 'required|unique:help_category,name,'.$request->id,
                    'status'  => 'required'
                    );

            // Edit Help Category Validation Custom Fields Name
            $niceNames = array(
                        'name'    => 'Name',
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
                $help_category = HelpCategory::find($request->id);

                $help_category->name        = $request->name;
                $help_category->description = isset($request->description) ? $request->description :'';
                $help_category->status      = $request->status;

                $help_category->save();
                $data['locale'][0] = 'en';
                  foreach($request->translations ?: array() as $translation_data) {  
                    if($translation_data){
                         $get_val = HelpCategoryLang::where('category_id',$help_category->id)->where('locale',$translation_data['locale'])->first();
                            if($get_val)
                                $help_category_lang = $get_val;
                            else
                                $help_category_lang = new HelpCategoryLang;
                        $help_category_lang->name        = $translation_data['name'];
                        $help_category_lang->description = isset($translation_data['description']) ? $translation_data['description'] : '';
                        $help_category_lang->locale      = $translation_data['locale'];
                        $help_category_lang->category_id     = $help_category->id;
                        $help_category_lang->save();
                        $data['locale'][] = $translation_data['locale'];
                    }
                }
                if(@$data['locale'])
                HelpCategoryLang::where('category_id',$help_category->id)->whereNotIn('locale',$data['locale'])->delete();
                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function

                return redirect('admin/help_category');
            }
        }
        else
        {
            return redirect('admin/help_category');
        }
    }

    /**
     * Delete Help Category
     *
     * @param array $request    Input values
     * @return redirect     to Help Category View
     */
    public function delete(Request $request)
    {
        $count = Help::where('category_id', $request->id)->count();
        $subcategory_count = HelpSubCategory::where('category_id', $request->id)->count();

        if($count > 0)
            $this->helper->flash_message('error', 'Help have this Help Category. So, Delete that Help or Change that Help Help Category.'); // Call flash message function
        elseif($subcategory_count > 0)
            $this->helper->flash_message('error', 'Help Subcategory have this Help Category. So, Delete that Help Subcategory or Change that Help Subcategory.'); // Call flash message function
        else {
            HelpCategory::find($request->id)->delete();
            HelpCategoryLang::where('category_id',$request->id)->delete();
            $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function
        }
        return redirect('admin/help_category');
    }
}
