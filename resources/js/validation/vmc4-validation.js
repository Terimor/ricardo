import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import * as validators from './validators';
import creditCardType from 'credit-card-type'
import * as dateFns from 'date-fns';


const vmc4validation = function () {
	const config = {
		vmc4Form: {
			deal: {
				required
			},
			variant: {
				required
			},
		},
		form: {
			stepTwo: {
				fname: {
					required,
					minLength: minLength(2)
				},
				lname: {
					required,
					minLength: minLength(2)
				},
			},
			stepThree: {
				cardNumber: {
					required,
					isValid (val) {
						val = val ? val.replace(/\s/g, '') : '';

		        const creditCardTypeList = creditCardType(val);
		        const commonRule = val.length > 12 && val.length <= 19;

		        if (creditCardTypeList.length === 0) {
		          return false;
		        }

		        return creditCardTypeList[0].lengths.includes(val.length) || commonRule;
					}
				},
				cvv: {
					required,
					minLength: minLength(3)
				},
				city: {
					required
				},
				state: {
					required
				},
			}
		}
	};

	validators.setEmailValidationRule.call(this, config.form.stepTwo, 'email');
	validators.setPhoneValidationRule.call(this, config.form.stepTwo, 'phone');
	validators.setZipCodeValidationRule.call(this, config.form.stepThree, 'zipCode');
	validators.setCountryValidationRule.call(this, config.form.stepThree, 'country');
	validators.setCardHolderValidationRule.call(this, config.form.stepThree, 'cardHolder');
	validators.setMonthValidationRule.call(this, config.form.stepThree, 'month');
  validators.setYearValidationRule.call(this, config.form.stepThree, 'year');
  validators.setTermsValidationRule.call(this, config.form.stepThree, 'terms');

	this.$parent.setExtraFieldsValidationRules(config.vmc4Form);

	if (this.extraFields.state) {
    config.vmc4Form.state = config.form.stepThree.state;
    delete config.form.stepThree.state;
  }

	return config
};

export default vmc4validation