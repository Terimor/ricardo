<template>
  
  <select-field
    :standart="true"
    v-if="extraFields.state"
    v-model="form.state"
    v-loading="isLoading && isLoading.address"
    element-loading-spinner="el-icon-loading"
    :list="extraFields.state.items"
    :validation="$v.form.state"
    :validationMessage="textRequired"
    :label="textTitle"
    theme="variant-1" />

</template>


<script>
  
  export default {

    name: 'State',

    props: [
      'extraFields',
      'isLoading',
      'country',
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
