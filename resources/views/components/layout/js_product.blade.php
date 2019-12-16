@if (isset($product))
  <script type="text/javascript">

    var fbpixelidjs = '{{ $product->fb_pixel_id ?? '' }}';
    var adwordsconvidjs = '{{ $product->gads_conversion_id ?? '' }}';
    var adwordsconvlabeljs = '{{ $product->gads_conversion_label ?? '' }}';
    var adwordsconvretargetjs = '{{ $product->gads_retarget_id ?? '' }}';

  </script>
@endif
