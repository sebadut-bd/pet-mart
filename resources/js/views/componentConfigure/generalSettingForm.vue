<template>
    <div class="content py-1 general-settings">
        <div v-if="loading" class="text-center py-5 text-muted">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2 mb-0">Loading settings…</p>
        </div>

        <div v-else-if="loadError" class="alert alert-danger d-flex justify-content-between align-items-center">
            <span>{{ loadError }}</span>
            <button type="button" class="btn btn-sm btn-outline-danger" @click="load">Try again</button>
        </div>

        <div v-else-if="!groupNames.length" class="alert alert-warning">
            No settings found. Run <code>php artisan db:seed --class=GeneralSettingSeeder</code> to create them.
        </div>

        <form v-else @submit.prevent="save" novalidate>
            <div v-for="group in groupNames" :key="group" class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white border-bottom d-flex align-items-center">
                    <i :class="['fa', groupIcon(group), 'text-success me-2']"></i>
                    <h5 class="mb-0 text-capitalize">{{ groupLabel(group) }}</h5>
                </div>
                <div class="card-body">
                    <div v-for="item in groups[group]" :key="item.key" class="row g-3 align-items-start py-3 setting-row">
                        <div class="col-md-3">
                            <label :for="'set-' + item.key" class="form-label fw-semibold mb-1">{{ item.display_name }}</label>
                            <div class="small text-muted">
                                <span v-if="item.is_public" class="badge bg-success-subtle text-success">Shown on website</span>
                                <span v-else class="badge bg-secondary-subtle text-secondary">Admin only</span>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <!-- Image setting -->
                            <template v-if="item.type === 'image'">
                                <div class="banner-preview mb-2" :class="{ empty: !item.image_url }">
                                    <img v-if="item.image_url" :src="item.image_url" :alt="item.display_name" />
                                    <div v-else class="text-muted text-center">
                                        <i class="fa fa-image fa-2x mb-2 d-block"></i>
                                        No image yet. The website shows its default design.
                                    </div>
                                    <div v-if="uploading[item.key]" class="banner-progress">
                                        <i class="fa fa-spinner fa-spin me-1"></i> Uploading {{ progress[item.key] || 0 }}%
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <label class="btn btn-success btn-sm mb-0" :class="{ disabled: uploading[item.key] }">
                                        <i class="fa fa-upload me-1"></i> {{ item.image_url ? 'Change image' : 'Upload image' }}
                                        <input :id="'set-' + item.key" type="file" class="d-none"
                                               accept="image/jpeg,image/png,image/webp"
                                               :disabled="uploading[item.key]"
                                               @change="uploadImage(item, $event)" />
                                    </label>
                                    <button v-if="item.image_url" type="button" class="btn btn-outline-danger btn-sm"
                                            :disabled="uploading[item.key]" @click="removeImage(item)">
                                        <i class="fa fa-trash me-1"></i> Remove
                                    </button>
                                    <span class="small text-muted">
                                        JPG, PNG or WebP, max 5 MB, at least 800×300 px.
                                        <template v-if="item.key === 'home_hero_banner'">Recommended 1920×700 px, subject in the centre.</template>
                                    </span>
                                </div>
                                <div v-if="errors[item.key]" class="text-danger small mt-1">{{ errors[item.key] }}</div>
                            </template>

                            <!-- Text-like settings -->
                            <template v-else>
                                <textarea v-if="item.type === 'textarea'" :id="'set-' + item.key" rows="3"
                                          class="form-control" :class="{ 'is-invalid': errors[item.key] }"
                                          v-model="item.value"></textarea>
                                <select v-else-if="item.type === 'boolean'" :id="'set-' + item.key"
                                        class="form-select" v-model="item.value">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <input v-else :id="'set-' + item.key"
                                       :type="item.type === 'email' ? 'email' : item.type === 'url' ? 'url' : 'text'"
                                       class="form-control" :class="{ 'is-invalid': errors[item.key] }"
                                       v-model="item.value" :placeholder="placeholder(item)" />
                                <div v-if="errors[item.key]" class="invalid-feedback d-block">{{ errors[item.key] }}</div>
                                <div v-else class="form-text">Leave empty to use the website default.</div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky-save bg-white border-top py-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" :disabled="saving" @click="load">Reset</button>
                <button type="submit" class="btn btn-success px-4" :disabled="saving">
                    <i :class="['fa', saving ? 'fa-spinner fa-spin' : 'fa-save', 'me-1']"></i> Save settings
                </button>
            </div>
        </form>
    </div>
</template>

<script>
const GROUP_META = {
    homepage: { label: 'Homepage', icon: 'fa-home' },
    general: { label: 'General', icon: 'fa-cog' },
    contact: { label: 'Contact', icon: 'fa-phone' },
    footer: { label: 'Footer', icon: 'fa-columns' },
    social: { label: 'Social media', icon: 'fa-share-alt' },
    seo: { label: 'SEO', icon: 'fa-search' },
};
const GROUP_ORDER = ['homepage', 'general', 'contact', 'footer', 'social', 'seo'];
const MAX_BYTES = 5 * 1024 * 1024;

export default {
    name: 'generalSettingForm',
    data() {
        return {
            loading: true,
            loadError: '',
            saving: false,
            groups: {},
            errors: {},
            uploading: {},
            progress: {},
        };
    },
    computed: {
        groupNames() {
            const names = Object.keys(this.groups);
            return names.sort((a, b) => {
                const ia = GROUP_ORDER.indexOf(a), ib = GROUP_ORDER.indexOf(b);
                return (ia < 0 ? 99 : ia) - (ib < 0 ? 99 : ib);
            });
        },
    },
    methods: {
        url(path) {
            return this.urlGenerate(path);
        },
        groupLabel(g) {
            return (GROUP_META[g] && GROUP_META[g].label) || g;
        },
        groupIcon(g) {
            return (GROUP_META[g] && GROUP_META[g].icon) || 'fa-sliders';
        },
        placeholder(item) {
            if (item.type === 'email') return 'support@example.com';
            if (item.type === 'url') return 'https://';
            return '';
        },
        notify(type, message) {
            this.$toastr(type, message, type === 'error' ? 'Error' : type === 'warning' ? 'Warning' : 'Success');
        },
        errorMessage(err, fallback) {
            const data = err && err.response && err.response.data;
            if (data && data.errors) {
                const first = Object.values(data.errors)[0];
                return Array.isArray(first) ? first[0] : String(first);
            }
            if (err && err.response && err.response.status === 419) return 'Your session expired. Refresh the page and try again.';
            return (data && data.message) || fallback;
        },
        load() {
            this.loading = true;
            this.loadError = '';
            this.errors = {};
            this.axios.get(this.url('api/general_setting'), { headers: { Accept: 'application/json' } })
                .then((res) => {
                    if (parseInt(res.data.status) !== 2000) throw new Error(res.data.message || 'Could not load settings.');
                    this.groups = res.data.result || {};
                })
                .catch((err) => {
                    this.loadError = this.errorMessage(err, 'Could not load settings.');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        save() {
            const settings = {};
            Object.values(this.groups).forEach((items) => {
                items.forEach((item) => {
                    if (item.type !== 'image') settings[item.key] = item.value === null ? '' : String(item.value);
                });
            });
            this.saving = true;
            this.errors = {};
            this.axios.post(this.url('api/general_setting'), { settings }, { headers: { Accept: 'application/json' } })
                .then((res) => {
                    const status = parseInt(res.data.status);
                    if (status === 2000) {
                        this.notify('success', res.data.message || 'Settings saved.');
                    } else if (status === 3000) {
                        const errs = {};
                        Object.entries(res.data.result || {}).forEach(([k, v]) => { errs[k] = Array.isArray(v) ? v[0] : v; });
                        this.errors = errs;
                        const list = Object.values(errs);
                        this.notify('warning', list.length === 1 ? list[0] : `${list.length} settings need attention: ${list.join(' ')}`);
                    } else {
                        this.notify('error', res.data.message || 'Settings could not be saved.');
                    }
                })
                .catch((err) => this.notify('error', this.errorMessage(err, 'Settings could not be saved.')))
                .finally(() => {
                    this.saving = false;
                });
        },
        uploadImage(item, event) {
            const file = event.target.files && event.target.files[0];
            event.target.value = '';
            if (!file) return;
            this.$delete(this.errors, item.key);
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.$set(this.errors, item.key, 'Please choose a JPG, PNG or WebP image.');
                this.notify('error', this.errors[item.key]);
                return;
            }
            if (file.size > MAX_BYTES) {
                this.$set(this.errors, item.key, 'The image is larger than 5 MB.');
                this.notify('error', this.errors[item.key]);
                return;
            }
            const form = new FormData();
            form.append('image', file);
            this.$set(this.uploading, item.key, true);
            this.$set(this.progress, item.key, 0);
            this.axios.post(this.url(`api/general_setting/${item.key}/image`), form, {
                headers: { Accept: 'application/json', 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (e) => {
                    if (e.total) this.$set(this.progress, item.key, Math.round((e.loaded / e.total) * 100));
                },
            })
                .then((res) => {
                    item.image_url = res.data.result && res.data.result.image_url;
                    this.notify('success', res.data.message || 'Image updated.');
                })
                .catch((err) => {
                    const msg = this.errorMessage(err, 'The image could not be uploaded.');
                    this.$set(this.errors, item.key, msg);
                    this.notify('error', msg);
                })
                .finally(() => {
                    this.$set(this.uploading, item.key, false);
                });
        },
        removeImage(item) {
            this.$swal({
                title: `Remove ${item.display_name}?`,
                text: 'The website will go back to its default design.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Remove',
            }).then((result) => {
                if (!result.value) return;
                this.$set(this.uploading, item.key, true);
                this.axios.delete(this.url(`api/general_setting/${item.key}/image`), { headers: { Accept: 'application/json' } })
                    .then((res) => {
                        item.image_url = null;
                        this.notify('success', res.data.message || 'Image removed.');
                    })
                    .catch((err) => this.notify('error', this.errorMessage(err, 'The image could not be removed.')))
                    .finally(() => {
                        this.$set(this.uploading, item.key, false);
                    });
            });
        },
    },
    mounted() {
        this.load();
    },
};
</script>

<style scoped>
.setting-row + .setting-row {
    border-top: 1px solid #f1f3f5;
}
.banner-preview {
    position: relative;
    width: 100%;
    max-width: 720px;
    aspect-ratio: 1920 / 700;
    border-radius: 12px;
    overflow: hidden;
    background: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
}
.banner-preview.empty {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
}
.banner-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.banner-progress {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.6);
    color: #fff;
    font-weight: 600;
}
.sticky-save {
    position: sticky;
    bottom: 0;
    z-index: 5;
}
.bg-success-subtle {
    background-color: #d1fae5;
}
.bg-secondary-subtle {
    background-color: #e9ecef;
}
</style>
