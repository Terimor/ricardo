<script type="text/javascript">

  // JS data
  var js_data = {
    cdn_url: '{{ $cdn_url }}',
    is_black_friday: new Date().getTime() > new Date(2019, 10, 25).getTime() && new Date().getTime() < new Date(2019, 11, 3).getTime(),
    is_christmas: new Date().getTime() > new Date(2019, 11, 3).getTime() && new Date().getTime() < new Date(2019, 11, 25).getTime(),
  };

  // GET params
  var query_params = location.search
    .substr(1).split('&').filter(item => !!item).map(item => item.split('='))
    .reduce((acc, item) => { acc[item[0]] = item[1]; return acc; }, {});


  // GET params to JS variables
  for (let key of Object.keys(query_params)) {
    if (window[key + 'js'] === undefined) {
      window[key + 'js'] = query_params[key];
    }
  }

  // affiliate variables
  window.aff_idjs = window.affidjs = window.aff_idjs || window.affidjs || 0;
  window.offer_idjs = window.offeridjs = window.offer_idjs || window.offeridjs || 0;

</script>
