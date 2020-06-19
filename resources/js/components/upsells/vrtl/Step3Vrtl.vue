<template>
  <div>
    <div
      class="order-upgraded">
      {{ vc_upsells_order_upgraded }}
    </div>

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
      <div class="page-title">{{ lcta_title }}</div>
      <div class="upsell-long-name">{{ name }}</div>
      
      <div class="upsell-last-call-text" v-html="lcta_description"></div>
      
      <div class="last-call-card">
        <div class="last-call-card-title">{{ vc_upsells_last_call_card_title }}</div>
      
        <div class="last-call-card-inside">
          <img class="last-call-card-image" :src="imageUrl" />
      
          <div class="last-call-card-download">{{ vc_upsells_last_call_card_download }}</div>
          <div class="last-call-card-label-1">{{ vc_upsells_last_call_card_label_1 }}</div>
          <div class="last-call-card-price">{{ priceFormatted }}</div>
      
          <div
            class="last-call-card-submit"
            @click.once="addProduct(1)"
            :key="buttonKey"
          >
            {{ vc_upsells_last_call_card_submit }}
          </div>
      
          <div
            class="last-call-card-label-2"
            @click.once="addProduct(1)"
            :key="buttonKey"
          >
            {{ vc_upsells_last_call_card_label_2 }}
          </div>
        </div>
      </div>

      <div
        class="last-call-card-label-3"
        @click.once="nextAccessoryStep"
        :key="buttonKey"
      >
        {{ vc_upsells_last_call_card_label_3 }}
      </div>
    </div>
  </div>
</template>

<script>
  import { t } from '../../../utils/i18n';
  import upsells from '../../../mixins/upsells';
  import { getUppSells } from '../../../services/upsells';

  export default {
    name: 'Step3Vrtl',

    mixins: [upsells],

    props: {
      id: String, 
      discount: Number, 
      accessoryStep: Number, 
      nextAccessoryStep: { type: Function },
      isRootLoading: Boolean,
      buttonKey: Number
    },

    data: () => ({
      cdn_url: js_data.cdn_url,
      product: js_data.product,
      upsellPrices: {},
      description: null,
      finalPrice: null,
      finalPricePure: null,
      name: null,
      title: null,
      lcta_title: null,
      lcta_description: null,
      price: null,
      priceFormatted: null,
      imageUrl: null,
      isLoading: false,
    }),

    computed: {
      vc_upsells_order_upgraded: () => t('vc_upsells.order_upgraded'),
      vc_upsells_last_call_card_title () {
        const title = this.title || '';
        return t('vc_upsells.last_call_card.title', {'product': title});
      },
      vc_upsells_last_call_card_download: () => t('vc_upsells.last_call_card.download'),
      vc_upsells_last_call_card_label_1: () => t('vc_upsells.last_call_card.label_1'),
      vc_upsells_last_call_card_submit: () => t('vc_upsells.last_call_card.submit'),
      vc_upsells_last_call_card_label_2: () => t('vc_upsells.last_call_card.label_2'),
      vc_upsells_last_call_card_label_3 () {
        const productName = this.name || '';
        return t('vc_upsells.last_call_card.label_3', {'product': productName})
      }
    },

    mounted() {
      this.isLoading = true;
      getUppSells(this.id, 2, this.accessoryStep)
        .then(res => {
          if (res && res.data) {
            this.upsellPrices = res.data.upsell.upsellPrices;
            this.name = res.data.upsell.long_name;
            this.title = res.data.upsell.upsell_title;
            this.lcta_title = res.data.upsell.upsell_lcta_title;
            this.lcta_description = res.data.upsell.upsell_lcta_description;

            this.description = this.id === js_data.product.id && res.data.upsell.upsell_plusone_text
              ? res.data.upsell.upsell_plusone_text
              : res.data.upsell.description;

            this.imageUrl = res.data.upsell.image;
            this.priceFormatted = this.upsellPrices['1'] && this.upsellPrices['1'].price_text || '';
            this.price = this.upsellPrices['1'] && this.upsellPrices['1'].price || 0;
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
