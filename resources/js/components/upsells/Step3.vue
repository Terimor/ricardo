<template>
  <div class="step-3">
    <div v-if="id && name">
      <h3>
        <span class="orange">
          {{ cantLeaveText }}
        </span>
      </h3>
      <h5>
        {{ lastChanceText }} {{ name }} {{ forJustText }}
        <span class="price">
          {{ priceFormatted }}!
        </span>
      </h5>
      <div class="upsells-component__item">
        <div
          class="benefits"
          v-html="description"
        ></div>
        <div class="image">
          <img
            :src="imageUrl"
            :alt="`image for ${name}`"
          >
        </div>
      </div>
    </div>
    <div class="upsells-component__bot">
      <select-field
        v-if="upsellPrices"
        :standart="true"
        :label="`${pleaseChooseText}:`"
        v-model="quantity"
        :list="selectList"
      />
      <green-button
        :is-loading="isLoading"
        @click="add(quantity)"
      >
        {{ addToCartText }}
      </green-button>
    </div>
  </div>
</template>

<script>
import { t } from '../../utils/i18n';
import upsells from '../../mixins/upsells';
import { getUppSells } from '../../services/upsells';
export default {
  name: 'Step3',
  mixins: [upsells],
  props: ['id', 'accessoryStep', 'accessoryList'],
  data () {
    return {
      quantity: 1,
      upsellPrices: null,
      finalPrice: null,
      finalPricePure: null,
      name: null,
      description: null,
      priceFormatted: null,
      price: null,
      imageUrl: null,
      isLoading: false,
    }
  },

  computed: {
    cantLeaveText: () => t('upsells.cant_leave'),
    lastChanceText: () => t('upsells.last_chance'),
    forJustText: () => t('upsells.for_just'),
    addToCartText: () => t('upsells.add_to_cart'),
    pleaseChooseText: () => t('upsells.choose'),

    selectList() {
      const data = Array(Number(this.selectedProductData.quantity || this.selectedProductData.deal || 1) + this.selectedProductUpsellsQuantity).fill('').slice(0, 5).map((item, index) => {
        const value = index + 1

        return item = {
          label: `${value}x ${this.name} - ${this.upsellPrices && this.upsellPrices[value].price_text}`,
          text: `${value}x ${this.name} - ${this.upsellPrices && this.upsellPrices[value].price_text}`,
          value: value,
        }
      });

      return data;
    },

    selectedProductUpsellsQuantity() {
      let productUpsellsQuantity = 0;

      if (this.id === js_data.product.id) {
        return 0;
      }

      if (this.accessoryList.length > 0) {
        productUpsellsQuantity = this.accessoryList.filter(order => order.id === js_data.product.id).length;
      }

      return productUpsellsQuantity;
    },

    selectedProductData() {
      let selectedProductData = {};

      try {
        const data = localStorage.getItem('selectedProductData') || localStorage.getItem('saved_form');
        selectedProductData = JSON.parse(data) || {};
      }
      catch (err) {

      }

      return selectedProductData;
    }
  },

  mounted() {
    this.isLoading = true;
    getUppSells(this.id, this.selectedProductData.quantity || this.selectedProductData.deal || 1, this.accessoryStep)
      .then(res => {
        if (res && res.data) {
          this.upsellPrices = res.data.upsell.upsellPrices;
          this.name = res.data.upsell.long_name;

          this.description = this.id === js_data.product.id && res.data.upsell.upsell_plusone_text
            ? res.data.upsell.upsell_plusone_text
            : res.data.upsell.description;

          this.imageUrl = res.data.upsell.upsell_hero_image;
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
    add(quantity) {
      quantity = +quantity;

      this.finalPrice = this.upsellPrices && this.upsellPrices[quantity] && this.upsellPrices[quantity].price_text || '';
      this.finalPricePure = this.upsellPrices && this.upsellPrices[quantity] && this.upsellPrices[quantity].price || 0;

      this.addToCart(quantity);
    }
  }
};
</script>

<style lang="scss">
.upsells-component {
  .step-3 {
    h3 {
      color: #d4513a;
      margin-bottom: 0;
      text-align: center;
    }

    h5 {
      text-transform: uppercase;
    }

    .price {
      color: #0d840d;
    }

    .content-with-image {
      position: relative;

      img {
        width: 100%;
        height: auto;
      }

      .text-container {
        width: 100%;
        height: 100%;

        & > h2 {
          font-size: 24px;
        }

        .right {
          text-align: right;
          padding-bottom: 10px;

          img {
            width: 50%;
          }
        }
      }
    }

    .upsells-component__bot {
      flex-direction: column;
      align-items: center;

      & > .green-button-animated {
        width: 80%;
        max-width: 100%;
      }

      & > .select {
        width: 40%;
        margin-bottom: 20px;
      }
    }

    @media screen and (max-width: 768px) {
      .content-with-image {
        .last {
          font-size: 15px;
        }

        h2 {
          margin: 0;
          font-size: 18px;
        }
      }

      .upsells-component__bot {
        & > .select {
          width: 100%;
        }

        & > .green-button-animated {
          width: 100%;
        }

        i {
          font-size: 14px;
        }
      }
    }
  }
}
</style>
