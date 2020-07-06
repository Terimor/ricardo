@extends('layouts.app')

@section('title', $page_title)

@section('js_deps')
    <script type="text/javascript">
      js_deps.show([]);
    </script>
@endsection

@section('js_data')
    <script type="text/javascript">
      js_data.i18n.phrases = @json($loadedPhrases, JSON_UNESCAPED_UNICODE);
    </script>
@endsection


@section('content')
    <div class="contacts bg-white">
        <div class="container py-5">
            <order-status order-code="{{$code}}" order-email="{{$email}}"/>
        </div>
    </div>
@endsection

@section('scripts')

    <script
        src="{{ mix_cdn('assets/js/app.js') }}"
        onload="js_deps.ready('page-scripts')"
        async></script>

@endsection
