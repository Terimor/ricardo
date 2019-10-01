export const goTo = (url) => {
  let curr =  localStorage.getItem('order_currency') || '';


  window.location.href = `${url}${window.location
    .search.replace(/\?/g, '&')}&cur=${curr}`;
};
