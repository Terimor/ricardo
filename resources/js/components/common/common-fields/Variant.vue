<template>

  <div
    v-if="visible"
    class="variant-field scroll-when-error"
    :class="{ opened: opened, invalid: invalid, up: up }">

    <div class="variant-field-label">{{ textLabel }}</div>

    <div class="inside">

      <div
        ref="variant_field_input"
        class="variant-field-input"
        @click="toggle">

        <img v-if="value" :src="image" class="variant-field-input-image" alt="" />
        <div v-if="!value" class="variant-field-input-label empty">{{ textLabel }}</div>
        <div v-if="value" class="variant-field-input-label">{{ items_by_code[value].name }}</div>
        <i class="fa fa-angle-down"></i>
      </div>

      <div v-if="invalid" class="error">{{ textError }}</div>

      <div
        v-if="opened"
        class="variant-field-backdrop"
        @click="toggle"></div>

      <transition name="slide-down">
        <div
          v-if="opened"
          class="variant-field-dropdown">

          <div
            v-for="item of items"
            class="variant-field-item"
            :class="{ active: item.code === value }"
            @click="change(item.code)">

            <img :src="item.quantity_image[1]" alt="" />
            <div>{{ item.name }}</div>
          </div>

        </div>
      </transition>

    </div>

  </div>

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      '$v',
    ],


    data() {
      return {
        opened: false,
        up: false,
      };
    },


    computed: {

      value() {
        return this.form[this.name];
      },

      visible() {
        return this.items.length > 1 && (!js_query_params.variant || js_query_params.variant === '0');
      },

      invalid() {
        return this.$v.$dirty && this.$v.$invalid;
      },

      textLabel() {
        return this.$t('checkout.select_variant');
      },

      textError() {
        return this.$t('checkout.select_variant');
      },

      items() {
        return js_data.product.skus || [];
      },

      items_by_code() {
        return this.items.reduce((acc, sku) => {
          acc[sku.code] = sku;
          return acc;
        }, {});
      },

      image() {
        return this.items_by_code[this.value].quantity_image[1];
      },

    },


    methods: {

      toggle() {
        if (!this.opened) {
          const item_height = 80;
          const input_rect = this.$refs.variant_field_input.getBoundingClientRect();
          const free_space = document.documentElement.clientHeight - (input_rect.top + input_rect.height);
          this.up = free_space < item_height * this.items.length;
        }

        this.opened = !this.opened;
      },

      change(value) {
        this.opened = false;
        this.form[this.name] = value;
        this.$emit('change', value);
      },

    },

  };

</script>


<style lang="scss" scoped>
  
  .variant-field {
    display: flex;
    flex-direction: column;
  }

  .variant-field-label {
    margin-bottom: 6px;
  }

  .variant-field > .inside {
    display: flex;
    flex-direction: column;
    position: relative;
  }

  .variant-field-input {
    align-items: center;
    background: linear-gradient(to bottom,#f7f8fa,#e7e9ec);
    border: 1px solid #dcdfe6;
    border-radius: 3px;
    cursor: pointer;
    display: flex;
    padding: 8px 15px;

    &:hover {
      background: linear-gradient(to bottom,#e7e9ec,#f7f8fa);
    }

    .variant-field.opened & {
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(102, 175, 233, 0.6);
      border-color: #409eff;
    }

    .variant-field.invalid & {
      animation: 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s 1 normal both running shadow-drop-center-error;
    }

    .variant-field-input-image {
      height: 80px;
      margin-right: 10px;

      [dir="rtl"] & {
        margin-left: 10px;
        margin-right: 0;        
      }
    }

    .variant-field-input-label {
      color: #555;
      font-size: 14px;

      &.empty {
        align-items: center;
        display: flex;
        font-size: 16px;
        height: 80px;
      }
    }

    i {
      color: #c0c4cc;
      font-size: 18px;
      margin-left: auto;
      margin-right: -4px;
      transition: transform .3s;

      [dir="rtl"] & {
        margin-left: -4px;
        margin-right: auto;
      }

      .variant-field.opened & {
        transform: rotate(180deg);
      }
    }
  }

  .variant-field-backdrop {
    bottom: 0;
    left: 0;
    position: fixed;
    right: 0;
    top: 0;
    z-index: 5;
  }

  .variant-field-dropdown {
    background-color: #fff;
    border: 1px solid #e4e7ed;
    border-radius: 4px;
    box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
    display: flex;
    flex-direction: column;
    left: 0;
    padding: 6px 0;
    position: absolute;
    right: 0;
    top: 100%;
    transition: all .3s ease;
    z-index: 10;

    .variant-field.up & {
      bottom: 100%;
      top: auto;
    }

    &.slide-down-enter, &.slide-down-leave-to {
      padding: 0;

      .variant-field-item {
        height: 0;
      }
    }
  }

  .variant-field-item {
    align-items: center;
    cursor: pointer;
    display: flex;
    font-size: 14px;
    height: 80px;
    overflow: hidden;
    padding: 0 20px;
    transition: all .3s ease, background-color 0s;

    &:hover {
      background-color: #f5f7fa;
    }

    &.active {
      color: #409eff;
      font-weight: 700;
    }

    img {
      height: 80px;
      margin: 0 10px;
    }
  }

</style>
