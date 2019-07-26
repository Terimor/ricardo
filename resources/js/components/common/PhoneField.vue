<template>
  <label class="phone-input-container" :class="theme">
    <span class="label">{{label}}</span>
    <input type="tel" @input="input" :value="value" :id="id">
  </label>
</template>

<script>
export default {
  name: 'PhoneField',
  props: ['value', 'label', 'theme'],
  computed: {
    id () {
      return 'phone-' + this.label.replace(/[ ]/g, '-').toLowerCase()
    }
  },
  methods: {
    input (e) {
      this.$emit('input', e.target.value)
    }
  },
  mounted () {
    window.intlTelInput(document.querySelector(`#${this.id}`))
  }
}
</script>

<style lang="scss">
  .phone-input-container {
    width: 100%;
    display: flex;
    flex-direction: column;
    margin-bottom: 10px;

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
