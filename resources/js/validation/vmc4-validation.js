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
				zipCode: {
					required,
					/*isValidZipcode(value) {
						return isValidZipcode(value, this.form.stepThree.country || checkoutData.countryCode);
					},*/
				},
			}
		}
	};

	validators.setEmailValidationRule(config.form.stepTwo, 'email');
	validators.setPhoneValidationRule(config.form.stepTwo, 'phone');
	validators.setCountryValidationRule(config.form.stepThree, 'country');
	validators.setMonthValidationRule(config.form.stepThree, 'month');
  validators.setYearValidationRule(config.form.stepThree, 'year');

	this.$parent.setExtraFieldsValidationRules(config.vmc4Form);

	if (this.extraFields.state) {
    config.vmc4Form.state = config.form.stepThree.state;
    delete config.form.stepThree.state;
  }

	return config
};

export default vmc4validation