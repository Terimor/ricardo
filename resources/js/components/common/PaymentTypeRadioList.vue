<template>
  
  <radio-button-group
    class="main__credit-card-switcher payment-type-radio-list"
    :value="value"
    :list="list"
    @input="input" />

</template>


<script>

  import { t } from '../../utils/i18n';
  import creditCards from '../../mixins/creditCards';


  export default {

    name: 'PaymentTypeRadioList',

    props: [
      'value',
      'country',
    ],

    mixins: [
      creditCards,
    ],

    computed: {

      list() {
        return [
          {
            label: '<div class="title">' + t('checkout.credit_cards') + '</div>' + this.creditCardsAvailableHTML,
            value: 'credit-card',
            class: 'green-button-animated green-button-active',
          },
          /*{
            label: t('checkout.bank_payments'),
            value: 'bank-payment',
            class: 'bank-payment',
          },*/
        ];
      },

    },

    methods: {

      input(value) {
        this.$emit('input', value);
      },

    },

  };

</script>


<style lang="scss">

  .payment-type-radio-list {
    text-align: left;

    .cards {
      display: flex;
      flex-wrap: wrap;
      margin: 8px -4px 0;

      .card {
        display: flex;
        flex-direction: column;
        margin: 4px 4px;

        img {
          max-width: 56px;
        }
      }
    }
  }
  
</style>
