<template>
  <div v-if="$v" class="flex-wrap payment-form-smc7">
    <Country
      :$v="$v.form.country"
      :form="paymentForm"
      name="country" />
    <ZipCode
      v-if="countryCode === 'br'"
      :$v="$v.form.zipCode"
      :isLoading="isLoading"
      @setBrazilAddress="setBrazilAddress"
      :country="paymentForm.country"
      :form="paymentForm"
      :placeholder="true"
      name="zipCode" />
    <text-field
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
      :extraFields="extraFields"
      :withPlaceholder="true"
      :form="paymentForm"
      :$v="$v" />
    <text-field
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
      :country="paymentForm.country"
      :extraFields="extraFields"
      :isLoading="isLoading"
      :form="paymentForm"
      :$v="$v" />
    <text-field
        v-else
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
      v-if="countryCode !== 'br'"
      :$v="$v.form.zipCode"
      :isLoading="isLoading"
      @setBrazilAddress="setBrazilAddress"
      :country="paymentForm.country"
      :form="paymentForm"
      :placeholder="true"
      name="zipCode" />
    <h2><span>{{paySecurelyWith}}</span></h2>
    <radio-button-group
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
      :extraFields="extraFields"
      :form="paymentForm"
      :$v="$v" />

    <form id="payment-data-form" v-if="paymentForm.paymentProvider !== 'paypal'">

      <CardHolder
        v-if="$root.isAffIDEmpty"
        :$v="$v.form.cardHolder"
        :form="paymentForm"
        :placeholder="true"
        name="cardHolder" />

      <CardType
        :extraFields="extraFields"
        :form="paymentForm"
        :$v="$v" />

      <CardNumber
        :$v="$v.form.cardNumber"
        :placeholder="true"
        placeholderText="**** **** **** ****"
        :paymentMethodURL="paymentMethodURL"
        @setPaymentMethodByCardNumber="value => $emit('setPaymentMethodByCardNumber', value)"
        :form="paymentForm"
        name="cardNumber" />
      <div class="card-date-cvv">
        <CardDate
          :$v="$v.form.cardDate"
          :form="paymentForm"
          name="cardDate" />
        <CVV
          :$v="$v.form.cvv"
          :form="paymentForm"
          name="cvv" />
      </div>
      <DocumentType
        :extraFields="extraFields"
        :form="paymentForm"
        :$v="$v" />
      <DocumentNumber
        :extraFields="extraFields"
        :form="paymentForm"
        :$v="$v" />
    </form>

  </div>
</template>
<script>
	import PayMethodItem from "./PayMethodItem";
  import PaymentMethod from './extra-fields/PaymentMethod';
  import ZipCode from './common-fields/ZipCode';
  import Country from './common-fields/Country';
  import CardHolder from './common-fields/CardHolder';
  import CardNumber from './common-fields/CardNumber';
  import CardDate from './common-fields/CardDate';
  import CVV from './common-fields/CVV';
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
      CardNumber,
      CardDate,
      CVV,
      State,
      District,
      CardType,
      DocumentType,
      DocumentNumber,
    },
		props: [
      '$v',
      'paymentForm',
      'extraFields',
      'paymentMethodURL',
    ],
		data() {
			return {
        isLoading: {
          address: false,
        },
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
      textStateRequired: () => t('checkout.payment_form.state.required'),
		},

		methods: {
      setBrazilAddress(res) {
        this.paymentForm.streetAndNumber = res.address;
        this.paymentForm.city = res.city;
        this.paymentForm.state = res.state;
      },
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

    .card-icons {
      display: flex;
    }

    .fa.fa-question-circle {
      cursor: pointer;
    }

    .card-date-cvv {
      display: flex;
      width: 100%;
    }

    #card-date-field {
      padding-right: 30px;
      width: 70%;
    }

    #cvv-field {
      width: 30%;
    }

  }

</style>
