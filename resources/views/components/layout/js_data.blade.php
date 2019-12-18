<script type="text/javascript">

  // JS data
  var js_data = {
    cdn_url: '{{ $cdn_url }}',
    i18n: {
      phrases: {},
      images: {},
    },
    is_black_friday: new Date().getTime() > new Date(2019, 10, 25).getTime() && new Date().getTime() < new Date(2019, 11, 3).getTime(),
    is_christmas: new Date().getTime() > new Date(2019, 11, 3).getTime() && new Date().getTime() < new Date(2019, 11, 25).getTime(),
  };

  // GET params
  var js_query_params = location.search
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
  var aff_idjs = affidjs = window.aff_idjs || window.affidjs || 0;
  var offer_idjs = offeridjs = window.offer_idjs || window.offeridjs || 0;

</script>
