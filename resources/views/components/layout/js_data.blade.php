<script type="text/javascript">

  var js_data = {
    i18n: {},
  };


  // GET params to JS variables
  new URL(location).searchParams.forEach(function(value, key) {
    if (window[key + 'js'] === undefined) {
      window[key + 'js'] = value;
    }
  });

  // affiliate variables
  window.aff_idjs = window.affidjs = window.aff_idjs || window.affidjs || 0;
  window.offer_idjs = window.offeridjs = window.offer_idjs || window.offeridjs || 0;

</script>
