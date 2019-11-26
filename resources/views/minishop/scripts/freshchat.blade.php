@if (isset($freshchat_token))

  <script
    src="https://wchat.freshchat.com/js/widget.js"
    onload="js_deps.ready('freshchat')"
    async></script>

  <script type="text/javascript">
    js_deps.wait(['freshchat'], function() {
      fcWidget.init({
        token: "{{ $freshchat_token }}",
        host: "https://wchat.freshchat.com"
      });
    });
  </script>

@endif
