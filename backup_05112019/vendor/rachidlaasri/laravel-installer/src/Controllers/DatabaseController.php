<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use RachidLaasri\LaravelInstaller\Helpers\DatabaseManager;
use Illuminate\Support\Facades\Validator;
use Input;
use Hash;
use App\Models\SiteSettings;
use App\Models\Admin;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        // Admin Settings validation rules
         $rules = array(
        'site_name'      => 'required|max:255',
        'username'      => 'required|max:255',
        'email'           => 'required|max:255|email',
        'password'        => 'required|min:6',
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) 
        {
            return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
        }
        else
        {
            if($response = $this->databaseManager->migrateAndSeed())
            {
                $site_settings = SiteSettings::find(1);
                $site_settings->value = Input::get('site_name');
                $site_settings->save();

                $admin = Admin::find(1);
                $admin->email = Input::get('email');
                $admin->username = Input::get('username');
                $admin->password = Hash::make(Input::get('password'));
                $admin->save();
            }

            return redirect()->route('LaravelInstaller::final')
                            ->with(['message' => $response]);
        }
    }
}
