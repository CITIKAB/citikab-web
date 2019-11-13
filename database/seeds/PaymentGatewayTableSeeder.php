<?php

use Illuminate\Database\Seeder;

class PaymentGatewayTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_gateway')->delete();

        DB::table('payment_gateway')->insert([
                /*['name' => 'paypal_id', 'value' => 'vinoth@trioangle.com', 'site' => 'PayPal'],
                ['name' => 'username', 'value' => 'vinoth_api1.trioangle.com', 'site' => 'PayPal'],
                ['name' => 'password', 'value' => '3CAX5SY2RQC2W3NC', 'site' => 'PayPal'],
                ['name' => 'signature', 'value' => 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-A1qi2qL9cJx.2Na4RHlJcHL6v4bt', 'site' => 'PayPal'],
                ['name' => 'mode', 'value' => 'sandbox', 'site' => 'PayPal'],
                ['name' => 'app_id', 'value' => 'APP-80W284485P519543T', 'site' => 'PayPal'],
                ['name' => 'client', 'value' => 'ASeeaUVlKXDd8DegCNSuO413fePRLrlzZKdGE_RwrWqJOVVbTNJb6-_r6xX9GdsRUVNc8butjTOIK_Xm', 'site' => 'PayPal'],
                ['name' => 'secret', 'value' => 'ENCGBUb_QSpHzGIAxjtSehkRIAI9lOELOiZUUjZUTEdjACeILOUUG58ijBNsuzdV-RPyDbHNxYTPkapn', 'site' => 'PayPal'],
                ['name' => 'publish', 'value' => 'pk_test_764boQ9IBVx4RSKjr1Fx2a7W', 'site' => 'Stripe'],
                ['name' => 'secret', 'value' => 'sk_test_xaaV9BdpFcTmWaVoU28gwuOm', 'site' => 'Stripe'],*/
                ['name' => 'merchant_id', 'value' => 'GP0000001', 'site' => 'Gladepay'],
                ['name' => 'merchant_key', 'value' => '123456789', 'site' => 'Gladepay'],
                ['name' => 'url', 'value' => 'https://demo.api.gladepay.com/payment', 'site' => 'Gladepay'],
            ]);
    }
}
