<template>
<div class="step-2">
    <h5>To thank you, we would like to offer you this <span class="green-up">{{name}}</span> for 50% OFF!</h5>
    <div class="upsells-component__item">
        <ul class="benefits">
            <li v-for="item in benefitList"><i class="fa fa-check" aria-hidden="true"></i> {{item}}</li>
        </ul>
        <div class="image">
            <img :src="imageUrl" alt="">
        </div>
    </div>
    <div class="upsells-component__bot justify-content-center">
        <green-button @click="addToCart(1)">YES! I want to add 1 EcoFuel TO My Order For Just ${{price.toLocaleString()}}</green-button>
    </div>
</div>
</template>

<script>
  import upsells from '../../mixins/upsells'
  export default {
    name: 'StepWithOneItem',
    mixins: [upsells],
    props: {
      name: {
        default: 'PINK PHONE'
      },
      benefitList: {
        default () {
          return [
            'Lorem ipsum dolor sit amet.',
            'Lorem ipsum dolor sit amet. Hello',
            'Lorem ipsum dolor sit amet. Lorem',
            'Lorem ipsum dolor sit amet.',
          ]
        }
      },
      imageUrl: {
        default: '/images/headphones-pink.png'
      },
      price: {
        default: 49.99
      }
    }
  };
</script>

<style scoped>

</style>
