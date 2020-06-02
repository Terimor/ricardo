<template>
  <div>
    <div class="page-title">{{ vc_upsells_title }}</div>
    <div class="page-note" v-html="vc_upsells_note"></div>
    
    <el-main v-if="isLoading" v-loading.fullscreen.lock="isLoading">
      <el-row :gutter="20">
        <el-col :span="16"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="8"><div class="grid-content bg-purple"></div></el-col>
      </el-row>

      <el-row :gutter="20">
        <el-col :span="8"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="8"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="4"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="4"><div class="grid-content bg-purple"></div></el-col>
      </el-row>

      <el-row :gutter="20">
        <el-col :span="4"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="16"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="4"><div class="grid-content bg-purple"></div></el-col>
      </el-row>

      <el-row :gutter="20">
        <el-col :span="16"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="8"><div class="grid-content bg-purple"></div></el-col>
      </el-row>

      <el-row :gutter="20">
        <el-col :span="8"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="8"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="4"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="4"><div class="grid-content bg-purple"></div></el-col>
      </el-row>

      <el-row :gutter="20">
        <el-col :span="4"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="16"><div class="grid-content bg-purple"></div></el-col>
        <el-col :span="4"><div class="grid-content bg-purple"></div></el-col>
      </el-row>
    </el-main>

    <div v-if="!isLoading">
      <div class="upsell-subtitle">{{ subtitle }}</div>
      <div class="upsell-title">Congrats on your purchase of {{ product.product_name }}.</div>
      <div class="upsell-subtitle2">{{ subtitle }}</div>
      <div class="upsell-letter" v-html="letter_content"></div>

      <div class="upsell-letter2">
        <center><strong>OK, I Want To Save Money On Electricity And Gas ... But How Do I Get Started?</strong></center><br>

        <center>Normally you'd then have to go out and buy your own biodiesel kit which can cost up to $3000 but not anymore... With this step by step video guide, I'll show you how to set up your own kit at home and get mixing and saving money straight away.</center>
        
        <iframe
          v-if="vimeo_id"
          class="upsell-vimeo"
          :src="'https://player.vimeo.com/video/' + vimeo_id"
          allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen=""
          frameborder="0"></iframe>
        
        <strong>So you see this guide is actually worth hundreds, maybe thousands of dollars because of the amount you'll save in the long run.</strong><br><br>
        But you won't have to pay {{ price20Formatted }}.<br><br>
        Not even {{ price10Formatted }}.<br><br>
        If you download this guide right now, I can let you have it for only <strong>{{ priceFormatted }}</strong><br><br>
        <strong>Why the discount?</strong> Because I have been in your shoes, and now that I have gotten off the grid... I made it my life's mission to spread this method to every family that needs it.<br><br>
        This price is just to cover the research and development costs (this method you are about to discover is refined over 13 months), production and distribution costs.<br><br>
        This investment is tiny considering thousands of dollars you'll be able to save.<br><br>
        And of course, you have absolutely no risk because you are protected by our...
      </div>
    </div>

    <div class="guarantee">
      <img class="guarantee-image" :src="cdn_url + '/assets/images/upsells/guarantee.png'" />

      <div class="guarantee-content">
        <div class="guarantee-title">{{ vc_upsells_guarantee_title }}</div>
        <div class="guarantee-text" v-html="vc_upsells_guarantee_text"></div>
      </div>
    </div>
  
    <div v-if="!isLoading">
      <div class="last-call-title">{{ lcta_title }}</div>
      <div class="last-call-text" v-html="lcta_description"></div>

      <div class="last-call-card">
        <div class="last-call-card-title">{{ vc_upsells_last_call_card_title }}</div>

        <div class="last-call-card-inside">
          <img class="last-call-card-image" :src="imageUrl" />
          <div class="last-call-card-download">{{ vc_upsells_last_call_card_download }}</div>
          <div class="last-call-card-old-price" v-html="vc_upsells_last_call_card_old_price"></div>
          <div class="last-call-card-label-1">{{ vc_upsells_last_call_card_label_1 }}</div>
          <div class="last-call-card-price">{{ priceFormatted }}</div>

          <div
            class="last-call-card-submit"
            @click="addProduct(1)"
          >
            {{ vc_upsells_last_call_card_submit }}
          </div>
          
          <div
            class="last-call-card-label-2"
            @click="addProduct(1)"
          >
            {{ vc_upsells_last_call_card_label_2 }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { t } from '../../../utils/i18n';
  import upsells from '../../../mixins/upsells';
  import { getUppSells } from '../../../services/upsells';

  export default {
    name: 'Step1Vrtl',

    mixins: [upsells],

    props: ['id', 'discount', 'accessoryStep'],

    data: () => ({
      cdn_url: js_data.cdn_url,
      product: js_data.product,
      upsellPrices: {},
      description: null,
      letter_content: null,
      subtitle: null,
      vimeo_id: null,
      finalPrice: null,
      finalPricePure: null,
      name: null,
      title: null,
      lcta_title: null,
      lcta_description: null,
      price: null,
      price3: null,
      price20: null,
      price10: null,
      priceFormatted: null,
      price3Formatted: null,
      price20Formatted: null,
      price10Formatted: null,
      imageUrl: null,
      isLoading: false,
    }),

    computed: {
      vc_upsells_title: () => t('vc_upsells.title'),
      vc_upsells_note: () => t('vc_upsells.note'),
      vc_upsells_guarantee_title: () => t('vc_upsells.guarantee.title'),
      vc_upsells_guarantee_text: () => t('vc_upsells.guarantee.text'),
      vc_upsells_last_call_card_download: () => t('vc_upsells.last_call_card.download'),
      vc_upsells_last_call_card_label_1: () => t('vc_upsells.last_call_card.label_1'),
      vc_upsells_last_call_card_submit: () => t('vc_upsells.last_call_card.submit'),
      vc_upsells_last_call_card_label_2: () => t('vc_upsells.last_call_card.label_2'),
      vc_upsells_last_call_card_label_3: () => t('vc_upsells.last_call_card.label_3'),
      vc_upsells_last_call_card_title () { 
        const name = this.name;
        return t('vc_upsells.last_call_card.title', {'PRODUCT': name})
      },
      vc_upsells_last_call_card_old_price () {
        const price3 = this.price3Formatted;
        return t('vc_upsells.last_call_card.old_price', {'amount': `<span class="value">${price3}</span>`});
      }
    },

    mounted() {
      this.isLoading = true;
      getUppSells(this.id, 3, this.accessoryStep)
        .then(res => {
          if (res && res.data) {
            this.upsellPrices = res.data.upsell.upsellPrices;
            this.name = res.data.upsell.long_name;
            this.lcta_title = res.data.upsell.upsell_lcta_title;
            this.lcta_description = res.data.upsell.upsell_lcta_description;

            this.description = this.id === js_data.product.id && res.data.upsell.upsell_plusone_text
              ? res.data.upsell.upsell_plusone_text
              : res.data.upsell.description;

            this.letter_content = res.data.upsell_letter;
            this.subtitle = res.data.upsell_subtitle;
            this.title = res.data.upsell_title;
            this.vimeo_id = res.data.upsell_video_id;

            this.imageUrl = res.data.upsell.image;
            this.priceFormatted = this.upsellPrices['1'] && this.upsellPrices['1'].price_text || '';
            this.price = this.upsellPrices['1'] && this.upsellPrices['1'].price || 0;
            this.price20 = this.upsellPrices['20'] && this.upsellPrices['20'].price || 0;
            this.price20Formatted = this.upsellPrices['20'] && this.upsellPrices['20'].price_text || 0;
            this.price10 = this.upsellPrices['10'] && this.upsellPrices['10'].price || 0;
            this.price10Formatted = this.upsellPrices['10'] && this.upsellPrices['10'].price_text || 0;
            this.price3 = this.upsellPrices['3'] && this.upsellPrices['3'].price || 0;
            this.price3Formatted = this.upsellPrices['3'] && this.upsellPrices['3'].price_text || 0;
            this.finalPrice = this.upsellPrices['1'] && this.upsellPrices['1'].price_text || '';
            this.finalPricePure = this.upsellPrices['1'] && this.upsellPrices['1'].price || 0;
          }
        })
        .then(() => {
          this.isLoading = false;
        });
    },

    methods: {
      addProduct(quantity) {
        quantity = +quantity;

        if (quantity === 1) {
          this.finalPrice = this.upsellPrices && this.upsellPrices['1'] && this.upsellPrices['1'].price_text || '';
          this.finalPricePure = this.upsellPrices && this.upsellPrices['1'] && this.upsellPrices['1'].price || 0;
        } else {
          this.finalPrice = this.upsellPrices && this.upsellPrices['2'] && this.upsellPrices['2'].price_text || '';
          this.finalPricePure = this.upsellPrices && this.upsellPrices['2'] && this.upsellPrices['2'].price || 0;
        }

        this.addToCart(quantity);
      }
    }
  };
</script>
