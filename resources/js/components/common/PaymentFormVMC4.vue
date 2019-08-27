<template>
  <div class="d-flex flex-column payment-form-vmc4">
    <div class="col-md-12">
      <h4 class="form-steps-title">
        <b>{{step}}/{{maxSteps}}</b>
        <span>Choose your Deal</span>
      </h4>

      <div class="step step-1" v-if="step === 1">
        <radio-button-group
            :withCustomLabels="true"
            v-model="paymentForm.deal"
            :list="list">
          <radio-button-item-deal
              :showPerUnitPrice=false
              :showDiscount=true
              :value="paymentForm.deal"
              v-for="item in list"
              :item="{
                  ...item,
                  value: item.totalQuantity,
                }"
              :key="item.value"
              @checkDeal="setBackgroundForChecked"
              @finish-render="setBackgroundForChecked(paymentForm.deal)"/>
        </radio-button-group>
        <h2>Please select your variant</h2>
        <select-field
            popperClass="smc7-popover-variant"
            v-model="paymentForm.variant"
            :rest="{
                placeholder: 'Variant'
              }"
            :list="variantList"/>
      </div>

      <div class="step step-2" v-if="step === 2">
        <div class="full-name">
          <text-field
              validationMessage="Please enter your first name"
              theme="variant-1"
              label="First Name"
              class="first-name"
              v-model="paymentForm.fname"/>
          <text-field
              validationMessage="Please enter your last name"
              theme="variant-1"
              label="Last Name"
              class="last-name"
              v-model="paymentForm.lname"/>
        </div>
        <text-field
            validationMessage="Please enter a valid e-mail"
            theme="variant-1"
            label="Your Email Address"
            v-model="paymentForm.email"/>
        <phone-field
            @onCountryChange="setCountryCodeByPhoneField"
            validationMessage="Please enter a valid phone number"
            :countryCode="paymentForm.countryCodePhoneField"
            theme="variant-1"
            label="Your Phone Number"
            :rest="{
                    autocomplete: 'off'
                  }"
            v-model="paymentForm.phone"/>
      </div>

      <div class="step step-3" v-if="step === 3">
        <h2>Pay Securely With:</h2>
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
                :value="paymentForm.paymentType"/>
          </div>
        </radio-button-group>
        <form id="payment-data-form" v-if="paymentForm.paymentType !== 'paypal'">
          <text-field
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
          <div class="card-info">
            <div class="card-info__labels" >
              <span class="label">Card Valid Until</span>
              <span class="label"> </span>
            </div>
            <div class="card-date">
              <select-field
                  validationMessage="Required"
                  :rest="{
                      placeholder: 'Month'
                    }"
                  theme="variant-1"
                  :list="Array.apply(null, Array(12)).map((_, idx) => ({ value: idx + 1 }))"
                  v-model="paymentForm.month"/>
              <select-field
                  validationMessage="Required"
                  :rest="{
                      placeholder: 'Year'
                    }"
                  theme="variant-1"
                  :list="Array.apply(null, Array(10)).map((_, ind) => ({ value: new Date().getFullYear() + ind }))"
                  v-model="paymentForm.year"/>
              <div class="card-cvv">
                <text-field
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
                    v-model="paymentForm.cvv"
                    postfix="<i class='fa fa-question-circle'></i>"
                />
              </div>
            </div>

          </div>
          <el-dialog
              @click="isOpenCVVModal = false"
              class="cvv-popup"
              title="Where do I find my security code?"
              :visible.sync="isOpenCVVModal">
            <div class="cvv-popup__content">
              <p>The CVV code is a 3 digit number that you can find on the back of your credit card. On AMEX cards it is
                a 4 digit number, found on the front of your credit card.</p>
              <div><img src="/images/cvv_popup.jpg" alt=""></div>
              <p>Where to find the 3 digit security code (Visa/Mastercard)</p>
            </div>
          </el-dialog>
        </form>
        <text-field
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
            validationMessage="Please enter your zip code"
            theme="variant-1"
            label="Zip Code"
            :rest="{
              placeholder: 'Zip code'
            }"
            id="zip-code-field"
            v-model="paymentForm.zipCode"/>
        <select-field
            validationMessage="Invalid field"
            theme="variant-1"
            label="Country"
            class="country"
            :rest="{
           placeholder: 'Country'
        }"
            :list="countryList"
            v-model="paymentForm.country"/>
      </div>
      <div class="buttons">
        <button
            v-if="paymentForm.paymentType !== 'paypal' && step === 3"
            @click="submit"
            class="submit-btn"
            id="purchase-button"
            type="button">
          YES! SEND ME MY PURCHASE WITH FREE SHIPPING NOW
        </button>
        <button
            v-if="paymentForm.paymentType === 'paypal' && step === 3"
            @click="submit"
            class="submit-btn"
            id="purchase-button-paypal"
            type="button">
          <span class="purchase-button-text">Buy Now Risk-Free with</span>
          <img src="/images/cc-icons/paypal-highq.png" alt="Paypal">
        </button>
        <div class="form-navigation">
          <button @click="step !== 3 ? step++ : step"
                  v-if="step !== 3"
                  :style="step>1">Next
          </button>
          <button v-if="step > 1"
                  class="back-btn"
                  @click="step--"><< Go Back
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
	import RadioButtonItemDeal from "./RadioButtonItemDeal";
	import PayMethodItem from "./PayMethodItem";
	import {getCardUrl} from "../../utils/checkout";

	export default {
		name: "PaymentFormVMC4",
		components: {PayMethodItem, RadioButtonItemDeal},
		props: ['paymentForm', 'countryList', 'cardNames', 'list', 'variantList', 'countryCode'],
		data() {
			return {
				step: 1,
				maxSteps: 3,
				isOpenCVVModal: false
			}
		},
		computed: {
			cardUrl() {
				return getCardUrl(this.paymentForm.cardType)
			}
		},
		methods: {
			submit() {
				this.$v.form.$touch();
				//
				// if (this.$v.form.deal.$invalid) {
				// 	this.setPromotionalModal(true)
				// }
			},
			setBackgroundForChecked(checkbox) {
				let allBoxes = document.getElementsByClassName('radio-button-deal');
				let boxContent = document.querySelector(`.item-${checkbox}`);
				if (allBoxes.length) {
					for (let i of allBoxes) {
						i.classList.remove("isChecked");
					}
				}

				if (checkbox && boxContent) {
					boxContent.classList.add("isChecked");
				}
			},
			setCountryCodeByPhoneField(val) {
				this.$emit('changedCountryCodePhone', val)
			},
			openCVVModal() {
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
  .payment-form-vmc4 {
    .form-steps-title {
      text-align: center;
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

      .label-container-radio input:checked ~ .checkmark:after {
        display: block;
      }

      .price {
        position: relative;
        margin-right: 60px;

        .bestseller {
          position: absolute;
          left: -50px;
          top: -26px;
        }
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


      .input-container.variant-1 input,
      .phone-input-container.variant-1 input {
        background-color: #ffffff;
        font-size: 14px;
        border-radius: unset;
        border: 0;
        border-bottom: 1px solid #d2d2d2;

        &:focus {
          box-shadow: none;
        }
      }

    }

    .step-3 {
      .pay-method-item img {
        max-height: 60px;
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
          .select.variant-1:first-child {
            width: 30%;
          }
          .select.variant-1:last-child {
            width: 40%;
          }
          .card-cvv {
            width: 30%;

            .input-container{
              .label {
                margin-bottom: 0;
              }
            }
          }
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