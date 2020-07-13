<template>
    <div class="modal" tabindex="-1" role="dialog" id="address-modal" :style="'display: '+displayModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{$t('support.address.change')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelEdit">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="payment-form__delivery-address">
                        <ZipCode
                            v-if="form.country === 'br'"
                            :$v="$v.form.zipcode"
                            :isLoading="isLoading"
                            @setBrazilAddress="setBrazilAddress"
                            :country="form.country"
                            :form="form"
                            name="zipcode" />
                        <Street
                            :$v="$v.form.street"
                            :isLoading="isLoading"
                            :form="form"
                            name="street" />
                        <Building
                            :extraFields="extraFields"
                            :form="form"
                            :$v="$v.form" />
                        <Complement
                            :isLoading="isLoading"
                            :extraFields="extraFields"
                            :form="form"
                            :$v="$v.form" />
                        <District
                            :isLoading="isLoading"
                            :extraFields="extraFields"
                            :form="form"
                            :$v="$v.form" />
                        <City
                            :$v="$v.form.city"
                            :isLoading="isLoading"
                            :form="form"
                            name="city" /> <br/>
                        <State
                            :placeholder="stateExtraField && stateExtraField.type === 'dropdown'"
                            :country="form.country"
                            :stateExtraField="stateExtraField"
                            :isLoading="isLoading"
                            :form="form"
                            :$v="$v.form" /> <br/>
                        <ZipCode
                            v-if="form.country !== 'br'"
                            :$v="$v.form.zipcode"
                            :isLoading="isLoading"
                            @setBrazilAddress="setBrazilAddress"
                            :country="form.country"
                            :form="form"
                            name="zipcode" /> <br/>
                        <Country
                            :$v="$v.form.country"
                            :form="form"
                            name="country" />
                        <br />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="changeAddress">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="cancelEdit">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import addressValidation from '../validation/address-validation';
  import globals from '../mixins/globals';
  import * as extraFields from '../mixins/extraFields';
  import Street from './common/common-fields/Street';
  import City from './common/common-fields/City';
  import ZipCode from './common/common-fields/ZipCode';
  import Country from './common/common-fields/Country';
  import State from './common/extra-fields/State';
  import Building from './common/extra-fields/Building';
  import Complement from './common/extra-fields/Complement';
  import District from './common/extra-fields/District';

  export default {
    name: "ChangeOrderAddress",
    props: ['order'],
    components: {
      Street,
      City,
      ZipCode,
      Country,
      State,
      Building,
      Complement,
      District,
    },
    mixins: [
      globals,
      extraFields.tplMixin,
    ],
    data() {
      return {
        displayModal: 'none',
        isLoading: {
          address: false,
        },
        form: {
          countryCodePhoneField: null,
          street: null,
          city: null,
          zipcode: null,
          country: null,
        },
      }
    },

    validations: addressValidation,

    methods: {

      async changeAddress() {
        if (this.$v.$invalid) {
          return false;
        }
        this.$emit('submit', this.form);
      },

      setBrazilAddress(res) {
        this.form.street = res.address || this.form.street;
        this.form.city = res.city || this.form.city;
        this.form.state = res.state || this.form.state;
        this.form.district = res.district || this.form.district;
        this.form.complement = res.complement || this.form.complement;
      },

      setInputFields() {
        this.form.country = this.order.shipping_country;
        this.form.zipcode = this.order.shipping_zip;
        this.form.street = this.order.shipping_street;
        this.form.city = this.order.shipping_city;
        this.form.district = this.order.shipping_street2;
        this.form.complement = this.order.shipping_apt;
        this.form.building = this.order.shipping_building;

        setTimeout(() => {
          this.form.state = this.order.shipping_state;
          this.displayModal = 'block'
        }, 500)
      },


      cancelEdit() {
        this.$emit('cancelEdit')
      }

    },
    mounted() {
      this.setInputFields();
    },

    watch: {
      order() {
        this.setInputFields();
      }
    }

  }
</script>

<style scoped>

</style>
