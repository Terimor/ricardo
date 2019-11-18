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


export function ipqsCheck(fields = {}) {
  return new Promise(resolve => {
    let attempt = 0;

    const searchParams = new URL(location).searchParams;

    if (searchParams.get('3ds') === 'failure') {
      let result = null;

      try {
        result = JSON.parse(localStorage.getItem('3ds_ipqs'));
      }
      catch (err) {
        
      }

      if (result) {
        resolve(result);
        return;
      }
    }

    if (!window.Startup) {
      resolve(null);
      return;
    }

    const sendRequest = () => {
      attempt++;

      for (let key of searchParams.keys()) {
        Startup.Store(key, searchParams.get(key));
      }

      for (let key of Object.keys(fields)) {
        Startup.FieldStore(key, fields[key]);
      }

      Startup.success = result => {
        localStorage.setItem('3ds_ipqs', JSON.stringify(result));
        resolve(result);
      };

      Startup.failure = result => {
        if (attempt < 3) {
          setTimeout(sendRequest, 1000);
        } else {
          resolve(null);
        }
      };

      Startup.Init();
    };

    sendRequest();
  });
}
