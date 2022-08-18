<?php

use App\Models\Gateway;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaypalAdvancedGateway extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $fields = new \stdClass;
        $fields->testMode = false;
        $fields->clientId = "";
        $fields->appSecret = "";
        $fields->baseUrl = 'https://api-m.sandbox.paypal.com';


        $gateway = new Gateway;
        $gateway->name = 'Paypal Advanced';
        $gateway->key = Str::lower(Str::random(32));
        $gateway->provider = 'PaypalAdvanced';
        $gateway->is_offsite = false;
        $gateway->fields = json_encode($fields);
        $gateway->visible = true;
        $gateway->site_url = 'https://paypal.com';
        $gateway->default_gateway_type_id = 1;
        $gateway->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
