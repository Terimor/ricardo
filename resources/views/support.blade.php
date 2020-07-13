@extends('layouts.app', ['loadVue' => true])

@section('title', $page_title)


@section('js_data')
    <script type="text/javascript">
      js_data.i18n.phrases = @json($loadedPhrases, JSON_UNESCAPED_UNICODE);
      js_data.countries = [];
    </script>
@endsection


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
    href="{{ mix_cdn('assets/css/support.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

  <link
    href="{{ mix_cdn('assets/js/app.vue.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts')
    <script
        src="{{ mix_cdn('assets/js/app.js') }}"
        onload="js_deps.ready('page-scripts')"
        async></script>
@endsection


@section('content')
    <div class="contacts bg-white">
        <div class="container py-5">
            <order-status support-code="{{$code}}" order-email="{{$email}}"/>
        </div>
    </div>
@endsection
