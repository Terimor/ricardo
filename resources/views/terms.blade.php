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
            {!! t('terms.content', ['websitename' => $website_name, 'address' => $company_address]) !!}
            @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
        </div>
    </div>
</div>
@endsection
