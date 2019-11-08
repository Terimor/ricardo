import { queryParams } from '../utils/queryParams';
import wait from '../utils/wait';


wait(
  () => !!window.Startup,
  () => {
    Startup.success = () => {};
    Startup.failure = () => {};

    Startup.AfterResult(result => {
      Startup.success(result);
    });

    Startup.AfterFailure(result => {
      Startup.failure(result);
    });
  },
);


export function check(fields = {}) {
  return new Promise(resolve => {
    const params = queryParams();

    if (!window.Startup) {
      resolve(null);
      return;
    }

    for (const key of Object.keys(params)) {
      Startup.Store(key, params[key]);
    }

    for (const key of Object.keys(fields)) {
      Startup.FieldStore(key, fields[key]);
    }

    if (location.pathname.startsWith('/checkout')) {
      if (!params.tpl) {
        Startup.Store('tpl', 'emc1');
      }

      if (!params.product) {
        Startup.Store('product', checkoutData.product.skus[0] && checkoutData.product.skus[0].code || null);
      }
    }

    Startup.success = result => {
      resolve(result);
    };

    Startup.failure = result => {
      resolve(null);
    };

    Startup.Init();
  });
}
