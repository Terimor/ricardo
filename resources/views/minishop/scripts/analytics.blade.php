@if (isset($ga_id))

  <script
    src="https://www.googletagmanager.com/gtag/js?id={{ $ga_id }}"
    onload="js_deps.ready('analytics')"
    async></script>

  <script type="text/javascript">
    window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $ga_id }}');
  </script>

@endif
