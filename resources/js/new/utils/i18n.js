const textarea = document.createElement('textarea');

const specials = {
  'checkout.payment_form.state': 'i18n_fix_state',
  'checkout.payment_form.state.placeholder': 'i18n_fix_state',
  'checkout.payment_form.zipcode': 'i18n_fix_zipcode',
  'checkout.payment_form.zipcode.placeholder': 'i18n_fix_zipcode',
};


export default {

  methods: {

    t(phrase, args = {}, options = {}) {
      let translated = '';

      if (js_data.i18n.phrases[phrase]) {
        translated = js_data.i18n.phrases[phrase];

        if (specials[phrase]) {
          options.lang = options.lang || js_data.lang_code || 'en';
          options.country = options.country || js_data.country_code || 'us';

          translated = this[specials[phrase]](translated, options);
        }
      } else {
        this.i18n_log_error('`' + phrase + '` not found in translations. Arguments: ' + JSON.stringify(args), {
          loaded_phrases: js_data.i18n.phrases,
        });
      }

      for (const key of Object.keys(args)) {
        const placeholder = '#' + key.toUpperCase() + '#';
        translated = translated.split(placeholder).join(args[key]);

        if (args[key] === undefined || args[key] === null) {
          i18n_log_error('Null of undefined placeholder for `' + phrase + '`: ' + placeholder + '. Arguments: ' + JSON.stringify(args), {
            loaded_phrases: js_data.i18n.phrases,
          });
        }
      }

      const non_translated = translated.match(/#[A-Z0-9_]+#/g) || [];

      if (non_translated.length > 0) {
        i18n_log_error('Non-translated placeholders for `' + phrase + '`: ' + non_translated.join(', ') + '. Arguments: ' + JSON.stringify(args), {
          loaded_phrases: js_data.i18n.phrases,
        });
      }

      textarea.innerHTML = translated;
      translated = textarea.innerText;

      return translated;
    },

    timage(name) {
      let translated = {
        title: '',
        url: '',
      };

      if (js_data.i18n.images[name] && js_data.i18n.images[name].url) {
        translated = js_data.i18n.images[name];
      } else {
        i18n_log_error('`' + name + '` is in use, but no such key or empty url.', {
          loaded_images: js_data.i18n.images,
        });
      }

      return translated;
    },

    i18n_log_error(err_text, data) {
      if (window.Sentry) {
        Sentry.captureMessage(err_text);
      }

      this.fetch_post('/log-data', {
        'logger-type': 'error',
        error: err_text,
        data,
      })
      .catch(err => {

      });

      console.error(err_text);
    },

    i18n_fix_state(translated, options) {
      switch (options.lang) {
        case 'en':
          switch (options.country) {
            case 'us':
            case 'au':
              translated = 'State';
              break;
            case 'gb':
              translated = 'County';
              break;
          }
          break;
        case 'es':
          switch (options.country) {
            case 'es':
              translated = 'Comunidad autónoma';
              break;
            case 'uy':
            case 'py':
            case 'bo':
            case 'co':
              translated = 'Departamento';
              break;
            case 'ec':
            case 'ar':
              translated = 'Provincia';
              break;
            case 've':
            case 'mx':
              translated = 'Estado';
              break;
            case 'pe':
            case 'cl':
              translated = 'Región';
              break;
          }
          break;
        case 'de':
          switch (options.country) {
            case 'de':
            case 'at':
              translated = 'Bundesland';
              break;
            case 'ch':
              translated = 'Kanton';
              break;
            case 'li':
              translated = 'Gemeinde';
              break;
          }
          break;
        case 'fr':
          switch (options.country) {
            case 'fr':
              translated = 'Département';
              break;
            case 'be':
              translated = 'Province';
              break;
            case 'ch':
            case 'lu':
              translated = 'Canton';
              break;
            case 'ca':
              translated = 'Région';
              break;
          }
          break;
        case 'nl':
          switch (options.country) {
            case 'nl':
              translated = 'Provincie';
              break;
            case 'be':
              translated = 'Deelstaat';
              break;
          }
          break;
      }

      return translated;
    },

    i18n_fix_zipcode(translated, options) {
      switch (options.lang) {
        case 'en':
          switch (options.country) {
            case 'us':
              translated = 'Zip code';
              break;
            case 'au':
              translated = 'Postcode';
              break;
            case 'gb':
              translated = 'Postal code';
              break;
          }
          break;
        case 'es':
          switch (options.country) {
            case 'es':
            case 'uy':
            case 'py':
            case 'ec':
            case 've':
            case 'ar':
            case 'pe':
            case 'bo':
            case 'cl':
            case 'mx':
            case 'co':
              translated = 'Código Postal';
              break;
          }
          break;
        case 'de':
          switch (options.country) {
            case 'de':
            case 'at':
            case 'ch':
            case 'li':
              translated = 'PLZ';
              break;
          }
          break;
        case 'fr':
          switch (options.country) {
            case 'fr':
            case 'be':
            case 'ch':
            case 'lu':
            case 'ca':
              translated = 'Code postal';
              break;
          }
          break;
        case 'nl':
          switch (options.country) {
            case 'nl':
            case 'be':
              translated = 'Postcode';
              break;
          }
          break;
      }

      return translated;
    },

  },

};
