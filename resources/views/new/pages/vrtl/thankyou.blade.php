@extends('layouts.app', ['product' => $product, 'loadVue' => true])

@section('title', $page_title)


@section('js_deps')
  <script type="text/javascript">
    js_deps.show([
      'page-styles'
    ]);
  </script>
@endsection


@section('styles')
  <link
    href="{{ mix_cdn('assets/css/new/pages/vrtl/thankyou.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts')
  <script
    src="{{ mix_cdn('assets/js/new/pages/vrtl/thankyou.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content')
  <div id="thank-you-vrtl" class="vrtl-thank-you-page">
    <div class="vrtl-thank-you-page-header">

      <div class="header-product-image" style="background-image: url({{ !empty($product->image)  && is_array($product->image)? $product->image[0] : '' }})"></div>

      <div class="header-main">
        <h2 class="header-product-title">{{ t('thankyou.vrtl.congratulations', ['product' => $product->product_name]) }}</h2>

        <p class="header-product-payment-msg">"{{ t('thankyou.vrtl.charge_msg', ['code' => $product->product_name]) }}"</p>

        <p class="header-product-video-info-msg">
          @php $courses_count = !empty($product->sale_videos) && is_array($product->sale_videos) ? count($product->sale_videos) : ''; @endphp

          {{ t('thankyou.vrtl.video_msg', ['count' => $courses_count, 'product' => $product->product_name]) }}
        </p>
      </div>

      <div class="header-main-down-icon"></div>

    </div>

    <div class="vrtl-thank-you-page-main">
      <div class="main-section-content">
        <h3 class="main-section-title">{{ t('thankyou.vrtl.download') }}</h3>

        <div class="section-tabs">
          <div class="section-tab" :class="{ 'section-tab-active': tabActive === 'PRODUCT' }" @click="setTab('PRODUCT', $event)">{{ $product->product_name }}</div>

          @if(!empty($product->free_files) && is_array($product->free_files))
            <div class="section-tab" :class="{ 'section-tab-active': tabActive === 'BONUSES' }" @click="setTab('BONUSES', $event)">{{ t('thankyou.vrtl.bonuses') }}</div>
          @endif
        </div>

        <div class="section-content" v-show="tabActive === 'PRODUCT'">
          @if((!empty($product->sale_files) && is_array($product->sale_files)) || (!empty($product->upsells_files) && is_array($product->upsells_files)))
            <div class="product-files-sect">
              <h6 class="product-files-title">{{ t('thankyou.vrtl.files_intro', ['product' => $product->product_name]) }}</h6>
            </div>
          @endif

          @if(!empty($product->sale_files) && is_array($product->sale_files))
            @foreach($product->sale_files as $index => $file)
              <div @click="collapseHeadClick" class="product-file-collapse-head active">
                {{ $file['title'] }}
              </div>

              <div class="product-file-collapse-content">
                <div class="product-files-list">
                  <div class="product-file">
                    @php $fileName = explode(".", $file['url']); @endphp
                    @if(end($fileName) === 'pdf')
                      <div @click="productFilePreviewClick" class="product-file-image-preview" style="background-image: url({{ $file['image'] }})"></div>

                      <div class="product-file-pdf-preview">
                        <embed src= "{{ $file['url'] }}" width= "100%" height= "350">
                      </div>
                    @endif

                    <a href="{{ $file['url'] }}" target="_blank">{{ t('thankyou.vrtl.download_file') }}: {{ $file['title'] }}</a>
                  </div>
                </div>
              </div>
            @endforeach
          @endif

          @if(!empty($product->upsells_files) && is_array($product->upsells_files))
            @foreach($product->upsells_files as $index => $file)
              <div @click="collapseHeadClick" class="product-file-collapse-head active">
                {{ $file['title'] }}
              </div>

              <div class="product-file-collapse-content">
                <div class="product-files-list">
                  <div class="product-file">
                    @php $fileName = explode(".", $file['url']); @endphp
                    @if(end($fileName) === 'pdf')
                      <div @click="productFilePreviewClick" class="product-file-image-preview" style="background-image: url({{ $file['image'] }})"></div>

                      <div class="product-file-pdf-preview">
                        <embed src= "{{ $file['url'] }}" width= "100%" height= "350">
                      </div>
                    @endif

                    <a href="{{ $file['url'] }}" target="_blank">{{ t('thankyou.vrtl.download_file') }}: {{ $file['title'] }}</a>
                  </div>
                </div>
              </div>
            @endforeach
          @endif
          
          <div class="product-videos-sect">
            @if((!empty($product->sale_videos) && is_array($product->sale_videos)) || !empty($product->upsells_videos) && is_array($product->upsells_videos))
              <h6 class="product-videos-title">{{ t('thankyou.vrtl.videos') }}</h6>
              <p class="product-videos-descr">{{ t('thankyou.vrtl.videos_subtitle', ['product' => $product->product_name]) }}</p>

              <div class="product-videos-collapse-help-msg">{{ t('thankyou.vrtl.toggle_videos') }}</div>
            @endif

            @if(!empty($product->sale_videos) && is_array($product->sale_videos))
              @foreach($product->sale_videos as $index => $video)
                <div @click="collapseHeadClick" class="product-file-collapse-head active">
                  {{ $video['title'] }}
                </div>

                <div class="product-file-collapse-content">
                  <iframe width="100%" height="300" src="{{ $video['url'] }}" frameborder="0" allow="autoplay;fullscreen" allowfullscreen></iframe>
                </div>
              @endforeach
            @endif

            @if(!empty($product->upsells_videos) && is_array($product->upsells_videos))
              @foreach($product->upsells_videos as $index => $video)
                <div @click="collapseHeadClick" class="product-file-collapse-head active">
                  {{ $video['title'] }}
                </div>

                <div class="product-file-collapse-content">
                  <iframe width="100%" height="300" src="{{ $video['url'] }}" frameborder="0" allow="autoplay;fullscreen" allowfullscreen></iframe>
                </div>
              @endforeach
            @endif
          </div>
        </div>

        @if(!empty($product->free_files) && is_array($product->free_files))
          <div class="section-content" v-show="tabActive === 'BONUSES'">
            @foreach($product->free_files as $index => $file)
              <div @click="collapseHeadClick" class="product-file-collapse-head active">
                {{ $file['title'] }}
              </div>

              <div class="product-file-collapse-content">
                <div class="product-files-list">
                  <div class="product-file">
                    @php $fileName = explode(".", $file['url']); @endphp
                    @if(end($fileName) === 'pdf')
                      <div @click="productFilePreviewClick" class="product-file-image-preview" style="background-image: url({{ $file['image'] ?? $file['image'] }})"></div>

                      <div class="product-file-pdf-preview">
                        <embed src= "{{ $file['url'] }}" width= "100%" height= "350">
                      </div>
                    @endif

                    <a href="{{ $file['url'] }}" target="_blank">{{ t('thankyou.vrtl.download_file') }}: {{ $file['title'] }}</a>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif

        <div class="thank-you-page-email">{{ t('thankyou.vrtl.email') }}: <a class="thank-you-page-email-link" href="mailto:support@freepowersecret.com">support@freepowersecret.com</a></div>

        <div class="footer-main-down-icon"></div>
      </div>
    </div>
  </div>
  @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
@endsection
