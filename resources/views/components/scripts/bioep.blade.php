@if ($HasVueApp && Request::get('exit'))

  <script
    src="{{ mix_cdn('assets/scripts/bioep.min.js') }}"
    onload="js_deps.ready('bioep')"
    async></script>

@endif
