<template>

  <text-field
    id="state-field"
    v-if="stateExtraField && stateExtraField.type === 'text'"
    v-model="form.state"
    :validation="$v.state"
    :validationMessage="textRequired"
    v-loading="isLoading.address"
    element-loading-spinner="el-icon-loading"
    :label="textTitle"
    :rest="{
      placeholder: placeholder
        ? textPlaceholder
        : null,
      autocomplete: 'shipping region',
      name: 'ship-state',
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

  <select-field
    :standart="true"
    v-else-if="stateExtraField && stateExtraField.type === 'dropdown'"
    v-model="form.state"
    :list="stateExtraField.items"
    :validation="$v.state"
    :validationMessage="textRequired"
    v-loading="isLoading.address"
    element-loading-spinner="el-icon-loading"
    :rest="{
      placeholder: placeholder
        ? textPlaceholder
        : null,
    }"
    :label="textTitle"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

</template>


<script>
  
  export default {

    name: 'State',

    props: [
      'isLoading',
      'country',
      'stateExtraField',
      'placeholder',
      'tabindex',
      'order',
      'form',
      '$v',
    ],


    computed: {

      textTitle() {
        return this.$t('checkout.payment_form.state', {}, { country: this.country });
      },

      textRequired() {
        return this.$t('checkout.payment_form.state.required');
      },

      textPlaceholder() {
        return this.$t('checkout.payment_form.state.selected');
      },

    },

  };

</script>


<style lang="scss">
  
</style>
