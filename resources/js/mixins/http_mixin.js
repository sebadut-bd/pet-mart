export default {
    data() {
        return {
            filter: {},
        };
    },
    methods: {
        httpErrorMessage(error, fallback = 'Something went wrong. Please try again.') {
            const res = error && error.response;
            if (!res) return 'No connection to the server. Check your network and try again.';
            const data = res.data || {};
            const bag = data.errors || (parseInt(data.status) === 3000 ? data.result : null);
            if (bag && typeof bag === 'object') {
                const first = Object.values(bag)[0];
                if (first) return Array.isArray(first) ? first[0] : String(first);
            }
            if (res.status === 403) return data.message || 'You do not have permission to do this.';
            if (res.status === 404) return 'This record no longer exists. Refresh the page.';
            if (res.status === 429) return 'Too many requests. Please wait a moment and try again.';
            return data.message || fallback;
        },

        adminRequest(method, url, data = {}, { silent = false, params = {} } = {}) {
            return this.axios({ method, url: this.urlGenerate(url), data, params })
                .then((response) => {
                    const body = response.data || {};
                    if (parseInt(body.status) !== 2000) {
                        const err = new Error(body.message || 'Request failed');
                        err.response = { status: 422, data: body };
                        throw err;
                    }
                    if (!silent && body.message) this.$toastr('success', body.message, 'Done');
                    return body.result;
                })
                .catch((error) => {
                    this.$toastr('error', this.httpErrorMessage(error), 'Error');
                    throw error;
                });
        },

        getDataList(page = 1, callback = false) {
            const _this = this;
            page = Number.isInteger(page) ? page : 1;
            _this.$store.commit('currentPagination', page);
            const params = Object.assign({}, this.filter, _this.formFilter, { page });
            _this.$store.commit('httpRequest', true);
            _this.axios({ method: 'get', url: _this.urlGenerate(), params })
                .then(function (response) {
                    const body = response.data;
                    _this.$store.commit('httpRequest', false);
                    if (parseInt(body.status) === 2000) {
                        _this.$store.commit('dataList', body.result);
                    } else {
                        _this.$store.commit('dataList', { data: [] });
                        _this.$toastr('error', body.message || 'The list could not be loaded.', 'Error');
                    }
                    if (typeof callback === 'function') callback(body.result);
                })
                .catch(function (error) {
                    _this.$store.commit('httpRequest', false);
                    _this.$toastr('error', _this.httpErrorMessage(error), 'Error');
                });
        },

        getGeneralData(keys, callback = false) {
            const _this = this;
            _this.axios.post(_this.urlGenerate('api/general'), keys).then((response) => {
                Object.keys(response.data.result || {}).forEach((key) => {
                    _this.$set(_this.requiredData, key, response.data.result[key]);
                });
                if (typeof callback === 'function') callback(response.data.result);
            }).catch(function (error) {
                _this.$toastr('error', _this.httpErrorMessage(error), 'Error');
            });
        },

        getData(url, method = 'get', params = {}, data = {}, callback = false) {
            const _this = this;
            _this.axios({ method, url, params, data }).then((response) => {
                const body = response.data;
                if (parseInt(body.status) === 2000) {
                    if (body.message) _this.$toastr('success', body.message, 'Done');
                    if (typeof callback === 'function') callback(body.result);
                    else _this.$store.commit('requiredData', body.result);
                } else {
                    _this.$toastr('warning', body.message, 'Warning');
                }
            }).catch(function (error) {
                _this.$store.commit('httpRequest', false);
                _this.$toastr('error', _this.httpErrorMessage(error), 'Error');
            });
        },

        changeStatus(obj = {}, showMessage = true) {
            const _this = this;
            _this.$store.commit('httpRequest', true);
            _this.axios({ method: 'post', url: `${_this.urlGenerate()}/status`, data: obj })
                .then(function (response) {
                    _this.$store.commit('httpRequest', false);
                    _this.getDataList(_this.currentPagination);
                    if (showMessage) _this.$toastr('success', response.data.message, 'Done');
                })
                .catch(function (error) {
                    _this.$store.commit('httpRequest', false);
                    if (showMessage) _this.$toastr('error', _this.httpErrorMessage(error), 'Error');
                });
        },

        submitForm(formData, model = true, callback = false, checkValidation = true, url = false) {
            const _this = this;
            const isEdit = _this.formType === 2;
            const URL = isEdit ? `${_this.urlGenerate(url)}/${_this.updateId}` : _this.urlGenerate(url);
            const pageNumber = isEdit ? _this.currentPagination : 1;

            this.$validator.validate().then((valid) => {
                if (!valid && checkValidation) return;
                this.$validator.errors.clear();
                _this.$store.commit('httpRequest', true);
                _this.axios({ method: isEdit ? 'put' : 'post', url: URL, data: formData }).then(function (response) {
                    const body = response.data;
                    _this.$store.commit('httpRequest', false);

                    if (parseInt(body.status) === 2000) {
                        if (model) {
                            _this.closeModal(model);
                            _this.getDataList(pageNumber);
                        }
                        _this.$toastr('success', body.message || 'Saved.', 'Done');
                    } else if (parseInt(body.status) === 3000) {
                        _this.$toastr('warning', body.message, 'Check the form');
                        _this.assignValidationError(body.result);
                    } else {
                        _this.$toastr('error', body.message || 'Not saved.', 'Error');
                    }
                    if (typeof callback === 'function') callback(body);
                }).catch(function (error) {
                    _this.$store.commit('httpRequest', false);
                    const res = error.response;
                    if (res && res.status === 422 && res.data && res.data.errors) {
                        _this.assignValidationError(res.data.errors);
                    }
                    _this.$toastr('error', _this.httpErrorMessage(error), 'Error');
                });
            });
        },

        deleteInformation(index, id, url = false, callback = false, callDataList = true) {
            const _this = this;
            _this.confirmBox(function (ok) {
                if (!ok) return;
                _this.axios.delete(url || `${_this.urlGenerate()}/${id}`).then(function (response) {
                    const body = response.data;
                    if (parseInt(body.status) === 2000) {
                        _this.$toastr('success', body.message || 'Deleted.', 'Done');
                        if (callDataList) _this.getDataList(_this.currentPagination || 1);
                        if (typeof callback === 'function') callback(true);
                    } else {
                        _this.$toastr('warning', body.message, 'Warning');
                    }
                }).catch(function (error) {
                    _this.$toastr('error', _this.httpErrorMessage(error), 'Error');
                });
            }, 'Delete this item?', 'This cannot be undone.');
        },

        getConfigurations(callback = null) {
            const _this = this;
            _this.axios.post(_this.urlGenerate('api/configurations'), {}).then(function (response) {
                if (parseInt(response.data.status) === 2000) {
                    _this.$store.commit('Config', response.data.result);
                    _this.$store.commit('allMenus', response.data.result.menus);
                    if (typeof callback === 'function') callback(response.data.result);
                }
            }).catch(function (error) {
                _this.$toastr('error', _this.httpErrorMessage(error, 'The menu could not be loaded.'), 'Error');
            });
        },
    },
};
