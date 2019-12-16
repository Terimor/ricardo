import { searchPopulate } from '../services/queryParams';


export const goTo = (pathname, exclude) => {
  location.href = searchPopulate(pathname, exclude);
};
