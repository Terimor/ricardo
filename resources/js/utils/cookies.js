export function clearCookies(options = {}) {
  const except = options && options.except && Array.isArray(options.except)
    ? options.except
    : [];

  for (let cookie of document.cookie.split(';')) {
    const name = decodeURIComponent(cookie.split('=')[0].trim());

    if (except.indexOf(name) === -1) {
      deleteCookie(name);
    }
  }
}


export function getCookie(name) {
  const matches = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}


export function setCookie(name, value, options = {}) {
  const domain = '.' + location.hostname.split('.').slice(-2).join('.');
  options = { domain, path: '/', ...options };

  if (options.expires && options.expires.toUTCString) {
    options.expires = options.expires.toUTCString();
  }

  let updatedCookie = encodeURIComponent(name) + '=' + encodeURIComponent(value);

  for (let optionKey in options) {
    updatedCookie += '; ' + optionKey;

    if (options[optionKey] !== true) {
      updatedCookie += '=' + options[optionKey];
    }
  }

  document.cookie = updatedCookie;
}


export function deleteCookie(name) {
  setCookie(name, '', { 'max-age': -1 });
}
