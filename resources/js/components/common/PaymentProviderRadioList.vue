<template>
  
  <radio-button-group
    class="main__credit-card-switcher payment-provider-radio-list"
    :list="list"
    :value="value"
    @input="input">

    <CreditCardsList
      slot="credit-card" />

  </radio-button-group>

</template>


<script>

  import { t } from '../../utils/i18n';
  import CreditCardsList from './CreditCardsList';


  export default {

    name: 'PaymentProviderRadioList',

    props: [
      'value',
    ],

    components: {
      CreditCardsList,
    },

    computed: {

      list() {
        return [
          {
            slot: 'credit-card',
            value: 'credit-card',
            label: t('checkout.credit_cards'),
            class: 'green-button-animated green-button-active',
          },
          /*{
            value: 'bank-payment',
            label: t('checkout.bank_payments'),
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


<style lang="scss" scoped>

  .payment-provider-radio-list {
    .credit-cards-list {
      margin: 8px -4px 0;
    }
  }

</style>
