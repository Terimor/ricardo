@if ($HasVueApp && Request::get('exit') && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro' && !$is_new_engine)

  <script
    src="{{ mix_cdn('assets/scripts/bioep.min.js') }}"
    onload="js_deps.ready('bioep')"
    async></script>

@endif
