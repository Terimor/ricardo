export default function searchPopulate(url, exclude = []) {
  url = url || '';

  let pathname = url.split('?')[0];
  let search = url.split('?')[1] || '';
  search = search.split('#')[0];

  let url_query_params = search
    .split('&')
    .filter(item => !!item)
    .map(item => item.split('='))
    .reduce((acc, item) => {
      acc[decodeURIComponent(item[0])] = decodeURIComponent(item[1]);
      return acc;
    }, {});

  for (let name of Object.keys(js_query_params)) {
    if (!url_query_params[name] && exclude.indexOf(name) === -1) {
      url_query_params[name] = js_query_params[name];
    }
  }

  let new_search = [];

  for (let name of Object.keys(url_query_params)) {
    new_search.push(encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || ''));
  }

  new_search = new_search.length > 0
    ? '?' + new_search.join('&')
    : '';

  return pathname + new_search;
}
