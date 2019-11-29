<template>

  <text-field
    id="zip-code-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
      autocomplete: 'postal-code',
      name: 'postal-code',
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @blur="blur" />

</template>


<script>

  let cache = {};


  export default {

    props: [
      'form',
      'name',
      'country',
      'isLoading',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.zipcode', {}, { country: this.country });
      },

      textRequired() {
        return this.$t('checkout.payment_form.zipcode.required');
      },

    },


    methods: {

      blur() {
        const value = this.form[this.name];

        if (this.country !== 'br') {
          return;
        }

        if (this.$v.$invalid) {
          return;
        }

        if (cache[value]) {
          return this.apply(cache[value]);
        }

        this.isLoading.address = true;

        fetch('/address-by-zip?zipcode=' + value)
          .then(res => res.json())
          .then(res => {
            this.isLoading.address = false;
            cache[value] = res;
            this.apply(res);
          })
          .catch(err => {
            this.isLoading.address = false;
          });
      },

      apply(res) {
        if (res.address || res.city || res.state) {
          this.$emit('setBrazilAddress', res);
        }
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
