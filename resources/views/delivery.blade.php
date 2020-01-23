@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('js_deps')

  <script type="text/javascript">
    js_deps.show([
      'bootstrap.css',
      'layout-styles',
      'page-styles',
    ]);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/css/static.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

@endsection


@section('content')
<div class="static">
    <div class="container">
        <div class="static__wrapper">
            {!! t('delivery.content', ['websitename' => $website_name, 'address' => $placeholders['address'], 'email' => $placeholders['email'],
            'phone' => $placeholders['phone'], 'number' => $placeholders['number'], 'company' => $placeholders['company']]) !!}
            @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
        </div>
    </div>
</div>
@endsection
