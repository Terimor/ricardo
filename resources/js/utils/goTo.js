import searchPopulate from './searchPopulate';


export const goTo = (pathname, exclude) => {
  location.href = searchPopulate(pathname, exclude);
};
