<template>
  <div v-if="$v" class="d-flex flex-wrap payment-form-smc7">
    <Country
      :$v="$v.form.country"
      :form="paymentForm"
      name="country" />
    <ZipCode
      v-if="paymentForm.country === 'br'"
      :$v="$v.form.zipCode"
      :isLoading="isLoading"
      @setBrazilAddress="setBrazilAddress"
      :country="paymentForm.country"
      :form="paymentForm"
      :placeholder="true"
      name="zipCode" />
    <Street
      :$v="$v.form.streetAndNumber"
      :placeholder="true"
      :isLoading="isLoading"
      :form="paymentForm"
      name="streetAndNumber" />
    <Building
      :extraFields="extraFields"
      :placeholder="true"
      :form="paymentForm"
      :$v="$v.form" />
    <Complement
      :isLoading="isLoading"
      :extraFields="extraFields"
      :placeholder="true"
      :form="paymentForm"
      :$v="$v.form" />
    <District
      :isLoading="isLoading"
      :extraFields="extraFields"
      :placeholder="true"
      :form="paymentForm"
      :$v="$v.form" />
    <City
      :$v="$v.form.city"
      :placeholder="true"
      :isLoading="isLoading"
      :form="paymentForm"
      name="city" />
    <State
      :country="paymentForm.country"
      :stateExtraField="stateExtraField"
      :isLoading="isLoading"
      :placeholder="true"
      :form="paymentForm"
      :$v="$v.form" />
    <ZipCode
      v-if="paymentForm.country !== 'br'"
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
      :$v="$v.form" />

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
        :$v="$v.form" />

      <CardNumber
        :$v="$v.form.cardNumber"
        :placeholder="true"
        placeholderText="**** **** **** ****"
        :paymentMethodURL="paymentMethodURL"
        @setPaymentMethodByCardNumber="value => $emit('setPaymentMethodByCardNumber', value)"
        :form="paymentForm"
        name="cardNumber" />

      <CardDate
        :$v="$v.form.cardDate"
        :form="paymentForm"
        name="cardDate" />

      <CVV
        :$v="$v.form.cvv"
        :form="paymentForm"
        name="cvv" />

      <DocumentType
        :extraFields="extraFields"
        :form="paymentForm"
        :$v="$v.form" />

      <DocumentNumber
        :extraFields="extraFields"
        :form="paymentForm"
        :$v="$v.form" />

    </form>

  </div>
</template>
<script>
	import PayMethodItem from "./PayMethodItem";
  import PaymentMethod from './extra-fields/PaymentMethod';
  import Street from './common-fields/Street';
  import City from './common-fields/City';
  import ZipCode from './common-fields/ZipCode';
  import Country from './common-fields/Country';
  import CardHolder from './common-fields/CardHolder';
  import CardNumber from './common-fields/CardNumber';
  import CardDate from './common-fields/CardDate';
  import CVV from './common-fields/CVV';
  import State from './extra-fields/State';
  import Building from './extra-fields/Building';
  import Complement from './extra-fields/Complement';
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
      Street,
      City,
      ZipCode,
      Country,
      CardHolder,
      CardNumber,
      CardDate,
      CVV,
      State,
      Building,
      Complement,
      District,
      CardType,
      DocumentType,
      DocumentNumber,
    },
		props: [
      '$v',
      'paymentForm',
      'extraFields',
      'stateExtraField',
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

      cardNames() {
        const paymentMethods = this.$root.paymentMethods || {};

        const values = Object.keys(this.$root.paymentMethods || [])
          .filter(name => name !== 'instant_transfer');

        if (this.paymentForm.installments === 1 && this.$root.paypalEnabled) {
          values.push('instant_transfer');
        }

        return values.map(value => ({
          value,
          text: paymentMethods[value] && paymentMethods[value].name || '',
          label: paymentMethods[value] && paymentMethods[value].name || '',
          imgUrl: paymentMethods[value] && paymentMethods[value].logo || '',
        }));
      },

      paySecurelyWith : () => t('checkout.pay_securely_with'),
		},

		methods: {
      setBrazilAddress(res) {
        this.paymentForm.streetAndNumber = res.address || this.paymentForm.streetAndNumber;
        this.paymentForm.city = res.city || this.paymentForm.city;
        this.paymentForm.state = res.state || this.paymentForm.state;
        this.paymentForm.district = res.district || this.paymentForm.district;
        this.paymentForm.complement = res.complement || this.paymentForm.complement;
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

    #building-field {
      padding-right: 10px;
      width: 50%;

      [dir="rtl"] & {
        padding-left: 10px;
        padding-right: 0;
      }
    }

    #complement-field {
      width: 50%;
    }

    #card-date-field {
      width: 130px;
    }

    #cvv-field {
      width: 120px;
      margin-left: 10px;

      [dir="rtl"] & {
        margin-left: 0;
        margin-right: 10px;
      }
    }

  }

</style>
