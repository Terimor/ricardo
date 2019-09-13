<template>
  <div class="step-3">
    <div v-if="data">
      <h3>
        <span class="orange">
          You Can'y Leave Without Taking ADVANTAGE of this SPECIAL OFFER!
        </span>
      </h3>
      <h5>
        Last chance! Get 1 more eazyclean for just
        <span class="price">
          {{ priceFormatted }}!
        </span>
      </h5>
      <div class="content-with-image">
        <div class="text-container">
          <p
            class="last"
            v-html="description"
          />
        </div>
      </div>
      <div class="upsells-component__bot">
        <select-field
          v-if="upsellPrices"
          label="Please choose:"
          v-model="quantity"
          :list="selectList"
        />
        <green-button
          :is-loading="isLoading"
          @click="addToCart(quantity)"
        >
          Add To Cart
        </green-button>
      </div>
    </div>
  </div>
</template>

<script>
import upsells from '../../mixins/upsells';
export default {
  name: 'Step3',
  mixins: [upsells],
  props: {
    data: {
      default: null
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
  data () {
    return {
      quantity: 1,
      upsellPrices: null,
    }
  },

  watch: {
    id(id) {
      if(id) {
        this.getUppSells(this.id, 3).then(({ data }) => {
          this.upsellPrices = data.upsell.upsellPrices
        });
      }
    }
  },

  computed: {
    selectList() {
      const data = Array(3).fill('').map((item, index) => {
        const value = index + 1

        return item = {
          label: `${value}x ${this.name} - ${this.upsellPrices && this.upsellPrices[value].value_text}`,
          text: `${value}x ${this.name} - ${this.upsellPrices && this.upsellPrices[value].value_text}`,
          value: value,
        }
      })

      return data;
    },
  },
};
</script>

<style lang="scss">
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

</style>
