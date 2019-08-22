<template>
  <label class="label-container-radio radio-button-deal">
    <img class="share" src="/images/share.png" v-if="showShareArrow">
    <input type="radio"
           :checked="item.value === value"
           name="radio"
           :value="item.value">
    <div class="label-container-radio__label">
      <span>{{item.text}}</span>
      <span>${{item.price}}</span>
    </div>
    <div class="label-container-radio__subtitle" v-if="item.value !== 1">
      ${{Math.round((item.price / item.totalQuantity * 100) ) / 100}} / Unit
    </div>
    <span class="checkmark"></span>
  </label>
</template>

<script>
	export default {
		name: 'radio-button-item-deal',
		props: [
			'item',
			'value',
			'showShareArrow'
		],
		methods: {
			input(e) {
				return this.$emit('input', e.target.value)
			}
		}
	}
</script>

<style lang="scss">
  .radio-button-deal {
    position: relative;

    .label-container-radio__label {
      display: flex;
      justify-content: space-between;
    }
  }
</style>
