@if (!empty($html_to_app['pixels']))
  @foreach($html_to_app['pixels'] as $pixel)

    {!! !empty($pixel['code']) ? $pixel['code'] : '' !!}

  @endforeach
@endif
