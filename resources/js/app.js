import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import VeeValidate from 'vee-validate';
import axios from 'axios';
import VueAxios from 'vue-axios';
import VueApexCharts from 'vue-apexcharts';
import VueToastr from '@deveodk/vue-toastr';
import '@deveodk/vue-toastr/dist/@deveodk/vue-toastr.css';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import App from './App';
import routes from './routes';
import StoreData from './store';
import BaseMixin from './mixins/base_mixin';
import HttpMixin from './mixins/http_mixin';
import { initialize } from './helpers/general';

Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(VeeValidate, { events: 'input', fieldsBagName: '' });
Vue.use(VueAxios, axios);
Vue.use(VueSweetalert2);
Vue.use(VueToastr, { defaultPosition: 'toast-bottom-right', defaultType: 'info', defaultTimeout: 3500 });
Vue.component('apexchart', VueApexCharts);

Vue.filter('truncate', function (value, limit) {
    if (!value) return '';
    return value.length <= limit ? value : value.substring(0, limit) + '…';
});

Vue.mixin(BaseMixin);
Vue.mixin(HttpMixin);

const store = new Vuex.Store(StoreData);
const router = new VueRouter({
    mode: 'history',
    routes,
    linkActiveClass: 'active',
    scrollBehavior: () => ({ x: 0, y: 0 }),
});

initialize(store, router);

new Vue({
    el: '#app',
    components: { App },
    router,
    store,
});
