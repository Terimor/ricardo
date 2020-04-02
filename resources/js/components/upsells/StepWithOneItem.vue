<template>
  <div class="step-2">
    <transition
      name="component-fade"
      mode="out-in"
    >
      <div v-if="id && !isLoading">
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
        <div class="upsells-component__bot justify-content-center">
            <green-button
              @click="add(1)"
              :is-loading="isLoading"
            >
              {{ yesText }}! {{ iWantText }} 1 {{ name }} {{ toMyOrderTextText }} {{ priceFormatted }}
            </green-button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
  import { t } from '../../utils/i18n';
  import upsells from '../../mixins/upsells';
  import { getUppSells } from '../../services/upsells';

  export default {
    name: 'StepWithOneItem',
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

    data() {
      return {
        description: null,
        upsellPrices: {},
        name: '',
        price: 0,
        priceFormatted: '',
        finalPrice: null,
        finalPricePure: null,
        imageUrl: null,
        isLoading: false,
      }
    },

    watch: {
      id: {
        immediate: true,
        handler(newVal) {
          if (newVal) {
            this.isLoading = true;
            getUppSells(newVal, 1)
              .then(res => {
                if (res && res.data) {
                  this.upsellPrices = res.data.upsell.upsellPrices;
                  this.name = res.data.upsell.long_name;

                  this.description = newVal === js_data.product.id && res.data.upsell.upsell_plusone_text
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
          }
        },
      },
    },

    computed: {
      thankYouText: () => t('upsells.thank_you'),
      forText: () => t('upsells.for'),
      offText: () => t('upsells.off'),
      yesText: () => t('upsells.yes'),
      iWantText: () => t('upsells.want_add'),
      toMyOrderTextText: () => t('upsells.to_order'),
    },

    methods: {
      add(quantity) {
        quantity = +quantity;

        this.finalPrice = this.upsellPrices && this.upsellPrices[quantity] && this.upsellPrices[quantity].price_text || '';
        this.finalPricePure = this.upsellPrices && this.upsellPrices[quantity] && this.upsellPrices[quantity].price || 0;

        this.addToCart(quantity);
      }
    },
  };
</script>
