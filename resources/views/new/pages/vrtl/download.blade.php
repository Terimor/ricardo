@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('js_deps')
    <script type="text/javascript">
        js_deps.show([
            'page-styles',
        ]);
    </script>
@endsection


@section('styles')
    <link
        href="{{ mix_cdn('assets/css/new/pages/vrtl/download.css') }}"
        onload="js_deps.ready.call(this, 'page-styles')"
        rel="stylesheet"
        media="none" />
@endsection


@section('scripts')
    <script
        src="{{ mix_cdn('assets/js/new/pages/vrtl/download.js') }}"
        onload="js_deps.ready('page-scripts')"
        async></script>
@endsection


@section('content')
    Download page
@endsection
