@if (!empty($is_checkout) && empty($is_smartbell))

  @php
    $data_collertor_url = config('app.env') === 'local' || config('app.env') === 'development'
      ? 'tst.kaptcha.com'
      : 'ssl.kaptcha.com';
  @endphp

  <script
    src="https://{{ $data_collertor_url }}/collect/sdk?m=700000"
    onload="js_deps.ready('bluesnap_kount')"
    async></script>

  <script type="text/javascript">
    js_deps.wait(['bluesnap_kount'], function() {
      var ka_client = new window.ka.ClientSDK();

      ka_client.setupCallback({
        'collect-begin': function(params) {
          window.kount_params = params;
        },
      });

      document.documentElement.classList.add('kaxsdc');
      
      if (document.documentElement.getAttribute('data-event')) {
        document.documentElement.getAttribute('data-event') = 'load';
      }

      ka_client.autoLoadEvents();
    });
  </script>

@endif
