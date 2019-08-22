try {
    EBANX.config.setMode('test');
    EBANX.config.setPublishableKey('test_pk_A1llXb-KEIhiRXeJjx4HPw');
    EBANX.config.setCountry('br');
} catch (e) {
    console.error('Error with initialize EBANX')
    console.error(e)
}
