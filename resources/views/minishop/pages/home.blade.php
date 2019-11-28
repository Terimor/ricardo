@extends('minishop.layout')


@section('title', $page_title . ' - ' . t('minishop.title.home'))


@section('js_deps')

  <script type="text/javascript">
    js_deps.show(['page-styles']);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/css/minishop/home.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

@endsection


@section('scripts')

  <script
    src="{{ mix_cdn('assets/js/minishop/home.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>

@endsection


@section('content')

  <section class="content">
    <div class="container">

      @include('minishop.pages.home.welcome')
      @include('minishop.pages.home.products')

    </div>
  </section>

@endsection
