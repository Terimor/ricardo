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
      'withPaypal',
    ],

    computed: {

      paymentMethods() {
        return this.$root.paymentMethods;
      },

      creditCardsAvailableList() {
        const cardNames = Object.keys(this.$root.paymentMethods).filter(name => name !== 'instant_transfer');

        if (this.withPaypal) {
          cardNames.push('instant_transfer');
        }

        return cardNames;
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
