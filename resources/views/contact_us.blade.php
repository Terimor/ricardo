@extends('layouts.app')

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
    href="{{ mix_cdn('assets/css/contact-us.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

@endsection


@section('content')
<div class="contacts">
    <div class="container">
        <div class="contacts__wrapper">
            {!! t('contacts.content', ['websitename' => $website_name, 'address' => $placeholders['address'], 'email' => $placeholders['email'],
            'phone' => $placeholders['phone'], 'number' => $placeholders['number'], 'company' => $placeholders['company']]) !!}
            @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
        </div>
    </div>
</div>
@endsection
