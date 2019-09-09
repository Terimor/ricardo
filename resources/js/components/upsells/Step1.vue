<template>
    <div class="step-1">
        <h3><span class="congrats">CONGRATULATIONS!</span> You are our 500th customer!</h3>
        <h5>To thank you, we would like to offer you this <span class="green-up">{{name}}</span> for 50% OFF!</h5>

        <div class="upsells-component__item">
            <div class="benefits" v-html="descriptionHtml"></div>
            <div class="image">
                <img :src="imageUrl" alt="">
            </div>
        </div>
        <div class="upsells-component__bot">
            <green-button @click="addToCart(1)">
                YES! I want to add 1 {{name}} TO My Order For Just ${{price}}
            </green-button>
            <green-button @click="addToCart(2)">
                YES! I want to add 2 {{name}} TO My Order For Just ${{(price * 2)}}
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
        default: ''
      },
      imageUrl: {
        default: ''
      },
      price: {
        default: 9999.99
      },
      descriptionHtml: {
        default: "I'm description"
      }
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
