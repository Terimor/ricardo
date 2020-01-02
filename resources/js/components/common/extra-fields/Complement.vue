<template>

  <text-field
    id="complement-field"
    v-if="extraFields.complement"
    v-model="form.complement"
    :validation="$v.form.complement"
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

    name: 'Complement',

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
        return this.$t('checkout.payment_form.complement');
      },

      textRequired() {
        return this.$t('checkout.payment_form.complement.required');
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
