<template>

  <text-field
    id="first-name-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
      autocomplete: 'name',
      name: 'name',
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

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


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.first_name');
      },

      textRequired() {
        return this.$t('checkout.payment_form.first_name.required');
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
