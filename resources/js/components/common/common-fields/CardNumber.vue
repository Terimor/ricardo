<template>

  <text-field
    id="card-number-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? placeholderText || textLabel
        : null,
      pattern: '\\d*',
      type: 'tel',
      autocomplete: 'cc-number',
      'data-bluesnap': 'encryptedCreditCard',
    }"
    :prefix="`<img data-src='${paymentMethodURL}' />`"
    :postfix="`<i class='fa fa-lock'></i>`"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @input="input" />

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      'paymentMethodURL',
      'placeholderText',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    data() {
      return {
        oldValue: this.form[this.name],
      };
    },


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.card_number');
      },

      textRequired() {
        return this.$t('checkout.payment_form.card_number.required');
      },

    },


    methods: {

      input() {
        let value = this.form[this.name] || '';

        if (!/^[0-9]{0,19}$/.test(value.replace(/\s/g, ''))) {
          value = this.oldValue;
        }

        this.$emit('setPaymentMethodByCardNumber', value);

        this.form[this.name] = value;
        this.oldValue = value;
      },

    },

  };

</script>


<style lang="scss" scoped>
  
  #card-number-field {
    text-align: left;

    :global(.prefix > img) {
      height: 22px;
      width: auto;
    }

  }

</style>
