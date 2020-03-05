<script type="text/javascript">
  var txid = location.search.replace(/^.*txid=([^&#]*).*$/, '$1');

  var domain = location.hostname !== '127.0.0.1'
    ? '.' + location.hostname.split('.').slice(-2).join('.')
    : '127.0.0.1';

  document.cookie = 'txid=' + txid + '; Domain=' + domain + '; Path=/; SameSite=None';
</script>
