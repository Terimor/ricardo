@if (Route::is('checkout') || Route::is('checkout_price_set'))

  <link
    id="intlTelInputCss"
    href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.2/css/intlTelInput.css"
    onload="js_deps.ready.call(this, 'intl_tel_input.css')"
    rel="stylesheet"
    media="none" />

@endif