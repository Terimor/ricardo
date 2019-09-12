<template>
  <div class="step-2">
    <div v-if="data">
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
            YES! I want to add 1 {{ name }} TO My Order For Just {{price}}
          </green-button>
      </div>
    </div>
  </div>
</template>

<script>
  import upsells from '../../mixins/upsells'
  export default {
    name: 'StepWithOneItem',
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
  };
</script>

<style scoped>

</style>
