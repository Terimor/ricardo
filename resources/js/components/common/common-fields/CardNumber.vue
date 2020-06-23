<template>

  <text-field
    id="card-number-field"
    class="card_number-field"
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
    :prefix="`<img class='lazy' data-src='${paymentMethodURL}' />`"
    :postfix="`<i class='fa fa-lock'></i>`"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @input="input" />

</template>


<script>

  import globals from '../../../mixins/globals';
  import Cleave from 'cleave.js';


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


    mixins: [
      globals,
    ],


    data() {
      return {
        oldValue: this.form[this.name],
      };
    },


    mounted() {
      this.lazyload_update();

      js_deps.wait_for(() => {
        return !!document.querySelector('#card-number-field input');
      }, () => {
        var cleave = new Cleave('#card-number-field input', {
          creditCard: true,
          onCreditCardTypeChanged: function (type) {
              // update UI ...
          }
        });
      });
    },


    updated() {
      this.lazyload_update();
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

    :global(input) {
      font-family: 'Pathway Gothic One', sans-serif !important;
      font-size: 19px !important
    }

  }

</style>
