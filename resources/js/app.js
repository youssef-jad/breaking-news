
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import App from './components/App.vue';
import * as VueGoogleMaps from 'vue2-google-maps'
import VueSweetalert2 from 'vue-sweetalert2';


Vue.use(VueGoogleMaps, {
    load: {
      key: 'AIzaSyCIJOD4BWnu8o_S9KnM3McYYwHfznEngn8',
    }
})

Vue.use(VueSweetalert2)


const app = new Vue({
    el: '#app',
    components: {
        App
    }
});
