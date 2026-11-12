<template>
    <div class="pm-card" :class="tableClass">
        <div v-if="$slots.page_top" class="pm-card-head">
            <slot name="page_top"></slot>
        </div>

        <form v-if="showToolbar" class="pm-toolbar" @submit.prevent="search()" role="search">
            <div v-if="defaultFilter" class="pm-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" v-model="formFilter.keyword" class="form-control" :placeholder="searchPlaceholder" :aria-label="searchPlaceholder" />
            </div>
            <slot name="filter"></slot>
            <button v-if="defaultSearchButton && showSearchButton" class="btn btn-light" type="submit">
                <i class="fa-solid fa-filter"></i> <span class="d-none d-sm-inline">Apply</span>
            </button>
            <slot name="sorting"></slot>
        </form>

        <div class="live-preview" id="printDiv">
            <slot name="reportHeader"></slot>
            <div class="pm-table-wrap" :class="tableResponsive ? 'table-responsive' : ''">
                <div v-if="httpRequest" class="pm-table-loading" aria-live="polite">
                    <span class="spinner-border text-success" role="status" style="width: 1.8rem; height: 1.8rem"><span class="visually-hidden">Loading…</span></span>
                </div>
                <table class="table pm-table table-hover align-middle mb-0">
                    <thead v-if="tableHeading.length > 0">
                        <tr>
                            <th
                                v-for="(heading, index) in tableHeading"
                                :key="index"
                                scope="col"
                                :class="headingClass(heading, index)"
                            >{{ headingLabel(heading) }}</th>
                        </tr>
                    </thead>
                    <slot name="thead"></slot>
                    <tbody>
                        <slot name="data"></slot>
                    </tbody>
                </table>
                <div v-if="isEmpty && !httpRequest" class="pm-empty">
                    <div class="pm-empty-icon"><i :class="emptyIcon"></i></div>
                    <h4>{{ emptyTitle }}</h4>
                    <p>{{ emptyText }}</p>
                </div>
                <slot name="bottom_data"></slot>
            </div>
        </div>

        <slot name="pagination"></slot>
        <div v-if="defaultPagination && hasPages" class="pm-table-foot">
            <span>{{ summary }}</span>
            <pagination previousText="‹" nextText="›" :data="dataList" @paginateTo="getDataList"></pagination>
        </div>
        <slot name="modal"></slot>
    </div>
</template>

<script>
import Pagination from "../plugins/pagination/pagination";

export default {
    name: "dataTable",
    components: { Pagination },
    props: {
        tableHeading: { type: Array, default: () => [] },
        tableTitle: { type: String, default: "Data List" },
        tableResponsive: { type: Boolean, default: true },
        defaultAddButton: { type: Boolean, default: true },
        defaultFilter: { type: Boolean, default: true },
        defaultFilterWidth: { type: String, default: "" },
        tableClass: { type: String, default: "data_table" },
        defaultSearchMethod: { type: Boolean, default: true },
        defaultPagination: { type: Boolean, default: true },
        defaultSearchButton: { type: Boolean, default: true },
        showSearchButton: { type: Boolean, default: true },
        showToolbar: { type: Boolean, default: true },
        defaultObject: { type: [Array, Object], default: () => [] },
        searchPlaceholder: { type: String, default: "Search…" },
        rows: { type: Array, default: null },
        emptyIcon: { type: String, default: "fa-regular fa-folder-open" },
        emptyTitle: { type: String, default: "Nothing here yet" },
        emptyText: { type: String, default: "Try another filter, or add a new record." },
    },
    computed: {
        list() {
            if (this.rows) return this.rows;
            return (this.dataList && Array.isArray(this.dataList.data)) ? this.dataList.data : null;
        },
        isEmpty() {
            return Array.isArray(this.list) && this.list.length === 0;
        },
        hasPages() {
            return this.dataList && this.dataList.last_page > 1;
        },
        summary() {
            const d = this.dataList || {};
            if (!d.total) return '';
            const from = d.from || ((d.current_page - 1) * d.per_page + 1);
            const to = Math.min(from + (d.data ? d.data.length : 0) - 1, d.total);
            return `Showing ${from}–${to} of ${d.total}`;
        },
    },
    methods: {
        search() {
            if (this.defaultSearchMethod) {
                this.getDataList();
            } else {
                this.$emit("search");
            }
        },
        headingLabel(h) {
            return typeof h === 'object' && h !== null ? h.label : h;
        },
        headingClass(h, index) {
            if (typeof h === 'object' && h !== null && h.class) return h.class;
            const label = String(this.headingLabel(h)).toLowerCase();
            return label.startsWith('action') && index === this.tableHeading.length - 1 ? 'text-end' : '';
        },
        assignDefaultFilter() {
            Object.keys(this.defaultObject || {}).forEach((key) => {
                this.$set(this.formFilter, key, this.defaultObject[key]);
            });
        },
    },
    mounted() {
        this.assignDefaultFilter();
    },
};
</script>
