import app from '../../app';


js_deps.wait(['vue'], () => {
    new Vue({

        el: '#app',


        mixins: [
            app,
        ],

    });
});
