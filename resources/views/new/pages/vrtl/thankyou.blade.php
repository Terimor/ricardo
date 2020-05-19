@extends('layouts.app', ['product' => $product])

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

      <div class="header-product-image" style="background-image: url({{ $product->image && count($product->image) > 0 ? $product->image[0] : '' }})"></div>
      
      <div class="header-main">
        <h2 class="header-product-title">{{ t('thankyou.vrtl.congratulations', ['product' => $product->product_name]) }}</h2>

        <p class="header-product-payment-msg">"{{ t('thankyou.vrtl.charge_msg', ['descriptor' => $product->product_name]) }}"</p>

        <p class="header-product-video-info-msg">
          @php $courses_count = $product->sale_videos && count($product->sale_videos) > 0 ? count($product->sale_videos) : ''; @endphp

          {{ t('thankyou.vrtl.video_msg', ['courses' => $courses_count, 'product' => $product->product_name]) }} 
        </p>
      </div>

      <div class="header-main-down-icon">
        <font-awesome-icon :icon="angleDown" :style="{ color: '#666' }" />
      </div>

    </div>

    <div class="vrtl-thank-you-page-main">
      <div class="main-section-content">
        <h3 class="main-section-title">{{ t('thankyou.vrtl.download') }}</h3>
        
        <div class="section-tabs">
          <b-tabs>
            <b-tab active>
              <template v-slot:title>
                <div class="section-tab section-tab-active">{{ $product->product_name }}</div>
              </template>

              <div class="section-content">
                @if($product->sale_files && count($product->sale_files) > 0)
                  <div class="product-files-sect">
                    <h6 class="product-files-title">{{ t('thankyou.vrtl.files_intro', ['product' => $product->product_name]) }}</h6>
                  </div>

                  @foreach($product->sale_files as $index => $file)
                    <div class="product-file-collapse-head" v-b-toggle="'product-file-collapse-{{ $index }}'">
                      <span><font-awesome-icon :icon="caretRight" /></span>
                      <span>{{ $file['title'] }}</span>
                    </div>

                    <b-collapse id="product-file-collapse-{{ $index }}">
                      <div class="product-file-collapse-content">
                        <div class="product-files-list">
                          <a href="{{ $file['url'] }}" target="_blank" class="product-file">{{ $file['title'] }}</a>
                        </div>
                      </div>
                    </b-collapse>
                  @endforeach
                @endif
                
                @if($product->sale_videos && count($product->sale_videos) > 0)
                  <div class="product-videos-sect">
                    <h6 class="product-videos-title">{{ t('thankyou.vrtl.videos') }}</h6>
                    <p class="product-videos-descr">{{ t('thankyou.vrtl.videos_subtitle', ['product' => $product->product_name]) }}</p>

                    <div class="product-videos-collapse-help-msg">{{ t('thankyou.vrtl.toggle_videos') }}</div>

                    @foreach($product->sale_videos as $index => $video)
                      <div class="product-file-collapse-head" v-b-toggle="'product-video-collapse-{{ $index }}'">
                        <span><font-awesome-icon :icon="caretRight" /></span>
                        <span>{{ @$video['title'] }}</span>
                      </div>

                      <b-collapse id="product-video-collapse-{{ $index }}">
                        <div class="product-file-collapse-content">
                          <iframe width="100%" height="300" src="{{ $video['url'] }}" frameborder="0" allow="autoplay;fullscreen" allowfullscreen></iframe>
                        </div>
                      </b-collapse>
                    @endforeach
                  </div>
                @endif
              </div>
            </b-tab>

            @if($product->free_files && count($product->free_files) > 0)
              <b-tab>
                <template v-slot:title>
                  <div class="section-tab">{{ t('thankyou.vrtl.bonuses') }}</div>
                </template>

                <div class="section-content">
                  @foreach($product->free_files as $index => $file)
                    <div class="product-file-collapse-head" v-b-toggle="'product-file-collapse-{{ $index }}'">
                      <span><font-awesome-icon :icon="caretRight" /></span>
                      <span>{{ $file['title'] }}</span>
                    </div>

                    <b-collapse id="product-file-collapse-{{ $index }}">
                      <div class="product-file-collapse-content">
                        <div class="product-files-list">
                          <a href="{{ $file['url'] }}" target="_blank" class="product-file">{{ $file['title'] }}</a>
                        </div>
                      </div>
                    </b-collapse>
                  @endforeach
                </div>
              </b-tab>
            @endif
          </b-tabs>
        </div>

        <div class="thank-you-page-email">{{ t('thankyou.vrtl.email') }}: <a class="thank-you-page-email-link" href="mailto:support@freepowersecret.com">support@freepowersecret.com</a></div>

        <div class="footer-main-down-icon">
          <font-awesome-icon :icon="angleDown" :style="{ color: '#666' }" />
        </div>
      </div>
    </div>
  </div>
@endsection
