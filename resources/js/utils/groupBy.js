/**
 * Group items of an array by their specific property.
 *
 * @param {Array} arr An array to group.
 * @param {String} param A parameter of array's item to group by.
 * @returns {Object}
 *
 * @example
 * const array = [
  *   { id: 1, prop1: 'a', prop2: ['d', 'y'] },
  *   { id: 2, prop1: 'a', prop2: 'c' },
  *   { id: 3, prop1: 'b', prop2: 'd' },
  * ];
  * groupBy(array, 'prop1');
  * // => {
  * //   a: [
  * //       { id: 1, prop1: 'a', prop2: ['d', 'y'] },
  * //       { id: 2, prop1: 'a', prop2: 'c' },
  * //   ],
  * //   b: [
  * //       { id: 3, prop1: 'b', prop2: 'd' },
  * //   ],
  * // }
  * groupBy(array, 'prop2');
  * // => {
  * //   d: [
  * //     { id: 1, prop1: 'a', prop2: ['d',  'y'] },
  * //     { id: 3, prop1: 'b', prop2: 'd' }
  * //   ],
  * //   y: [
  * //     { id: 1, prop1: 'a', prop2: ['d', 'y'] }
  * //   ],
  * //   c: [
  * //     { id: 2, prop1: 'a', prop2: 'c' }
  * //   ]
  * // }
  */
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
