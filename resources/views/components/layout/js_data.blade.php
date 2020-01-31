<script type="text/javascript">

  // JS data
  window.js_data = {
    cdn_url: '{{ $cdn_url }}',
    i18n: {
      phrases: {},
      images: {},
    },
    is_black_friday: false,//new Date().getTime() > new Date(2019, 10, 25).getTime() && new Date().getTime() < new Date(2019, 11, 3).getTime(),
    is_christmas: false,//new Date().getTime() > new Date(2019, 11, 3).getTime() && new Date().getTime() < new Date(2019, 11, 25).getTime(),
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
  @endif

  if (js_query_params.cur === '{aff_currency}') {
    delete window.curjs;
  }

  if (js_query_params.lang === '{lang}') {
    delete window.langjs;
  }

</script>
