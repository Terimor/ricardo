<template>

  <div
    class="terms-checkbox"
    :style="{ order: order || null }"
    :class="classObject">

    <label
      for="terms-checkbox"
      class="label-container-checkbox">

      <input
        type="checkbox"
        id="terms-checkbox"
        v-model="form[name]"
        :tabindex="tabindex || null" />

      <span class="checkmark"></span>

      <span v-html="textLabel"></span>

    </label>

  </div>
  
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

      invalid() {
        return this.$v.$dirty && !this.$v.$pending && this.$v.$invalid;
      },

      classObject() {
        return {
          invalid: this.invalid,
        };
      },

      textLabel() {
        return this.$t('checkout.payment_form.terms', { address: '/terms', domain: '/privacy' });
      },

    },

  };

</script>


<style lang="scss" scoped>

  label {
    font-size: 16px;
    line-height: 1.5;

    .invalid & {
      color: #e74c3c;
    }

    :global(a) {
      color: #333;

      .invalid & {
        color: #e74c3c;
      }
    }
  }

  .checkmark {
    top: 1px;
    left: 6px;

    .invalid & {
      animation: 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s 1 normal both running shadow-drop-center-error;
    }
  }

  input:focus + .checkmark {
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
    border-color: #409EFF;
  }

</style>
