<script type="text/javascript">
  (function() {
    var txid_from_get = js_query_params.txid || '';
    var txid_from_cookie = decodeURIComponent((document.cookie.match(new RegExp('(?:^|; )txid=([^;]*)')) || [])[1] || '');

    if (txid_from_get.length >= 20) {
      var domain = '.' + location.hostname.split('.').slice(-2).join('.');
      document.cookie = 'txid=' + encodeURIComponent(txid_from_get) + '; Domain=' + domain + '; Path=/';
    }

    window.txid = window.txidjs = txid_from_get.length >= 20
      ? txid_from_get
      : txid_from_cookie.length >= 20
        ? txid_from_cookie
        : undefined;
  })();
</script>
