export const goTo = (url) => {
  window.location.href = url+window.location.search.replace(/\?/g, '&');
};
