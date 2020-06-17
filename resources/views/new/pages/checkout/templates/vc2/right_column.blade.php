<div class="right-column">
  
  <template v-if="!isPurchasAlreadyExists">
    <div class="title">
      <i class="fa fa-shopping-cart"></i>
      {!! t('vc2.summary.title') !!}
    </div>

    @include('new.pages.checkout.form.installments')

    <div class="product">
      <div class="product-summary">
        <div class="name">{!! $product->product_name !!}</div>
        
        <div class="text">
          <i class="fa fa-download"></i>
          {!! t('vc2.product.text') !!}
        </div>
        
        <div class="total"><span v-cloak>@{{ xprice_total_text }}</span></div>
      </div>

      <div class="product-image-wrap">
        <img :src="productImage" alt="" class="product-image">
      </div>
    </div>

    <div class="access">
      {!! t('vc2.product.access') !!}
    </div>    
  </template>

  <purchas-already-exists v-else />

</div>
