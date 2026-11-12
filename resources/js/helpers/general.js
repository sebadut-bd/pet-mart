import axios from 'axios';

export function initialize(store, router) {
    router.beforeEach((to, from, next) => {
        window.document.title = to.meta.pageTitle ? `${to.meta.pageTitle} · PetMart Admin` : 'PetMart Admin';

        store.state.dataList = {};
        store.state.formObject = {};
        store.state.formType = 1;
        store.state.updateId = '';
        store.state.formFilter = { keyword: '', perPage: 15, status: '' };
        next();
    });

    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['Accept'] = 'application/json';

    axios.interceptors.response.use(function (response) {
        return response;
    }, function (error) {
        const status = error.response ? parseInt(error.response.status) : 0;
        if (status === 401) {
            window.location.assign(`${window.baseUrl || ''}/login`);
        } else if (status === 419) {
            window.location.reload();
        }
        return Promise.reject(error);
    });
}
