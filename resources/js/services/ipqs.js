let cache = null;


window.IPQ = {

  Callback() {
    Startup.AfterResult(result => {
      IPQ.success(result);
    });

    Startup.AfterFailure(result => {
      IPQ.failure(result);
    });

    IPQ.sendRequest();
  },

};


export function ipqsCheck(fields = {}) {
  let attempt = 0;

  return new Promise(resolve => {
    if (cache) {
      return resolve(cache);
    }

    IPQ.sendRequest = () => {
      attempt++;

      for (let key of Object.keys(js_query_params)) {
        Startup.Store(key, js_query_params[key]);
      }

      for (let key of Object.keys(fields)) {
        Startup.FieldStore(key, fields[key]);
      }

      Startup.Init();
    };

    IPQ.success = result => {
      localStorage.setItem('3ds_ipqs', JSON.stringify(result));
      cache = result;
      resolve(result);
    };

    IPQ.failure = result => {
      //if (attempt < 3) {
        //setTimeout(IPQ.sendRequest, 1000);
      //} else {
        resolve(null);
      //}
    };

    if (js_query_params['3ds'] === 'failure') {
      let result = null;

      try {
        result = JSON.parse(localStorage.getItem('3ds_ipqs'));
      }
      catch (err) {

      }

      if (result) {
        cache = result;
        return resolve(result);
      }
    }

    if (!window.Startup) {
      let script = document.createElement('script');
      script.src = 'https://www.clkscore.com/api/*/' + js_data.ipqualityscore_api_hash + '/learn.js';
      document.body.appendChild(script);
    } else {
      IPQ.sendRequest();
    }
  });
}
