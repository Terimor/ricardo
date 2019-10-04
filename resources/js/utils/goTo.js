export const goTo = (url, options = {}) => {
  const urlObj = new URL(url, location);
  const curObj = new URL(location);

  curObj.searchParams.forEach((value, key) => {
    if (!urlObj.searchParams.has(key)) {
      urlObj.searchParams.set(key, value);
    }
  });

  if (options.exclude) {
    for (const key of options.exclude) {
      urlObj.searchParams.delete(key);
    }
  }

  location.href = urlObj.pathname + urlObj.search;
};
