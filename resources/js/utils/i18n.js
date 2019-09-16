/**
 * Get translated phrase
 *
 * @param {String} phrase Phrase code
 * @param {Object} args Hash of placeholders
 * @returns {String} Translated phrase
 *
 */

export function t(phrase, args = {}) {
  let translated = phrase;

  const loadedPhrases = window.loadedPhrases || {};

  if (loadedPhrases[phrase]) {
    translated = loadedPhrases[phrase];
  } else {
    console.error('URGENT: `' + phrase + '` not found in translations. Arguments: ' + JSON.stringify(args));
  }

  for (const key of Object.keys(args)) {
    const placeholder = '#' + key.toUpperCase() + '#';
    translated = translated.split(placeholder).join(args[key]);

    if (args[key] === undefined || args[key] === null) {
      console.error('URGENT: Null of undefined placeholder for `' + phrase + '`: ' + placeholder + '. Arguments: ' + JSON.stringify(args));
    }
  }

  const nonTranslated = translated.match(/#[A-Z0-9_]+#/g) || [];

  if (nonTranslated.length > 0) {
    console.error('URGENT: Non-translated placeholders for `' + phrase + '`: ' + nonTranslated.join(', ') + '. Arguments: ' + JSON.stringify(args));
  }

  return translated;
}
