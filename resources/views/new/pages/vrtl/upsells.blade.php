@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('js_data')
  <script type="text/javascript">
    js_data.product = @json($product, JSON_UNESCAPED_UNICODE);
    js_data.order_customer = @json($orderCustomer, JSON_UNESCAPED_UNICODE);
    js_data.upsells = @json([['id' => '123'], ['id' => '456'], ['id' => '789']], JSON_UNESCAPED_UNICODE);
  </script>
@endsection


@section('js_prerender')
  @include('new.pages.vrtl.upsells.prerender.thankyou_redirect')
@endsection


@section('js_deps')
  <script type="text/javascript">
    js_deps.show([
      'page-styles',
    ]);
  </script>
@endsection


@section('fonts')
  @include('new.fonts.roboto')
@endsection


@section('styles')
  <link
    href="{{ mix_cdn('assets/css/new/pages/vrtl/upsells.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts')
  <script
    src="{{ mix_cdn('assets/js/new/pages/vrtl/upsells.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content')
  <div class="upsells">
    <div
      v-if="step < 3"
      :class="{ ['step' + step]: true }"
      class="inside">
      @include('new.pages.vrtl.upsells.steps.step1')
    </div>
    <div
      v-else
      :class="{ ['step' + step]: true }"
      class="inside">
      @include('new.pages.vrtl.upsells.steps.step3')
    </div>
  </div>
@endsection
