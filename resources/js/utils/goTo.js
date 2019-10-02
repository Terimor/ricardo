export const goTo = (url) => {
  const urlObj = new URL(url, location);
  const curObj = new URL(location);

  curObj.searchParams.forEach((value, key) => {
    if (!urlObj.searchParams.has(key)) {
      urlObj.searchParams.set(key, value);
    }
  });

  location.href = urlObj.pathname + urlObj.search;
};
