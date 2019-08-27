<template>
  <div class="select" :class="theme">
    <span v-if="label" class="label">{{label}}</span>
    <el-select
      v-bind="rest"
      @input="onChange"
      :value="value"
      :disabled="disabled"
      :style="{
        ...invalid && { 'animation': '0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s 1 normal both running shadow-drop-center-error' }
      }"
      :popper-class="popperClass">
      <template v-for="item in list">
        <el-option
          :key="item.value"
          :label="item.label"
          :value="item.value">
          <div class="select__label" v-html="item.text || item.value"></div>
        </el-option>
      </template>
    </el-select>
    <span class="error" v-show="invalid">{{validationMessage}}</span>
  </div>
</template>

<script>
export default {
  name: 'Select',
  props: [
    'list',
    'value',
    'popperClass',
    'theme',
    'label',
    'disabled',
    'rest',
    'validation',
    'validationMessage',
  ],
  computed: {
    invalid () {
      return this.validation && this.validation.$dirty && this.validation.$invalid
    }
  },
  methods: {
    onChange (e) {
      this.$emit('input', e, this.value)
      if (this.validation) {
        this.validation.$touch()
      }
    }
  }
}
</script>

<style lang="scss">
  .select {
    width: 100%;
    display: flex;
    flex-direction: column;

    .label {
      margin-bottom: 6px;
    }

    input {
      height: 35px;
    }

    &.variant-1 {
      input {
        border: 1px solid #ddd;
        border-radius: 3px;
        background-color: rgba(255,253,228,.6);
        height: 40px;
        padding: 0 15px;

        &:focus {
          box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
          border-color: #409EFF;
        }
      }
    }
  }

  .el-select-dropdown {
    .popper__arrow {
      display: none;
    }
  }

  .el-popper[x-placement^=bottom] {
    margin-top: 0;
  }
</style>
