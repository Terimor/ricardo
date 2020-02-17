<div class="page-title">
  <template v-if="step === 1">{!! t('vc_upsells.title') !!}</template>
  <template v-if="step === 2">{!! t('vc_upsells.title2', ['count' => '$27', 'amount' => '$37']) !!}</template>
</div>
<div class="page-note">{!! t('vc_upsells.note') !!}</div>
<div class="upsell-subtitle">Tired of paying through the nose for petrol for your car or elctricity for your home?</div>
<div class="upsell-title">Congrats on your purchase of Free Power Secrets.</div>
<div class="upsell-subtitle2">Here you have the chance to Slash Your Power Bill By 68% or More In Less Than 7 Days... Guaranteed!</div>
<div class="upsell-letter">
  <center><i>And this doesn't involve any DIY solar panels or other generators that do not live up to the promise.</i></center><br>
  Dear friend,<br><br>
  Honestly, I'm tired of beign taken for a ride.<br><br>
  My power gas bills were shooting through the roof. I was so anxious when the mailman arrives, and that I was forced to pay hundreds of dollars each month to the Big Energy Companies.<br><br>
  I started researching to see how I could bring the bills down..and discovered companies selling DIY solar panel plans. I thought to myself, "Why not give a try?"<br><br>
  <strong>It Was A Complete Waste Of Time and Money</strong><br><br>
  Here's why: While DIY solar panels do actually work, you cannot build them under $200..or so they advertised. In reality, it would require more than #10,000 to make it work, and if you include in the time and effort spent in making them, it would be much cheaper and easier just to go and buy solar panels at retail prices. Plus, it's actually extremely difficult to build them.<br><br>
  But in my research, I stumbled upon this "Liquid Gold" that helped many families to get off the grid, save thousands every year, and never owe a single red cent to Big Power again.<br><br>
  <strong>What Is This "Liquid Gold"?</strong><br><br>
  Biodiesel.<br><br>
  But why make your own and not just buy regular diesel at the store?<br><br>
  First of all it's a lot cheaper to make it yourself.<br><br>
  The cost of the raw materials you need, usually vegetable oil, are much cheaper than the cost of petro-diesel. Big Oil actually marks the price of diesels.<br><br>
  Right now diesel costs <strong>$4.14 per gallon.</strong><br><br>
  Well... <strong>biodiesel can be made for a measly $0.70 pre gallon.</strong><br><br>
  If you used 1200 gallons of diesel in a year that would cost you $4,800 whereas if you made your own concoction of the liquid gold call biodiesel it would cost you only $840.<br><br>
  <strong>That's yearly saving of $4000!</strong><br><br>
  And you can use this biodiesel to <strong>run your car..even power your home</strong> if you have a diesel generator (which I'm going to tell you where to find these for cheap).<br><br>
  This method has been proven and it works.
</div>
<div class="upsell-letter2">
  <center><strong>OK, I Want To Save Money On Electricity And Gas ... But How Do I Get Started?</strong></center><br>
  <center>Normally you'd then have to go out and buy your own biodiesel kit which can cost up to $3000 but not anymore... With this step by step video guide, I'll show you how to set up your own kit at home and get mixing and saving money straight away.</center>
  <iframe
    class="upsell-vimeo"
    src="https://player.vimeo.com/video/37044888"
    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
    allowfullscreen=""
    frameborder="0"></iframe>
  <strong>So you see this guide is actually worth hundreds, maybe thousands of dollars because of the amount you'll save in the long run.</strong><br><br>
  But you won't have to pay $1000.<br><br>
  Not even $500.<br><br>
  If you download this guide right now, I can let you have it for only <strong>$37.</strong><br><br>
  <strong>Why the discount?</strong> Because I have been in your shoes, and now that I have gotten off the grid... I made it my life's mission to spread this method to every family that needs it.<br><br>
  This price is just to cover the research and development costs (this method you are about to discover is refined over 13 months), production and distribution costs.<br><br>
  This investment is tiny considering thousands of dollars you'll be able to save.<br><br>
  And of course, you have absolutely no risk because you are protected by our...
</div>
<div class="guarantee">
  <img class="guarantee-image" src="{{ $cdn_url . '/assets/images/upsells/guarantee.png' }}" />
  <div class="guarantee-content">
    <div class="guarantee-title">{!! t('vc_upsells.guarantee.title') !!}</div>
    <div class="guarantee-text">{!! t('vc_upsells.guarantee.text') !!}</div>
  </div>
</div>
<div class="last-call-title">"Two Roads Lay Ahead..."</div>
<div class="last-call-text">
  With this guide you have all you need to begin creating your own fuel at home.<br>
  So it's decision time...<br>
  You can click the link below right now and begin saving BIG on fuel costs.<br>
  Get in now and become a positive force in your community while saving hundreds of dollars at the same time...
</div>
<div class="last-call-card">
  <div class="last-call-card-title">{!! t('vc_upsells.last_call_card.title', ['product' => $product->product_name]) !!}</div>
  <div class="last-call-card-inside">
    <img class="last-call-card-image" src="{{ $product->image[0] ?? '' }}" />
    <div class="last-call-card-download">{!! t('vc_upsells.last_call_card.download') !!}</div>
    <div class="last-call-card-old-price">{!! t('vc_upsells.last_call_card.old_price', ['amount' => '<span class="value">$97</span>']) !!}</div>
    <div class="last-call-card-label-1">{!! t('vc_upsells.last_call_card.label_1') !!}</div>
    <div class="last-call-card-price">
      <template v-if="step === 1">$37!</template>
      <template v-if="step === 2">$27!</template>
    </div>
    <div
      class="last-call-card-submit"
      @click="add_upsell">
      {!! t('vc_upsells.last_call_card.submit') !!}
    </div>
    <div
      class="last-call-card-label-2"
      @click="add_upsell">
      {!! t('vc_upsells.last_call_card.label_2') !!}
    </div>
    <div
      class="last-call-card-label-3"
      @click="cancel">
      {!! t('vc_upsells.last_call_card.label_3') !!}
    </div>
  </div>
</div>
