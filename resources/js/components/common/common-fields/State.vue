<template>

  <text-field
    id="state-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    v-loading="isLoading.address"
    element-loading-spinner="el-icon-loading"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
      autocomplete: 'shipping region',
      name: 'ship-state',
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
      'country',
      'isLoading',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.state', {}, { country: this.country });
      },

      textRequired() {
        return this.$t('checkout.payment_form.state.required');
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
