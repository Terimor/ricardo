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
    @input="input"
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

      input() {
        let value = this.form[this.name];

        value = value.replace(/[^A-z0-9]/g, '')
        value = value.substr(0, 12);

        this.form[this.name] = value;
      },

      blur() {
        const value = this.form[this.name];

        if (this.country !== 'br') {
          return;
        }

        if (this.$v.$invalid) {
          return;
        }

        const zipcode = value
          ? value.replace(/[^0-9]/g, '')
          : '';

        if (cache[zipcode]) {
          return this.apply(cache[zipcode]);
        }

        this.isLoading.address = true;

        fetch('/address-by-zip?zipcode=' + zipcode)
          .then(resp => {
            if (!resp.ok) {
              throw new Error(resp.statusText);
            }

            return resp.json();
          })
          .then(res => {
            this.isLoading.address = false;
            cache[zipcode] = res;
            this.apply(res);
          })
          .catch(err => {
            this.isLoading.address = false;
          });
      },

      apply(res) {
        this.$emit('setBrazilAddress', res);
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
