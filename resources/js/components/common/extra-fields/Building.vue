<template>

  <text-field
    id="building-field"
    v-if="extraFields.building"
    v-model="form.building"
    :validation="$v.form.building"
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

    name: 'Building',

    props: [
      'isLoading',
      'placeholder',
      'extraFields',
      'tabindex',
      'order',
      'form',
      '$v',
    ],


    created() {
      console.log(this.form)
    },


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.building');
      },

      textRequired() {
        return this.$t('checkout.payment_form.building.required');
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
