<template>
    <button
        @click="!isLoading ? $emit('click') : (() => {})"
        class="green-button-animated"
        :class="{ 'green-button-active': !isLoading }"
        v-bind="rest"
        :disabled="isLoading"
    >
        <Spinner v-if="isLoading" :style="styleSpinner" />
        <div v-if="isLoading" class="green-button-disabled"></div>
        <span class="green-button-text" :style="styleText">
            <slot></slot>
        </span>
    </button>
</template>

<script>
  import Spinner from './preloaders/Spinner';

  export default {
    name: 'GreenButton',
    props: ['label', 'rest', 'isLoading', 'spinnerScale'],
    components: {
      Spinner,
    },
    computed: {
      styleSpinner() {
        return {
          transform: 'scale(' + (this.spinnerScale || .8) + ')',
        };
      },
      styleText() {
        return {
          visibility: this.isLoading ? 'hidden' : 'visible',
        };
      },
    },
  };
</script>

<style lang="scss">
    .green-button-animated {
        bottom: 0;
        box-shadow: rgb(180, 181, 181) 2px 2px 2px 0;
        color: rgb(255, 255, 255);
        //min-height: 92px;
        height: auto;
        position: relative;
        display: block;
        text-decoration: none solid rgb(255, 255, 255);
        text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
        text-transform: capitalize;
        top: 0;
        width: 100%;
        column-rule-color: rgb(255, 255, 255);
        perspective-origin: 195.688px 46px;
        transform-origin: 195.695px 46px;
        caret-color: rgb(255, 255, 255);
        background: rgb(255, 47, 33) linear-gradient(rgb(15, 155, 15), rgb(13, 132, 13)) repeat scroll 0% 0% / auto padding-box border-box;
        border: 1px solid rgb(15, 155, 15);
        border-radius: 3px 3px 3px 3px;
        font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
        margin: 0 0 15px;
        outline: rgb(255, 255, 255) none 0;
        padding: 20px;
        transition: all 0.2s linear 0s;

        &.green-button-active {
            cursor: pointer;
        }

        &:before {
            opacity: 0;
            font-family: FontAwesome!important;
            content: '\f054';
            width: 0;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 0 50% 50% 0;
            background-color: rgba(255,255,255,.3);
            transition: all .2s linear 0s;
        }

        &.green-button-active:hover {
            background-image: linear-gradient(to bottom,#6d4 0,#3d6c04 100%);

            &:before {
                opacity: 1;
                width: 30px;
            }
        }

        &:after {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            cursor: pointer;
            text-shadow: rgba(0, 0, 0, 0.3) -1px -1px 0;
            text-transform: capitalize;
            column-rule-color: rgb(255, 255, 255);
            caret-color: rgb(255, 255, 255);
            border: 0 none rgb(255, 255, 255);
            font: normal normal 700 normal 18px / 25.7143px "Noto Sans", sans-serif;
            outline: rgb(255, 255, 255) none 0;
        }

        .green-button-disabled {
            background-color: #fff;
            bottom: 0;
            left: 0;
            opacity: .5;
            position: absolute;
            right: 0;
            top: 0;
            z-index: 1;
        }

        .checkmark {
            margin-top: -9px;
            top: 50%;
        }

        .spinner {
            left: 50%;
            margin-left: -32px;
            margin-top: -32px;
            position: absolute;
            top: 50%;
            z-index: 0;
        }
    }
</style>
