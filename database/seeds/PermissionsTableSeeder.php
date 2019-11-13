<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->delete();

        DB::table('permissions')->insert([
              ['name' => 'manage_admin',                 'display_name' => 'Manage Admin',                 'description' => 'Manage Admin'],              
              ['name' => 'manage_send_message',          'display_name' => 'Manage Send Message',          'description' => 'Manage Send Message'],              
              ['name' => 'view_rider',                   'display_name' => 'View Rider',                   'description' => 'View Rider'],   
              ['name' => 'add_rider',                    'display_name' => 'Add Rider',                    'description' => 'Add Rider'], 
              ['name' => 'edit_rider',                   'display_name' => 'Edit Rider',                   'description' => 'Edit Rider'],
              ['name' => 'delete_rider',                 'display_name' => 'Delete Rider',                 'description' => 'Delete Rider'],
              ['name' => 'view_driver',                  'display_name' => 'View Driver',                  'description' => 'View Driver'],     
              ['name' => 'add_driver',                   'display_name' => 'Add Driver',                   'description' => 'Add Driver'],
              ['name' => 'edit_driver',                  'display_name' => 'Edit Driver',                  'description' => 'Edit Driver'],
              ['name' => 'delete_driver',                'display_name' => 'Delete Driver',                'description' => 'Delete Driver'],
              ['name' => 'manage_car_type',              'display_name' => 'Manage Car Type',              'description' => 'Manage Car Type'],              
              ['name' => 'manage_map',                   'display_name' => 'Manage Map',                   'description' => 'Manage Map'],              
              ['name' => 'manage_statements',            'display_name' => 'Manage Statements',            'description' => 'Manage Statements'],              
              ['name' => 'manage_trips',                 'display_name' => 'Manage Trips',                 'description' => 'Manage Trips'],              
              ['name' => 'manage_wallet',                'display_name' => 'Manage Wallet',                'description' => 'Manage Wallet'],              
              ['name' => 'manage_owe_amount',            'display_name' => 'Manage Owe Amount',            'description' => 'Manage Owe Amount'],              
              ['name' => 'manage_promo_code',            'display_name' => 'Manage Promo Code',            'description' => 'Manage Promo Code'],              
              ['name' => 'manage_driver_payments',              'display_name' => 'Manage Payments',              'description' => 'Manage Payments'],              
              ['name' => 'manage_cancel_trips',          'display_name' => 'Manage Cancel Trips',          'description' => 'Manage Cancel Trips'],              
              ['name' => 'manage_rating',                'display_name' => 'Manage Rating',                'description' => 'Manage Rating'],              
              ['name' => 'manage_fees',                  'display_name' => 'Manage Fees',                  'description' => 'Manage Fees'], 
              ['name' => 'manage_site_settings',         'display_name' => 'Manage Site Settings',         'description' => 'Manage Site Settings'],              
              ['name' => 'manage_api_credentials',       'display_name' => 'Manage Api Credentials',       'description' => 'Manage Api Credentials'],              
              ['name' => 'manage_payment_gateway',       'display_name' => 'Manage Payment Gateway',       'description' => 'Manage Payment Gateway'],              
              ['name' => 'manage_requests',              'display_name' => 'Manage Requests',              'description' => 'Manage Requests'],              
              ['name' => 'manage_join_us',               'display_name' => 'Manage Join Us',               'description' => 'Manage Join Us'],              
              ['name' => 'manage_currency',              'display_name' => 'Manage Currency',              'description' => 'Manage Currency'],              
              ['name' => 'manage_static_pages',          'display_name' => 'Manage Static Pages',          'description' => 'Manage Static Pages'],
              ['name' => 'manage_metas',                 'display_name' => 'Manage Metas',                 'description' => 'Manage Metas'],
              ['name' => 'manage_locations',             'display_name' => 'Manage Locations',             'description' => 'Manage Locations'],
              ['name' => 'manage_peak_based_fare',       'display_name' => 'Manage Peak Based Fare',       'description' => 'Manage Peak Based Fare'],
              ['name' => 'email_settings',               'display_name' => 'Manage Email Settings',        'description' => 'Manage Email Settings'],
              ['name' => 'send_email',                   'display_name' => 'Send Email',                   'description' => 'Send Email'],
              ['name' => 'manage_language',              'display_name' => 'Manage Language',              'description' => 'Manage Language'],
              ['name' => 'manage_help', 'display_name' => 'Manage Help', 'description' => 'Manage Help'],
              ['name' => 'manage_country', 'display_name' => 'Manage Country', 'description' => 'Manage Country'],
              ['name' => 'manage_heat_map', 'display_name' => 'Manage HeatMap', 'description' => 'Manage HeatMap'],
              ['name' => 'manage_manual_booking', 'display_name' => 'Manual Booking', 'description' => 'Manual Booking'],
              ['name' => 'view_company',                  'display_name' => 'View Company',                  'description' => 'View Company'],     
              ['name' => 'add_company',                   'display_name' => 'Add Company',                   'description' => 'Add Company'],
              ['name' => 'edit_company',                  'display_name' => 'Edit Company',                  'description' => 'Edit Company'],
              ['name' => 'delete_company',                'display_name' => 'Delete Company',                'description' => 'Delete Company'],
              ['name' => 'manage_company_payment',                'display_name' => 'Manage Company Payment',                'description' => 'Manage Company Payment'],
              ['name' => 'manage_payments',                'display_name' => 'Manage Payments',                'description' => 'Manage Payments'],
              ['name' => 'manage_vehicle',                'display_name' => 'Manage Vehicle',                'description' => 'Manage Vehicle'],
            ]);
    }
}
