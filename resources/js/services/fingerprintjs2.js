import fingerprintjs2 from 'fingerprintjs2';
import { queryParams } from  '../utils/queryParams';

let hash = null;

const initPages = [
  '/checkout',
  '/splash',
];


export default function calculateHash() {
  if (hash) {
    return Promise.resolve(hash);
  }

  return fingerprintjs2.getPromise({fonts: {extendedJsFonts: true} }).then(components => {
    const values = components.map(component => component.value);
    hash = fingerprintjs2.x64hash128(values.join(''), 31);
    return hash;
  });
}


function applyDiscount() {
  for (let page of initPages) {
    if (location.pathname.startsWith(page)) {
      Promise.resolve()
        .then(calculateHash)
        .then(hash => fetch('/apply-discount', {
          method: 'post',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({
            f: hash,
            url: location.href,
          }),
        }))
        .catch(err => {

        });
    }
  }
}


if (document.readyState !== 'complete') {
  document.addEventListener('readystatechange', () =>  {
    if (document.readyState === 'complete') {
      if (!queryParams()['3ds']) {
        setTimeout(applyDiscount, 1000);
      }
    }
  });
} else {
  if (!queryParams()['3ds']) {
    setTimeout(applyDiscount, 1000);
  }
}
