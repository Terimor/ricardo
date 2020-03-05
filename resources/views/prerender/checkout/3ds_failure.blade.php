@if (Request::get('3ds_restore') && Request::get('3ds') === 'failure')
  <script type="text/javascript">
    (function() {
      localStorage.setItem('order_failed', localStorage.getItem('odin_order_id'));
    })();
  </script>
@endif
