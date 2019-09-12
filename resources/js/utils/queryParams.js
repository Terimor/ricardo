export const queryParams = () => {
  return location.search
    .slice(1)
    .split('&')
    .map((it) => it && it.split('='))
    .filter(Boolean)
    .reduce((acc, [key, value]) => {
      acc[key] = value

      return acc
    }, {})
}
