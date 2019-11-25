<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">

@if (isset($ga_id))
  <meta name="ga-id" content="{{ $ga_id }}">
@endif
