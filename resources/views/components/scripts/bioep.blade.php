@if ($HasVueApp && Request::get('exit') && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  <script
    src="{{ mix_cdn('assets/scripts/bioep.min.js') }}"
    onload="js_deps.ready('bioep')"
    async></script>

@endif
