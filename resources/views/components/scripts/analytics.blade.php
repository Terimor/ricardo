@if (!empty($ga_id) && empty($is_smartbell))

  <script
    src="https://www.googletagmanager.com/gtag/js?id={{ $ga_id }}"
    onload="js_deps.ready('analytics')"
    async></script>

  <script type="text/javascript">
    window.jsanalytics = @json($ga_id);
    window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',@json($ga_id));
  </script>

  @if (!empty($product) && !empty($product->goptimize_id))
    <script type="text/javascript">
      gtag('config', @json($ga_id), { optimize_id: @json($product->goptimize_id) });
    </script>
  @endif

@endif
