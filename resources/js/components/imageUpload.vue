<template>
    <div>
        <div
            class="pm-upload"
            :class="{ 'is-drag': dragging }"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <img v-if="previewUrl" :src="previewUrl" class="pm-upload-preview" alt="Preview">
            <span v-else class="pm-upload-preview"><i class="fa-regular fa-image fa-lg"></i></span>

            <div class="min-w-0 flex-grow-1">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-light btn-sm" :disabled="uploading" @click="$refs.file.click()">
                        <i :class="uploading ? 'fa-solid fa-circle-notch fa-spin' : 'fa-solid fa-arrow-up-from-bracket'"></i>
                        {{ uploading ? 'Uploading…' : (value ? 'Replace' : 'Upload image') }}
                    </button>
                    <button v-if="value && !uploading" type="button" class="btn btn-sm btn-outline-danger" @click="$emit('input', '')">
                        <i class="fa-regular fa-trash-can"></i> Remove
                    </button>
                </div>
                <p class="form-text mb-0 mt-1">{{ hint }}</p>
            </div>
            <input ref="file" type="file" accept="image/jpeg,image/png,image/webp" class="d-none" @change="onPick">
        </div>
        <span v-if="error" class="error_message">{{ error }}</span>
    </div>
</template>

<script>
export default {
    name: "imageUpload",
    props: {
        value: { type: String, default: '' },
        folder: { type: String, required: true },
        hint: { type: String, default: 'JPG, PNG or WebP, up to 5 MB, at least 200 × 200 px.' },
    },
    data() {
        return { uploading: false, dragging: false, error: '', localPreview: '' };
    },
    computed: {
        previewUrl() {
            if (this.localPreview) return this.localPreview;
            if (!this.value) return '';
            return /^https?:\/\//.test(this.value) ? this.value : `${window.UploadPath}/${this.value}`;
        },
    },
    watch: {
        value(v) {
            if (!v) this.localPreview = '';
        },
    },
    methods: {
        onPick(e) {
            const file = e.target.files && e.target.files[0];
            e.target.value = '';
            if (file) this.upload(file);
        },
        onDrop(e) {
            this.dragging = false;
            const file = e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) this.upload(file);
        },
        upload(file) {
            this.error = '';
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.error = 'Choose a JPG, PNG or WebP image.';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.error = 'The image must be 5 MB or smaller.';
                return;
            }
            const form = new FormData();
            form.append('image', file);
            form.append('folder', this.folder);
            this.uploading = true;
            this.axios.post(this.urlGenerate('api/v1/admin/uploads/images'), form)
                .then((res) => {
                    this.localPreview = res.data.result.url;
                    this.$emit('input', res.data.result.path);
                })
                .catch((err) => {
                    this.error = this.httpErrorMessage(err, 'The image could not be uploaded.');
                })
                .finally(() => {
                    this.uploading = false;
                });
        },
    },
}
</script>
