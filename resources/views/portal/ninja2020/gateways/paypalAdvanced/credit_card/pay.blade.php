@extends('portal.ninja2020.layout.payments', ['gateway_title' => ctrans('texts.payment_type_credit_card'), 'card_title' => ctrans('texts.payment_type_credit_card')])

@section('gateway_head')
    <script
        src="https://www.paypal.com/sdk/js?components=buttons,hosted-fields&client-id={{$gateway->access_token}}"
        data-client-token="{{$gateway->access_token}}"
    ></script>
@endsection

@section('gateway_content')

    <form action="{{ route('client.payments.response') }}" method="post" id="server_response">
        @csrf
        <input type="hidden" name="payment_hash" value="{{ $payment_hash }}">
        <input type="hidden" name="company_gateway_id" value="{{ $gateway->paypalAdvanced->company_gateway->id }}">
        <input type="hidden" name="payment_method_id" value="{{$payment_method_id}}">
        <input type='hidden' name="paypal_order_id" id="paypal_order_id" value='{{$gateway->order_id}}'>
        <input type="hidden" name="dataValue" id="dataValue"/>
        <input type="hidden" name="dataDescriptor" id="dataDescriptor"/>
        <input type="submit" style="display: none" id="form_btn">

    @component('portal.ninja2020.components.general.card-element', ['title' => ctrans('texts.payment_type')])
        {{ ctrans('texts.credit_card') }}
    @endcomponent

    @include('portal.ninja2020.gateways.includes.payment_details')

    @component('portal.ninja2020.components.general.card-element', ['title' => 'Pay with Credit Card'])
        @include('portal.ninja2020.gateways.paypalAdvanced.includes.credit_card')
    @endcomponent

    @include('portal.ninja2020.gateways.includes.pay_now',['type' => 'submit'])
    </form>
@endsection

@section('gateway_footer')

@endsection
