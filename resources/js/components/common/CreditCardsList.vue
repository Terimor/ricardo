<template>
  
  <div
    class="credit-cards-list">

    <div
      v-for="cardName of creditCardsAvailableList"
      :class="{ ['credit-card-' + cardName]: true }"
      class="credit-card-item">

      <img
        :src="paymentMethods[cardName].logo"
        :title="paymentMethods[cardName].name" />

    </div>

  </div>

</template>


<script>

  export default {

    name: 'CreditCardsList',

    props: [
      'country',
      'withPaypal',
    ],

    computed: {

      paymentMethods() {
        return checkoutData.paymentMethods;
      },

      creditCardsAvailableList() {
        return this.country && Object.keys(checkoutData.paymentMethods).filter(name => name !== 'instant_transfer');
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
        max-width: 56px;
      }
    }
  }
  
</style>
