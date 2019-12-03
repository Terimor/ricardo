<template>

  <phone-field
    id="phone-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
      autocomplete: 'off',
      name: 'phone',
    }"
    :countryCode="ccform[ccname]"
    @onCountryChange="setCountryCodeByPhoneField"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      'ccform',
      'ccname',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.phone');
      },

      textRequired() {
        return this.$t('checkout.payment_form.phone.required');
      },

      countryCode() {
        return checkoutData.countryCode;
      },

    },


    methods: {

      setCountryCodeByPhoneField(value) {
        if (value.iso2) {
          this.ccform[this.ccname] = value.iso2;
        }
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
