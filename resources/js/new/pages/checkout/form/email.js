import { required, email } from 'vuelidate/lib/validators';

let cache = {};


export default {

  data() {
    return {
      form: {
        email: null,
      },
      email_validation: null,
      extra_validation: {
        '$v.form.email.block': false,
        '$v.form.email.suggest': '',
        '$v.form.email.warning': false,
        '$v.form.email.disposable': false,
      },
    };
  },


  validations() {
    return {
      email: {
        required,
        email,
        valid() {
          return this.email_validation
            ? this.email_validation.valid
            : true;
        },
      },
    };
  },


  watch: {

    email_validation(value) {
      this.extra_validation['$v.form.email.block'] = value ? value.block : false;
      this.extra_validation['$v.form.email.suggest'] = value ? value.suggest : '';
      this.extra_validation['$v.form.email.warning'] = value ? value.warning : false;
      this.extra_validation['$v.form.email.disposable'] = value ? value.disposable : false;

      if (value && value.suggest) {
        setTimeout(() => {
          const element = this.$refs.email_field.querySelector('.suggestion');

          if (element) {
            element.innerHTML = value.suggest;

            element.addEventListener('click', () => {
              this.form.email = value.suggest;
              this.email_validation = null;
              event.preventDefault();
            });
          }
        }, 100);
      }
    },

  },


  methods: {

    email_input() {
      this.email_validation = null;
    },

    email_blur() {
      this.check_for_leads_request();

      const value = this.form.email || '';

      if (this.$v.form.email.$invalid) {
        return;
      }

      if (cache[value]) {
        this.email_validation = cache[value];
        return
      }

      this.fetch_get('/validate-email?email=' + value)
        .then(this.fetch_json)
        .then(body => {
          cache[value] = body;
          this.email_validation = body;
        })
        .catch(err => {

        });
    },

  },

};
