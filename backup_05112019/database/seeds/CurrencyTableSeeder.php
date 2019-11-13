<?php

use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('currency')->delete();
    	
        DB::table('currency')->insert([
                ['name' => 'US Dollar','code' => 'USD','symbol' => '&#36;','rate' => '1.00', 'default_currency' => '1', 'paypal_currency' => 'Yes'],
                ['name' => 'Pound Sterling','code' => 'GBP','symbol' => '&pound;','rate' => '0.65', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Europe','code' => 'EUR','symbol' => '&euro;','rate' => '0.88', 'default_currency' => '0', 'paypal_currency' => 'Yes'],
                ['name' => 'Australian Dollar','code' => 'AUD','symbol' => '&#36;','rate' => '1.41', 'default_currency' => '0', 'paypal_currency' => 'Yes'],
                ['name' => 'Singapore','code' => 'SGD','symbol' => '&#36;','rate' => '1.41', 'default_currency' => '0', 'paypal_currency' => 'No', 'paypal_currency' => 'No'],
                ['name' => 'Swedish Krona','code' => 'SEK','symbol' => 'kr','rate' => '8.24', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Danish Krone','code' => 'DKK','symbol' => 'kr','rate' => '6.58', 'default_currency' => '0', 'paypal_currency' => 'Yes'],
                ['name' => 'Mexican Peso','code' => 'MXN','symbol' => '$','rate' => '16.83', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Brazilian Real','code' => 'BRL','symbol' => 'R$','rate' => '3.88', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Malaysian Ringgit','code' => 'MYR','symbol' => 'RM','rate' => '4.31', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Philippine Peso','code' => 'PHP','symbol' => 'P','rate' => '46.73', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Swiss Franc','code' => 'CHF','symbol' => '&euro;','rate' => '0.97', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'India','code' => 'INR','symbol' => '&#x20B9;','rate' => '66.24', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Argentine Peso','code' => 'ARS','symbol' => '&#36;','rate' => '9.35', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Canadian Dollar','code' => 'CAD','symbol' => '&#36;','rate' => '1.33', 'default_currency' => '0', 'paypal_currency' => 'Yes'],
                ['name' => 'Chinese Yuan','code' => 'CNY','symbol' => '&#165;','rate' => '6.37', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Czech Republic Koruna','code' => 'CZK','symbol' => 'K&#269;','rate' => '23.91', 'default_currency' => '0', 'paypal_currency' => 'Yes'],
                ['name' => 'Hong Kong Dollar','code' => 'HKD','symbol' => '&#36;','rate' => '7.75', 'default_currency' => '0', 'paypal_currency' => 'Yes'],
                ['name' => 'Hungarian Forint','code' => 'HUF','symbol' => 'Ft','rate' => '276.41', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Indonesian Rupiah','code' => 'IDR','symbol' => 'Rp','rate' => '14249.50', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Israeli New Sheqel','code' => 'ILS','symbol' => '&#8362;','rate' => '3.86', 'default_currency' => '0', 'paypal_currency' => 'Yes'],
                ['name' => 'Japanese Yen','code' => 'JPY','symbol' => '&#165;','rate' => '120.59', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'South Korean Won','code' => 'KRW','symbol' => '&#8361;','rate' => '1182.69', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Norwegian Krone','code' => 'NOK','symbol' => 'kr','rate' => '8.15', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'New Zealand Dollar','code' => 'NZD','symbol' => '&#36;','rate' => '1.58', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Polish Zloty','code' => 'PLN','symbol' => 'z&#322;','rate' => '3.71', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Russian Ruble','code' => 'RUB','symbol' => 'p','rate' => '67.75', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Thai Baht','code' => 'THB','symbol' => '&#3647;','rate' => '36.03', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Turkish Lira','code' => 'TRY','symbol' => '&#8378;','rate' => '3.05', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'New Taiwan Dollar','code' => 'TWD','symbol' => '&#36;','rate' => '32.47', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'Vietnamese Dong','code' => 'VND','symbol' => '&#8363;','rate' => '22471.00', 'default_currency' => '0', 'paypal_currency' => 'No'],
                ['name' => 'South African Rand','code' => 'ZAR','symbol' => 'R','rate' => '13.55', 'default_currency' => '0', 'paypal_currency' => 'No'],
            ]);
    }
}
