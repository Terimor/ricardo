<template>
    <div class="flex-wrap payment-form" :class="{ 'is-brazil': isBrazil }">
        <h2>Step 4: Contact Information</h2>
        <text-field
            :validation="$v.form.fname"
            validationMessage="Please enter your first name"
            theme="variant-1"
            label="First Name"
            class="first-name"
            :rest="{
              autocomplete: 'given-name'
            }"
            v-model="paymentForm.fname"/>
        <text-field
            :validation="$v.form.lname"
            validationMessage="Please enter your last name"
            theme="variant-1"
            label="Last Name"
            class="last-name"
            :rest="{
              autocomplete: 'family-name'
            }"
            v-model="paymentForm.lname"/>
        <text-field-with-placeholder
            :validation="$v.form.dateOfBirth"
            validationMessage="Invalid date"
            v-if="countryCode === 'DE'"
            :rest="{
              'format': 'dd/mm/yyyy',
            }"
            placeholder="DD/MM/YYYY"
            v-model="paymentForm.dateOfBirth"
            theme="variant-1"
            label="Your Date Of Birth"
        />
        <text-field
            :validation="$v.form.email"
            validationMessage="Please enter a valid e-mail"
            theme="variant-1"
            label="Your Email Address"
            :rest="{
              autocomplete: 'email'
            }"
            v-model="paymentForm.email"/>
        <phone-field
            :validation="$v.form.phone"
            validationMessage="Please enter a valid phone number"
            :countryCode="countryCode"
            theme="variant-1"
            label="Your Phone Number"
            :rest="{
              autocomplete: 'off'
            }"
            v-model="paymentForm.phone"/>
        <h2>Step 5: Delivery Address</h2>
        <div class="payment-form__delivery-address">
            <text-field
                :validation="$v.form.street"
                validationMessage="Please enter your street"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1 street"
                :label="isBrazil ? 'Street' : 'Street And Number'"
                :rest="{
                  autocomplete: 'street-address'
                }"
                v-model="paymentForm.street"/>
            <text-field
                :validation="$v.form.number"
                validationMessage="Required"
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
                :validation="$v.form.city"
                validationMessage="Please enter your city"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                theme="variant-1"
                label="City"
                :rest="{
                    autocomplete: 'shipping locality'
                }"
                v-model="paymentForm.city"/>
            <select-field
                v-if="countryCode === 'BR' || countryCode === 'MX'"
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                validationMessage="Please enter or select your state"
                theme="variant-1"
                label="State"
                :rest="{
                  placeholder: 'State'
                }"
                :list="stateList"
                v-model="paymentForm.state"/>
            <text-field
                v-else
                v-loading="isLoading.address"
                element-loading-spinner="el-icon-loading"
                validationMessage="Please enter or select your state"
                theme="variant-1"
                label="State"
                v-model="paymentForm.state"/>
            <text-field
                :validation="$v.form.zipcode"
                validationMessage="Please enter your zip code"
                theme="variant-1"
                label="Zip Code"
                id="zip-code-field"
                v-model="paymentForm.zipcode"/>
            <select-field
                :validation="$v.form.country"
                validationMessage="Invalid field"
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
            :validation="$v.form.cardType"
            validationMessage="Invalid Card Type"
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
            :validation="$v.form.cardNumber"
            :rest="{
              pattern: '\\d*',
              type: 'tel',
              autocomplete: 'cc-number'
            }"
            validationMessage="Please enter a credit card number."
            class="card-number"
            theme="variant-1"
            label="Card Number"
            v-model="paymentForm.cardNumber"
            :prefix="`<img src='${cardUrl}' />`"
            :postfix="`<i class='fa fa-lock'></i>`"
        />
        <div class="card-date" :class="{ 'with-error': !$v.form.year.isValid && $v.form.year.$dirty }">
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
            <span class="error" v-show="!$v.form.year.isValid && $v.form.year.$dirty">Card is expired</span>
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
              autocomplete: 'cc-csc'
            }"
            v-model="paymentForm.cvv"
            postfix="<i class='fa fa-question-circle'></i>"
        />
        <text-field-with-placeholder
          :validation="$v.form.documentNumber"
          validationMessage="Required"
          v-model="paymentForm.documentNumber"
          v-if="countryCode === 'BR'"
          placeholder="___.___.___-__"
          :rest="{
            'format': '___.___.___-__',
            'pattern': '\\d*',
            type: 'tel'
          }"
          theme="variant-1"
          label="Document number" />
        <text-field-with-placeholder
          :validation="$v.form.documentNumber"
          validationMessage="Required"
          v-model="paymentForm.documentNumber"
          v-if="countryCode === 'CO'"
          placeholder="1234567890"
          :rest="{
            'format': '1234567890',
          }"
          theme="variant-1"
          label="Document number" />
        <button
          @click="submit"
          id="purchase-button"
          type="button"
          class="green-button-animated">
          <span class="purchase-button-text">YES! SEND ME MY PURCHASE WITH FREE SHIPPING NOW</span><img src="//static.saratrkr.com/images/paypal-button-text.png" class="purchase-button-image" alt='' />
        </button>
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
    </div>
</template>

<script>
  import apiUrlList from '../../constants/api-url'
  import { debounce } from '../../utils/common'
  import creditCardType from 'credit-card-type'

  export default {
    name: 'PaymentForm',
    props: ['input', 'countryList', 'isBrazil', 'countryCode', 'installments', 'paymentForm', '$v', 'stateList'],
    data () {
      return {
        isLoading: {
          address: false
        },
        cardType: null,
        isOpenCVVModal: false
      }
    },
    computed: {
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

        if (this.countryCode === 'BR') {
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
        } else if (this.countryCode === 'CO') {
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
        if (+val !== 1 && this.countryCode === 'MX') {
          this.paymentForm.cardType = 'credit'
        }
      }
    },
    methods: {
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
      }, 333),
      submit () {
        this.$v.form.$touch();

        if (this.$v.form.deal.$invalid) {
          this.$emit('setPromotionalModal', true)
        }

        if (this.$v.form.$pending || this.$v.form.$error) {
          if (this.$v.form.deal.$invalid) {
            document.querySelector('.main__deal').scrollIntoView()
          } else {
            document.querySelector('.payment-form').scrollIntoView()
          }

          return;
        }

        console.log('submitted')
      }
    }
  };
</script>

<style lang="scss">
    @import "../../../sass/variables";

    .green-button-animated {
        cursor: pointer;
        bottom: 0;
        box-shadow: rgb(180, 181, 181) 2px 2px 2px 0;
        color: rgb(255, 255, 255);
        height: 92px;
        position: relative;
        text-decoration: none solid rgb(255, 255, 255);
        text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
        text-transform: capitalize;
        top: 0;
        width: 100%;
        column-rule-color: rgb(255, 255, 255);
        perspective-origin: 195.688px 46px;
        transform-origin: 195.695px 46px;
        caret-color: rgb(255, 255, 255);
        background: rgb(255, 47, 33) linear-gradient(rgb(15, 155, 15), rgb(13, 132, 13)) repeat scroll 0% 0% / auto padding-box border-box;
        border: 1px solid rgb(15, 155, 15);
        border-radius: 3px 3px 3px 3px;
        font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
        margin: 0 0 15px;
        outline: rgb(255, 255, 255) none 0;
        padding: 20px;
        transition: all 0.2s linear 0s;

        &:before {
            opacity: 0;
            font-family: FontAwesome!important;
            content: '\f054';
            width: 0;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 0 50% 50% 0;
            background-color: rgba(255,255,255,.3);
            transition: all .2s linear 0s;
        }

        &:hover {
            background-image: linear-gradient(to bottom,#6d4 0,#3d6c04 100%);

            &:before {
                opacity: 1;
                width: 30px;
            }
        }

        &:after {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            cursor: pointer;
            text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
            text-transform: capitalize;
            column-rule-color: rgb(255, 255, 255);
            caret-color: rgb(255, 255, 255);
            border: 0 none rgb(255, 255, 255);
            font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
            outline: rgb(255, 255, 255) none 0;
        }
    }

    .payment-form {
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
                    color: #c0392b;
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

        .purchase-button-image {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            cursor: pointer;
            display: none;
            max-width: 100%;
            text-align: center;
            text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
            text-transform: capitalize;
            vertical-align: middle;
            column-rule-color: rgb(255, 255, 255);
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
    }
</style>
