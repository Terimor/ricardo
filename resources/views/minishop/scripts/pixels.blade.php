@if (!empty($htmlToApp['pixels']))
  @foreach($htmlToApp['pixels'] as $pixel)

    {!! !empty($pixel['code']) ? $pixel['code'] : '' !!}

  @endforeach
@endif
