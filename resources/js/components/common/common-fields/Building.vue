<template>

  <text-field
    id="building-field"
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
      'isLoading',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.building');
      },

      textRequired() {
        return this.$t('checkout.payment_form.building.required');
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
