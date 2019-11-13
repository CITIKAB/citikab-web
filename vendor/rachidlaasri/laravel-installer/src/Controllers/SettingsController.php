<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use App\Http\Requests;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    /**
     * Display the settings check page.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        return view('vendor.installer.settings');
    }
}
