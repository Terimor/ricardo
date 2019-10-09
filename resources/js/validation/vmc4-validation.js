import { required, minLength, email, numeric } from 'vuelidate/lib/validators'
import postcode from 'postcode-validator'
import creditCardType from 'credit-card-type'
import * as dateFns from 'date-fns';


const vmc4validation = function () {
	const config = {
		form: {
			deal: {
				required
			},
			variant: {
				required
			},
			stepTwo: {
				fname: {
					required,
					minLength: minLength(2)
				},
				lname: {
					required,
					minLength: minLength(2)
				},
				email: {
					required,
					type: email
				},
				phone: {
					required,
					isValidPhone (val) {
						const phoneNumber = libphonenumber.parsePhoneNumberFromString(val || '', this.form.countryCodePhoneField.toUpperCase())

						if (phoneNumber) {
							return phoneNumber.isValid()
						}

						return true
					}
				}
			},
			stepThree: {
				cardNumber: {
					type: numeric,
					required,
					isValid (val) {
						const creditCardTypeList = creditCardType(val);

						const commonRule = val.length > 12 && val.length <= 19;

						if (creditCardTypeList.length === 1) {
							return creditCardTypeList[0].lengths.includes(val.length) || commonRule
						}

						return commonRule
					}
				},
				month: {
					required,
					isValid (val) {
						return val > 0 && val <= 12
					}
				},
				year: {
					required,
					isValid (val) {
						return dateFns.isFuture(new Date(val, this.form.stepThree.month))
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
					isValidZipcode (val) {
						return postcode.validate(val, this.form.stepThree.country || checkoutData.countryCode)
					}
				},
				country: {
					required
				}
			}
		}
	};

	return config
};

export default vmc4validation