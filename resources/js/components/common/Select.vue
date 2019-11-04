<template>
  <div class="select scroll-when-error" :class="theme">
    <span v-if="label" class="label">{{label}}</span>
    <el-select
      v-if="!standart"
      no-data-text="No match data"
      no-match-text="No match text"
      v-bind="rest"
      @visible-change="onVisibleChange"
      @input="onChange"
      :value="value"
      :filterable="filterable"
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
          <div
            v-if="opened"
            class="select__label"
            v-html="item.text || item.label || item.value"></div>
        </el-option>
      </template>
    </el-select>
    <select
      v-else
      ref="select"
      :value="value"
      @input="onChange"
      class="el-input__inner select-standart"
      :style="{
        ...invalid && { 'animation': '0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s 1 normal both running shadow-drop-center-error' }
      }">
      <option 
        v-if="rest && rest.placeholder"
        v-html="rest.placeholder"
        style="display:none"
        :value="null"></option>
      <option
        v-for="item in list"
        :key="item.value"
        :value="item.value"
        v-html="item.text || item.label || item.value"></option>
    </select>
    <span class="error" v-show="invalid">{{validationMessage}}</span>
  </div>
</template>

<script>
export default {
  name: 'Select',
  props: [
    'standart',
    'list',
    'value',
    'popperClass',
    'theme',
    'label',
    'disabled',
    'rest',
    'validation',
    'validationMessage',
    'filterable',
  ],
  data() {
    return {
      opened: false,
    };
  },
  computed: {
    invalid () {
      return this.validation && this.validation.$dirty && this.validation.$invalid
    }
  },
  methods: {
    onVisibleChange(opened) {
      this.opened = opened;
    },
    onChange (e) {
      if (!this.standart) {
        this.$emit('input', e, this.value)
      } else {
        this.$emit('input', this.$refs.select.value, this.value);
      }
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
      input, select {
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
      select.select-standart {
        padding: 0 10px;
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
