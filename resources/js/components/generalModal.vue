<template>
    <div class="modal fade" :id="modalId" :data-bs-backdrop="backDrop" tabindex="-1" aria-hidden="true" :aria-labelledby="`${modalId}-title`">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" :class="modalSize">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" :id="`${modalId}-title`">
                        <slot name="title"></slot>
                        <template v-if="!$slots.title">{{ title || modalTitle }}</template>
                    </h5>
                    <button type="button" class="btn-close" aria-label="Close" @click="closeModal(modalId, false)"></button>
                </div>
                <div class="modal-body">
                    <slot name="body"></slot>
                </div>
                <div v-if="isEnableFooter || $slots.buttons" class="modal-footer">
                    <slot name="buttons"></slot>
                    <button v-if="isEnableFooter" type="button" @click="closeModal(modalId, false)" class="btn btn-light">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "generalModal",
    props: {
        modalId: { type: String, default: 'generalModal' },
        modalSize: { type: String, default: 'modal-lg' },
        isEnableFooter: { type: Boolean, default: true },
        backDrop: { type: String, default: 'true' },
        title: { type: String, default: '' },
    },
}
</script>
