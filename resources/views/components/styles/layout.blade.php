@if (!$is_new_engine)

  <link
    href="{{ mix_cdn('assets/css/app.css') }}"
    onload="js_deps.ready.call(this, 'layout-styles')"
    rel="stylesheet"
    media="none" />

@endif
