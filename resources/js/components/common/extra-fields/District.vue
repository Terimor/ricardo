<template>
  
  <text-field
    id="district-field"
    v-if="extraFields.district"
    v-model="form.district"
    :validation="$v.district"
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

    name: 'District',

    props: [
      'isLoading',
      'placeholder',
      'extraFields',
      'tabindex',
      'order',
      'form',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.complemento');
      },

      textRequired() {
        return this.$t('checkout.payment_form.complemento.required');
      },

    },

  };

</script>


<style lang="scss">
  
</style>
