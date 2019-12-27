import fingerprintjs2 from 'fingerprintjs2';

let cache = null;

const initPages = [
  '/checkout',
  '/splash',
];


export default function calculate() {
  if (cache) {
    return Promise.resolve(cache);
  }

  return fingerprintjs2.getPromise().then(components => {
    cache = components;
    return cache;
  });
}


for (let page of initPages) {
  if (location.pathname.startsWith(page)) {
    setTimeout(() => calculate().then(data => fetch('/apply-discount', {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        f: data,
      }),
    })), 1000);
  }
}
