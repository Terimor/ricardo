<template>
<div class="step-2">
    <h5>To thank you, we would like to offer you this <span class="green-up">{{name}}</span> for 50% OFF!</h5>
    <div class="upsells-component__item">
        <div class="benefits" v-html="descriptionHtml"></div>
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
      imageUrl: {
        default: '/images/headphones-pink.png'
      },
      price: {
        default: 49.99
      },
      descriptionHtml: {
        default: "I'm description"
      }
    }
  };
</script>

<style scoped>

</style>
