<?php

/**
 * StartService Provider
 *
 * @package     Gofer
 * @subpackage  Provider
 * @category    Service
 * @author      Trioangle Product Team
 * @version     1.7
 * @link        http://trioangle.com
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;
use Config;
use Schema;
use Auth;
use App;
use Session;
use App\Models\Pages;
use App\Models\Language;
use App\Models\Currency;
use App\Models\SiteSettings;
use App\Models\JoinUs;

class StartServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    	
        if(Schema::hasTable('pages'))
            $this->pages(); // Calling Pages function

        if(Schema::hasTable('site_settings'))
            $this->site_settings(); // Calling Site Settings function

        if(Schema::hasTable('join_us'))
            $this->join_us(); // Calling Join US function

        if(Schema::hasTable('currency'))
            $this->currency(); // Calling Join US function

       if(Schema::hasTable('language'))
            $this->language(); // Calling Join US function

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
       
    }
	
    // Share Static Pages data to whole software
    public function pages()
    {
        // Pages lists for footer
        $company_pages = Pages::select('url', 'name')->where('status', '=', 'Active')->where('footer', 'yes')->get();

        View::share('company_pages', $company_pages);
    }
    
    // Share Join Us data to whole software
    public function join_us()
    {
        $join_us = JoinUs::whereNotIn('name', ['app_store_rider','app_store_driver', 'play_store_rider','play_store_driver'])->get();
        $app_links = JoinUs::whereIn('name', ['app_store_rider','app_store_driver', 'play_store_rider','play_store_driver'])->get();
        
        View::share('join_us', $join_us);
        View::share('app_links', $app_links);
    }

    public function currency()
    {
        // Currency code lists for footer
        $currency = Currency::codeSelect();
        View::share('currency_select', $currency);
        
        $default_currency = Currency::active()->defaultCurrency()->first();        
        
        if(!@$default_currency)
            $default_currency = Currency::active()->first();

        Session::put('currency', $default_currency->code);
        Session::put('symbol', $default_currency->symbol);

        View::share('default_currency', $default_currency);
        View::share('default_country', 'India');
       
    }
    public function language()
    {
        // Language lists for footer
        $language = Language::translatable()->pluck('name', 'value');
        View::share('language', $language);
        
        // Default Language for footer
        $default_language = Language::translatable()->where('default_language', '=', '1')->limit(1)->get();
        View::share('default_language', $default_language);
        if($default_language->count() > 0) {
            Session::put('language', $default_language[0]->value);
            App::setLocale($default_language[0]->value);
        }
    }


    // Share Site Settings data to whole software
    public function site_settings()
    {
        $site_settings = SiteSettings::all();

        View::share('site_name', $site_settings[0]->value);
        View::share('head_code', $site_settings[7]->value);

        if($site_settings[10]->value == '' && @$_SERVER['HTTP_HOST'] && !\App::runningInConsole()){
            
            $url = "http://".$_SERVER['HTTP_HOST'];
            $url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);

            SiteSettings::where('name','site_url')->update(['value' =>  $url]);
        }

     }


}
