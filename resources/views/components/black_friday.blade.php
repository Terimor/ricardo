@if (Route::is('checkout') || Route::is('checkout_price_set'))

  <div
    ref="blackFriday"
    class="black-friday-toolbar hidden">

    <div class="inside">
      {!! t('black_friday.content') !!}
    </div>

  </div>

@endif
