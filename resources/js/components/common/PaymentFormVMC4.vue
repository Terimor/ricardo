<template>
  <div v-if="$v">
    <div class="d-flex flex-column payment-form-vmc4">
      <div class="vmc4__deal">
      <div class="col-md-12">
        <h4 class="form-steps-title">
          <b>{{step}}/{{maxSteps}}</b>
          <span>Choose your Deal</span>
        </h4>


        <div class="step step-1" v-if="step === 1">
          <slot name="installment" />

          <radio-button-group
            :withCustomLabels="false"
            v-model="form.deal"
            @input="setWarrantyPriceText"
            :list="list"
            />

          <h2>Please select your variant</h2>
          <select-field
              popperClass="smc7-popover-variant"
              v-model="form.variant"
              :rest="{
                placeholder: 'Variant'
              }"
              :list="variantList"/>

          <slot name="warranty" />
        </div>

        <div class="step step-2" v-if="step === 2">
          <div class="full-name">
            <text-field
                :validation="$v.form.stepTwo.fname"
                validationMessage="Please enter your first name"
                theme="variant-1"
                label="First Name"
                class="first-name"
                v-model="form.stepTwo.fname"/>
            <text-field
                :validation="$v.form.stepTwo.lname"
                validationMessage="Please enter your last name"
                theme="variant-1"
                label="Last Name"
                class="last-name"
                v-model="form.stepTwo.lname"/>
          </div>
          <text-field
              :validation="$v.form.stepTwo.email"
              validationMessage="Please enter a valid e-mail"
              theme="variant-1"
              label="Your Email Address"
              v-model="form.stepTwo.email"/>
          <phone-field
              :validation="$v.form.stepTwo.phone"
              @onCountryChange="setCountryCodeByPhoneField"
              validationMessage="Please enter a valid phone number"
              :countryCode="form.countryCodePhoneField"
              theme="variant-1"
              label="Your Phone Number"
              :rest="{
                    autocomplete: 'off'
                  }"
              v-model="form.stepTwo.phone"/>
        </div>

        <div class="step step-3" v-if="step === 3">
          <h2>Pay Securely With:</h2>
          <radio-button-group
              :withCustomLabels="true"
              v-model="form.paymentType">
            <div class="card-types">
              <pay-method-item
                  :validation="$v.form.stepThree.cardType"
                  v-for="item in cardNames"
                  :key="item.value"
                  :input="{
                  value: item.value,
                  imgUrl: item.imgUrl,
                }"
                  :value="form.paymentType"/>
            </div>
          </radio-button-group>
          <form v-if="form.paymentType !== 'paypal' && form.paymentType">
            <text-field
                :validation="$v.form.stepThree.cardNumber"
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
                v-model="form.stepThree.cardNumber"
                :prefix="`<img src='${cardUrl}' alt='Card Number' />`"
                :postfix="`<i class='fa fa-lock'></i>`"
            />
            <div class="card-info">
              <div class="card-info__labels">
                <span class="label">Card Valid Until</span>
                <span class="label"> </span>
              </div>
              <div class="card-date">
                <select-field
                    :validation="$v.form.stepThree.month"
                    validationMessage="Required"
                    :rest="{
                      placeholder: 'Month'
                    }"
                    theme="variant-1"
                    :list="Array.apply(null, Array(12)).map((_, idx) => ({ value: idx + 1 }))"
                    v-model="form.stepThree.month"/>
                <select-field
                    :validation="$v.form.stepThree.year"
                    validationMessage="Required"
                    :rest="{
                      placeholder: 'Year'
                    }"
                    theme="variant-1"
                    :list="Array.apply(null, Array(10)).map((_, ind) => ({ value: new Date().getFullYear() + ind }))"
                    v-model="form.stepThree.year"/>
                <div class="card-cvv">
                  <text-field
                      :validation="$v.form.stepThree.cvv"
                      @click-postfix="openCVVModal"
                      validationMessage="Required"
                      class="cvv-field"
                      theme="variant-1"
                      :rest="{
                        maxlength: 4,
                        pattern: '\\d*',
                        type: 'tel',
                        autocomplete: 'cc-csc',
                        'data-bluesnap': 'encryptedCvv'
                      }"
                      v-model="form.stepThree.cvv"
                      postfix="<i class='fa fa-question-circle'></i>"
                  />
                </div>
              </div>
              <text-field
                  :validation="$v.form.stepThree.city"
                  validationMessage="Please enter your city"
                  element-loading-spinner="el-icon-loading"
                  theme="variant-1"
                  label="City"
                  :rest="{
                    placeholder: 'City',
                    autocomplete: 'shipping locality'
                   }"
                  v-model="form.stepThree.city"/>
              <text-field
                  :validation="$v.form.stepThree.state"
                  validationMessage="Please enter your state"
                  element-loading-spinner="el-icon-loading"
                  theme="variant-1"
                  label="State"
                  :rest="{
                    placeholder: 'State',
                    autocomplete: 'shipping locality'
                  }"
                  v-model="form.stepThree.state"/>
              <text-field
                  :validation="$v.form.stepThree.zipCode"
                  validationMessage="Please enter your zip code"
                  theme="variant-1"
                  label="Zip Code"
                  :rest="{
                    placeholder: 'Zip code'
                  }"
                  id="zip-code-field"
                  v-model="form.stepThree.zipCode"/>
              <select-field
                  :validation="$v.form.stepThree.country"
                  validationMessage="Invalid field"
                  theme="variant-1"
                  label="Country"
                  class="country"
                  :rest="{
                     placeholder: 'Country'
                  }"
                  :list="countryList"
                  v-model="form.stepThree.country"/>
            </div>
            <el-dialog
                @click="isOpenCVVModal = false"
                class="cvv-popup"
                title="Where do I find my security code?"
                :visible.sync="isOpenCVVModal">
              <div class="cvv-popup__content">
                <p>The CVV code is a 3 digit number that you can find on the back of your credit card. On AMEX cards it
                  is
                  a 4 digit number, found on the front of your credit card.</p>
                <div><img src="/images/cvv_popup.jpg" alt=""></div>
                <p>Where to find the 3 digit security code (Visa/Mastercard)</p>
              </div>
            </el-dialog>
          </form>
        </div>
        <div class="buttons">
          <button
              v-if="form.paymentType !== 'paypal' && step === 3"
              @click="submit"
              class="submit-btn"
              type="button">
            YES! SEND ME MY PURCHASE WITH FREE SHIPPING NOW
          </button>
          <button
              v-if="form.paymentType === 'paypal' && step === 3"
              @click="submit"
              class="submit-btn paypal-btn"
              type="button">
            <span class="purchase-button-text">Buy Now Risk-Free with</span>
            <img src="/images/cc-icons/paypal-highq.png" alt="Paypal">
          </button>
          <div class="form-navigation">
            <button @click="step !==3 && isAllowNext(step)? step++ : step"
                    v-if="step !== 3 "
                    :style="step>1">Next
            </button>
            <button v-if="step > 1 && !form.paymentType"
                    class="back-btn"
                    @click="step--"><< Go Back
            </button>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>

</template>
<script>
	import RadioButtonItemDeal from "./RadioButtonItemDeal";
	import PayMethodItem from "./PayMethodItem";
	import {getCardUrl, preparePurchaseData} from "../../utils/checkout";
	import vmc4validation from "../../validation/vmc4-validation";
	import {fade} from "../../utils/common";

	export default {
		name: "PaymentFormVMC4",
		components: {PayMethodItem, RadioButtonItemDeal},
		validations: vmc4validation,
		props: [
			'countryList',
			'cardNames',
			'list',
			'variantList',
      'countryCode',
      'installments',
			'checkoutData'
		],
		data() {
			return {
				step: 1,
				maxSteps: 3,
				isOpenCVVModal: false,
				form: {
					stepTwo: {
						fname: null,
						lname: null,
						email: null,
						phone: null,
					},
					stepThree: {
						cardNumber: '',
						month: null,
						year: null,
						cvv: null,
						country: checkoutData.countryCode,
						city: null,
						state: null,
						zipCode: null,
						cardType: null
					},
					countryCodePhoneField: checkoutData.countryCode,
					deal: 1,
					variant: checkoutData.product.skus[0].code || "",
					installments: 1,
					paymentType: null,
				}
			}
    },
    mounted() {
      this.setWarrantyPriceText(this.form.deal)
    },
		computed: {
			cardUrl() {
				return getCardUrl(this.form.cardType)
			}
		},
		watch: {
			'form.variant'(val) {
				fade('out', 300, document.querySelector('#main-prod-image'), true)
					.then(() => {
						let productImageUrl = this.variantList.find(variant => variant.value === val).imageUrl;
						if(productImageUrl) {
							this.$emit('productImageChanged', productImageUrl)
            }
						fade('in', 300, document.querySelector('#main-prod-image'), true)
					});
			},
			'step'(val) {
				fade('out', 300, document.querySelector('.payment-form-vmc4'), true)
					.then(() => {
						fade('in', 300, document.querySelector('.payment-form-vmc4'), true)
					});
      },
      installments (val) {
        if (+val !== 1 && this.countryCode === 'MX') {
          this.paymentForm.cardType = 'credit'
        }
      }
		},
		methods: {
      setWarrantyPriceText(value) {
        this.$emit('setWarrantyPriceText', value)
      },
			submit() {
				this.$v.form.$touch();
				this.$emit('onSubmit', this.form)
			},
			setCountryCodeByPhoneField(val) {
				if (val.iso2) {
					this.form.countryCodePhoneField = val.iso2.toUpperCase()
				}
			},
			openCVVModal() {
				const node = document.querySelector('.cvv-popup .el-dialog');
				const listener = () => {
					this.isOpenCVVModal = false
				};
				node.removeEventListener('click', listener);
				node.addEventListener('click', listener);

				this.isOpenCVVModal = true
			},
			isAllowNext(step) {
				const checkStepTwo = this.$v.form.stepTwo.$invalid;

				const checkStepThree =
					this.form.paymentType !== 'paypal' &&
					this.$v.form.stepThree.$invalid;

				step === 3 && checkStepThree ? this.$v.form.stepThree.$touch() :
					step === 2 && checkStepTwo ? this.$v.form.stepTwo.$touch() : this.step++
			}
		}
	}
</script>
<style lang="scss">
@import "../../../sass/variables";

  .payment-form-vmc4 {
    .form-steps-title {
      text-align: center;
    }

    .el-input{
      &__inner{
        border-radius: 0;
      }
    }

    .input-container.variant-1 input,
    .phone-input-container.variant-1 input {
      background-color: #ffffff;
      font-size: 14px;
      border-radius: 0;
      border: 0;
      border-bottom: 1px solid #d2d2d2;

      &:focus {
        box-shadow: none;
      }
    }

    .step-1 {
      .radio-button-deal {
        font-size: 16px;
        padding: 20px 20px 20px 45px;
        border: none;
        cursor: pointer;
        margin: 5px 0;

        &:hover {
          background: #fef5eb;
        }
      }

      .radio-button-deal .checkmark {
        top: 20px;
      }

      .label-container-radio {
        border: none;
        margin: 5px 0;
      }
      .label-container-radio:hover {
        background-color: #fef5eb;
        border: none;
      }

      .label-container-radio input:checked ~ .checkmark:after {
        display: block;
      }

      .radio-button-group {
        margin: 24px 8px;
      }

      .main-row {
        display: flex;
        justify-content: space-between;
        position: relative;
      }
      .discount {
        margin-left: 4px;
      }
      .prices {
        margin-right: 50px;
      }
      .red {
        color: $red;
      }
      .best-seller {
        position: absolute;
        top: -26px;
        right: 50px;

      }
    }

    .step-2 {
      .full-name {
        display: flex;
        margin-bottom: 15px;

        .first-name {
          width: 40%;
          margin-right: 10px;
        }

        .last-name {
          width: calc(60% - 11px);
        }
      }
    }

    .step-3 {
      .pay-method-item img {
        max-height: 45px;
        margin-right: 15px;
      }

      .prefix > img {
        height: 22px;
        width: auto;
      }

      .card-info {
        display: flex;
        flex-direction: column;
        align-items: center;


        .card-date {
          display: flex;

          .select.variant-1:nth-child(1) {
            margin-right: 5px;
            width: 30%;
          }

          .select.variant-1:nth-child(2) {
            margin-right: 20px;
            width: 40%;
          }

          .select.variant-1:last-child {
            width: 40%;
          }

          .card-cvv {
            width: 30%;

            .input-container {
              .label {
                margin-bottom: 0;
              }
            }
          }
        }
      }

      .el-input {
        &__inner {
          background: #ffffff;
          border-radius: 0;
          border: 0;
          border-bottom: 1px solid #d2d2d2;
        }
      }
    }

    .buttons button:not(.back-btn),
    .submit-btn {
      width: 100%;
      border: none;
      border-radius: 3px;
      position: relative;
      padding: 18px 30px;
      margin: 10px 1px;
      font-size: 16px;
      font-weight: 400;
      text-transform: uppercase;
      letter-spacing: 0;
      will-change: box-shadow, transform;
      -webkit-transition: box-shadow .2s cubic-bezier(.4, 0, 1, 1), background-color .2s cubic-bezier(.4, 0, .2, 1);
      transition: box-shadow .2s cubic-bezier(.4, 0, 1, 1), background-color .2s cubic-bezier(.4, 0, .2, 1);
      box-shadow: 0 2px 2px 0 rgba(76, 175, 80, .14), 0 3px 1px -2px rgba(76, 175, 80, .2), 0 1px 5px 0 rgba(76, 175, 80, .12);
      background-color: #4caf50;
      color: #fff;

      &:hover {
        opacity: 1;
        -webkit-transition: box-shadow .5s, background .5s ease;
        transition: box-shadow .5s, background .5s ease;
        box-shadow: -2px 2px 18px #4caf50;
        -moz-box-shadow: -2px 2px 18px #4caf50;
        -webkit-box-shadow: -2px 2px 18px #4caf50;
      }
    }

    .buttons button.paypal-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #ffc438;
      border: 3px solid #feae01;
      color: #000000;
      font-weight: 700;

      & > img {
        margin-left: 5px;
      }

      &:hover {
        box-shadow: -2px 2px 18px #feae01;
      }
    }

    .form-navigation {
      display: flex;
      flex-direction: column;
      align-items: center;

      .back-btn {
        width: 50%;
        outline: none;
        border: none;
        position: relative;
        padding: 18px 30px;
        margin: 10px 1px;
        font-size: 16px;
        font-weight: 400;
        letter-spacing: 0;
        cursor: pointer;
        background-color: transparent;
        text-align: center;
        color: #337ABE;
      }
    }
  }
</style>
