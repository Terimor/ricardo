<script type="text/javascript">
  (function() {
    if (!localStorage.getItem('vrtl_show_upsells')) {
      var url_search = Object.keys(js_query_params)
        .map(function(name) {
          return encodeURIComponent(name) + '=' + encodeURIComponent(js_query_params[name] || '');
        })
        .join('&');

      location.href = '/vrtl/thankyou?' + url_search;
    }
  })();
</script>
