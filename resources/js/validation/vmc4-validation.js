import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import * as validators from './validators';


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
	validators.setCardNumberValidationRule.call(this, config.form.stepThree, 'cardNumber');
  validators.setCardDateValidationRule.call(this, config.form.stepThree, 'cardDate');
  validators.setCVVValidationRule.call(this, config.form.stepThree, 'cvv');
  validators.setTermsValidationRule.call(this, config.form.stepThree, 'terms');

	this.$parent.setExtraFieldsValidationRules(config.vmc4Form);

	if (this.extraFields.state) {
    config.vmc4Form.state = config.form.stepThree.state;
    delete config.form.stepThree.state;
  }

	return config
};

export default vmc4validation