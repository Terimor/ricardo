<template>
  <div class="flex-wrap payment-form-smc7">
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
    <radio-button-group
        :withCustomLabels="true"
        v-model="paymentForm.paymentType"
    >
      <div class="card-types">
        <pay-method-item
            v-for="item in cardNames"
            :key="item.value"
            :input="{
            value: item.value,
            imgUrl: item.imgUrl,
          }"
            :value="paymentForm.paymentType" />
      </div>

    </radio-button-group>

    <select-field
        :validation="$v.form.paymentType"
        validationMessage="Invalid field"
        theme="variant-1"
        :rest="{
                  placeholder: 'VISA'
                }"
        :list="cardNames"
        v-model="paymentForm.paymentType"/>
    <form id="payment-data-form" v-if="paymentForm.paymentType !== 'paypal'">
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
      <el-dialog
          @click="isOpenCVVModal = false"
          class="cvv-popup"
          title="Where do I find my security code?"
          :visible.sync="isOpenCVVModal">
        <div class="cvv-popup__content">
          <p>The CVV code is a 3 digit number that you can find on the back of your credit card. On AMEX cards it is a 4 digit number, found on the front of your credit card.</p>
          <div><img src="/images/cvv_popup.jpg" alt=""></div>
          <p>Where to find the 3 digit security code (Visa/Mastercard)</p>
        </div>
      </el-dialog>
    </form>

  </div>
</template>
<script>
	import {getCardUrl} from "../../utils/checkout";
	import creditCardType from 'credit-card-type'
	import PayMethodItem from "./PayMethodItem";

	export default {
		name: "PaymentFormSMC7",
		components: {PayMethodItem},
		props: ['$v', 'paymentForm', 'countryList', 'cardNames'],
		data() {
			return {
				cardType: null,
				isOpenCVVModal: false
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
			openCVVModal () {
				const node = document.querySelector('.cvv-popup .el-dialog');
				const listener = () => {
					this.isOpenCVVModal = false
				};
				node.removeEventListener('click', listener);
				node.addEventListener('click', listener);

				this.isOpenCVVModal = true
			}
		}
	}
</script>
<style lang="scss">
  .payment-form-smc7 {
    .card-types {
      display: flex;
      width: 100%;
      flex-wrap: wrap;
      .pay-method-item {
        cursor: pointer;
        margin: 5px 12px;
      }
    }
    .cvv-popup {
      .el-dialog {
        margin-top: 10vh !important;
        max-width: 600px;
      }
      .el-dialog__header {
        display: flex;
        justify-content: center;
      }
      .el-dialog__title {
        text-align: center;
        font-size: 20px;
        font-weight: 700;
      }

      &__content {
        display: flex;
        flex-direction: column;
        align-items: center;

        p {
          font-family: 'Noto Sans', sans-serif;
          font-size: 17px;
          width: 100%;
        }

        img {
          max-width: 300px;
          height: auto;
          margin: 0 auto;
        }
      }
    }

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
    .card-icons {
      display: flex;
    }
    .fa.fa-question-circle {
      cursor: pointer;
    }
  }


</style>