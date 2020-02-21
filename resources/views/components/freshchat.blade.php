@if (Route::is('contact-us') && empty($is_smartbell))
  <script>
    function initFreshChat() {
      if (window.fcWidget) {
        window.fcWidget.init({
          token: "{{ $FreshchatToken }}",
          host: "https://wchat.freshchat.com"
        });
      }
    }
    function initialize(i,t){var e;i.getElementById(t)?initFreshChat():((e=i.createElement("script")).id=t,e.async=!0,e.src="https://wchat.freshchat.com/js/widget.js",e.onload=initFreshChat,i.head.appendChild(e))}function initiateCall(){initialize(document,"freshchat-js-sdk")}window.addEventListener?window.addEventListener("load",initiateCall,!1):window.attachEvent("load",initiateCall,!1);
  </script>
@endif
