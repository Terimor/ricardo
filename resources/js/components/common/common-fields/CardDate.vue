<template>

  <text-field-with-placeholder
    id="card-date-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="validationMessage"
    :placeholder="textPlaceholder"
    :label="textLabel"
    :rest="{
      format: textPlaceholder,
    }"
    class="input-container variant-1"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @input="input" />

</template>


<script>

  import  { applyMaskForInput } from '../../../utils/checkout';


  export default {

    props: [
      'form',
      'name',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.card_date.label');
      },

      textPlaceholder() {
        return this.$t('checkout.payment_form.card_date.placeholder');
      },

      textRequired() {
        return this.$t('checkout.payment_form.card_date.required');
      },

      textExpired() {
        return this.$t('checkout.payment_form.card_date.expired');
      },

      validationMessage() {
        if (!this.$v.required || !this.$v.isValid) {
          return this.textRequired;
        }

        return this.textExpired;
      },

    },


    methods: {

      input() {
        this.form[this.name] = applyMaskForInput(this.form[this.name], 'xx/xx', ['\\d', '\\d', '/', '\\d', '\\d']);
      },

    },

  };

</script>


<style lang="scss" scoped>

</style>
