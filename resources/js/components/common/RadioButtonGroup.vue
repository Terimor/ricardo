<template>
  <form
    class="radio-button-group"
    @input="onInput">

    <template v-if="withCustomLabels">
      <slot/>
    </template>
    <template v-else v-for="input in list">
      <label :style="input.styles" :key="input.value" class="label-container-radio"
             :class="[input.class, {disabled: input.isOutOfStock, labeled: input.isLabeled}]">
        <div class="label-container-radio__label">
          <div v-if="input.label" class="title" v-html="input.label" />
          <slot v-if="input.slot" :name="input.slot" />
        </div>
        <input @change="onInput" type="radio"
               :checked="input.value == value"
               name="radio"
               :value="input.value"
               :disabled="input.isOutOfStock">
        <span class="checkmark"></span>
      </label>
    </template>
  </form>
</template>

<script>
export default {
  name: 'RadioButtonGroup',
  props: [
    'list',
    'value',
    'labelStyles',
    'withCustomLabels',
    'validation'
  ],
  methods: {
    onInput (e) {
      this.$emit('input', e.target.value)
    }
  }
}
</script>

<style lang="scss">
  .label-container-radio {
    display: block;
    position: relative;
    cursor: pointer;
    font-size: 22px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    border: 1px solid transparent;
    padding: 10px 10px 10px 35px;
    margin: 0 -7px;
  }

  .label-container-radio:hover {
    background: #fef9ae;
    border-color: rgba(0,0,0,.2);
    border-radius: 2px;
  }

  .label-container-radio.disabled {
    background-color: transparent !important;
    border: none;
    opacity: .5;
    cursor: default;
    &:before {
      position: absolute;
      top: 0;
      left: 0;
      display: block;
      content: '';
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,.5);
      z-index: 100;
    }
    &:hover {
      background: rgba(0,0,0,.5);
      border-color: transparent;
      border-radius: 0;
    }
    * {
      text-decoration: line-through;
    }
  }

  .label-container-radio input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
  }

  .label-container-radio .checkmark {
    position: absolute;
    top: 15px;
    left: 15px;
    height: 18px;
    width: 18px;
    background-color: #fff;
    border: 1px solid #9e9e9e;
    border-radius: 50%;
  }

  .label-container-radio .checkmark:after {
    content: "";
    position: absolute;
    display: none;
  }

  .label-container-radio input:checked ~ .checkmark:after {
    display: block;
  }

  .label-container-radio .checkmark:after {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 100%;
    height: 12px;
    width: 12px;
    background-color: #2f4a92;
  }

  .radio-button-group {
    .label-container-radio {
      padding: 15px 15px 15px 45px;
    }
  }
</style>
