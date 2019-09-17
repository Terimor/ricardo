<template>
  <div class="step-2">
    <div v-if="id && name">
      <h5>
        To thank you, we would like to offer you this
        <span class="green-up">
          {{ name }}
        </span> for 50% OFF!
      </h5>
      <div class="upsells-component__item">
          <div
            class="benefits"
            v-html="description"
          />
      </div>
      <div class="upsells-component__bot justify-content-center">
          <green-button
            @click="addToCart(1)"
            :is-loading="isLoading"
          >
            YES! I want to add 1 {{ name }} TO My Order For Just {{ priceFormatted }}
          </green-button>
      </div>
    </div>
  </div>
</template>

<script>
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
      isLoading: {
        type: Boolean,
        default: false,
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
      }
    },

    watch: {
      id: {
        immediate: true,
        handler(newVal) {
          if (newVal) {
            getUppSells(newVal, 1).then(({ data }) => {
              this.name = data.upsell.long_name;
              this.description = data.upsell.description;
              this.upsellPrices = data.upsell.upsellPrices;
              this.imageUrl = data.upsell.image;
              this.priceFormatted = this.currentPrices.price_text;
              this.price = this.currentPrices.price;
              this.finalPrice = this.currentPrices.price_text;
              this.finalPricePure = this.currentPrices.price;
            });
          }
        },
      },
    },

    computed: {
      currentPrices() {
        return this.upsellPrices['1'] && this.upsellPrices['1'];
      }
    },
  };
</script>

<style scoped>

</style>
