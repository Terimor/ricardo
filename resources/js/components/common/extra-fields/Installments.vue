<template>
  
  <select-field
    v-if="visible"
    v-model="form.installments"
    id="installments-field"
    :label="textTitle"
    theme="variant-1"
    :list="list"
    :tabindex="tabindex"
    :order="order"
    @input="input" />

</template>


<script>

  import { tplMixin } from '../../../mixins/extraFields';
  
  export default {

    name: 'Installments',

    props: [
      'extraFields',
      'form',
      'tabindex',
      'order',
    ],


    computed: {

      visible: tplMixin.computed.installmentsVisible,

      list() {
        return this.extraFields.installments
          ? this.extraFields.installments.items.map(item => ({
              value: item.value,
              label: this.$t(item.phrase),
              text: this.$t(item.phrase),
            }))
          : [];
      },

      textTitle() {
        return this.$t('checkout.payment_form.installments.title');
      },

    },


    methods: {

      input(newValue, oldValue) {
        this.$emit('input', newValue, oldValue);
      },

    },

  };

</script>


<style lang="scss">
  
</style>
