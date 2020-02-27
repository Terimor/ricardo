<template>

  <text-field
    id="cvv-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
      autocomplete: 'cc-csc',
      'data-bluesnap': 'encryptedCvv',
    }"
    postfix="<i class='fa fa-question-circle'></i>"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @click-postfix="openModal"
    @input="input">

    <el-dialog
      class="cvv-popup"
      :title="textPopupTitle"
      :visible.sync="isModalOpen"
      @click="isModalOpen = false">

      <div class="cvv-popup__content">
        <p v-html="textPopupLine1"></p>
        <div><img :src="$root.cdn_url + '/assets/images/cvv_popup.jpg'" alt=""></div>
        <p v-html="textPopupLine2"></p>
      </div>

    </el-dialog>

  </text-field>

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    data() {
      return {
        isModalOpen: false,
      };
    },


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.card_cvv');
      },

      textRequired() {
        return this.$t('checkout.payment_form.card_cvv.required');
      },

      textPopupTitle() {
        return this.$t('checkout.payment_form.cvv_popup.title');
      },

      textPopupLine1() {
        return this.$t('checkout.payment_form.cvv_popup.line_1');
      },

      textPopupLine2() {
        return this.$t('checkout.payment_form.cvv_popup.line_2');
      },

    },


    methods: {

      input() {
        let value = this.form[this.name] || '';

        value = value.replace(/[^0-9]/g, '');
        value = value.substr(0, 4);

        this.form[this.name] = value;
      },

      openModal() {
        const node = document.querySelector('.cvv-popup .el-dialog');

        if (node) {
          node.removeEventListener('click', this.closeModal);
          node.addEventListener('click', this.closeModal);
        }

        this.isModalOpen = true;
      },

      closeModal() {
        this.isModalOpen = false;
      },

    },

  };

</script>


<style lang="scss" scoped>

  @import "../../../../sass/variables";

  #cvv-field {
    :global(.postfix) {
      cursor: pointer;
    }
  }
  
  .cvv-popup {
    :global(.el-dialog) {
      margin-top: 10vh!important;
      max-width: 600px;
    }

    :global(.el-dialog__header) {
      display: flex;
      justify-content: center;
    }

    :global(.el-dialog__title) {
      text-align: center;
      font-size: 20px;
      font-weight: 700;
    }
  }

  .cvv-popup__content {
    display: flex;
    flex-direction: column;
    align-items: center;

    p {
      font-family: 'Noto Sans', sans-serif;
      font-size: 17px;
      width: 100%;
    }

    img {
      margin: 0 auto;
      max-width: 300px;
      height: auto;
    }
  }

  @media screen and ($s-down) {
    .cvv-popup {
      :global(.el-dialog) {
        width: 90%;
      }
    }
  }

</style>
