<script type="text/javascript">
  js_data.pixels = @json($html_to_app['pixels'] ?? [], JSON_UNESCAPED_UNICODE) || [];
</script>

@if (!empty($html_to_app['pixels']))

  @php
    $pixels_to_print = array_filter($html_to_app['pixels'], function($pixel) {
      return !in_array($pixel['type'], ['cart', 'payment', 'checkout']);
    });
    usort($pixels_to_print, function($a, $b) {
      if ($a['type'] === 'view' && $b['type'] === 'view') return 0;
      if ($a['type'] === 'view') return -1;
      if ($b['type'] === 'view') return 1;
      return 0;
    });
  @endphp

  @foreach($pixels_to_print as $pixel)
    {!! !empty($pixel['code']) ? $pixel['code'] : '' !!}
  @endforeach

@endif
