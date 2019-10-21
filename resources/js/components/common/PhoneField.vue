<template>
  <label
    class="phone-input-container scroll-when-error"
    :class="{
      [theme]: theme,
      invalid: invalid
    }">
    <span class="label">{{label}}</span>
    <input
      :style="{
        ...invalid && { 'animation': '0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s 1 normal both running shadow-drop-center-error' }
      }"
      type="tel"
      @input="input"
      :value="value"
      :id="id">
    <span v-show="invalid" class="error">{{validationMessage}}</span>
  </label>
</template>

<script>
  let idCounter = 0;

  export default {
    name: 'PhoneField',
    props: ['value', 'label', 'theme', 'validation', 'validationMessage', 'countryCode'],

    data() {
      return {
        id: 'phone-' + idCounter++,
      };
    },

    computed: {
      invalid () {
        return this.validation && this.validation.$dirty && this.validation.$invalid
      }
    },

    methods: {
      input (e) {
        this.$emit('input', e.target.value)
        if (this.validation) {
          this.validation.$touch()
        }
      }
    },

    mounted () {
      const selector = document.querySelector(`#${this.id}`)

      window.intlTelInput(selector, {
        initialCountry: this.countryCode,
        separateDialCode: true
      })

      selector.addEventListener('countrychange', () => {
        this.$emit('onCountryChange', window.intlTelInputGlobals.getInstance(selector).getSelectedCountryData())
      });
    }
  }
</script>

<style lang="scss">
  .phone-input-container {
    width: 100%;
    display: flex;
    flex-direction: column;
    margin-bottom: 10px;

      &.invalid {
        .label, .error {
          color: #e74c3c;
        }
      }

    input {
      outline: none;
      width: 100%;
      color: rgb(85, 85, 85);
    }

    .iti__selected-flag {
      outline: none;
    }

    .label {
      margin-bottom: 6px;
    }

    &.variant-1 {
      input {
        border: 1px solid #ddd;
        border-radius: 3px;
        background-color: rgba(255, 253, 228, .6);
        height: 40px;
        padding: 0 15px 0 46px;

        &:focus {
          box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
          border-color: #409EFF;
        }
      }
    }
  }
</style>
