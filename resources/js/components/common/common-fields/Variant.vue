<template>

  <select-field
    id="variant-field"
    v-if="visible"
    v-model="form[name]"
    :validation="$v"
    :label="textLabel"
    popperClass="emc1-popover-variant"
    theme="variant-1"
    :list="list"
    :tabindex="tabindex"
    :order="order"
    @input="input" />

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      'tabindex',
      'order',
      '$v',
    ],


    computed: {

      visible() {
        return this.list.length > 1 && (!js_query_params.variant || js_query_params.variant === '0');
      },

      textLabel() {
        return this.$t('checkout.select_variant');
      },

      list() {
        return checkoutData.product.skus.map(variant => ({
          label: variant.name,
          text: `<div><img src="${variant.quantity_image[1]}" alt=""><span>${variant.name}</span></div>`,
          value: variant.code,
          imageUrl: variant.quantity_image[1],
        }));
      },

    },


    methods: {

      input(newValue, oldValue) {
        this.$emit('input', newValue, oldValue);
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
