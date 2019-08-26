<template>
  <label :class="`label-container-radio radio-button-deal item-${item.value}`">
    <img class="share" src="/images/share.png" v-if="showShareArrow">
    <input type="radio"
           :checked="item.value === value"
           name="radio"
           @input="input"
           :value="item.value">
    <div class="label-container-radio__label">
      <span>{{item.text}} {{showDiscount ? " " + item.discountText : ""}}</span>
      <span class="price">${{item.price}}</span>
    </div>
    <div class="label-container-radio__subtitle" v-if="showPerUnitPrice && item.value !== 1">
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
			'showShareArrow',
			'showPerUnitPrice',
			'showDiscount'
		],
		methods: {
			input(e) {
				return this.$emit('checkDeal', e.target.value)
			}
		},
    mounted(){
			this.$nextTick(function () {
				this.$emit('finish-render')
			})
    }
	}
</script>

<style lang="scss">
  .radio-button-deal {
    position: relative;
    margin: 5px 0;

    .label-container-radio__label {
      display: flex;

      .price {
        margin-left: auto;
      }
    }
  }

  .isChecked {
    background: #fef5eb
  }
</style>
