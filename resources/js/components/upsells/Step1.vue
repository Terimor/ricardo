<template>
    <div class="step-1">
        <h3><span class="congrats">CONGRATULATIONS!</span> You are our 500th customer!</h3>
        <h5>To thank you, we would like to offer you this <span class="green-up">{{name}}</span> for 50% OFF!</h5>

        <div class="upsells-component__item">
            <ul class="benefits">
                <li v-for="it in benefitList"><i class="fa fa-check" aria-hidden="true"></i> {{it}}</li>
            </ul>
            <div class="image">
                <img :src="imageUrl" alt="">
            </div>
        </div>
        <div class="upsells-component__bot">
            <green-button @click="addToCart(1)">
                YES! I want to add 1 EcoFuel TO My Order For Just ${{price.toLocaleString()}}
            </green-button>
            <green-button @click="addToCart(2)">YES! I want to add 2 EcoFuel TO My Order For Just ${{(price * 2).toLocaleString()}}
            </green-button>
        </div>
    </div>
</template>

<script>
  import upsells from '../../mixins/upsells'

  export default {
    name: 'Step1',
    props: {
      name: {
        default: 'GOLD PHONE'
      },
      benefitList: {
        default() {
          return [
            'Very Easy to Use - Plug & Drive',
            'Very Easy to Use - Plug & Drive',
            'Very Easy to Use - Plug & Drive',
            'Very Easy to Use - Plug & Drive',
          ];
        }
      },
      imageUrl: {
        default: '/images/headphones-gold.png'
      },
      price: {
        default: 49.99
      },
    },
    mixins: [upsells],
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
