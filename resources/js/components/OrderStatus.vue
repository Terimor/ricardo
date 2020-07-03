<template>
    <div class="contacts__wrapper">


        <div v-if="alertMessage" :class="'alert alert-' + this.alertType + ' alert-dismissible fade show'" role="alert">
            {{alertMessage}}
        </div>
        <form method="post" @submit.prevent="getOrderInfo" :class="showError ? 'was-validated' : ''" v-if="showForm">
            <div class="row">
                <div class="col-md-4">
                    <label for="order_email">Enter email</label>
                    <div class="input-group">
                        <input type="email" id="order_email" name="email" class="form-control" v-model="email" @change="showEmailError=false" required>
                        <div class="invalid-feedback">
                            Please input your email address.
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="order_code">Code</label>
                    <div class="input-group">
                        <input type="text"
                               :required="codeRequired"  @change="showEmailError=false"
                               id="order_code" name="code" class="form-control" v-model="code">

                        <div class="invalid-feedback" v-if="codeRequired">
                            Please input your order code or request code by email
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="submit">Get order status</button>

                    </div>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <div class="input-group">
                        <button class="btn btn-outline-danger" type="button" @click.prevent="requestCode">Request code</button>
                    </div>
                </div>
            </div>


        </form>

        <div class="card my-5">
            <ul class="list-group list-group-flush">
                <order-detail v-if="orders.length==1" :order="orders[0]" :is-active="true" />
                <order-detail v-else
                  v-for="order in orders"
                  :order="order"
                  :key="order.number"
                  :is-active="activeOrder == order.number"
                  @click="setActive(order.number)"
                />
            </ul>
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
        activeOrder: null
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


        if (response.status != 1) {
          this.alertType = 'danger';
        } else {
          this.alertType = 'success';
          this.showForm = false;
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
