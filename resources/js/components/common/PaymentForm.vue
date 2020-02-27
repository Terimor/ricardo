<template>
    <form v-if="$v" class="flex-wrap payment-form">
        <h2>
          {{ firstTitle }}
        </h2>
        <div class="payment-form__contact-information">
          <FirstName
            :$v="$v.form.fname"
            :form="paymentForm"
            name="fname" />
          <LastName
            :$v="$v.form.lname"
            :form="paymentForm"
            name="lname" />
          <Email
            :$v="$v.form.email"
            :form="paymentForm"
            name="email" />
          <Phone
            :$v="$v.form.phone"
            :ccform="paymentForm"
            ccname="countryCodePhoneField"
            :form="paymentForm"
            name="phone" />
        </div>
        <h2>
          {{ secondTitle }}
        </h2>
        <div class="payment-form__delivery-address">
            <ZipCode
              v-if="paymentForm.country === 'br'"
              :$v="$v.form.zipcode"
              :isLoading="isLoading"
              @setBrazilAddress="setBrazilAddress"
              :country="paymentForm.country"
              :form="paymentForm"
              name="zipcode" />
            <Street
              :$v="$v.form.street"
              :isLoading="isLoading"
              :form="paymentForm"
              name="street" />
            <Building
              :extraFields="extraFields"
              :form="paymentForm"
              :$v="$v.form" />
            <Complement
              :isLoading="isLoading"
              :extraFields="extraFields"
              :form="paymentForm"
              :$v="$v.form" />
            <District
              :isLoading="isLoading"
              :extraFields="extraFields"
              :form="paymentForm"
              :$v="$v.form" />
            <City
              :$v="$v.form.city"
              :isLoading="isLoading"
              :form="paymentForm"
              name="city" />
            <State
              :placeholder="true"
              :country="paymentForm.country"
              :stateExtraField="stateExtraField"
              :isLoading="isLoading"
              :form="paymentForm"
              :$v="$v.form" />
            <ZipCode
              v-if="paymentForm.country !== 'br'"
              :$v="$v.form.zipcode"
              :isLoading="isLoading"
              @setBrazilAddress="setBrazilAddress"
              :country="paymentForm.country"
              :form="paymentForm"
              name="zipcode" />
            <Country
              :$v="$v.form.country"
              :form="paymentForm"
              name="country" />
        </div>
        <template v-if="paymentForm.paymentProvider !== 'bank-payment'">
            <h2>
              {{ thirdTitle }}
            </h2>
            <div class="payment-form__payment-details">
              <CardHolder
                v-if="$root.isAffIDEmpty"
                :$v="$v.form.cardHolder"
                :form="paymentForm"
                name="cardHolder" />
              <CardType
                :extraFields="extraFields"
                :form="paymentForm"
                :$v="$v.form" />
              <div id="payment-data-form">
                <CardNumber
                  :$v="$v.form.cardNumber"
                  :paymentMethodURL="paymentMethodURL"
                  @setPaymentMethodByCardNumber="setPaymentMethodByCardNumber"
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
              </div>
              <DocumentType
                :extraFields="extraFields"
                :form="paymentForm"
                :$v="$v.form" />
              <DocumentNumber
                :extraFields="extraFields"
                :form="paymentForm"
                :$v="$v.form" />
            </div>
        </template>
        <!-- vmp41/42 block -->
        <span v-show="hasWarranty" class="warranty-field-button">
          <label for="warranty-field" class="label-container-checkbox">
            <i class="fa fa-arrow-left slide-left warranty-field-arrow"></i>
            <i class="fa fa-arrow-right slide-right warranty-field-arrow"></i>
            <span v-html="textWarranty"></span>: {{quantityOfInstallments}} {{warrantyPriceText}}
            <input id="warranty-field" type="checkbox" v-model="paymentForm.isWarrantyChecked">
            <span class="checkmark"></span>
          </label>
          <span class="warranty-field-icon">
            <img :data-src="$root.cdn_url + '/assets/images/best-saller.png'" alt="">
          </span>
        </span>
        <Terms
          v-if="$root.isAffIDEmpty"
          :$v="$v.form.terms"
          :form="paymentForm"
          name="terms" />
        <p v-if="paymentError" id="payment-error" class="error-container" v-html="paymentError"></p>
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
    </form>
</template>

<script>
  import { ipqsCheck } from '../../services/ipqs';
  import { t } from '../../utils/i18n';
  import { debounce } from '../../utils/common'
  import queryToComponent from '../../mixins/queryToComponent';
  import scrollToError from '../../mixins/formScrollToError';
  import { sendCheckoutRequest, get3dsErrors } from '../../utils/checkout';
  import purchasMixin from '../../mixins/purchas';
  import Spinner from './preloaders/Spinner';
  import FirstName from './common-fields/FirstName';
  import LastName from './common-fields/LastName';
  import Email from './common-fields/Email';
  import Phone from './common-fields/Phone';
  import Street from './common-fields/Street';
  import City from './common-fields/City';
  import ZipCode from './common-fields/ZipCode';
  import Country from './common-fields/Country';
  import CardHolder from './common-fields/CardHolder';
  import CardNumber from './common-fields/CardNumber';
  import CardDate from './common-fields/CardDate';
  import CVV from './common-fields/CVV';
  import Terms from './common-fields/Terms';
  import State from './extra-fields/State';
  import Building from './extra-fields/Building';
  import Complement from './extra-fields/Complement';
  import District from './extra-fields/District';
  import CardType from './extra-fields/CardType';
  import DocumentType from './extra-fields/DocumentType';
  import DocumentNumber from './extra-fields/DocumentNumber';
  import logger from '../../mixins/logger';


  export default {
    name: 'PaymentForm',
    props: [
      'input',
      'paymentForm',
      '$v',
      'firstTitle',
      'secondTitle',
      'thirdTitle',
      'hasWarranty',
      'quantityOfInstallments',
      'warrantyPriceText',
      'extraFields',
      'stateExtraField',
      'paymentMethodURL',
    ],
    mixins: [
      queryToComponent,
      purchasMixin,
      scrollToError,
      logger,
    ],
    components: {
      Spinner,
      FirstName,
      LastName,
      Email,
      Phone,
      Street,
      City,
      ZipCode,
      Country,
      CardHolder,
      CardNumber,
      CardDate,
      CVV,
      Terms,
      State,
      Building,
      Complement,
      District,
      CardType,
      DocumentType,
      DocumentNumber,
    },
    data () {
      return {
        isLoading: {
          address: false,
        },
        isSubmitted: false,
        paymentError: '',
      }
    },

    created() {
      if (this.queryParams['3ds'] === 'failure') {
        get3dsErrors().then(paymentError => {
          this.paymentError = paymentError;

          if (this.paymentForm.country === 'br') {
            let ipqs = null; try { ipqs = JSON.parse(localStorage.getItem('3ds_ipqs')); } catch (err) {};

            this.log_data('BRAZIL: EMC1 - Credit Card - 3ds failure', {
              error: paymentError,
              form: { ...this.$v.form.$model, cardNumber: undefined },
              fraud_chance: ipqs ? ipqs.fraud_chance : null,
              ipqs,
            });
          }

          setTimeout(() => {
            const element = document.querySelector('#payment-error');

            if (element && element.scrollIntoView) {
              element.scrollIntoView();
            }
          }, 100);
        });
      }

      if (this.queryParams['3ds'] === 'pending' && this.queryParams['bs_pf_token']) {
        setTimeout(() => {
          this.isSubmitted = true;

          sendCheckoutRequest({ bs_3ds_pending: true })
            .then(res => {
              if (res.paymentError) {
                this.paymentError = res.paymentError;
                this.isSubmitted = false;
              }
            })
            .catch(err => {
              this.paymentError = t('checkout.payment_error');
              this.isSubmitted = false;
            });

          const element = document.querySelector('#purchase-button');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }
        }, 1000);
      }
    },

    computed: {
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

      textWarranty: () => t('checkout.warranty'),
      textSubmitButton: () => t('checkout.payment_form.submit_button'),
      textPaymentError: () => t('checkout.payment_error'),
    },

    methods: {
      setPaymentMethodByCardNumber(value) {
        this.$emit('setPaymentMethodByCardNumber', value);
        this.$emit('set-payment-method-by-cardnumber', value);
      },
      setBrazilAddress(res) {
        this.paymentForm.street = res.address || this.paymentForm.street;
        this.paymentForm.city = res.city || this.paymentForm.city;
        this.paymentForm.state = res.state || this.paymentForm.state;
        this.paymentForm.district = res.district || this.paymentForm.district;
        this.paymentForm.complement = res.complement || this.paymentForm.complement;
      },
      submit () {
        let ipqsResult = null;
        const { paymentForm, exp } = this;

        this.$v.form.$touch();

        if (this.$v.form.$invalid && this.paymentForm.country === 'br') {
          this.log_data('BRAZIL: EMC1 - Credit Card - form validation', {
            invalid: Object.keys(this.$v.form)
              .filter(name => name !== 'cardNumber' && !!this.$v.form[name].$invalid)
              .reduce((acc, name) => { acc[name] = this.$v.form.$model[name]; return acc; }, {}),
            form: { ...this.$v.form.$model, cardNumber: undefined },
          });
        }

        if (this.$v.form.deal.$invalid) {
          const element = document.querySelector('.main__deal');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

          this.$emit('setPromotionalModal', true);
          return;
        } else if (this.$v.form.variant.$invalid) {
          const element = document.querySelector('.variant-selection');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

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

        this.paymentError = '';
        this.isSubmitted = true;

        const phoneNumber = paymentForm.phone.replace(/[^0-9]/g, '');
        const cardNumber = paymentForm.cardNumber.replace(/[^0-9]/g, '');

        if (paymentForm.emailForceInvalid) {
          if (this.paymentForm.country === 'br') {
            this.log_data('BRAZIL: EMC1 - Credit Card - email blocked', {
              email: this.paymentForm.email,
              form: { ...this.$v.form.$model, cardNumber: undefined },
            });
          }

          return setTimeout(() => {
            this.paymentError = t('checkout.abuse_error');
            this.isSubmitted = false;
          }, 1000);
        }

        let data = {
          deal: paymentForm.deal,
          variant: paymentForm.variant,
          isWarrantyChecked: paymentForm.isWarrantyChecked,
          paymentProvider: paymentForm.paymentProvider,
          fname: paymentForm.fname,
          lname: paymentForm.lname,
          email: paymentForm.email,
          phone: paymentForm.phone,
          countryCodePhoneField: paymentForm.countryCodePhoneField,
          street: paymentForm.street,
          city: paymentForm.city,
          zipcode: paymentForm.zipcode,
          country: paymentForm.country,
        };

        this.$parent.setExtraFieldsForLocalStorage(data);
        this.setDataToLocalStorage(data);

        Promise.resolve()
          .then(() => {
            let data = {
              order_amount: this.getOrderAmount(paymentForm.deal, paymentForm.isWarrantyChecked),
              billing_first_name: paymentForm.fname,
              billing_last_name: paymentForm.lname,
              billing_country: paymentForm.country,
              billing_address_1: paymentForm.street,
              billing_city: paymentForm.city,
              billing_region: this.extraFields.state
                ? paymentForm.state
                : '',
              billing_postcode: paymentForm.zipcode,
              billing_email: paymentForm.email,
              billing_phone: this.dialCode + phoneNumber,
              credit_card_bin: cardNumber.substr(0, 6),
              credit_card_expiration_month: paymentForm.cardDate.split('/')[0],
              credit_card_expiration_year: paymentForm.cardDate.split('/')[1],
              cvv_code: paymentForm.cvv,
            };

            if (window.sha256) {
              data.credit_card_hash = sha256(cardNumber);
            }

            return ipqsCheck(data);
          })
          .then(result => {
            ipqsResult = result;
          })
          .then(() => {
            let data = {
              product: {
                sku: paymentForm.variant,
                qty: parseInt(paymentForm.deal, 10),
                is_warranty_checked: paymentForm.isWarrantyChecked,
              },
              contact: {
                phone: {
                  country_code: this.dialCode,
                  number: phoneNumber,
                },
                first_name: paymentForm.fname,
                last_name: paymentForm.lname,
                email: paymentForm.email,
              },
              address: {
                city: paymentForm.city,
                country: paymentForm.country,
                zip: paymentForm.zipcode,
                street: paymentForm.street,
              },
              card: {
                number: cardNumber,
                cvv: paymentForm.cvv,
                month: paymentForm.cardDate.split('/')[0],
                year: '20' + paymentForm.cardDate.split('/')[1],
              },
              ipqs: ipqsResult,
            };

            if (this.$root.isAffIDEmpty) {
              data.card.holder = paymentForm.cardHolder;
            }

            this.$parent.setExtraFieldsForCardPayment(data);

            return sendCheckoutRequest(data);
          })
          .then(res => {
            if (res.status !== 'ok' && this.paymentForm.country === 'br') {
              this.log_data('BRAZIL: EMC1 - Credit Card - response', {
                error: res.paymentError,
                res: { ...res, paymentError: undefined },
                form: { ...this.$v.form.$model, cardNumber: undefined },
                fraud_chance: ipqsResult.fraud_chance,
                ipqs: ipqsResult,
              });
            }

            if (res.paymentError) {
              this.paymentError = res.paymentError;
              this.isSubmitted = false;
            }
          })
          .catch(err => {
            this.paymentError = t('checkout.payment_error');
            this.isSubmitted = false;
          });
      }
    },
  };
</script>

<style lang="scss">
    @import "../../../sass/variables";

    .payment-form {
        display: flex;

        h2 {
            width: 100%;
        }

        .variant-1, .date-picker-manual {
            margin-bottom: 10px;
        }

        .variant-1 {
            .fa.fa-question-circle {
                cursor: pointer;
            }
        }

        &__contact-information,
        &__delivery-address,
        &__payment-details, {
            display: flex;
            flex-wrap: wrap;
            width: 100%;

            .el-loading-mask {
                top: 30px;
                background-color: rgba(255, 255, 255, .5);

                .el-loading-spinner {
                    margin-top: 0;
                    transform: translateY(-50%);
                    top: 20px;
                }
            }
        }

        #first-name-field {
          width: 40%;
          padding-right: 10px;

          [dir="rtl"] & {
            padding-left: 10px;
            padding-right: 0;
          }
        }

        #last-name-field {
          width: 60%;
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

        @media screen and ($s-down) {
            .el-dialog {
                width: 90%;
                margin-top: 15% !important;
            }
        }

        #purchase-button:focus {
          box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
          border-color: #409EFF;
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
      width: 100%;
      position: relative;
      display: flex;
      padding: 0;
      margin: 40px 0;
      background: linear-gradient(to bottom, #f7f7f7, #dddddd);
      border: 1px solid #a5a5a5;
      transition: all .3s;
      &:hover, &:focus, &:active {
          background: linear-gradient(to bottom, #dddddd, #f7f7f7);
      }
      .label-container-checkbox {
          margin: 0;
          padding: 13px 10px 22px 75px;
          font-size: 18px;

          [dir="rtl"] & {
            padding: 13px 75px 22px 10px;
          }

          .checkmark {
              left: 40px;
              top: 13px;

              [dir="rtl"] & {
                left: auto;
                right: 40px;
              }
          }
      }

      .fa-arrow-left {
        display: none;
        position: absolute;
        right: 10px;
        top: 13px;
        z-index: 2;
        

        [dir="rtl"] & {
          display: block;
        }
      }

      .fa-arrow-right {
        position: absolute;
        left: 10px;
        top: 13px;
        z-index: 2;

        [dir="rtl"] & {
          display: none;
        }
      }
    }

    &-icon {
      position: absolute;
      right: -15px;
      top: -15px;
      width: 30px;
      height: 30px;

      [dir="rtl"] & {
        left: -15px;
        right: auto;
        transform: rotate(-24deg);
      }

      img {
        max-width: 100%;
      }
    }
  }

  #payment-data-form {
    display: flex;
    flex-wrap: wrap;
  }

</style>
