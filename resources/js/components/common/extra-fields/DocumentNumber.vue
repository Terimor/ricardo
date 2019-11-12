<template>
  
  <text-field-with-placeholder
    v-if="extraFields.document_number"
    v-model="form.document_number"
    :validation="$v.form.document_number"
    :validationMessage="textRequired"
    :placeholder="placeholder"
    :label="textTitle"
    :rest="{
      'format': placeholder,
    }"
    class="input-container variant-1"
    theme="variant-1" />

</template>


<script>
  
  export default {

    name: 'DocumentNumber',

    props: [
      'extraFields',
      'form',
      '$v',
    ],


    computed: {

      textTitle() {
        return this.$t('checkout.payment_form.document_number.title');
      },

      textRequired() {
        return this.$t('checkout.payment_form.document_number.required');
      },

      placeholder() {
        return typeof this.extraFields.document_number.placeholder === 'object'
          ? this.extraFields.document_number.placeholder[this.form.document_type] || ''
          : this.extraFields.document_number.placeholder;
      },

    },

  };

</script>


<style lang="scss">
  
</style>
