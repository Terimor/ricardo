@if (!$HasVueApp && !Route::is('splash'))
  <div
    ref="support_toolbar"
    id="static-topbar"
    class="hidden">

    <div class="inside">
      {!! t('static_topbar.content') !!}
    </div>

  </div>
@endif
