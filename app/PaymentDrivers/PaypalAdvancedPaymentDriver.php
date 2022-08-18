<?php


namespace App\PaymentDrivers;


use App\Models\GatewayType;
use App\PaymentDrivers\PaypalAdvanced\CreditCard;
use App\Utils\Traits\MakesHash;

class PaypalAdvancedPaymentDriver extends BaseDriver
{
    use MakesHash;

    public $gateway; //initialized gateway

    public $payment_method; //initialized payment method

    public static $methods = [
        GatewayType::CREDIT_CARD => CreditCard::class,
    ];

    /**
     * Returns the gateway types.
     */
    public function gatewayTypes(): array
    {
        $types = [];

        $types[] = GatewayType::CREDIT_CARD;

        return $types;
    }

    public function setPaymentMethod($payment_method_id)
    {
        $class = self::$methods[$payment_method_id];
        $this->payment_method = new $class($this);
        return $this;
    }

    public function authorizeView(array $data)
    {
        return $this->payment_method->authorizeView($data); //this is your custom implementation from here
    }

    public function authorizeResponse($request)
    {
        return $this->payment_method->authorizeResponse($request);  //this is your custom implementation from here
    }

    public function processPaymentView(array $data)
    {
        return $this->payment_method->paymentView($data);  //this is your custom implementation from here
    }

    public function processPaymentResponse($request)
    {
        return $this->payment_method->paymentResponse($request); //this is your custom implementation from here
    }
}
