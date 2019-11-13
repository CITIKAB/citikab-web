<?php

use Illuminate\Database\Seeder;

class ApiCredentialsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('api_credentials')->delete();
        
        DB::table('api_credentials')->insert([
            ['name' => 'key', 'value' => 'AIzaSyB6lCQnISdsSUVFdcQYxaHxXXjvKDn9wcs', 'site' => 'GoogleMap'],
            ['name' => 'server_key', 'value' => 'AIzaSyB6lCQnISdsSUVFdcQYxaHxXXjvKDn9wcs', 'site' => 'GoogleMap'],
            ['name' => 'sid', 'value' => 'ACf64f4d6b2a55e7c56b592b6dec3919ae', 'site' => 'Twillo'],
            ['name' => 'token', 'value' => 'bc887b0e7159ab5cb0945c3fc59b345a', 'site' => 'Twillo'],
            ['name' => 'from', 'value' => '+15594238858', 'site' => 'Twillo'],
            ['name' => 'server_key', 'value' => 'AIzaSyB0efJyL4VKIbR2rTcugSC_z-m3z06hjEk', 'site' => 'FCM'],
            ['name' => 'sender_id', 'value' => '253756802947', 'site' => 'FCM'],                
            ['name' => 'client_id', 'value' => '1105678852897547', 'site' => 'Facebook'],
            ['name' => 'client_secret', 'value' => '64c4d6d3dc2ba3471297c17585a60aff', 'site' => 'Facebook'],
            ['name' => 'account_kit_id', 'value' => '247798396156171', 'site' => 'Facebook'],
            ['name' => 'account_kit_secret', 'value' => 'dbf19abccb6e2ceb6631e41180370068', 'site' => 'Facebook'],
            ['name' => 'client_id', 'value' => '200332964350-lkr7e12upf315qpg404a402s31f4qncn.apps.googleusercontent.com', 'site' => 'Google'],
            ['name' => 'client_secret', 'value' => 'SPe8bYCFXpv8oDyygaWrofJw', 'site' => 'Google'],
        ]);
    }
}
