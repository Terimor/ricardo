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
    theme="variant-1"
    @input="input" />

</template>


<script>

  let traverseCount = 0;


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


    methods: {

      input() {
        if (this.placeholder === 'xxx.xxx.xxx-xx') {
          traverseCount = 0;
          this.traverse();
        }
      },

      traverse() {
        let value = this.form.document_number || '';
        
        for (let i = 0; i < value.length; i++) {
          let symbol = this.placeholder.substr(i, 1) || '';

          if (!symbol) {
            this.form.document_number = value.substr(0, i);
            return;
          }

          if (!/[A-z0-9]/.test(symbol) && value[i] !== symbol) {
            this.form.document_number = value.substr(0, i) + symbol + value.substr(i);

            if (traverseCount++ < 100) {
              this.traverse();
            }

            return;
          }
        }
      },

    },

  };

</script>


<style lang="scss">
  
</style>
