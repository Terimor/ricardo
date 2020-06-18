<template>
  <div>
    <div class="page-title" v-if="!upsellDiscountOffered">{{ vc_upsells_title }}</div>
    <div class="page-title" v-if="upsellDiscountOffered">{{ vc_upsells_title2 }}</div>
    <div class="page-note" v-html="vc_upsells_note"></div>
    
    <el-main v-if="isLoading || isRootLoading" v-loading.fullscreen.lock="isLoading || isRootLoading">
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
      <div class="upsell-title">{{ vc_upsells_congrats_title }}</div>
      <div class="upsell-subtitle2">{{ lcta_title }}</div>
      <!-- <div class="upsell-letter" v-html="description"></div> -->

      <div class="upsell-letter2">
        <center v-html="description"></center><br>

        <iframe
          v-if="vimeo_id"
          class="upsell-vimeo"
          :src="'https://player.vimeo.com/video/' + vimeo_id"
          allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen=""
          frameborder="0"></iframe>
        
        <div v-html="lcta_description"></div>
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
      <div class="last-call-title">{{ title }}</div>

      <div class="last-call-card">
        <div class="last-call-card-title">{{ vc_upsells_last_call_card_title }}</div>

        <div class="last-call-card-inside">
          <img class="last-call-card-image" :src="imageUrl" />
          <div class="last-call-card-download">{{ vc_upsells_last_call_card_download }}</div>
          <div class="last-call-card-old-price" v-html="vc_upsells_last_call_card_old_price"></div>
          <div class="last-call-card-label-1">{{ vc_upsells_last_call_card_label_1 }}</div>
          <div class="last-call-card-price" v-if="!upsellDiscountOffered">{{ priceFormatted }}</div>
          <div class="last-call-card-price" v-if="upsellDiscountOffered">{{ price30dFormatted }}</div>

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

        <div
          class="last-call-card-label-3"
          @click="nextAccessoryStep"
        >
          {{ vc_upsells_last_call_card_label_3 }}
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

    props: {
      id: String, 
      discount: Number, 
      accessoryStep: Number, 
      nextAccessoryStep: { type: Function },
      setUpsellDiscountAdded: { type: Function },
      upsellDiscount: Boolean,
      upsellDiscountOffered: Boolean,
      isRootLoading: Boolean
    },

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
      price30d: null,
      price10: null,
      priceFormatted: null,
      price3Formatted: null,
      price20Formatted: null,
      price30dFormatted: null,
      price10Formatted: null,
      imageUrl: null,
      isLoading: false,
    }),

    computed: {
      vc_upsells_congrats_title () {
        const name = this.product.product_name || '';
        return t('vc_upsells.congrats_title', {'product': name});
      },
      vc_upsells_title: () => t('vc_upsells.title'),
      vc_upsells_title2 () {
        const price30d = this.price30dFormatted || '';
        const price = this.priceFormatted || '';

        return t('vc_upsells.title2', {'count': price30d, 'amount': price})
      },
      vc_upsells_note: () => t('vc_upsells.note'),
      vc_upsells_guarantee_title: () => t('vc_upsells.guarantee.title'),
      vc_upsells_guarantee_text: () => t('vc_upsells.guarantee.text'),
      vc_upsells_last_call_card_download: () => t('vc_upsells.last_call_card.download'),
      vc_upsells_last_call_card_label_1: () => t('vc_upsells.last_call_card.label_1'),
      vc_upsells_last_call_card_submit: () => t('vc_upsells.last_call_card.submit'),
      vc_upsells_last_call_card_label_2: () => t('vc_upsells.last_call_card.label_2'),
      vc_upsells_last_call_card_label_3: () => t('vc_upsells.last_call_card.label_3'),
      vc_upsells_last_call_card_title () { 
        const name = this.name || '';
        return t('vc_upsells.last_call_card.title', {'PRODUCT': name})
      },
      vc_upsells_last_call_card_old_price () {
        const price3 = this.price3Formatted || '';
        return t('vc_upsells.last_call_card.old_price', {'amount': `<span class="value">${price3}</span>`});
      },
      vc_upsells_last_call_card_label_3 () {
        const productName = this.name || '';
        return t('vc_upsells.last_call_card.label_3', {'product': productName})
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

            this.letter_content = res.data.upsell.upsell_letter;
            this.subtitle = res.data.upsell.upsell_subtitle;
            this.title = res.data.upsell.upsell_title;
            this.vimeo_id = res.data.upsell.upsell_video_id;

            this.imageUrl = res.data.upsell.image;
            this.priceFormatted = this.upsellPrices['1'] && this.upsellPrices['1'].price_text || '';
            this.price = this.upsellPrices['1'] && this.upsellPrices['1'].price || 0;
            this.price20 = this.upsellPrices['20'] && this.upsellPrices['20'].price || 0;
            this.price20Formatted = this.upsellPrices['20'] && this.upsellPrices['20'].price_text || 0;
            this.price30d = this.upsellPrices['30d'] && this.upsellPrices['30d'].price || 0;
            this.price30dFormatted = this.upsellPrices['30d'] && this.upsellPrices['30d'].price_text || 0;
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

        if (this.upsellDiscountOffered) {
          this.setUpsellDiscountAdded();
        }

        this.addToCart(quantity);
      }
    }
  };
</script>
