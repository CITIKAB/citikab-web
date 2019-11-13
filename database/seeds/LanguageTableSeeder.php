<?php

use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('language')->delete();
    	
        DB::table('language')->insert([
        	    
  				     ['name' => 'English','value' => 'en','is_translatable' => '1','default_language' => '1','status' => 'Active'],
                  				      
                     ['name' => 'Persian','value' => 'fa','is_translatable' => '1','default_language' => '0','status' => 'Active'],
                     
                     ['name' => 'Arabic','value' => 'ar','is_translatable' => '1','default_language' => '0','status' => 'Active'],
                     
                     ['name' => 'Spanish','value' => 'es','is_translatable' => '1','default_language' => '0','status' => 'Active'],
  				      
        	]);
    }
}
