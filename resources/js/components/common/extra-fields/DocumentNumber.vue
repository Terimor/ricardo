<template>
  
  <text-field-with-placeholder
    id="document-number-field"
    v-if="extraFields.document_number"
    v-model="form.document_number"
    :validation="$v.document_number"
    :validationMessage="textRequired"
    :placeholder="placeholder"
    :label="textTitle"
    :rest="{
      'format': placeholder,
    }"
    class="input-container variant-1"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @input="input" />

</template>


<script>

  import  { applyMaskForInput } from '../../../utils/checkout';


  export default {

    name: 'DocumentNumber',

    props: [
      'extraFields',
      'tabindex',
      'order',
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

      schema() {
        return !Array.isArray(this.extraFields.document_number.schema)
          ? this.extraFields.document_number.schema[this.form.document_type] || []
          : this.extraFields.document_number.schema;
      },

      placeholder() {
        return typeof this.extraFields.document_number.placeholder === 'object'
          ? this.extraFields.document_number.placeholder[this.form.document_type] || ''
          : this.extraFields.document_number.placeholder;
      },

    },


    methods: {

      input() {
        this.form.document_number = applyMaskForInput(this.form.document_number, this.placeholder, this.schema);
      },

    },

  };

</script>


<style lang="scss">
  
</style>
