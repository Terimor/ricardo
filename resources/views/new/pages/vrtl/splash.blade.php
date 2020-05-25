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
    href="{{ mix_cdn('assets/css/new/pages/vrtl/splash.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('content')
  <div class="splash-virtual-page">
    <div class="splash-virtual-header-notification">
      <span id="splashVirtualImportant">Important:</span>
      Due to such high demand this massivaly discouunted price may be taken down anytime soon. I can only guarantee this discount for a limited time. Act now below to ensure you do not miss out.
    </div>

    <div class="splash-virtual-page-main">
      <div class="splash-virtual-wait">Wait!!!</div>
      
      <div class="text-center">
        <div class="splash-virtual-save">
          Save
          <span id="spalshVirtualPrice">$12</span>
          Right now
        </div>
      </div>

      <div class="splash-virtual-add-msg">Get the entire <span id="splashVirtualProductName">"Free power secrets"</span> package + Free Support for <span class="underlined">Only $37</span></div>
      
      <div class="text-center">
        <img src="{{ $cdn_url }}/assets/images/splash/product.png" alt="" class="splash-virtual-product-img">
        
        <div class="splash-virtual-discount-text">Discount Guaranteed For The Next</div>

        <div class="splash-virtual-timer">7:36</div>

        <div class="splash-virtual-discount">Discounted Price: $37</div>

        <div class="splash-virtual-discount-btn">Claim your discount</div>

        <div class="splash-virtual-discount-link">Claim Your $12 Discount Now!</div>

        <div class="splash-virtual-discount-secure-text"><span>- Instant Access</span> <span class="ml-3">- Safe and Secure Payment</span>  <span class="ml-3">- Satisfaction Guaranteed</span></div>
      </div>

      <hr class="my-4">

      <div class="splash-virtual-guarantee-block">
        <div class="guarantee-img-wrap">
          <img src="{{ $cdn_url }}/assets/images/splash/guarantee.png" alt="" class="guarantee-img">
        </div>

        <div class="guarantee-text">
          <p>If you don't think that the resources, advice, tips, and cost-saving strategies inside this guide lie up to my promises... or if this doesn't save you any times more than you paid for it... heck, even if don't like how the font looks...</p>
          <p>At ANY TIME for the next <b>60 days</b> just let me know and I'll refund your tiny investment right away with no questions asked.</p>
          <p>This waya, all the risk is on me...</p>
          <p>And you'll experience what it is like to have you very own "free power secret" on a tight budget and limited space!</p>
        </div>
      </div>

      <div class="splash-virtual-what-getting-block">
        <h4 class="getting-block-title">Here's What You're Getting:</h4>
        
        <div class="text-center">
          <img class="img-fluid" src="{{ $cdn_url }}/assets/images/splash/product-use.png" alt="">
        </div>
        
        <div class="px-5">
          <p class="mt-5"><b>The Step-By-Step Guide To Build Your Fuel Generator In Any Spacee - </b> Everything you need to build free power secret guaranteed! - Video Guides and PDF blueprints, step-bt-step, how to instructions, advice, strategies.</p>

          <p class="mt-4"><img src="{{ $cdn_url }}/assets/images/splash/check-icon.png" alt=""> <b>Comprehensive materials list covering exactly what you nee, and where you ca get it.</b></p>

          <p class="mt-4"><b>You'll find out where you should build your Free Power Secret,</b> and now one MICROSCOPIC adjustment can doule the amount of fuel it produces...</p>

          <p class="mt-4"><img src="{{ $cdn_url }}/assets/images/splash/check-icon.png" alt=""><b>Get instant access.</b> You can download everything immediatly after purchase.</p>

          <p class="mt-4">A time-sensitive discount price when you order now. (I reserve to <span class="underlined">end this discount price and the bonus at anytime.</span> If you want this deal, you'll have to order today)</p>

          <p class="mt-4"><b>Remember, you are protected by my 100% money back policy.</b> If you are not stisfied for any reason within 60 days, I guarantee I will refund each and every penny you paid.</p>
        </div>
        
        <div class="text-center">
          <div class="splash-virtual-discount-text mt-4">Discount Guaranteed For The Next</div>

          <div class="splash-virtual-timer">7:36</div>

          <div class="splash-virtual-discount">Discounted Price: $37</div>

          <div class="splash-virtual-discount-btn">Claim your discount</div>

          <div class="splash-virtual-discount-link">Claim Your $12 Discount Now!</div>

          <div class="splash-virtual-discount-secure-text"><div class="italic">This is a special launch offer price and I reserve the right to end it anytime</div></div>
        </div>
      </div>

      <div class="splash-virtual-materials-notification">
        <div class="materials-notification-img-wrap">
          <img src="{{ $cdn_url }}/assets/images/splash/file-types-icon.png" alt="" class="materials-notification-img">
        </div>

        <div class="splash-virtual-materials-notification-title">No shipping costs saves you money</div>
        <div class="splash-virtual-materials-notification-descr">All materials are DIGITAL and send to you instantly in Video and PDF formats. Any computer can use it! That means you can start TODAY!</div>
      </div>
    </div>

    <div class="splash-virtual-page-footer">
      <img src="{{ $cdn_url }}/assets/images/splash/clickbank.png" alt="" id="splashVirtualFooterClickbank">

      <div class="splash-virtual-page-footer-content">
        <div class="splash-virtual-secure-imgs">
          <div class="secure-img-wrap"><img src="{{ $cdn_url }}/assets/images/splash/ssl-checkout.png" alt=""></div>
          <div class="secure-img-wrap"><img src="{{ $cdn_url }}/assets/images/splash/dmca-protected.png" alt=""></div>
          <div class="secure-img-wrap"><img src="{{ $cdn_url }}/assets/images/splash/ssl-security.png" alt=""></div>
        </div>

        <div class="splash-virtual-footer-text">
          <p>ClickBank is the retailer of products on this site. CLICKBANK is a registered trademark of Click Sales Inc., a Delaware corporation located at 1444S. Entertainment Ave., Suite 410 Boise, ID 83709, USA and used by permission. Clickbank's role as retailer does not constitute and endorsement, approval or review of these products or any claim, statement or opinion used in promotion of these products.</p>

          <p>Testimonials, case studies, and examples found on this page are results that have been forwarded to us by users of "Tyranny Liberator" products and related products, and may not reflect the typical purchaser's experience, may not apply to the average person and are not intended to represent or guarantee that anyone will chieve the same or similar results.</p>
        </div>

        <div class="splash-virtual-footer-copyright">Free Power Secrets</div>
      </div>
    </div>
  </div>
@endsection
