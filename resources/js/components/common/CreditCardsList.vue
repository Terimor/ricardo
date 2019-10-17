<template>
  
  <div
    class="credit-cards-list">

    <div
      v-for="cardName of creditCardsAvailableList"
      :class="{ ['credit-card-' + cardName]: true }"
      class="credit-card-item">

      <img
        :src="creditCards[cardName].image"
        :title="creditCards[cardName].title" />

    </div>

  </div>

</template>


<script>

  import creditCards from '../../mixins/creditCards';


  export default {

    name: 'CreditCardsList',

    props: [
      'country',
    ],

    mixins: [
      creditCards,
    ],

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
