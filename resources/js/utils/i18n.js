/**
 * Get translated phrase
 *
 * @param {String} phrase Phrase code
 * @param {Object} args Hash of placeholders
 * @returns {String} Translated phrase
 *
 */
import logger from '../mixins/logger';


const textarea = document.createElement('textarea');

const specials = {
  'checkout.payment_form.state': hookState,
  'checkout.payment_form.state.placeholder': hookState,
  'checkout.payment_form.zipcode': hookZipCode,
  'checkout.payment_form.zipcode.placeholder': hookZipCode,
};


export function t(phrase, args = {}, options = {}) {
  let translated = '';

  if (js_data.i18n.phrases[phrase]) {
    translated = js_data.i18n.phrases[phrase];

    if (specials[phrase]) {
      const lang = options.lang || js_data.lang_code || 'en';
      const country = options.country || js_data.country_code || 'us';

      translated = specials[phrase](lang, country, translated);
    }
  } else {
    logError('`' + phrase + '` not found in translations. Arguments: ' + JSON.stringify(args), {
      loaded_phrases: js_data.i18n.phrases,
    });
  }

  for (const key of Object.keys(args)) {
    const placeholder = '#' + key.toUpperCase() + '#';
    translated = translated.split(placeholder).join(args[key]);

    if (args[key] === undefined || args[key] === null) {
      logError('Null of undefined placeholder for `' + phrase + '`: ' + placeholder + '. Arguments: ' + JSON.stringify(args), {
        loaded_phrases: js_data.i18n.phrases,
      });
    }
  }

  const nonTranslated = translated.match(/#[A-Z0-9_]+#/g) || [];

  if (nonTranslated.length > 0) {
    logError('Non-translated placeholders for `' + phrase + '`: ' + nonTranslated.join(', ') + '. Arguments: ' + JSON.stringify(args), {
      loaded_phrases: js_data.i18n.phrases,
    });
  }

  textarea.innerHTML = translated;
  translated = textarea.innerText || translated;

  return translated;
}


export function timage(name) {
  let translated = {
    title: '',
    url: '',
  };

  if (js_data.i18n.images[name] && js_data.i18n.images[name].url) {
    translated = js_data.i18n.images[name];
  } else {
    logError('`' + name + '` is in use, but no such key or empty url.', {
      loaded_images: js_data.i18n.images,
    });
  }

  return translated;
}


function logError(errText, data) {
  if (window.Sentry) {
    Sentry.captureMessage(errText);
  }

  logger.methods.log_data(errText, data);

  console.error(errText);
}


function hookState(lang, country, translated) {
  switch (lang) {
    case 'en':
      switch (country) {
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
      switch (country) {
        case 'es':
          translated = 'Comunidad aut??noma';
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
          translated = 'Regi??n';
          break;
      }
      break;
    case 'de':
      switch (country) {
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
      switch (country) {
        case 'fr':
          translated = 'D??partement';
          break;
        case 'be':
          translated = 'Province';
          break;
        case 'ch':
        case 'lu':
          translated = 'Canton';
          break;
        case 'ca':
          translated = 'R??gion';
          break;
      }
      break;
    case 'nl':
      switch (country) {
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
}


function hookZipCode(lang, country, translated) {
  switch (lang) {
    case 'en':
      switch (country) {
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
      switch (country) {
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
          translated = 'C??digo Postal';
          break;
      }
      break;
    case 'de':
      switch (country) {
        case 'de':
        case 'at':
        case 'ch':
        case 'li':
          translated = 'PLZ';
          break;
      }
      break;
    case 'fr':
      switch (country) {
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
      switch (country) {
        case 'nl':
        case 'be':
          translated = 'Postcode';
          break;
      }
      break;
  }

  return translated;
}
