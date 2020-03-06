@if (!empty($is_checkout) && empty($is_smartbell))

  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-lazyload/12.3.0/lazyload.min.js"
    onload="js_deps.ready('lazyload')"
    async></script>

  <script type="text/javascript">
    js_deps.wait(['lazyload'], function() {
      if (window.LazyLoad) {
        window.lazyLoadInstance = new LazyLoad({
          elements_selector: '.lazy',
        });
      }
    });
  </script>

@endif
