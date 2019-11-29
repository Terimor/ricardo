<template>
  
  <select-field
    :standart="true"
    v-if="extraFields.document_type"
    v-model="form.document_type"
    :validation="$v.form.document_type"
    :list="documentTypeList"
    :validationMessage="textRequired"
    :label="textTitle"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

</template>


<script>
  
  export default {

    name: 'DocumentType',

    props: [
      'extraFields',
      'tabindex',
      'order',
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

      documentTypeList() {
        return this.extraFields.document_type.items.map(item => ({
          value: item.value,
          label: this.$t(item.phrase),
          text: this.$t(item.phrase),
        }));
      },

    },

  };

</script>


<style lang="scss">
  
</style>
