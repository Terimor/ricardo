<script type="text/javascript">

  // JS data
  window.js_data = {
    cdn_url: @json($cdn_url ?? '', JSON_UNESCAPED_UNICODE),
    i18n: {
      phrases: {},
      images: {},
    },
  };

  // GET params
  window.js_query_params = location.search
    .substr(1)
    .split('&')
    .filter(function(item) {
      return !!item;
    })
    .map(function(item) {
      return item.split('=');
    })
    .reduce(function(acc, item) {
      acc[decodeURIComponent(item[0])] = decodeURIComponent(item[1]);
      return acc;
    }, {});

  @if (!empty($priceSet))
    js_query_params.cop_id = @json($priceSet, JSON_UNESCAPED_UNICODE);
  @endif

  // GET params to JS variables
  Object.keys(js_query_params).forEach(function(key) {
    if (window[key + 'js'] === undefined) {
      window[key + 'js'] = js_query_params[key];
    }
  });

  // affiliate variables
  window.aff_idjs = affidjs = window.aff_idjs || window.affidjs || 0;
  window.offer_idjs = offeridjs = window.offer_idjs || window.offeridjs || 0;

  // product variables
  @if (isset($product))
    window.fbpixelidjs = '{{ $product->fb_pixel_id ?? '' }}';
    window.adwordsconvidjs = '{{ $product->gads_conversion_id ?? '' }}';
    window.adwordsconvlabeljs = '{{ $product->gads_conversion_label ?? '' }}';
    window.adwordsconvretargetjs = '{{ $product->gads_retarget_id ?? '' }}';
    window.jsgoogleoptimize = '{{ $product->goptimize_id ?? '' }}';
  @endif

  if (js_query_params.cur === '{aff_currency}') {
    delete window.curjs;
  }

  if (js_query_params.lang === '{lang}') {
    delete window.langjs;
  }

</script>
