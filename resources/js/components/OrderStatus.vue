<template>
    <div class="contacts__wrapper">

        <change-order-address
          v-if="editingAddressOrder"
          :order="editingAddressOrder"
          @cancelEdit="editingAddressOrder=null"
          @submit="saveOrderAddress"
        />

        <div v-if="alertMessage" :class="'alert alert-' + this.alertType + ' alert-dismissible fade show'" role="alert">
            {{alertMessage}}
        </div>
        <form method="post" @submit.prevent="getOrderInfo" :class="showError ? 'was-validated' : ''" v-if="showForm">
            <div class="row">
                <div class="col-md-3 col-xl-4">
                    <label for="order_email">{{$t('support.enter_email')}}</label>
                    <div class="input-group">
                        <input ref="email" type="email" id="order_email" name="email" class="form-control" v-model="email" @change="showEmailError=false" required>
                        <div class="invalid-feedback">
                            {{$t('support.email_required')}}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-xl-4 mt-4 mt-md-0">
                    <label for="order_code">{{$t('support.code')}}</label>
                    <div class="input-group">
                        <input type="text"
                               ref="code"
                               :required="codeRequired"  @change="showEmailError=false"  pattern="[0-9]{6}"
                               id="order_code" name="code" class="form-control" v-model="code">

                        <div class="invalid-feedback" v-if="codeRequired">
                            {{$t('support.code_required')}}
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                  <label>&nbsp;</label>
                  
                  <div>
                    <button class="btn btn-outline-secondary"
                            type="submit"
                            :disabled="submitDisabled"
                    >
                        {{$t('support.get_order_status')}}
                    </button>

                    <div class="d-block d-sm-inline-block mt-2 mt-sm-0 ml-sm-2">
                      <button class="btn btn-outline-success"
                              type="button"
                              @click.prevent="requestCode"
                              :disabled="requestCodeDisabled"
                      >
                          {{$t('support.request_code')}}
                      </button>
                    </div>
                  </div>
                </div>
            </div>


        </form>

        <div class="card my-5" v-if="orders.length">
          <div class="table-responsive">
            <table class="table orders-statuses-table">
                <thead>
                    <tr>
                        <th>{{$t('support.order')}}</th>
                        <th>{{$t('support.status')}}</th>
                        <th>{{$t('support.date')}}</th>
                        <th>{{$t('support.total_paid')}}</th>
                    </tr>

                </thead>
                <tbody>

                <template v-if="orders.length==1">
                    <tr>
                        <td>{{orders[0].number}}</td>
                        <td>{{$t('support.status.' + orders[0].status)}}</td>
                        <td>{{orders[0].created_at}}</td>
                        <td>{{orders[0].total_paid}}</td>
                    </tr>
                    <order-detail :order="orders[0]" :is-active="true" @editAddressClick="openAddressForm" />
                </template>
                <template v-else v-for="order in orders">
                    <tr @click="setActive(order)" style="cursor: pointer" :class="{'highlight-row': activeOrder && activeOrder.number == order.number}">
                        <td>{{order.number}}</td>
                        <td>{{$t('support.status.' + order.status)}}</td>
                        <td>{{order.created_at}}</td>
                        <td>{{order.total_paid}}</td>
                    </tr>
                    <order-detail
                        :order="order"
                        :key="order.number"
                        :is-active="activeOrder && activeOrder.number == order.number"
                        @editAddressClick="openAddressForm"
                    />
                </template>

                </tbody>
            </table>
          </div>
        </div>


    </div>
</template>

<script>
  import Cleave from 'cleave.js';
  import ChangeOrderAddress from "./ChangeOrderAddress";

  export default {
    name: "OrderStatus",
    components: {ChangeOrderAddress},
    props: ['supportCode', 'orderEmail'],
    data() {
      return {
        email: this.orderEmail,
        code: this.supportCode,
        showError: false,
        codeRequired: true,
        alertType: '',
        alertMessage: '',
        showForm: true,
        orders: [],
        activeOrder: null,
        requestCodeDisabled: false,
        submitDisabled: false,
        editingAddressOrder: null,
      }
    },

    methods: {
      setActive(order) {

        this.editingAddressOrder = null
        this.activeOrder = (this.activeOrder && this.activeOrder.number == order.number) ? null : order;
        this.alertMessage = '';
        this.alertType = '';
      },

      openAddressForm(order) {
        this.alertMessage = '';
        this.alertType = '';
        js_data.countries = order.countries;

        this.editingAddressOrder = order;
      },

      validateCode() {
        return this.$refs['code'].checkValidity();
      },

      async saveOrderAddress(data) {
        const confirmed = confirm(this.$t('support.confirm.address_change'));
        if (!confirmed) {
          return false;
        }
        data.number = this.editingAddressOrder.number;
        data.email = this.email;
        data.code = this.code;

        this.editingAddressOrder = null;
        const response = await fetch('/change-order-address', {
          method: 'post',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(data)
        }).then(resp => {
          return resp.json();
        });

        if (response.status != 1) {
          this.alertMessage = response.message;
          this.alertType = 'danger';
        } else {
          this.alertMessage = response.message;
          this.alertType = 'success';
          for (let key in this.orders) {
            if (this.orders[key].number == response.order.number) {
              this.orders[key] = response.order;
              this.activeOrder = response.order;
            }
          }
        }

      },

      async getOrderInfo() {

        this.orders = [];
        this.alertMessage = '';
        this.alertType = '';
        this.codeRequired = true;
        if (!this.email || !this.code || !this.validateEmail() || !this.validateCode()) {
          this.showError = true;
          return false;
        }
        this.showError = false;
        this.submitDisabled = true;
        const response = await fetch('/get-order-info', {
          method: 'post',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({
            email: this.email,
            code: this.code
          }),
        }).then(resp => {
          return resp.json();
        });

        if (response.status != 1) {
          this.alertMessage = response.message;
          this.alertType = 'danger';
          this.submitDisabled = false;
        } else {
          this.orders = response.orders
          if (this.orders.length == 1) {
            this.activeOrder = this.orders[0];
          }
        }
      },

      validateEmail() {
        return this.$refs['email'].checkValidity();
      },

      async requestCode() {
        this.showError = false;
        this.alertMessage = '';
        this.alertType = '';
        this.codeRequired = false;
        if (!this.email || !this.validateEmail()) {
          this.showError = true;
          return false;
        }
        this.requestCodeDisabled = true;
        this.showError = false;
        const response = await fetch('/request-support-code', {
          method: 'post',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({
            email: this.email
          }),
        }).then(resp => {
          return resp.json();
        });

        if (response.status != 1) {
          this.requestCodeDisabled = false;
          this.alertType = 'danger';
        } else {
          this.alertType = 'success';
        }
        this.alertMessage = response.message;
      },

    },
    mounted() {
      if (this.code && this.email) {
        this.getOrderInfo();
      }

      js_deps.wait_for(() => {
        return !!document.getElementById('order_code');
      }, () => {
        var cleave = new Cleave('#order_code', {
          numericOnly: true,
          blocks: [6]
        });
      });
    }
  }
</script>

<style scoped>

</style>
