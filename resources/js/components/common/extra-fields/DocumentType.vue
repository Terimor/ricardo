<template>
  
  <select-field
    :standart="true"
    v-if="extraFields.document_type"
    v-model="form.document_type"
    :validation="$v.form.document_type"
    :list="extraFields.document_type.items"
    :validationMessage="textRequired"
    :label="textTitle"
    theme="variant-1" />

</template>


<script>
  
  export default {

    name: 'DocumentType',

    props: [
      'extraFields',
      'form',
      '$v',
    ],


    computed: {

      textTitle() {
        return this.$t('checkout.payment_form.document_type.title');
      },

      textRequired() {
        return this.$t('checkout.payment_form.document_type.required');
      },

    },

  };

</script>


<style lang="scss">
  
</style>
