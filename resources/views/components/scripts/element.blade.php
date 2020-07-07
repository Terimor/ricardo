@if (($HasVueApp || !empty($loadVue)) && !$is_new_engine)

  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.11.1/index.js"
    onload="js_deps.ready('element')"
    defer></script>

@endif
