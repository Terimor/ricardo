<template>
  <div class="step-2">
    <transition
      name="component-fade"
      mode="out-in"
    >
      <div v-if="id && !isLoading">
        <h5>
          To thank you, we would like to offer you this
          <span class="green-up">
            {{ name }}
          </span>
          <span v-if="discount">
            for {{ discount }}% OFF!
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
              YES! I want to add 1 {{ name }} TO My Order For Just {{ priceFormatted }}
            </green-button>
        </div>
      </div>
    </transition>
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
      currentPrices() {
        return this.upsellPrices['1'] && this.upsellPrices['1'];
      }
    },
  };
</script>
