@if (empty($HasVueApp) && !Route::is('splash') && empty($is_minishop) && !$is_new_engine)
  <div
    ref="support_toolbar"
    id="static-topbar"
    class="hidden">

    <div class="inside">
      {!! t('static_topbar.content') !!}
    </div>

  </div>
@endif
