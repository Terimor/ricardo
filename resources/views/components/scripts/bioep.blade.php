@if ($HasVueApp && Request::get('exit') !== '0' && empty($is_smartbell) && !$is_new_engine)

  <script
    src="{{ mix_cdn('assets/scripts/bioep.min.js') }}"
    onload="js_deps.ready('bioep')"
    async></script>

@endif
