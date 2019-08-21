<template>
  <div class="flex-wrap">
    <select-field
        :validation="$v.form.country"
        validationMessage="Invalid field"
        theme="variant-1"
        label="Country"
        class="country"
        :rest="{
           placeholder: 'Country'
        }"
        :list="countryList"
        v-model="paymentForm.country"/>
    <text-field
        :validation="$v.form.streetAndNumber"
        validationMessage="Please enter your street and number"
        element-loading-spinner="el-icon-loading"
        theme="variant-1 street"
        label="Street And Number"
        :rest="{
          placeholder: 'Street and number',
          autocomplete: 'street-address'
                }"
        v-model="paymentForm.streetAndNumber"/>
    <text-field
        :validation="$v.form.city"
        validationMessage="Please enter your city"
        element-loading-spinner="el-icon-loading"
        theme="variant-1"
        label="City"
        :rest="{
          placeholder: 'City',
          autocomplete: 'shipping locality'
         }"
        v-model="paymentForm.city"/>
    <text-field
        :validation="$v.form.state"
        validationMessage="Please enter your state"
        element-loading-spinner="el-icon-loading"
        theme="variant-1"
        label="State"
        :rest="{
          placeholder: 'State',
          autocomplete: 'shipping locality'
        }"
        v-model="paymentForm.state"/>
    <text-field
        :validation="$v.form.zipCode"
        validationMessage="Please enter your zip code"
        theme="variant-1"
        label="Zip Code"
        :rest="{
          placeholder: 'Zip code'
        }"
        id="zip-code-field"
        v-model="paymentForm.zipCode"/>
    <h2>Pay Securely With:</h2>
    <select-field
        :validation="$v.form.paymentType"
        validationMessage="Invalid field"
        theme="variant-1"
        :rest="{
                  placeholder: 'VISA'
                }"
        :list="cardNames"
        v-model="paymentForm.paymentType"/>
    <form id="payment-data-form">
      <text-field
          :validation="$v.form.cardNumber"
          :rest="{
                  pattern: '\\d*',
                  type: 'tel',
                  placeholder: '**** **** **** ****',
                  autocomplete: 'cc-number',
                    'data-bluesnap': 'encryptedCreditCard'
                  }"
          validationMessage="Please enter a credit card number."
          class="card-number"
          theme="variant-1"
          label="Card Number"
          v-model="paymentForm.cardNumber"
          :prefix="`<img src='${cardUrl}' />`"
          :postfix="`<i class='fa fa-lock'></i>`"
      />
      <div class="card-date">
        <span class="label">Card Valid Until</span>
        <select-field
            :validation="$v.form.month"
            validationMessage="Required"
            :rest="{
                      placeholder: 'Month'
                    }"
            theme="variant-1"
            :list="Array.apply(null, Array(12)).map((_, idx) => ({ value: idx + 1 }))"
            v-model="paymentForm.month"/>
        <select-field
            :validation="$v.form.year"
            validationMessage="Required"
            :rest="{
                      placeholder: 'Year'
                    }"
            theme="variant-1"
            :list="Array.apply(null, Array(10)).map((_, ind) => ({ value: new Date().getFullYear() + ind }))"
            v-model="paymentForm.year"/>
      </div>
      <text-field
          @click-postfix="openCVVModal"
          :validation="$v.form.cvv"
          validationMessage="Required"
          class="cvv-field"
          theme="variant-1"
          label="CVV"
          :rest="{
                  maxlength: 4,
                  pattern: '\\d*',
                  type: 'tel',
                  autocomplete: 'cc-csc',
                  'data-bluesnap': 'encryptedCvv'
                }"
          v-model="paymentForm.cvv"
          postfix="<i class='fa fa-question-circle'></i>"
      />
    </form>

  </div>
</template>
<script>
	import {getCardUrl} from "../../utils/checkout";
	import creditCardType from 'credit-card-type'

	export default {
		name: "PaymentFormSMC7",
    props: ['$v', 'paymentForm', 'countryList', 'cardNames'],
		data() {
			return {
        cardType: null
			}
		},
		computed: {
			cardUrl() {
				return getCardUrl(this.cardType)
			}
		},
		watch: {
			'paymentForm.cardNumber'(cardNumber) {
				const creditCardTypeList = creditCardType(cardNumber);
				this.cardType = creditCardTypeList.length > 0 && cardNumber.length > 0
					? creditCardTypeList[0].type
					: null;
				this.paymentForm.paymentType = this.cardType = creditCardTypeList.length > 0 && cardNumber.length > 0
					? creditCardTypeList[0].type
					: null
			}
		},
		methods: {
			openCVVModal() {
				const node = document.querySelector('.cvv-popup .el-dialog')
				const listener = () => {
					this.isOpenCVVModal = false
				}
				node.removeEventListener('click', listener)
				node.addEventListener('click', listener)

				this.isOpenCVVModal = true
			}
		}
	}
</script>
<style lang="scss">
  .prefix {
    & > img {
      height: 22px;
      width: auto;
    }

    input {
      &:after {
        content: '\f023';
        display: block;
        color: #555;
        font-family: FontAwesome !important;
        position: absolute;
        top: 8px;
        right: 15px;
      }
    }
  }
</style>