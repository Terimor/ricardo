@if (!$HasVueApp && empty($loadVue))

  <script
    src="{{ mix_cdn('/assets/js/static.js') }}"
    onload="js_deps.ready('static')"
    async></script>

@endif
