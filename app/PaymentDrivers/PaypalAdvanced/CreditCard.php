<?php


namespace App\PaymentDrivers\PaypalAdvanced;


use App\Http\Requests\ClientPortal\Payments\PaymentResponseRequest;
use App\Models\Currency;
use App\Models\PaymentHash;
use App\PaymentDrivers\PaypalAdvancedPaymentDriver;
use App\Utils\Traits\MakesHash;
use Illuminate\Support\Facades\Http;

class CreditCard
{
    use MakesHash;

    public $paypalAdvanced;

    //from config
    private $base_url = "";
    private $client_id = "";
    private $app_secret = "";
    //retrieved from paypal
    public $client_token = "";
    public $access_token = "";
    public $order_id = "";

    public function __construct(PaypalAdvancedPaymentDriver $paypalAdvanced)
    {
        $this->paypalAdvanced = $paypalAdvanced;

        $this->paypal_base_uri = "https://api-m.sandbox.paypal.com";
        if ($this->paypalAdvanced->company_gateway->getConfigField('testMode')) {
            $this->paypal_base_uri = "https://api-m.sandbox.paypal.com";
        }
        $this->client_id = $this->paypalAdvanced->company_gateway->getConfigField('clientId');
        $this->app_secret = $this->paypalAdvanced->company_gateway->getConfigField('appSecret');
        $this->generateAccessToken();
    }

    private function getAccessToken()
    {
        $auth = base64_encode($this->client_id . ":" . $this->app_secret);

        $authorization = "Authorization: Basic " . $auth;
        $data = 'grant_type=client_credentials';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        if ($response->access_token) {
            $this->access_token = $response->access_token;
            return $this->access_token;
        }
    }

    private function generateAccessToken()
    {
        $accessToken = $this->getAccessToken();

        $authorization = "Authorization: Bearer " . $accessToken;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/identity/generate-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($ch));

        $this->client_token = $response->client_token;
    }


    public function paymentView(array $data)
    {
        $this->paypalAdvanced->payment_hash->data = array_merge((array)$this->paypalAdvanced->payment_hash->data, $data);
        $this->paypalAdvanced->payment_hash->save();
        $this->createPaypalOrder($this->paypalAdvanced->payment_hash);

        $data['gateway'] = $this;

        return render('gateways.paypalAdvanced.credit_card.pay', $data);
    }

    public function paymentResponse(PaymentResponseRequest $request)
    {
        if (!$request->paypal_order_id)
            return 'no';
        https://api-m.sandbox.paypal.com/v2/checkout/orders/9WH620610F196520F/authorize

       // $data = '{"payment_source":{"card": {"number": "' . $request->get('card_number') . '","expiry": "' . $request->get('expiry_year') . '-' . $request->get('expiry_month') . '","name": "' . $request->get('card_holders_name') . '","billing_address":{"address_line_1": "2211 N First Street","address_line_2": "Building 17","admin_area_2": "San Jose","admin_area_1": "CA","postal_code": "95131","country_code": "US"}}}}';
        $data = '{
"payment_source": {
"card": {
"number": "4111111111111111",
"expiry": "2022-02",
"name": "John Doe",
"billing_address": {
"address_line_1": "2211 N First Street",
"address_line_2": "Building 17",
"admin_area_2": "San Jose",
"admin_area_1": "CA",
"postal_code": "95131",
"country_code": "US"
}
}
}
}';
        $authorization = "Authorization: Bearer " . $this->access_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v2/checkout/orders/' . $request->paypal_order_id . '/confirm-payment-source');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = $authorization;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = json_decode(curl_exec($ch));
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);
        dd($result);
        if ($result->status == 'CREATED') {
            $this->order_id = $result->id;
        }
        return redirect('client/invoices')->withErrors('something went wrong');

    }

    public function createPaypalOrder(PaymentHash $payment_hash): string
    {
        $amount_with_fee = $payment_hash->data->total->amount_with_fee;
        $currency = Currency::find($payment_hash->data->client->settings->currency_id)->code;
        $authorization = "Authorization: Bearer " . $this->access_token;

        $data = '{"intent": "CAPTURE","purchase_units":[{"amount": {"currency_code": "' . $currency . '","value": "' . $amount_with_fee . '"}}]}';

        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = $authorization;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = json_decode(curl_exec($ch));
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        dd($result);

        if ($result->status == 'CREATED') {
            $this->order_id = $result->id;
        }
        return redirect('client/invoices')->withErrors('something went wrong');


    }
}
