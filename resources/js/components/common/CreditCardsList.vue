<template>
  
  <div
    class="credit-cards-list">

    <div
      v-for="paymentMethodName of paymentMethodsAvailableList"
      :class="{ ['credit-card-' + paymentMethodName]: true }"
      class="credit-card-item">

      <img
        class="lazy"
        :data-src="paymentMethods[paymentMethodName].logo"
        :title="paymentMethods[paymentMethodName].name" />

    </div>

  </div>

</template>


<script>

  import globals from '../../mixins/globals';


  export default {

    name: 'CreditCardsList',


    props: [
      'withPaypal',
    ],


    mixins: [
      globals,
    ],


    mounted() {
      this.lazyload_update();
    },


    updated() {
      this.lazyload_update();
    },


    computed: {

      paymentMethods() {
        return this.$root.paymentMethods || {};
      },

      paymentMethodsAvailableList() {
        let paymentMethodNames = Object.keys(this.$root.paymentMethods || [])
          .filter(name => name !== 'instant_transfer');

        if (this.withPaypal && this.$root.paypalEnabled) {
          paymentMethodNames.push('instant_transfer');
        }

        return paymentMethodNames;
      },

    },

  };

</script>


<style lang="scss" scoped>

  .credit-cards-list {
    display: flex;
    flex-wrap: wrap;
    text-align: left;

    .credit-card-item {
      display: flex;
      flex-direction: column;
      margin: 4px 4px;

      img {
        pointer-events: none;
        max-width: 56px;
      }
    }
  }
  
</style>
