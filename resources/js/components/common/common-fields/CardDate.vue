<template>

  <text-field-with-placeholder
    id="card-date-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="validationMessage"
    :placeholder="textPlaceholder"
    :label="textLabel"
    :rest="{
      format: textPlaceholder,
    }"
    class="input-container variant-1"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @input="input" />

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    data() {
      return {
        oldValue: this.form[this.name] || '',
      };
    },


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.card_date.label');
      },

      textPlaceholder() {
        return this.$t('checkout.payment_form.card_date.placeholder');
      },

      textRequired() {
        return this.$t('checkout.payment_form.card_date.required');
      },

      textExpired() {
        return this.$t('checkout.payment_form.card_date.expired');
      },

      validationMessage() {
        if (!this.$v.required || !this.$v.isValid) {
          return this.textRequired;
        }

        return this.textExpired;
      },

    },


    methods: {

      input() {
        const format = 'xx/xx';
        let value = this.form[this.name] || '';

        for (let i = 0; i < value.length; i++) {
          const symbol = format.substr(i, 1) || '';

          if (symbol === 'x') {
            if (Number.isNaN(+value.substr(i, 1))) {
              value = value.substr(0, i);
            }

            continue;
          }

          if (symbol !== value.substr(i, 1)) {
            value = value.substr(0, i);
          }
        }

        if (value.length === 2 && this.oldValue.length !== 3) {
          value += '/';
        }

        this.form[this.name] = value;
        this.oldValue = value;
      },

    },

  };

</script>


<style lang="scss" scoped>

</style>
