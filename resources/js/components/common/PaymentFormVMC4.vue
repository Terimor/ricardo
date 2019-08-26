<template>
  <div class="d-flex flex-column payment-form-vmc4">
    <div class="col-md-12">
      <h4 class="form-steps-title">
        <b>{{step}}/{{maxSteps}}</b>
        <span>Choose your Deal</span>
      </h4>
      <div class="step step-1">
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
              @finish-render="setBackgroundForChecked(checkedItem)"/>
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
      <div class="step step-2">
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
            v-model="phone"/>
      </div>

      <div class="step step-3">step 3</div>
      <div class="buttons d-flex">
        <button v-if="step > 1"
                @click="step--">Back
        </button>
        <button @click="step !== 3 ? step++ : step"
                :style="step>1 ? 'width: 50%;' : 'width: 100%;'">Next
        </button>
      </div>
    </div>
  </div>
</template>
<script>
	import RadioButtonItemDeal from "./RadioButtonItemDeal";
	import {fade} from "../../utils/common";

	export default {
		name: "PaymentFormVMC4",
		components: {RadioButtonItemDeal},
		props: ['paymentForm', 'countryList', 'cardNames', 'list', 'variantList', 'countryCode'],
		data() {
			return {
				step: 1,
				maxSteps: 3,
				checkedItem: this.paymentForm.deal,
				phone: ""
			}
		},
		watch: {
			'step'(val) {
				fade('out', 300, document.querySelector('.payment-form-vmc4'), true)
					.then(() => {
						const selectors = document.querySelectorAll("[class^='step']");
						for (let i of selectors) {
							if (i.className.includes(`step-${val}`)) {
								i.style.display = 'block'
							} else i.style.display = 'none'
						}

						fade('in', 300, document.querySelector('.payment-form-vmc4'), true)
					});
			},
		},
		methods: {
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
				console.log(val)
				if (val.iso2) {
					this.$emit('changedCountryCodePhone', val)
				}
			},
		}
	}
</script>
<style lang="scss">
  .payment-form-vmc4 {
    .form-steps-title {
      text-align: center;
    }

    .step-2,
    .step-3 {
      display: none;
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

    .buttons button {
      width: 50%;
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

  }
</style>