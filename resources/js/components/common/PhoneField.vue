<template>
  <label
    class="phone-input-container"
    :class="{
      [theme]: theme,
      invalid: invalid
    }">
    <span class="label">{{label}}</span>
    <input type="tel" @input="input" :value="value" :id="id">
    <span v-show="invalid" class="error">{{validationMessage}}</span>
  </label>
</template>

<script>
export default {
  name: 'PhoneField',
  props: ['value', 'label', 'theme', 'validation', 'validationMessage', 'countryCode'],
  computed: {
    id () {
      return 'phone-' + this.label.replace(/[ ]/g, '-').toLowerCase()
    },
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
    window.intlTelInput(document.querySelector(`#${this.id}`), {
      initialCountry: this.countryCode
    })
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
          color: red;
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
