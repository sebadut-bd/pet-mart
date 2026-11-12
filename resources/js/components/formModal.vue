<template>
    <div class="modal fade" :id="modalId" :data-bs-backdrop="backDrop" tabindex="-1" aria-hidden="true" :aria-labelledby="`${modalId}-title`">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" :class="dialogSize">
            <form class="modal-content" @submit.prevent="submit()" novalidate>
                <div class="modal-header" :class="colorClass">
                    <h5 class="modal-title" :id="`${modalId}-title`">{{ title || modalTitle }}</h5>
                    <button type="button" class="btn-close" aria-label="Close" @click="closeModal(modalId, resetFormDataOnCloseIcon)"></button>
                </div>
                <div class="modal-body">
                    <slot></slot>
                </div>
                <div v-if="isEnableFooter" class="modal-footer">
                    <slot name="footer-start"></slot>
                    <button v-if="isEnablePrint" type="button" @click="closeModal(modalId, resetFormData)" class="btn btn-light">
                        Cancel
                    </button>
                    <button v-if="isEnablePrintSubmit" type="submit" class="btn btn-primary" :disabled="httpRequest">
                        <i :class="httpRequest ? 'fa-solid fa-circle-notch fa-spin' : 'fa-solid fa-check'"></i> {{ submitText }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: "formModal",
    props: {
        resetFormData: { type: Boolean, default: true },
        resetFormDataOnCloseIcon: { type: Boolean, default: true },
        modalId: { type: String, default: 'formModal' },
        colorClass: { type: String, default: '' },
        modalSize: { type: String, default: 'modal-lg' },
        isEnableFooter: { type: Boolean, default: true },
        isEnablePrint: { type: Boolean, default: true },
        isEnablePrintSubmit: { type: Boolean, default: true },
        backDrop: { type: String, default: 'static' },
        submitText: { type: String, default: 'Save' },
        title: { type: String, default: '' },
    },
    computed: {
        dialogSize() {
            return { 'modal-xs': '', 'modal-md': '' }[this.modalSize] ?? this.modalSize;
        },
    },
    methods: {
        submit() {
            this.$emit('submit');
        },
    },
}
</script>
