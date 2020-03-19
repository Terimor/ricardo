@include('components.layout.js_data')

<script type="text/javascript">
  var stored_query_params = {};

  try {
    stored_query_params = JSON.parse(localStorage.getItem('3ds_params')) || {};
  } catch (err) {

  }

  var url_query_params = []
    .concat(Object.keys(stored_query_params))
    .concat(Object.keys(js_query_params))
    .reduce(function(acc, name) {
      acc[name] = js_query_params[name] === undefined
        ? stored_query_params[name]
        : js_query_params[name];

      return acc;
    }, {});

  var url_search = []
    .concat(
      Object.keys(url_query_params).map(function(name) {
        return encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || '');
      })
    )
    .concat(['apm_restore=1'])
    .join('&');

  location.href = location.pathname + '?' + url_search;
</script>
