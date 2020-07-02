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
                    <label for="order_password">Password</label>
                    <div class="input-group">
                        <input type="text"
                               :required="passwordRequired"  @change="showEmailError=false"
                               id="order_password" name="password" class="form-control" v-model="password">

                        <div class="invalid-feedback" v-if="passwordRequired">
                            Please input your order password or request password by email
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
                        <button class="btn btn-outline-danger" type="button" @click.prevent="requestPassword">Request password</button>
                    </div>
                </div>
            </div>


        </form>


        <div v-for="order in orders">

        </div>

    </div>
</template>

<script>
  export default {
    name: "OrderStatus",
    props: ['orderPassword', 'orderEmail'],
    data() {
      return {
        email: this.orderEmail,
        password: this.orderPassword,
        showError: false,
        passwordRequired: true,
        alertType: '',
        alertMessage: '',
        showForm: true,
        orders: []
      }
    },

    methods: {
      async getOrderInfo() {
        this.orders = [];
        this.alertMessage = '';
        this.alertType = '';
        this.passwordRequired = true;
        if (!this.email || !this.password) {
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
            password: this.password
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

      async requestPassword() {
        this.alertMessage = '';
        this.alertType = '';
        this.passwordRequired = false;
        if (!this.email) {
          this.showError = true;
          return false;
        }
        this.showError = false;
        const response = await fetch('/request-order-password', {
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

      mounted() {
        if (this.password && this.email) {
          this.getOrderInfo();
        }
      }
    }
  }
</script>

<style scoped>

</style>
