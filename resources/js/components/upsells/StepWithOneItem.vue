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
              @click="addToCart(1)"
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
            getUppSells(newVal, 1).then(({ data }) => {
              this.name = data.upsell.long_name;
              this.description = data.upsell.description;
              this.upsellPrices = data.upsell.upsellPrices;
              this.imageUrl = data.upsell.image;
              this.priceFormatted = this.currentPrices.price_text;
              this.price = this.currentPrices.price;
              this.finalPrice = this.currentPrices.price_text;
              this.finalPricePure = this.currentPrices.price;
            }).then(() => {
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

      currentPrices() {
        return this.upsellPrices['1'] && this.upsellPrices['1'];
      }
    },
  };
</script>
