<template>
    <div class="contacts__wrapper">


        <div v-if="alertMessage" :class="'alert alert-' + this.alertType + ' alert-dismissible fade show'" role="alert">
            {{alertMessage}}
        </div>
        <form method="post" @submit.prevent="getOrderInfo" :class="showError ? 'was-validated' : ''" v-if="showForm">
            <div class="row">
                <div class="col-md-4">
                    <label for="order_email">{{$t('support.enter_email')}}</label>
                    <div class="input-group">
                        <input type="email" id="order_email" name="email" class="form-control" v-model="email" @change="showEmailError=false" required>
                        <div class="invalid-feedback">
                            {{$t('support.email_required')}}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="order_code">{{$t('support.code')}}</label>
                    <div class="input-group">
                        <input type="text"
                               :required="codeRequired"  @change="showEmailError=false"
                               id="order_code" name="code" class="form-control" v-model="code">

                        <div class="invalid-feedback" v-if="codeRequired">
                            {{$t('support.code_required')}}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label>&nbsp;</label>

                    <div class="input-group">
                        <button class="btn btn-outline-secondary"
                                type="submit"
                                :disabled="submitDisabled"
                        >
                            {{$t('support.get_order_status')}}
                        </button>
                        <button class="btn btn-outline-danger request-code-button"
                                type="button"
                                @click.prevent="requestCode"
                                :disabled="requestCodeDisabled"
                        >
                            {{$t('support.request_code')}}
                        </button>
                    </div>
                </div>
                <div class="col-md-2">

                </div>
            </div>


        </form>

        <div class="card my-5" v-if="orders.length">
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
                        <td>{{orders[0].status}}</td>
                        <td>{{orders[0].created_at}}</td>
                        <td>{{orders[0].total_paid}}</td>
                    </tr>
                    <order-detail  :order="orders[0]" :is-active="true" />
                </template>
                <template v-else v-for="order in orders">
                    <tr @click="setActive(order.number)" style="cursor: pointer">
                        <td>{{order.number}}</td>
                        <td>{{order.status}}</td>
                        <td>{{order.created_at}}</td>
                        <td>{{order.total_paid}}</td>
                    </tr>
                    <order-detail :order="order" :key="order.number" :is-active="activeOrder == order.number"/>
                </template>

                </tbody>
            </table>

        </div>


    </div>
</template>

<script>
  export default {
    name: "OrderStatus",
    props: ['orderCode', 'orderEmail'],
    data() {
      return {
        email: this.orderEmail,
        code: this.orderCode,
        showError: false,
        codeRequired: true,
        alertType: '',
        alertMessage: '',
        showForm: true,
        orders: [],
        activeOrder: null,
        requestCodeDisabled: false,
        submitDisabled: false
      }
    },

    methods: {
      setActive(number) {
        this.activeOrder = this.activeOrder == number ? null : number;
      },
      async getOrderInfo() {

        this.orders = [];
        this.alertMessage = '';
        this.alertType = '';
        this.codeRequired = true;
        if (!this.email || !this.code) {
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

        this.submitDisabled = false;
        if (response.status != 1) {
          this.alertMessage = response.message;
          this.alertType = 'danger';
        } else {
          this.orders = response.orders
        }
      },

      async requestCode() {
        this.alertMessage = '';
        this.alertType = '';
        this.codeRequired = false;
        if (!this.email) {
          this.showError = true;
          return false;
        }
        this.requestCodeDisabled = true;
        this.showError = false;
        const response = await fetch('/request-order-code', {
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


        this.requestCodeDisabled = false;
        if (response.status != 1) {
          this.alertType = 'danger';
        } else {
          this.email = '';
          this.code = '';
          this.alertType = 'success';
        }
        this.alertMessage = response.message;
      },

    },
    mounted() {
      if (this.code && this.email) {
        this.getOrderInfo();
      }
    }
  }
</script>

<style scoped>

</style>
