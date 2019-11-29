<template>
  
  <text-field
    v-if="extraFields.district"
    v-model="form.district"
    :validation="$v.form.district"
    :validationMessage="textRequired"
    :label="textTitle"
    :rest="{
      placeholder,
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

</template>


<script>
  
  export default {

    name: 'District',

    props: [
      'withPlaceholder',
      'extraFields',
      'tabindex',
      'order',
      'form',
      '$v',
    ],


    computed: {

      textTitle() {
        return this.$t('checkout.payment_form.complemento');
      },

      textRequired() {
        return this.$t('checkout.payment_form.complemento.required');
      },

      placeholder() {
        return this.withPlaceholder
          ? this.textTitle
          : null;
      },

    },

  };

</script>


<style lang="scss">
  
</style>
