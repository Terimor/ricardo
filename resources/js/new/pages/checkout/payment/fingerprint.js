import fingerprintjs2 from 'fingerprintjs2';
import { queryParams } from  '../../../../utils/queryParams';

let cache = null;
let initialized = false;


export default {

  watch: {

    ready_state: {
      handler(value) {
        if (!initialized && this.ready_state === 'complete') {
          if (!queryParams['3ds']) {
            setTimeout(() => this.fingerprint_apply_discount(), 1000);
            initialized = true;
          }
        }
      },
      immediate: true,
    },

  },


  methods: {

    fingerprint_calculate() {
      if (cache) {
        return Promise.resolve(cache);
      }

      return fingerprintjs2.getPromise().then(components => {
        const values = components.map(component => component.value);
        const result = fingerprintjs2.x64hash128(values.join(''), 31);

        cache = result;
        return result;
      });
    },

    fingerprint_apply_discount() {
      return Promise.resolve()
        .then(() => {
          return this.fingerprint_calculate();
        })
        .then(result => {
          return this.fetch_post('/apply-discount', {
            f: result,
            url: location.href,
          });
        })
        .catch(err => {

        });
    },

  },

};
