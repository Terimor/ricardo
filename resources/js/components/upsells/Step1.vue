<template>
    <div class="step-1">
      <div v-if="id && name">
        <h3>
          <span class="congrats">
            {{ congratulationsText }}
          </span>
          {{ customerNumberText }}
        </h3>
        <h5>
          {{ thankYouText }}
          <span class="green-up">
            {{ name }}
          </span>
          <span v-if="discount">
            {{ forText }} {{ discount }}% {{ offText }}!
          </span>
          </h5>
        <div class="upsells-component__item">
          <div
            class="benefits"
            v-html="description"
          />
          <div class="image">
            <img
              :src="imageUrl"
              :alt="`image for ${name}`"
            >
          </div>
        </div>
        </div>
        <div class="upsells-component__bot">
          <green-button
            @click="addProduct(1)"
            :is-loading="isLoading || !upsellPrices['1']"
          >
              {{ yesText }}! {{ iWantText }} 1 {{ name }} {{ toMyOrderTextText }} {{ upsellPrices['1'] && upsellPrices['1'].price_text }}
          </green-button>
          <green-button
            @click="addProduct(2)"
            :is-loading="isLoading || !upsellPrices['2']"
          >
              {{ yesText }}! {{ iWantText }} 2 {{ name }} {{ toMyOrderTextText }} {{ upsellPrices['2'] && upsellPrices['2'].price_text }}
          </green-button>
        </div>
      </div>
    </div>
</template>

<script>
  import { t } from '../../utils/i18n';
  import upsells from '../../mixins/upsells';
  import { getUppSells } from '../../services/upsells';

  export default {
    name: 'Step1',

    mixins: [upsells],

    props: {
      id: {
        type: String,
        default: '',
      },
      discount: {
        type: Number,
        default: 0,
      },
    },

    data: () => ({
      upsellPrices: {},
      description: null,
      finalPrice: null,
      finalPricePure: null,
      name: null,
      price: null,
      priceFormatted: null,
      imageUrl: null,
      isLoading: false,
    }),

    computed: {
      congratulationsText: () => t('upsells.congratulations'),
      customerNumberText: () => t('upsells.customer_number'),
      thankYouText: () => t('upsells.thank_you'),
      forText: () => t('upsells.for'),
      offText: () => t('upsells.off'),
      yesText: () => t('upsells.yes'),
      iWantText: () => t('upsells.want_add'),
      toMyOrderTextText: () => t('upsells.to_order'),
    },

    mounted() {
      this.isLoading = true;
      getUppSells(this.id, 2)
        .then(res => {
          if (res && res.data) {
            this.upsellPrices = res.data.upsell.upsellPrices;
            this.name = res.data.upsell.long_name;
            this.description = res.data.upsell.description;
            this.imageUrl = res.data.upsell.image;
            this.priceFormatted = this.upsellPrices['1'] && this.upsellPrices['1'].price_text;
            this.price = this.upsellPrices['1'] && this.upsellPrices['1'].price;
          }
        })
        .then(() => {
          this.isLoading = false;
        });
    },

    methods: {
      addProduct(quantity) {
        if (quantity == 1) {
          this.finalPrice = this.upsellPrices['1'] && this.upsellPrices['1'].price_text;
          this.finalPricePure = this.upsellPrices['1'] && this.upsellPrices['1'].price;
        } else {
          this.finalPrice = this.upsellPrices['2'] && this.upsellPrices['2'].price_text;
          this.finalPricePure = this.upsellPrices['2'] && this.upsellPrices['2'].price;
        }

        this.addToCart(quantity);
      }
    }
  };
</script>

<style lang="scss">
.upsells-component {
  .step-1 {
    .congrats {
      color: #d4513a;
    }

    h3 {
      font-size: 30px;
      margin-bottom: 5px;
      text-align: center;
    }
  }
}
</style>
