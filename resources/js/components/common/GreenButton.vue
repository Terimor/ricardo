<template>
    <button @click="$emit('click')" class="green-button-animated" v-bind="rest">
        <span><slot></slot></span>
    </button>
</template>

<script>
  export default {
    name: 'GreenButton',
    props: ['label', 'rest']
  };
</script>

<style lang="scss">
    .green-button-animated {
        cursor: pointer;
        bottom: 0;
        box-shadow: rgb(180, 181, 181) 2px 2px 2px 0;
        color: rgb(255, 255, 255);
        height: 92px;
        position: relative;
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

        &:hover {
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
    }
</style>
