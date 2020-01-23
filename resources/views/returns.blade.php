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
            @if (empty(Request::get('aff_id')) && empty(Request::get('offer_id')))
              <div class="returns-address">
                <div class="label">{{ t('returns.address.label') }}</div>
                <select class="selector">
                  <option value="" data-value=""></option>
                  <option value="value1" data-value="{{ t('returns.address.value1') }}">{{ t('returns.address.option1') }}</option>
                  <option value="value2" data-value="{{ t('returns.address.value2') }}">{{ t('returns.address.option2') }}</option>
                  <option value="value3" data-value="{{ t('returns.address.value3') }}">{{ t('returns.address.option3') }}</option>
                </select>
                <div class="address"></div>
              </div>
            @endif
            {!! t('returns.content', ['websitename' => $website_name, 'address' => $placeholders['address'], 'email' => $placeholders['email'],
            'phone' => $placeholders['phone'], 'number' => $placeholders['number'], 'company' => $placeholders['company']]) !!}
            @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
        </div>
    </div>
</div>
@endsection
