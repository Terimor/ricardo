import wait from '../utils/wait';


const countryCode = window.checkoutData && checkoutData.countryCode
  ? checkoutData.countryCode
  : window.upsellsData && upsellsData.countryCode
    ? upsellsData.countryCode
    : window.upsells && upsells.countryCode
      ? upsells.countryCode
      : 'us';

if (countryCode === 'br') {
  wait(
    () => !!window.EBANX,
    () => {
      try {
        EBANX.config.setMode('test');
        EBANX.config.setPublishableKey('test_pk_A1llXb-KEIhiRXeJjx4HPw');
        EBANX.config.setCountry('br');
      } catch (err) {
        console.error('Error with initialize EBANX');
        console.error(err);
      }
    }
  );
}
