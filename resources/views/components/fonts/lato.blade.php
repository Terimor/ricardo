@if (!empty($is_minishop) || ((Route::is('checkout') || Route::is('checkout_price_set')) && request()->get('tpl') === 'fmc5'))

  <link
    href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap"
    onload="js_deps.ready.call(this, 'lato.css')"
    rel="stylesheet"
    media="none" />

@endif
