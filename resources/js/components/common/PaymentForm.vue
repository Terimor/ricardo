<template>
    <div class="flex-wrap payment-form" :class="{ 'is-brazil': isBrazil }">
        <h2>
          {{ firstTitle }}
        </h2>
        <text-field
            :validation="$v.form.fname"
            :validationMessage="textFirstNameRequired"
            theme="variant-1"
            :label="textFirstName"
            class="first-name"
            :rest="{
              autocomplete: 'given-name'
            }"
            v-model="paymentForm.fname"/>
        <text-field
            :validation="$v.form.lname"
            :validationMessage="textLastNameRequired"
            theme="variant-1"
            :label="textLastName"
            class="last-name"
            :rest="{
              autocomplete: 'family-name'
            }"
            v-model="paymentForm.lname"/>
        <text-field-with-placeholder
            :validation="$v.form.dateOfBirth"
            :validationMessage="textBirthdayRequired"
            v-if="countryCode === 'de'"
            :rest="{
              'format': 'dd/mm/yyyy',
            }"
            :placeholder="textBirthdayPlaceHolder"
            v-model="paymentForm.dateOfBirth"
            theme="variant-1"
            :label="textBirthday"
        />
        <text-field
            :validation="$v.form.email"
            :validationMessage="textEmailRequired"
            theme="variant-1"
            :label="textEmail"
            :rest="{
              autocomplete: 'email'
            }"
            v-model="paymentForm.email"/>
        <phone-field
            @onCountryChange="setCountryCodeByPhoneField"
            :validation="$v.form.phone"
            :validationMessage="textPhoneRequired"
            :countryCode="countryCode"
            theme="variant-1"
            :label="textPhone"
            :rest="{
              autocomplete: 'off'
            }"
            v-model="paymentForm.phone"/>
        <h2>
          {{ secondTitle }}
        </h2>
        <div class="payment-form__delivery-address">
            <text-field
                :validation="$v.form.street"
                :validationMessage="textStreetRequired"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1 street"
                :label="textStreet"
                :rest="{
                  autocomplete: 'street-address'
                }"
                v-model="paymentForm.street"/>
            <text-field
                :validation="$v.form.number"
                :validationMessage="textStreetNumberRequired"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                v-if="isBrazil"
                theme="variant-1 number"
                :label="textStreetNumber"
                v-model="paymentForm.number"/>
            <el-alert
              style="order: 1;"
              v-if="maxStreetAndNumberLength"
              title="Please use the field below for additional address instructions"
              type="error">
            </el-alert>
            <text-field
                v-if="isSpecialCountrySelected"
                theme="variant-1"
                :label="textComplemento"
                v-model="paymentForm.complemento"/>
            <text-field
                :validation="$v.form.city"
                :validationMessage="textCityRequired"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1"
                :label="textCity"
                :rest="{
                    autocomplete: 'shipping locality'
                }"
                v-model="paymentForm.city"/>
            <select-field
                filterable
                v-if="isSpecialCountrySelected"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                :validationMessage="textStateRequired"
                theme="variant-1"
                :label="textState"
                :rest="{
                  placeholder: textStatePlaceholder
                }"
                :list="stateList"
                v-model="paymentForm.state"/>
            <text-field
                v-else
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                :validationMessage="textStateRequired"
                theme="variant-1"
                :label="textState"
                v-model="paymentForm.state"/>
            <text-field
                :validation="$v.form.zipcode"
                :validationMessage="textZipcodeRequired"
                theme="variant-1"
                :label="textZipcode"
                id="zip-code-field"
                v-model="paymentForm.zipcode"/>
            <select-field
                filterable
                :validation="$v.form.country"
                :validationMessage="textCountryRequired"
                theme="variant-1"
                :label="textCountry"
                :rest="{
                  placeholder: textCountryPlaceholder
                }"
                :list="countryList"
                v-model="paymentForm.country"/>
        </div>
        <template v-if="paymentForm.paymentType !== 'bank-payment'">
            <h2>
              {{ thirdTitle }}
            </h2>
            <select-field
                v-if="paymentForm.country === 'mx'"
                :validation="$v.form.cardType"
                :validationMessage="textCardTypeRequired"
                :disabled="+installments !== 1"
                theme="variant-1"
                v-model="paymentForm.cardType"
                :label="textCardType"
                :rest="{
                  placeholder: textCardTypePlaceholder
                }"
                :list="[
                    {
                        value: 'debit',
                        label: textCardTypeDebit,
                        text: textCardTypeDebit,
                    }, {
                        value: 'credit',
                        label: textCardTypeCredit,
                        text: textCardTypeCredit,
                    }
                ]"/>
            <form id="payment-data-form">
                <text-field
                    :validation="$v.form.cardNumber"
                    :rest="{
                  pattern: '\\d*',
                  type: 'tel',
                  autocomplete: 'cc-number',
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
                <div class="card-date" :class="{ 'with-error': $v.form.year && !$v.form.year.isValid && $v.form.year.$dirty }">
                    <span class="label" v-html="textCardValidUntil"></span>
                    <select-field
                        :validation="$v.form.month"
                        :validationMessage="textCardValidMonthRequired"
                        :rest="{
                      placeholder: textCardValidMonthPlaceholder
                    }"
                        theme="variant-1"
                        :list="Array.apply(null, Array(12)).map((_, idx) => ({ value: idx + 1 }))"
                        v-model="paymentForm.month"/>
                    <select-field
                        :validation="$v.form.year"
                        :validationMessage="textCardValidYearRequired"
                        :rest="{
                      placeholder: textCardValidYearPlaceholder
                    }"
                        theme="variant-1"
                        :list="Array.apply(null, Array(10)).map((_, ind) => ({ value: new Date().getFullYear() + ind }))"
                        v-model="paymentForm.year"/>
                    <span class="error" v-show="$v.form.year && !$v.form.year.isValid && $v.form.year.$dirty" v-html="textCardExpired"></span>
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
            </form>
            <text-field-with-placeholder
                :validation="$v.form.documentNumber"
                :validationMessage="textDocumentNumberRequired"
                v-model="paymentForm.documentNumber"
                v-if="countryCode === 'br' || paymentForm.country === 'br'"
                placeholder="___.___.___-__"
                :rest="{
                  'format': '___.___.___-__',
                  'pattern': '\\d*',
                  type: 'tel'
                }"
                theme="variant-1"
                :label="textDocumentNumber" />
            <text-field-with-placeholder
                :validation="$v.form.documentNumber"
                :validationMessage="textDocumentNumberRequired"
                v-model="paymentForm.documentNumber"
                v-if="countryCode === 'co' || paymentForm.country === 'co'"
                placeholder="1234567890"
                :rest="{
                  'format': '1234567890',
                }"
                theme="variant-1"
                :label="textDocumentNumber" />
        </template>
        <button v-show="hasWarranty" class="warranty-field-button">
          <label for="warranty-field" class="label-container-checkbox">
            <i class="fa fa-arrow-right slide-right warranty-field-arrow"></i>
            <span v-html="textWarranty"></span>: {{quantityOfInstallments}} {{warrantyPriceText}}
            <input id="warranty-field" type="checkbox" v-model="paymentForm.isWarrantyChecked">
            <span class="checkmark"></span>
          </label>
          <span class="warranty-field-icon">
            <img src="/images/best-saller.png" alt="">
          </span>
        </button>
        <p v-if="isPaymentError" id="payment-error" class="error-container" v-html="textPaymentError"></p>
        <button
            @click="submit"
            :disabled="isSubmitted"
            id="purchase-button"
            type="button"
            class="green-button-animated"
            :class="{ 'green-button-active': !isSubmitted }">
            <Spinner v-if="isSubmitted" />
            <div v-if="isSubmitted" class="purchase-button-disabled"></div>
            <span class="purchase-button-text" :style="{ visibility: isSubmitted ? 'hidden' : 'visible' }" v-html="textSubmitButton"></span>
        </button>
        <el-dialog
          @click="isOpenCVVModal = false"
          class="cvv-popup"
          :title="textCVVPopupTitle"
          :visible.sync="isOpenCVVModal">
            <div class="cvv-popup__content">
                <p v-html="textCVVPopupLine1"></p>
                <div><img src="/images/cvv_popup.jpg" alt=""></div>
                <p v-html="textCVVPopupLine2"></p>
            </div>
        </el-dialog>
    </div>
</template>

<script>
  import apiUrlList from '../../constants/api-url'
  import { check as ipqsCheck } from '../../services/ipqs';
  import { t } from '../../utils/i18n';
  import { debounce } from '../../utils/common'
  import queryToComponent from '../../mixins/queryToComponent';
  import scrollToError from '../../mixins/formScrollToError';
  import { sendCheckoutRequest } from '../../utils/checkout';
  import purchasMixin from '../../mixins/purchas';
  import creditCardType from 'credit-card-type'
  import { stateList } from '../../resourses/state';
  import Spinner from './preloaders/Spinner';
  import { sha256 } from 'js-sha256';

  export default {
    name: 'PaymentForm',
    props: [
      'input',
      'countryList',
      'isBrazil',
      'countryCode',
      'installments',
      'paymentForm',
      '$v',
      'firstTitle',
      'secondTitle',
      'thirdTitle',
      'hasWarranty',
      'quantityOfInstallments',
      'warrantyPriceText',
    ],
    mixins: [
      queryToComponent,
      purchasMixin,
      scrollToError,
    ],
    components: {
      Spinner,
    },
    data () {
      return {
        isLoading: {
          address: false
        },
        cardType: null,
        isOpenCVVModal: false,
        isPaymentError: false,
        isSubmitted: false,
      }
    },

    created() {
      if (this.queryParams['3ds'] === 'failure') {
        this.isPaymentError = true;
      }
    },

    mounted() {
      if (this.isPaymentError && !this.isPurchasAlreadyExists) {
        setTimeout(() => document.querySelector('#payment-error').scrollIntoView(), 1000);
      }
    },

    computed: {
      maxStreetAndNumberLength() {
        const numberLength = this.paymentForm.number && this.paymentForm.number.length
        const streetLength = this.paymentForm.street && this.paymentForm.street.length

        if (this.countryCode === 'br' || this.countryCode === 'mx' || this.countryCode === 'co') {
          return numberLength + streetLength >= 35;
        } else {
          return false;
        }
      },
      stateList() {
        return (stateList[this.paymentForm.country] || []).map((it) => ({
          value: it,
          text: it,
          label: it,
        }));
      },

      isSpecialCountrySelected() {
        const specialCountries = ['br', 'mx', 'co'];
        return specialCountries.includes(this.countryCode) || specialCountries.includes(this.paymentForm.country);
      },

      dialCode() {
        const allCountries = window.intlTelInputGlobals.getCountryData();
        const phoneCountryCode = this.paymentForm.countryCodePhoneField;
        const country = allCountries.filter(item => item.iso2 === phoneCountryCode).shift();

        return country ? country.dialCode : '1';
      },

      exp () {
        const { month, year } = this.paymentForm

        if (month && year) {
          return month.length === 2 ? month : '0'.concat(month) + '/' + String(year)
        } else {
          return null
        }
      },
      cardUrl () {
        const cardMap = {
          'american-express': '/images/cc-icons/american-express.png',
          'aura': '/images/cc-icons/aura.png',
          'diners-club': '/images/cc-icons/diners-club.png',
          'discover': '/images/cc-icons/discover.png',
          'elo': '/images/cc-icons/elo.png',
          'hipercard': '/images/cc-icons/hipercard.png',
          'iconcc': '/images/cc-icons/iconcc.png',
          'jcb': '/images/cc-icons/jcb.png',
          'maestro': '/images/cc-icons/maestro.png',
          'mastercard': '/images/cc-icons/mastercard.png',
          'visa': '/images/cc-icons/visa.png'
        }

        return cardMap[this.cardType] || cardMap.iconcc
      },
      textFirstName: () => t('checkout.payment_form.first_name'),
      textFirstNameRequired: () => t('checkout.payment_form.first_name.required'),
      textLastName: () => t('checkout.payment_form.last_name'),
      textLastNameRequired: () => t('checkout.payment_form.last_name.required'),
      textBirthday: () => t('checkout.payment_form.birthday'),
      textBirthdayPlaceHolder: () => t('checkout.payment_form.birthday.placeholder'),
      textBirthdayRequired: () => t('checkout.payment_form.birthday.required'),
      textEmail: () => t('checkout.payment_form.email'),
      textEmailRequired: () => t('checkout.payment_form.email.required'),
      textPhone: () => t('checkout.payment_form.phone'),
      textPhoneRequired: () => t('checkout.payment_form.phone.required'),
      textStreet: () => t('checkout.payment_form.street'),
      textStreetRequired: () => t('checkout.payment_form.street.required'),
      textStreetNumber: () => t('checkout.payment_form.street_number'),
      textStreetNumberRequired: () => t('checkout.payment_form.street_number.required'),
      textComplemento: () => t('checkout.payment_form.complemento'),
      textCity: () => t('checkout.payment_form.city'),
      textCityRequired: () => t('checkout.payment_form.city.required'),
      textState: () => t('checkout.payment_form.state'),
      textStatePlaceholder: () => t('checkout.payment_form.state.placeholder'),
      textStateRequired: () => t('checkout.payment_form.state.required'),
      textZipcode: () => t('checkout.payment_form.zipcode'),
      textZipcodeRequired: () => t('checkout.payment_form.zipcode.required'),
      textCountry: () => t('checkout.payment_form.сountry'),
      textCountryPlaceholder: () => t('checkout.payment_form.сountry.placeholder'),
      textCountryRequired: () => t('checkout.payment_form.сountry.required'),
      textCardType: () => t('checkout.payment_form.card_type'),
      textCardTypeRequired: () => t('checkout.payment_form.card_type.required'),
      textCardTypePlaceholder: () => t('checkout.payment_form.card_type.placeholder'),
      textCardTypeDebit: () => t('checkout.payment_form.card_type.debit'),
      textCardTypeCredit: () => t('checkout.payment_form.card_type.credit'),
      textCardNumber: () => t('checkout.payment_form.card_number'),
      textCardNumberRequired: () => t('checkout.payment_form.card_number.required'),
      textCardValidUntil: () => t('checkout.payment_form.card_valid_until'),
      textCardValidMonthRequired: () => t('checkout.payment_form.card_valid_month.required'),
      textCardValidMonthPlaceholder: () => t('checkout.payment_form.card_valid_month.placeholder'),
      textCardValidYearRequired: () => t('checkout.payment_form.card_valid_year.required'),
      textCardValidYearPlaceholder: () => t('checkout.payment_form.card_valid_year.placeholder'),
      textCardExpired: () => t('checkout.payment_form.card_expired'),
      textCardCVV: () => t('checkout.payment_form.card_cvv'),
      textCardCVVRequired: () => t('checkout.payment_form.card_cvv.required'),
      textDocumentNumber: () => t('checkout.payment_form.document_number'),
      textDocumentNumberRequired: () => t('checkout.payment_form.document_number.required'),
      textWarranty: () => t('checkout.warranty'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      textCVVPopupTitle: () => t('checkout.payment_form.cvv_popup.title'),
      textCVVPopupLine1: () => t('checkout.payment_form.cvv_popup.line_1'),
      textCVVPopupLine2: () => t('checkout.payment_form.cvv_popup.line_2'),
      textPaymentError: () => t('checkout.payment_error'),
    },
    watch: {
      'paymentForm.cardNumber' (cardNumber) {
        const creditCardTypeList = creditCardType(cardNumber)
        this.cardType = creditCardTypeList.length > 0 && cardNumber.length > 0
          ? creditCardTypeList[0].type
          : null
      },
      'paymentForm.zipcode' (zipcode) {
        if (this.isBrazil && !this.$v.form.zipcode.$invalid) {
          this.getLocationByZipcode(zipcode)
        }
      },
      'paymentForm.dateOfBirth' (val) {
        let result = ''
        const configForSlash = [2, 5]
        for (let i = 0; i < val.length; i++) {
          if (configForSlash.includes(i)) {
            result += '/'
          }

          if (!isNaN(val[i]) && val[i] !== ' ') {
            result += val[i]
          }
        }

        this.paymentForm.dateOfBirth = result
      },
      'paymentForm.documentNumber' (val) {
        const isNumber = (val) => !isNaN(val) && val !== ' '

        if (this.countryCode === 'br') {
          let result = ''
          const configForDot = [3, 7]
          const configForDash = [11]
          for (let i = 0; i < val.length; i++) {
            if (configForDot.includes(i)) {
              result += '.'
            }

            if (configForDash.includes(i)) {
              result += '-'
            }

            if (isNumber(val[i])) {
              result += val[i]
            }
          }

          this.paymentForm.documentNumber = result
        } else if (this.countryCode === 'co') {
          let result = ''
          for (let i = 0; i < val.length; i++) {
            if (isNumber(val[i])) {
              result += val[i]
            }
          }
          this.paymentForm.documentNumber = result
        }
      },
      installments (val) {
        if (+val !== 1 && this.countryCode === 'mx') {
          this.paymentForm.cardType = 'credit'
        }
      }
    },
    methods: {
      setCountryCodeByPhoneField (val) {
        if (val.iso2) {
          this.paymentForm.countryCodePhoneField = val.iso2;
        }
      },
      openCVVModal () {
        const node = document.querySelector('.cvv-popup .el-dialog')
        const listener = () => {
            this.isOpenCVVModal = false
        }
        node.removeEventListener('click', listener)
        node.addEventListener('click', listener)

        this.isOpenCVVModal = true
      },
      getLocationByZipcode: debounce(function (zipcode) {
        const { ebanxpay: { url, integration_key } } = apiUrlList
        this.isLoading.address = true

        return Promise.resolve()
          .then(() => {
            return fetch('https://cors-anywhere.herokuapp.com/' + url + '/ws/zipcode?integration_key=' + encodeURIComponent(integration_key) + '&zipcode=' + encodeURIComponent(zipcode), {
              method: 'post',
            });
          })
          .then(res => res.json())
          .then(res => {
            this.isLoading.address = false

            if (res.status === 'ERROR') return

            const { zipcode: { address, city, state } } = res

            this.$emit('setAddress', {
              street: address,
              city,
              state,
            })
          })
          .catch((err) => {
            console.error(err)
            this.isLoading.address = false
          })
      }, 333),
      submit () {
        const { paymentForm, exp } = this;

        this.$v.form.$touch();

        if (this.$v.form.deal.$invalid) {
          document.querySelector('.main__deal').scrollIntoView();
          this.$emit('setPromotionalModal', true);
          return;
        }

        if (this.$v.form.$pending || this.$v.form.$error) {
          this.scrollToError();
          return;
        }

        if (this.isSubmitted) {
          return;
        }

        this.isPaymentError = false;
        this.isSubmitted = true;

        let fields = {
          billing_first_name: paymentForm.fname,
          billing_last_name: paymentForm.lname,
          billing_country: paymentForm.country,
          billing_address_1: paymentForm.street,
          billing_city: paymentForm.city,
          billing_region: paymentForm.state,
          billing_postcode: paymentForm.zipcode,
          billing_email: paymentForm.email,
          billing_phone: this.dialCode + paymentForm.phone,
        };

        if (paymentForm.paymentType === 'credit-card') {
          fields = {
            ...fields,
            credit_card_bin: paymentForm.cardNumber.substr(0, 6),
            credit_card_hash: sha256(paymentForm.cardNumber),
            credit_card_expiration_month: ('0' + paymentForm.month).slice(-2),
            credit_card_expiration_year: ('' + paymentForm.year).substr(2, 2),
            cvv_code: paymentForm.cvv,
          }
        }

        this.setDataToLocalStorage({
          deal: paymentForm.deal,
          variant: paymentForm.variant,
          isWarrantyChecked: paymentForm.isWarrantyChecked,
          installments: paymentForm.installments,
          paymentType: paymentForm.paymentType,
          cardType: paymentForm.cardType,
          fname: paymentForm.fname,
          lname: paymentForm.lname,
          dateOfBirth: paymentForm.dateOfBirth,
          email: paymentForm.email,
          phone: paymentForm.phone,
          countryCodePhoneField: paymentForm.countryCodePhoneField,
          street: paymentForm.street,
          streetNumber: paymentForm.number,
          complemento: paymentForm.complemento,
          city: paymentForm.city,
          state: paymentForm.state,
          zipcode: paymentForm.zipcode,
          country: paymentForm.country,
        });

        Promise.resolve()
          .then(() => ipqsCheck(fields))
          .then(ipqsResult => {
            if (paymentForm.paymentType === 'bank-payment') {
              this.isSubmitted = false;
              this.$emit('showCart');
              return;
            }

            if (paymentForm.paymentType === 'credit-card') {
              const data = {
                product: {
                  sku: paymentForm.variant,
                  qty: parseInt(paymentForm.deal, 10),
                  is_warranty_checked: paymentForm.isWarrantyChecked,
                },
                contact: {
                  phone: {
                    country_code: this.dialCode,
                    number: paymentForm.phone,
                  },
                  first_name: paymentForm.fname,
                  last_name: paymentForm.lname,
                  email: paymentForm.email,
                },
                address: {
                  city: paymentForm.city,
                  country: paymentForm.country,
                  zip: paymentForm.zipcode,
                  state: paymentForm.state,
                  street: paymentForm.street,
                },
                card: {
                  number: paymentForm.cardNumber,
                  cvv: paymentForm.cvv,
                  month: ('0' + paymentForm.month).slice(-2),
                  year: '' + paymentForm.year,
                  type: this.cardType,
                },
                ipqs: ipqsResult,
              };

              sendCheckoutRequest(data)
                .then(res => {
                  if (res.status !== 'ok') {
                    this.isPaymentError = true;
                    this.isSubmitted = false;
                  }
                });
            }
          })
      }
    },
  };
</script>

<style lang="scss">
    @import "../../../sass/variables";

    .payment-form {
        h2 {
            width: 100%;
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
        display: flex;

        .variant-1, .date-picker-manual {
            margin-bottom: 10px;
        }

        .variant-1 {
            .fa.fa-question-circle {
                cursor: pointer;
            }
        }

        &__delivery-address {
            display: flex;
            flex-wrap: wrap;
            width: 100%;

            .input-container, .select {
                order: 1;
            }

            .el-loading-mask {
                top: 26px;
                background-color: rgba(255, 255, 255, .5);

                .el-loading-spinner {
                    margin-top: 0;
                    transform: translateY(-50%);
                }
            }
        }

        &.is-brazil {
            .payment-form__delivery-address {
                #zip-code-field {
                    order: 0;
                }

                .street {
                    width: 60%;
                    margin-right: 10px;
                }

                .number {
                    width: calc(40% - 10px);
                }
            }
        }

        .first-name {
            width: 40%;
            margin-right: 10px;
        }

        .last-name {
            width: calc(60% - 11px);
        }

        .card-number {
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
        }

        .card-date {
            display: flex;
            flex-wrap: wrap;
            width: 70%;
            padding-right: 30px;
            margin-bottom: 10px;

            &.with-error {
                & > .label {
                    color: #e74c3c;
                }
            }

            & > .label {
                width: 100%;
                margin-bottom: 6px;
            }

            & > div {
                width: calc(40% - 5px);

                margin-right: 10px;

                &:last-child {
                    margin-right: 0;
                    width: calc(60% - 5px);
                }
            }
        }

        .cvv-field {
            width: calc(30%);
        }

        @media screen and ($s-down) {
            .el-dialog {
                width: 90%;
                margin-top: 15% !important;
            }
        }

        .purchase-button-text {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            cursor: pointer;
            text-align: center;
            text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
            text-transform: capitalize;
            column-rule-color: rgb(255, 255, 255);
            perspective-origin: 0 0;
            transform-origin: 0 0;
            caret-color: rgb(255, 255, 255);
            border: 0 none rgb(255, 255, 255);
            font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
            outline: rgb(255, 255, 255) none 0;

            &:after {
                box-sizing: border-box;
                color: rgb(255, 255, 255);
                cursor: pointer;
                text-align: center;
                text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
                text-transform: capitalize;
                column-rule-color: rgb(255, 255, 255);
                caret-color: rgb(255, 255, 255);
                border: 0 none rgb(255, 255, 255);
                font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
                outline: rgb(255, 255, 255) none 0;
            }

            &:before {
                box-sizing: border-box;
                color: rgb(255, 255, 255);
                cursor: pointer;
                text-align: center;
                text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
                text-transform: capitalize;
                column-rule-color: rgb(255, 255, 255);
                caret-color: rgb(255, 255, 255);
                border: 0 none rgb(255, 255, 255);
                font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
                outline: rgb(255, 255, 255) none 0;
            }
        }

        .purchase-button-disabled {
          background-color: #fff;
          bottom: 0;
          left: 0;
          opacity: .5;
          position: absolute;
          right: 0;
          top: 0;
          z-index: 1;
        }

        #payment-error {
          font-size: 17px;
          text-align: center;
          width: 100%;
        }
    }

  .warranty-field {
    &-button {
      position: relative;
      display: flex;
      padding: 10px;
      margin: 40px 0;
    }

    &-icon {
      position: absolute;
      right: -15px;
      top: -15px;
      width: 30px;
      height: 30px;

      img {
        max-width: 100%;
      }
    }

    &-arrow {
      position: absolute;
      left: -30px;
      top: 0;
      z-index: 2;
    }
  }

  .label-container-checkbox {
    position: relative;
    margin-left: 30px;
  }

  #payment-data-form {
    display: flex;
    flex-wrap: wrap;
  }

  @media screen and ($s-down) {
    .cvv-popup {
      .el-dialog {
        width: 90%;
      }
    }
}
</style>
