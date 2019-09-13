<template>
    <div class="step-1">
      <div v-if="name">
        <h3>
          <span class="congrats">
            CONGRATULATIONS!
          </span>
          You are our 500th customer!
        </h3>
        <h5>
          To thank you, we would like to offer you this
          <span class="green-up">
            {{ name }}
          </span>
          for 50% OFF!
          </h5>
        <div class="upsells-component__item">
            <div
              class="benefits"
              v-html="description"
            />
        </div>
        <div class="upsells-component__bot">
            <green-button
              @click="addToCart(1)"
              :is-loading="isLoading"
            >
                YES! I want to add 1 {{ name }} TO My Order For Just {{ priceFormatted }}
            </green-button>
            <green-button
              @click="addToCart(2)"
              :is-loading="isLoading"
            >
                YES! I want to add 2 {{ name }} TO My Order For Just {{ upsellPrices['2'] && upsellPrices['2'].value_text }}
            </green-button>
        </div>
      </div>
    </div>
</template>

<script>
  import upsells from '../../mixins/upsells'

  export default {
    name: 'Step1',

    mixins: [upsells],

    props: {
      data: {
        default: null,
      },
      name: {
        type: String,
        default: '',
      },
      description: {
        type: String,
        default: '',
      },
      price: {
        type: Number,
        default: 0,
      },
      priceFormatted: {
        type: String,
        default: '',
      },
      id: {
        type: String,
        default: '',
      },
      imageUrl: {
        type: String,
        default: '',
      },
      isLoading: {
        type: Boolean,
        default: false,
      },
    },

    data: () => ({
      upsellPrices: {},
    }),

    watch: {
      id(id) {
        if (id) {
          this.getUppSells(this.id, 2).then(({ data }) => {
            this.upsellPrices = data.upsell.upsellPrices
          });
        }
      }
    },
  };
</script>

<style lang="scss">
  .step-1 {
    .congrats {
      color: #d4513a;
    }

    & > h3 {
      font-size: 30px;
      margin-bottom: 5px;
      text-align: center;
    }
  }

</style>
