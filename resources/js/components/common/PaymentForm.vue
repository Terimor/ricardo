<template>
    <div class="flex-wrap payment-form" :class="{ 'is-brazil': isBrazil }">
        <h2>Step 4: Contact Information</h2>
        <text-field
            theme="variant-1"
            label="First Name"
            class="first-name"
            v-model="paymentForm.fname"/>
        <text-field
            theme="variant-1"
            label="Last Name"
            class="last-name"
            v-model="paymentForm.lname"/>
        <text-field-with-placeholder
            :invalid="!$v.form.dateOfBirth.isValidDate && $v.form.dateOfBirth.$dirty"
            :validation="$v.form.dateOfBirth"
            invalidMessage="Invalid date"
            v-if="countryCode === 'DE' || countryCode === 'CO' || countryCode === 'BR'"
            :rest="{
              'format': 'dd/mm/yyyy',
              'default-value': eighteenYearsAgo
            }"
            placeholder="DD/MM/YYYY"
            :disabledDate="disabledDate"
            v-model="paymentForm.dateOfBirth"
            theme="variant-1"
            label="Your Date Of Birth"
        />
        <text-field
            theme="variant-1"
            label="Your Email Address"
            v-model="paymentForm.email"/>
        <phone-field
            theme="variant-1"
            label="Your Phone Number"
            v-model="paymentForm.phone"/>
        <h2>Step 5: Delivery Address</h2>
        <div class="payment-form__delivery-address">
            <text-field
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1 street"
                :label="isBrazil ? 'Street' : 'Street And Number'"
                v-model="paymentForm.street"/>
            <text-field
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                v-if="isBrazil"
                theme="variant-1 number"
                label="Number"
                v-model="paymentForm.number"/>
            <text-field
                v-if="isBrazil"
                theme="variant-1"
                label="Complemento"
                v-model="paymentForm.complemento"/>
            <text-field
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1"
                label="City"
                v-model="paymentForm.city"/>
            <text-field
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1"
                label="State"
                v-model="paymentForm.state"/>
            <text-field
                theme="variant-1"
                label="Zip Code"
                id="zip-code-field"
                v-model="paymentForm.zipcode"/>
            <select-field
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1"
                label="Country"
                :rest="{
                  placeholder: 'Country'
                }"
                :list="countryList"
                v-model="paymentForm.country"/>
        </div>
        <h2>Step 6: Payment Details</h2>
        <select-field
            v-if="countryCode === 'MX'"
            :disabled="+installments !== 1"
            theme="variant-1"
            v-model="paymentForm.cardType"
            label="Please select your card type"
            :rest="{
              placeholder: 'Card type'
            }"
            :list="[
                {
                    value: 'debit',
                    label: 'Debit card',
                    text: 'Debit card',
                }, {
                    value: 'credit',
                    label: 'Credit card',
                    text: 'Credit card',
                }
            ]"/>
        <text-field
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
                :rest="{
                  placeholder: 'Month'
                }"
                theme="variant-1"
                :list="Array.apply(null, Array(12)).map((_, idx) => ({ value: idx + 1 }))"
                v-model="paymentForm.month"/>
            <select-field
                :rest="{
                    placeholder: 'Year'
                  }"
                theme="variant-1"
                :list="Array.apply(null, Array(10)).map((_, ind) => ({ value: new Date().getFullYear() + ind }))"
                v-model="paymentForm.year"/>
        </div>
        <text-field
            class="cvv-field"
            theme="variant-1"
            label="CVV"
            v-model="paymentForm.cvv"
            postfix="<i class='fa fa-question-circle'></i>"
        />
    </div>
</template>

<script>
  import apiUrlList from '../../constants/api-url'
  import { debounce } from '../../utils/common'
  import creditCardType from 'credit-card-type'

  export default {
    name: 'PaymentForm',
    props: ['input', 'countryList', 'isBrazil', 'countryCode', 'installments', 'paymentForm', '$v'],
    data () {
      return {
        isLoading: {
          address: false
        },
        cardType: null
      }
    },
    computed: {
      eighteenYearsAgo () {
        return Date.now() - 31536000000 * 18
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
          'visa': '/images/cc-icons/visa.png',
        }

        return cardMap[this.cardType] || cardMap.iconcc
      }
    },
    watch: {
      'paymentForm.cardNumber' (cardNumber) {
        const creditCardTypeList = creditCardType(cardNumber)
        this.cardType = creditCardTypeList.length > 0 && cardNumber.length > 0
          ? creditCardTypeList[0].type
          : null
      },
      'paymentForm.zipcode' (zipcode) {
        if (this.isBrazil) {
          this.getLocationByZipcode(zipcode)
        }
      },
      installments (val) {
        if (+val !== 1 && this.countryCode === 'MX') {
          this.paymentForm.cardType = 'credit'
        }
      }
    },
    methods: {
      disabledDate(date) {
        return date.getTime() > this.eighteenYearsAgo
      },
      getLocationByZipcode: debounce(function (zipcode) {
        const { ebanxpay: { url, integration_key } } = apiUrlList
        this.isLoading.address = true

        return axios.post('https://cors-anywhere.herokuapp.com/' + url + `/ws/zipcode`, null, { // TODO delete proxy
          params: {
            integration_key,
            zipcode,
          }
        })
          .then((res) => {
            this.isLoading.address = false

            if (res.data.status === 'ERROR') return

            const { data: { zipcode: { address, city, state } } } = res

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
      }, 333)
    }
  };
</script>

<style lang="scss">
    .payment-form {
        display: flex;

        .variant-1, .date-picker-manual {
            margin-bottom: 10px;
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
    }
</style>
