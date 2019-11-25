@if (config('app.env') !== 'local' && config('app.env') !== 'development')

  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js"
    onload="js_deps.ready('vue')"
    async></script>

@else

  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.js"
    onload="js_deps.ready('vue')"
    async></script>

@endif
