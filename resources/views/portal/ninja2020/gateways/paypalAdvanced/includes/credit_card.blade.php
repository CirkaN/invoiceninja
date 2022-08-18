<div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
     style="display: flex!important; justify-content: center!important;" id="authorize--credit-card-container">
    <div class="card-js" id="my-card" data-capture-name="true">
        <input class="name" required id="cardholder_name" name="card_holders_name" placeholder="{{ ctrans('texts.name')}}">
        <input  name='card_number' required id="card_number" placeholder="{{ctrans('texts.card_number')}}">
        <input  name="expiry_month"  required maxlength='2' id="expiration_month" placeholder="{{ctrans('texts.expiration_month')}}">
        <input  name="expiry_year"  required maxlength='4' minlength='4' id="expiration_year" placeholder="{{ctrans('texts.expiration_year')}}">
        <input type='password' class="cvc" minlength='3' maxlength='4' required name="cvc" id="cvv" placeholder="{{ctrans('texts.cvv')}}">
    </div>
    <div id="errors"></div>
</div>
