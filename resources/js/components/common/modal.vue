<template>

    <div class="modal-overlay">
        <div class="modal" :class="classlist">
            <header class="modal-header">
                <h2 class="modal-title"><slot name="modal-header"></slot></h2>
                <button @click="$emit('close')" class="modal-close">&#10006;</button>
            </header>
            <div class="modal-body">
                <slot name="modal-body"></slot>
            </div>
            <footer class="modal-footer">
                <slot name="modal-footer"></slot>
            </footer>
        </div>
    </div>

</template>

<script>
  export default {
    name: 'modal',
    props: [
      'classlist'
    ],
    computed: {},
    methods: {}
  }
</script>

<style lang="scss">
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 50px 20px;
        background: rgba(0,0,0,.5);
    }
    .modal {
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 5px;
        box-shadow: 1px 1px 1px 1px hsla(0,0%,83.9%,.4);
        &_xl {
            display: flex;
            flex-direction: column;
            height: 100%;
            .modal-body {
                flex: 1 1 100%;
            }
        }
    }
    .modal-header {
        position: relative;
        padding: 20px 40px;
        border-bottom: 1px solid #ccc;
        color: #000000;
    }
    .modal-title {
        margin: 0;
    }
    .modal-close {
        position: absolute;
        top: 10px;
        right: 10px;
        display: block;
        width: 20px;
        height: 20px;
        padding: 0;
        background-color: transparent;
        border: none;
        text-align: center;
        font-size: 20px;
        line-height: 1;
        color: #000000;
        cursor: pointer;
        transition: opacity .3s;
        &:hover, &:focus, &:active {
            opacity: .7;
        }
    }
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #ccc;
    }
</style>
