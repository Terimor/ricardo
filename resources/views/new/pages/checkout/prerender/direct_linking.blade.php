<script type="text/javascript">
  (function() {
    var offer_id = +(js_query_params.offer_id || js_query_params.offerid || null);
    var aff_id = +(js_query_params.aff_id || js_query_params.affid || null);
    var direct = +(js_query_params.direct || null);
    var txid = js_query_params.txid || '';

    if (offer_id > 0 && aff_id > 10 && direct === 1 && txid.length < 20) {
      var iframe_url = 'https://track.8xgb.com/aff_c?offer_id=' + offer_id + '&aff_id=' + aff_id;

      var params = [
        'aff_sub1',
        'aff_sub2',
        'aff_sub3',
        'aff_sub4',
        'aff_sub5',
        'aff_click_id',
        'url_id',
      ];

      params.forEach(function(param) {
        if (js_query_params[param]) {
          iframe_url += '&' + param + '=' + encodeURIComponent(js_query_params[param]);
        }
      });

      document.addEventListener('readystatechange', function() {
        if (document.readyState === 'interactive') {
          var iframe = document.createElement('iframe');

          iframe.src = iframe_url;
          iframe.style.display = 'none';

          document.body.appendChild(iframe);
        }
      });
    }
  })();
</script>
