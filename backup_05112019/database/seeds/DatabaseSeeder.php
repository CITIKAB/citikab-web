<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $this->call(CompaniesTableSeeder::class);
        $this->call(CurrencyTableSeeder::class);
        $this->call(VehiclesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
        $this->call(FeesTableSeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(SiteSettingsTableSeeder::class);
        $this->call(ApiCredentialsTableSeeder::class);
        $this->call(PaymentGatewayTableSeeder::class);
        $this->call(JoinUsTableSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(MetasTableSeeder::class);
        $this->call(LanguageTableSeeder::class);
        $this->call(EmailSettingsTableSeeder::class);
    }
}
