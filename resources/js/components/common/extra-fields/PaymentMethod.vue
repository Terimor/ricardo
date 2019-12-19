<template>
  
  <select-field
    v-model="form.payment_method"
    :validation="$v.form.payment_method"
    :validationMessage="textRequired"
    :list="paymentMethods"
    :rest="{
      placeholder: textTitle
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

</template>


<script>
  
  export default {

    name: 'PaymentMethod',

    props: [
      'extraFields',
      'tabindex',
      'order',
      'form',
      '$v',
    ],


    computed: {

      textTitle() {
        return this.$t('checkout.payment_form.card_type.title');
      },

      textRequired() {
        return this.$t('checkout.payment_form.card_type.required');
      },

      paymentMethods() {
        const paymentMethods = this.$root.paymentMethods || {};

        const values = Object.keys(this.$root.paymentMethods || [])
          .filter(name => name !== 'instant_transfer');

        if (this.form.installments === 1) {
          //values.push('instant_transfer');
        }

        return values.map(value => ({
          value,
          text: paymentMethods[value] && paymentMethods[value].name || '',
          label: paymentMethods[value] && paymentMethods[value].name || '',
          imgUrl: paymentMethods[value] && paymentMethods[value].logo || '',
        }));
      },

    },

  };

</script>


<style lang="scss">
  
</style>
