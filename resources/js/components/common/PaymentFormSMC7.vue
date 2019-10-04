<template>
  <div class="flex-wrap payment-form-smc7">
    <select-field
        :validation="$v.form.country"
        :validationMessage="textCountryRequired"
        theme="variant-1"
        :label="textCountry"
        class="country"
        :rest="{
          placeholder: textCountry
        }"
        :list="countryList"
        v-model="paymentForm.country"/>
    <text-field
        :validation="$v.form.streetAndNumber"
        :validationMessage="textStreetAndNumberRequired"
        element-loading-spinner="el-icon-loading"
        theme="variant-1 street"
        :label="textStreetAndNumber"
        :rest="{
          placeholder: textStreetAndNumber,
          autocomplete: 'street-address'
        }"
        v-model="paymentForm.streetAndNumber"/>
    <text-field
        :validation="$v.form.city"
        :validationMessage="textCityRequired"
        element-loading-spinner="el-icon-loading"
        theme="variant-1"
        :label="textCity"
        :rest="{
          placeholder: textCity,
          autocomplete: 'shipping locality'
        }"
        v-model="paymentForm.city"/>
    <text-field
        :validation="$v.form.state"
        :validationMessage="textStateRequired"
        element-loading-spinner="el-icon-loading"
        theme="variant-1"
        :label="textState"
        :rest="{
          placeholder: textState,
          autocomplete: 'shipping locality'
        }"
        v-model="paymentForm.state"/>
    <text-field
        :validation="$v.form.zipCode"
        :validationMessage="textZipcodeRequired"
        theme="variant-1"
        :label="textZipCode"
        :rest="{
          placeholder: textZipCode
        }"
        id="zip-code-field"
        v-model="paymentForm.zipCode"/>
    <h2><span>{{paySecurelyWith}}</span></h2>
    <radio-button-group
        :withCustomLabels="true"
        v-model="paymentForm.paymentType">
      <div class="card-types">
        <pay-method-item
          v-for="item in cardNames"
          :key="item.value"
          :input="{
            value: item.value,
            imgUrl: item.imgUrl,
          }"
          :value="paymentForm.paymentType"
        />
      </div>

    </radio-button-group>

    <select-field
        :validation="$v.form.paymentType"
        :validationMessage="textCardTypeRequired"
        theme="variant-1"
        :rest="{
          placeholder: textCardType
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
            autocomplete:
              'cc-number',
              'data-bluesnap': 'encryptedCreditCard'
          }"
          :validationMessage="textCardNumberRequired"
          class="card-number"
          theme="variant-1"
          :label="textCardNumber"
          v-model="paymentForm.cardNumber"
          :prefix="`<img src='${cardUrl}' />`"
          :postfix="`<i class='fa fa-lock'></i>`"
      />
      <div class="card-date">
        <span class="label">{{textCardValidUntil}}</span>
        <select-field
            filterable
            :validation="$v.form.month"
            :validationMessage="textCardValidMonthRequired"
            :rest="{
              placeholder: textCardValidMonthPlaceholder
            }"
            theme="variant-1"
            :list="Array.apply(null, Array(12)).map((_, idx) => ({ value: idx + 1 }))"
            v-model="paymentForm.month"/>
        <select-field
            filterable
            :validation="$v.form.year"
            :validationMessage="textCardValidYearRequired"
            :rest="{
              placeholder: textCardValidYearPlaceholder
            }"
            theme="variant-1"
            :list="Array.apply(null, Array(10)).map((_, ind) => ({ value: new Date().getFullYear() + ind }))"
            v-model="paymentForm.year"/>
      </div>
      <text-field
          @click-postfix="openCVVModal"
          :validation="$v.form.cvv"
          :validationMessage="textCardCVVRequired"
          class="cvv-field"
          theme="variant-1"
          :label="textCardCVV"
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
          :title="textCVVPopupTitle"
          :visible.sync="isOpenCVVModal">
        <div class="cvv-popup__content">
          <p>{{ textCVVPopupLine1 }}</p>
          <div><img src="/images/cvv_popup.jpg" alt=""></div>
          <p>{{ textCVVPopupLine2 }}</p>
        </div>
      </el-dialog>
    </form>

  </div>
</template>
<script>
	import {getCardUrl} from "../../utils/checkout";
	import creditCardType from 'credit-card-type'
	import PayMethodItem from "./PayMethodItem";
    import { t } from '../../utils/i18n';

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
      },

      textCountry : () => t('checkout.payment_form.сountry'),
      textStreetAndNumber : () => t('checkout.payment_form.street_and_number'),
      textStreetAndNumberRequired : () => t('checkout.payment_form.street_and_number.required'),
      paySecurelyWith : () => t('checkout.pay_securely_with'),
      textCity: () => t('checkout.payment_form.city'),
      textCityRequired: () => t('checkout.payment_form.city.required'),
      textState: () => t('checkout.payment_form.state'),
      textZipCode: () => t('checkout.payment_form.zipcode'),
      textZipCodeRequired: () => t('checkout.payment_form.zipcode.required'),
      textCountryRequired: () => t('checkout.payment_form.сountry.required'),
      textZipcodeRequired: () => t('checkout.payment_form.zipcode.required'),
      textCardNumberRequired: () => t('checkout.payment_form.card_number.required'),
      textStateRequired: () => t('checkout.payment_form.state.required'),
      textCardType: () => t('checkout.payment_form.card_type'),
      textCardTypeRequired: () => t('checkout.payment_form.card_type.required'),
      textCardNumber: () => t('checkout.payment_form.card_number'),
      textCardNumberRequired: () => t('checkout.payment_form.card_number.required'),
      textCardValidUntil: () => t('checkout.payment_form.card_valid_until'),
      textCardValidMonthRequired: () => t('checkout.payment_form.card_valid_month.required'),
      textCardValidMonthPlaceholder: () => t('checkout.payment_form.card_valid_month.placeholder'),
      textCardValidYearRequired: () => t('checkout.payment_form.card_valid_year.required'),
      textCardValidYearPlaceholder: () => t('checkout.payment_form.card_valid_year.placeholder'),
      textCardCVV: () => t('checkout.payment_form.card_cvv'),
      textCardCVVRequired: () => t('checkout.payment_form.card_cvv.required'),
      textCVVPopupTitle: () => t('checkout.payment_form.cvv_popup.title'),
      textCVVPopupLine1: () => t('checkout.payment_form.cvv_popup.line_1'),
      textCVVPopupLine2: () => t('checkout.payment_form.cvv_popup.line_2'),
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
