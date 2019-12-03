<template>
  
  <select-field
    :standart="true"
    v-if="extraFields.state"
    v-model="form.state"
    :list="extraFields.state.items"
    :validation="$v.form.state"
    :validationMessage="textRequired"
    v-loading="isLoading.address"
    element-loading-spinner="el-icon-loading"
    :label="textTitle"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

</template>


<script>
  
  export default {

    name: 'State',

    props: [
      'extraFields',
      'isLoading',
      'country',
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

    },

  };

</script>


<style lang="scss">
  
</style>
