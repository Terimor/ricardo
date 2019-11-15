import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import { setEmailValidationRule } from './validators';
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
				phone: {
					required,
					isValidPhone (val) {
						if (window.libphonenumber) {
							const phoneNumber = libphonenumber.parsePhoneNumberFromString(val || '', this.form.countryCodePhoneField.toUpperCase())

							if (phoneNumber) {
								return phoneNumber.isValid()
							}
						}

						return true
					}
				}
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
				month: {
					required,
				},
				year: {
					required,
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
				country: {
					required
				}
			}
		}
	};

	setEmailValidationRule(config.form.stepTwo, 'email');

	this.$parent.setExtraFieldsValidationRules(config.vmc4Form);

	if (this.extraFields.state) {
    config.vmc4Form.state = config.form.stepThree.state;
    delete config.form.stepThree.state;
  }

	return config
};

export default vmc4validation