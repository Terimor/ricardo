<template>
  
  <select-field
    v-if="visible"
    v-model="form.installments"
    id="installments"
    :label="textTitle"
    :list="list" />

</template>


<script>

  import { tplMixin } from '../../../mixins/extraFields';
  
  export default {

    name: 'Installments',

    props: [
      'extraFields',
      'form',
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

  };

</script>


<style lang="scss">
  
</style>
