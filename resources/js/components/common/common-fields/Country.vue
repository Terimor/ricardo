<template>

  <select-field
    :standart="true"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :list="$root.sortedCountryList"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textPlaceholder
        : null,
      autocomplete: 'country-name',
      name: 'country-name',
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @input="onChange"
  />

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],

    methods: {
      onChange(value) {
        this.$emit('change', value);
      }
    },


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.сountry');
      },

      textRequired() {
        return this.$t('checkout.payment_form.сountry.required');
      },

      textPlaceholder() {
        return this.$t('checkout.payment_form.сountry.placeholder');
      },

    },

  };

</script>


<style lang="scss" scoped>

</style>
