<template>
  <div v-if="$v" class="flex-wrap payment-form-smc7">
    <Country
      :order="-100"
      :tabindex="95"
      :$v="$v.form.country"
      :form="paymentForm"
      name="country" />
    <text-field
        :tabindex="101"
        :validation="$v.form.streetAndNumber"
        :validationMessage="textStreetAndNumberRequired"
        v-loading="isLoading.address"
        element-loading-spinner="el-icon-loading"
        theme="variant-1 street"
        :label="textStreetAndNumber"
        :rest="{
          placeholder: textStreetAndNumber,
          autocomplete: 'street-address'
        }"
        v-model="paymentForm.streetAndNumber"/>
    <District
      :tabindex="102"
      :extraFields="extraFields"
      :withPlaceholder="true"
      :form="paymentForm"
      :$v="$v" />
    <text-field
        :tabindex="103"
        :validation="$v.form.city"
        :validationMessage="textCityRequired"
        v-loading="isLoading.address"
        element-loading-spinner="el-icon-loading"
        theme="variant-1"
        :label="textCity"
        :rest="{
          placeholder: textCity,
          autocomplete: 'shipping locality'
        }"
        v-model="paymentForm.city"/>
    <State
      v-if="extraFields.state"
      :tabindex="104"
      :country="paymentForm.country"
      :extraFields="extraFields"
      :isLoading="isLoading"
      :form="paymentForm"
      :$v="$v" />
    <text-field
        v-else
        :tabindex="104"
        :validation="$v.form.state"
        :validationMessage="textStateRequired"
        v-loading="isLoading.address"
        element-loading-spinner="el-icon-loading"
        theme="variant-1"
        :label="textState"
        :rest="{
          placeholder: textState,
          autocomplete: 'shipping locality'
        }"
        v-model="paymentForm.state"/>
    <ZipCode
      :tabindex="countryCode === 'br' ? 99 : 105"
      :order="countryCode === 'br' ? -1 : null"
      :$v="$v.form.zipCode"
      :isLoading="isLoading"
      @setBrazilAddress="setBrazilAddress"
      :country="paymentForm.country"
      :form="paymentForm"
      :placeholder="true"
      name="zipCode" />
    <h2><span>{{paySecurelyWith}}</span></h2>
    <radio-button-group
        :tabindex="106"
        :withCustomLabels="true"
        v-model="paymentForm.payment_method">
      <div class="card-types">
        <pay-method-item
          v-for="item in cardNames"
          :key="item.value"
          :input="{
            value: item.value,
            imgUrl: item.imgUrl,
          }"
          :value="paymentForm.payment_method"
        />
      </div>
    </radio-button-group>

    <PaymentMethod
      :tabindex="107"
      :extraFields="extraFields"
      :form="paymentForm"
      :$v="$v" />

    <form id="payment-data-form" v-if="paymentForm.paymentProvider !== 'paypal'">

      <CardHolder
        v-if="$root.isAffIDEmpty"
        :tabindex="108"
        :$v="$v.form.cardHolder"
        :form="paymentForm"
        :placeholder="true"
        name="cardHolder" />

      <CardType
        :tabindex="109"
        :extraFields="extraFields"
        :form="paymentForm"
        :$v="$v" />

      <text-field
          :tabindex="110"
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
          :prefix="`<img src='${$parent.paymentMethodURL}' />`"
          :postfix="`<i class='fa fa-lock'></i>`"
      />
      <div class="card-date input-container" :class="{ invalid: $v.form && $v.form.month && $v.form.month.$dirty && $v.form.year && $v.form.year.$dirty && ($v.form.month.$invalid || $v.form.year.$invalid || isCardExpired) }">
        <span class="label">{{textCardValidUntil}}</span>
        <Month
          :tabindex="111"
          :$v="$v.form.month"
          :form="paymentForm"
          name="month" />
        <Year
          :tabindex="112"
          :$v="$v.form.year"
          :form="paymentForm"
          name="year" />
        <span
          class="error"
          v-if="paymentForm.month && paymentForm.year && isCardExpired"
          v-html="textCardExpired"></span>
      </div>
      <text-field
          :tabindex="113"
          @click-postfix="openCVVModal"
          :validation="$v.form.cvv"
          :validationMessage="textCardCVVRequired"
          class="cvv-field"
          theme="variant-1"
          :label="textCardCVV"
          :rest="{
            autocomplete: 'cc-csc',
            'data-bluesnap': 'encryptedCvv'
          }"
          v-model="paymentForm.cvv"
          postfix="<i class='fa fa-question-circle'></i>"
      />
      <DocumentType
        :tabindex="114"
        :extraFields="extraFields"
        :form="paymentForm"
        :$v="$v" />
      <DocumentNumber
        :tabindex="115"
        :extraFields="extraFields"
        :form="paymentForm"
        :$v="$v" />
      <el-dialog
          @click="isOpenCVVModal = false"
          class="cvv-popup"
          :title="textCVVPopupTitle"
          :visible.sync="isOpenCVVModal">
        <div class="cvv-popup__content">
          <p>{{ textCVVPopupLine1 }}</p>
          <div><img :src="$root.cdnUrl + '/assets/images/cvv_popup.jpg'" alt=""></div>
          <p>{{ textCVVPopupLine2 }}</p>
        </div>
      </el-dialog>
    </form>

  </div>
</template>
<script>
  import * as dateFns from 'date-fns';
	import PayMethodItem from "./PayMethodItem";
  import PaymentMethod from './extra-fields/PaymentMethod';
  import ZipCode from './common-fields/ZipCode';
  import Country from './common-fields/Country';
  import CardHolder from './common-fields/CardHolder';
  import Month from './common-fields/Month';
  import Year from './common-fields/Year';
  import State from './extra-fields/State';
  import District from './extra-fields/District';
  import CardType from './extra-fields/CardType';
  import DocumentType from './extra-fields/DocumentType';
  import DocumentNumber from './extra-fields/DocumentNumber';
  import purchasMixin from '../../mixins/purchas';
  import { t } from '../../utils/i18n';

	export default {
		name: "PaymentFormSMC7",
    mixins: [
      purchasMixin,
    ],
		components: {
      PayMethodItem,
      PaymentMethod,
      ZipCode,
      Country,
      CardHolder,
      Month,
      Year,
      State,
      District,
      CardType,
      DocumentType,
      DocumentNumber,
    },
		props: ['$v', 'paymentForm', 'extraFields'],
		data() {
			return {
        isLoading: {
          address: false,
        },
				isOpenCVVModal: false,
			}
		},
		computed: {

      countryCode() {
        return this.paymentForm.country;
      },

      cardNames() {
        const paymentMethods = this.$root.paymentMethods || {};

        const values = Object.keys(this.$root.paymentMethods || [])
          .filter(name => name !== 'instant_transfer');

        if (this.paymentForm.installments === 1) {
          values.push('instant_transfer');
        }

        return values.map(value => ({
          value,
          text: paymentMethods[value] && paymentMethods[value].name || '',
          label: paymentMethods[value] && paymentMethods[value].name || '',
          imgUrl: paymentMethods[value] && paymentMethods[value].logo || '',
        }));
      },

      isCardExpired() {
        return !dateFns.isFuture(new Date(this.paymentForm.year, this.paymentForm.month));
      },

      textState() {
        return t('checkout.payment_form.state', {}, { country: this.paymentForm.country });
      },

      textZipCode() {
        return t('checkout.payment_form.zipcode', {}, { country: this.paymentForm.country });
      },

      textStreetAndNumber : () => t('checkout.payment_form.street_and_number'),
      textStreetAndNumberRequired : () => t('checkout.payment_form.street_and_number.required'),
      paySecurelyWith : () => t('checkout.pay_securely_with'),
      textCity: () => t('checkout.payment_form.city'),
      textCityRequired: () => t('checkout.payment_form.city.required'),
      textZipCodeRequired: () => t('checkout.payment_form.zipcode.required'),
      textCardNumberRequired: () => t('checkout.payment_form.card_number.required'),
      textStateRequired: () => t('checkout.payment_form.state.required'),
      textCardNumber: () => t('checkout.payment_form.card_number'),
      textCardNumberRequired: () => t('checkout.payment_form.card_number.required'),
      textCardValidUntil: () => t('checkout.payment_form.card_valid_until'),
      textCardExpired: () => t('checkout.payment_form.card_expired'),
      textCardCVV: () => t('checkout.payment_form.card_cvv'),
      textCardCVVRequired: () => t('checkout.payment_form.card_cvv.required'),
      textCVVPopupTitle: () => t('checkout.payment_form.cvv_popup.title'),
      textCVVPopupLine1: () => t('checkout.payment_form.cvv_popup.line_1'),
      textCVVPopupLine2: () => t('checkout.payment_form.cvv_popup.line_2'),
		},
		watch: {
			'paymentForm.cardNumber'(newVal, oldValue) {
        newVal = newVal || '';

        if (!newVal.replace(/\s/g, '').match(/^[0-9]{0,19}$/)) {
          this.paymentForm.cardNumber = oldValue;
        }

        this.$parent.setPaymentMethodByCardNumber(newVal);
			},
      'paymentForm.cvv' (newVal, oldValue) {
          if(this.paymentForm.cvv) {
              if(newVal.match(/^[0-9]{0,4}$/g)) {
                  this.paymentForm.cvv = newVal;
              } else {
                  this.paymentForm.cvv = oldValue;
              }
          }
      }
		},
		methods: {
      setBrazilAddress(res) {
        this.paymentForm.streetAndNumber = res.address;
        this.paymentForm.city = res.city;
        this.paymentForm.state = res.state;
      },
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
    display: flex;
    flex-direction: column;

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
