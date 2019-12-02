@if (Route::is('checkout') || Route::is('checkout_price_set'))

  <div
    ref="christmas"
    class="christmas-toolbar hidden">

    <div class="inside">
      {!! t('christmas.content') !!}
    </div>

  </div>

@endif
