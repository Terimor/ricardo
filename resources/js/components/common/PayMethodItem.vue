<template>
  <label class="pay-method-item">
    <img
      class="lazy"
      alt="Pay Image"
      :data-src="input.imgUrl"
      :style="{
        'border':
          checked
          ? '2px solid rgba(255,59,0,.93)'
          :'none'
      }">
    <input
      type="radio"
      name="radio"
      :checked="checked"
      :value="input.value"
      @change="onInput"
    >
  </label>
</template>
<script>
  import globals from '../../mixins/globals';
	export default {
		name: 'PayMethodItem',
		props: ['value', 'input'],
    mixins: [
      globals,
    ],
    mounted() {
      this.lazyload_update();
    },
    updated() {
      this.lazyload_update();
    },
    computed: {
			checked () {
				return this.input.value === this.value
      }
    },
    methods: {
      onInput(e) {
        if (window.navigator && navigator.userAgent && /Edge|Trident/.test(navigator.userAgent) && this.$parent.onInput) {
          this.$parent.onInput(e);
        }
      },
    },
	}
</script>
<style lang="scss">
  .pay-method-item {
    cursor: pointer;
    img {
      pointer-events: none;
      max-height: 30px;
    }

    input {
      display: none;
    }
  }

</style>
