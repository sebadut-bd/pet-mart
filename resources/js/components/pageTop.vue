<template>
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap w-100">
        <div class="min-w-0">
            <h3 class="mb-0">{{ topPageTitle }}</h3>
            <p v-if="subtitle" class="mb-0">{{ subtitle }}</p>
        </div>
        <div class="pm-page-actions">
            <select
                v-if="showPerPage && showData($route.meta, 'isFormPage', true) !== true"
                v-model="formFilter.perPage"
                @change="getDataList()"
                class="form-select form-select-sm"
                style="width: auto"
                aria-label="Rows per page"
            >
                <option v-for="page in perPages" :key="page" :value="page">{{ page }} / page</option>
            </select>
            <slot></slot>
            <button
                v-if="defaultAddButton"
                type="button"
                class="btn btn-primary"
                @click="openModal('formModal', pageModalTitle, false, false, defaultObject), preMethod()"
            >
                <i class="fa-solid fa-plus"></i> {{ addLabel }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: "pageTop",
    props: {
        topPageTitle: { type: String, default: 'Data List' },
        subtitle: { type: String, default: '' },
        defaultAddButton: { type: Boolean, default: true },
        addLabel: { type: String, default: 'Add new' },
        pageModalTitle: { type: String, default: 'Add / edit' },
        defaultObject: { type: [Array, Object], default: () => [] },
        showPerPage: { type: Boolean, default: true },
    },
    methods: {
        preMethod() {
            this.$emit('preMethod');
        },
    },
}
</script>
