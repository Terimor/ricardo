import checkout from '../checkout';


export default {

  mixins: [
    checkout,
  ],


  validations() {
    return {
      ...checkout.validations.call(this),
    };
  },

};
