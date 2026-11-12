export default {
    data() {
        return {
            baseUrl: window.baseUrl || '',
            perPages: [10, 15, 20, 30, 50, 100],
        };
    },
    watch: {
        errors: {
            handler(value) {
                const _this = this;
                $('.is-invalid').removeAttr('title').removeClass('is-invalid');
                $('.error_message').remove();
                (value.items || []).forEach(function (val) {
                    const target = $(`[name='${val.field}']`);
                    const message = _this.replaceString(val.msg, val.field);
                    if ($(`#${val.field}_message`).length === 0) {
                        target.parent().append(`<span class="error_message" id="${val.field}_message">${$('<div>').text(message).html()}</span>`);
                    }
                    target.addClass('is-invalid').attr('title', message);
                });
            },
            deep: true,
        },
    },
    computed: {
        formType() { return this.$store.getters.formType; },
        formObject() { return this.$store.getters.formObject; },
        formFilter() { return this.$store.getters.formFilter; },
        dataList() { return this.$store.getters.dataList; },
        updateId() { return this.$store.getters.updateId; },
        httpRequest() { return this.$store.getters.httpRequest; },
        requiredData() { return this.$store.getters.requiredData; },
        modalTitle() { return this.$store.getters.modalTitle; },
        Config() { return this.$store.getters.Config; },
        allMenus() { return this.$store.getters.allMenus; },
        currentPagination() { return this.$store.getters.currentPagination; },
        Permissions() {
            const config = this.$store.getters.Config;
            return (config && Array.isArray(config.permissions)) ? config.permissions : [];
        },
    },
    methods: {
        can(permission) {
            return this.Permissions.includes(permission);
        },
        showData(dataArray, fieldName, retBoolean = false) {
            if (dataArray && dataArray[fieldName] !== undefined && dataArray[fieldName] !== null) {
                return dataArray[fieldName];
            }
            return retBoolean ? false : '-';
        },
        editData(data, updateId, model = 'formModal', callback = false) {
            this.$store.commit('formObject', data);
            this.$store.commit('updateId', updateId);
            this.$store.commit('formType', 2);
            if (model) this.openModal(model);
            if (typeof callback === 'function') callback(data);
        },
        openModal(modalName = 'formModal', title = false, callback = false, resetValidation = true, defaultObject = {}, formObject = false) {
            if (title) this.$store.commit('modalTitle', title);
            $('#' + modalName).modal('show');
            if (resetValidation) this.$validator.reset();
            Object.keys(defaultObject || {}).forEach((key) => {
                this.$set(formObject || this.formObject, key, defaultObject[key]);
            });
            if (typeof callback === 'function') callback(true);
        },
        closeModal(modalName = 'formModal', resetForm = true, resetFormType = true) {
            this.$validator.reset();
            $('#' + modalName).modal('hide');
            $('.error_message').remove();
            $('.is-invalid').removeClass('is-invalid');
            if (resetForm) this.$store.commit('formObject', {});
            if (resetFormType) this.$store.commit('formType', 1);
        },
        getUrl() {
            return (this.$route && this.$route.meta && this.$route.meta.dataUrl) || '';
        },
        urlGenerate(customUrl = false) {
            let target = customUrl || this.getUrl();
            if (target && !/^(api\/|https?:\/\/)/.test(target)) target = 'api/' + target;
            if (/^https?:\/\//.test(target)) return target;
            return `${window.baseUrl || ''}/${target}`;
        },
        assignValidationError(errors) {
            Object.keys(errors || {}).forEach((field) => {
                const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                this.$validator.errors.add({ id: field, field, name: field, msg });
            });
        },
        resetForm(formData) {
            if (formData && typeof formData === 'object') {
                Object.keys(formData).forEach((key) => { formData[key] = ''; });
            }
            return formData;
        },
        pageTitle() {
            return (this.$route && this.$route.meta) ? this.$route.meta.pageTitle : '';
        },
        indexToLabel(string) {
            if (typeof string !== 'string') return '';
            const s = string.replace(/_/g, ' ');
            return s.charAt(0).toUpperCase() + s.slice(1);
        },
        arrLength(array) {
            return array && array.length !== undefined ? array.length : 0;
        },
        showStatus(status, activeText = 'Active', inactiveText = 'Inactive') {
            return parseInt(status) === 1
                ? `<span class="pm-badge is-success pointer">${activeText}</span>`
                : `<span class="pm-badge is-danger pointer">${inactiveText}</span>`;
        },
        replaceString(string, name) {
            return string ? string.replace(name, ' ') : '';
        },
        confirmBox(callback = false, title = null, message = null) {
            this.$swal({
                title: title || 'Are you sure?',
                text: message || 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, continue',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true,
            }).then((result) => {
                if (typeof callback === 'function') callback(result.value);
            });
        },
        capitalize(string) {
            if (!string) return '';
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
        nameInitials(name) {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '?';
            if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        },
        avatarTone(seed) {
            const s = String(seed || '');
            let h = 0;
            for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) % 997;
            return 'tone-' + (h % 6);
        },
        statusTone(status) {
            return {
                active: 'is-success', live: 'is-success', resolved: 'is-success', approved: 'is-success', read: 'is-info',
                pending: 'is-warning', new: 'is-warning',
                rejected: 'is-danger', banned: 'is-danger', taken_down: 'is-danger', deleted: 'is-danger',
                sold: 'is-info', featured: 'is-violet',
                reviewed: 'is-neutral', expired: 'is-neutral',
            }[status] || 'is-neutral';
        },
        money(value, isFree = false) {
            if (isFree) return 'Free';
            if (value === null || value === undefined || value === '') return 'Price on request';
            return '৳ ' + Number(value).toLocaleString('en-IN');
        },
        dateTime(iso, withTime = true) {
            if (!iso) return '—';
            const opts = { day: '2-digit', month: 'short', year: 'numeric' };
            if (withTime) Object.assign(opts, { hour: '2-digit', minute: '2-digit' });
            return new Date(iso).toLocaleString('en-GB', opts);
        },
        timeAgo(iso) {
            if (!iso) return '';
            const s = Math.round((Date.now() - new Date(iso).getTime()) / 1000);
            if (s < 60) return 'just now';
            const units = [['year', 31536000], ['month', 2592000], ['week', 604800], ['day', 86400], ['hour', 3600], ['minute', 60]];
            for (const [name, secs] of units) {
                const n = Math.floor(s / secs);
                if (n >= 1) return `${n} ${name}${n > 1 ? 's' : ''} ago`;
            }
            return 'just now';
        },
        siteUrl(path = '') {
            return (window.frontendUrl || '').replace(/\/+$/, '') + path;
        },
    },
};
