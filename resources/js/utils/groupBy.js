export const groupBy = (arr, param, deepValue) => arr
  .reduce((obj, item) => {
    const value = item[param];

    return {
      ...obj,
      ...(!Array.isArray(value)
        ? {
          [value]:
          deepValue ? item[deepValue] : item,
        }
        : {
          ...(value
            .reduce((obj2, param2) => ({
              ...obj2,
              [param2]: [
                ...(obj2[param2] || []),
                deepValue ? item[deepValue] : item,
              ],
            }), obj)),
        }
      ),
    };
  }, {});
