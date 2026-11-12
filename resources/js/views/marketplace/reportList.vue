<template>
    <div>
        <div class="pm-page-head">
            <div>
                <h2>Listing reports</h2>
                <p>Reports from members about listings that may break the rules.</p>
            </div>
        </div>

        <data-table :table-heading="tableHeading" search-placeholder="Search by listing title…" :empty-icon="activeStatus === 'pending' ? 'fa-solid fa-check' : 'fa-regular fa-flag'" :empty-title="activeStatus === 'pending' ? 'No open reports' : 'Nothing here'" empty-text="Reports from members will appear here.">
            <template v-slot:page_top>
                <div class="pm-tabs" role="tablist" aria-label="Report status">
                    <button
                        v-for="tab in tabs"
                        :key="tab.value"
                        type="button"
                        role="tab"
                        :aria-selected="activeStatus === tab.value ? 'true' : 'false'"
                        :class="{ 'is-active': activeStatus === tab.value }"
                        @click="filterStatus(tab.value)"
                    >
                        {{ tab.label }} <span class="pm-count">{{ counts[tab.value] || 0 }}</span>
                    </button>
                </div>
            </template>

            <template v-slot:data>
                <tr v-for="report in (dataList.data || [])" :key="report.id">
                    <td>
                        <div v-if="report.product" class="d-flex align-items-center gap-12">
                            <img v-if="report.product.image" :src="report.product.image" class="pm-thumb" alt="" loading="lazy">
                            <span v-else class="pm-thumb"><i class="fa-regular fa-image"></i></span>
                            <div class="min-w-0">
                                <span class="pm-cell-title">{{ report.product.title }}</span>
                                <span class="pm-cell-sub">
                                    by {{ report.product.seller || 'unknown' }} ·
                                    <span class="pm-badge" :class="statusTone(report.product.status)" style="font-size: 11px; padding: 0 8px">{{ capitalize(report.product.status) }}</span>
                                </span>
                            </div>
                        </div>
                        <span v-else class="text-muted">Listing removed</span>
                    </td>
                    <td style="min-width: 220px">
                        <span class="fw-600 text-dark">{{ report.reason }}</span>
                        <div v-if="report.description" class="pm-cell-sub" style="white-space: normal">{{ report.description | truncate(140) }}</div>
                        <span v-if="report.reports_on_listing > 1" class="pm-badge no-dot is-danger mt-1">{{ report.reports_on_listing }} reports on this listing</span>
                    </td>
                    <td>
                        <span class="fw-600 text-dark">{{ report.reporter ? report.reporter.name : 'Deleted user' }}</span>
                        <div class="pm-cell-sub">{{ report.reporter ? report.reporter.phone : '' }}</div>
                    </td>
                    <td class="text-nowrap">{{ timeAgo(report.created_at) }}</td>
                    <td><span class="pm-badge" :class="statusTone(report.status)">{{ statusLabel(report.status) }}</span></td>
                    <td>
                        <div class="pm-actions">
                            <a v-if="report.product && ['active', 'sold'].includes(report.product.status)" :href="siteUrl(`/product/${report.product.slug}`)" target="_blank" rel="noopener" class="btn btn-sm btn-light" title="View on site">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                            <template v-if="report.status === 'pending'">
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="openTakeDown(report)" :disabled="!report.product || report.product.status === 'deleted'">
                                    <i class="fa-solid fa-ban"></i> Take down
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" :disabled="busyId === report.id" @click="resolve(report)">
                                    <i class="fa-solid fa-check"></i> Resolved
                                </button>
                                <button type="button" class="btn btn-sm btn-light" :disabled="busyId === report.id" @click="dismiss(report)" title="No action needed">
                                    Dismiss
                                </button>
                            </template>
                        </div>
                    </td>
                </tr>
            </template>
        </data-table>

        <form-modal modal-id="takeDownModal" modal-size="modal-md" title="Take down listing" submit-text="Take down" :reset-form-data="false" :reset-form-data-on-close-icon="false" @submit="submitTakeDown">
            <p class="text-muted" v-if="target && target.product">
                <strong class="text-dark">{{ target.product.title }}</strong> will be hidden from the site, and all open reports on it will be marked resolved. The seller sees the reason below.
            </p>
            <label class="form-label" for="takedown-reason">Reason for the seller <span class="req">*</span></label>
            <textarea id="takedown-reason" v-model="reason" class="form-control" rows="4" maxlength="500" placeholder="e.g. Removed after reports: this breed cannot be sold on PetMart."></textarea>
        </form-modal>
    </div>
</template>

<script>
import DataTable from "../../components/dataTable";
import FormModal from "../../components/formModal";

export default {
    name: "reportList",
    components: { DataTable, FormModal },
    data() {
        return {
            tableHeading: ['Listing', 'Reason', 'Reported by', 'When', 'Status', 'Actions'],
            tabs: [
                { value: 'pending', label: 'Open' },
                { value: 'resolved', label: 'Resolved' },
                { value: 'reviewed', label: 'Dismissed' },
            ],
            activeStatus: 'pending',
            target: null,
            reason: '',
            busyId: null,
        };
    },
    computed: {
        counts() {
            return (this.dataList && this.dataList.counts) || {};
        },
    },
    methods: {
        filterStatus(status) {
            this.activeStatus = status;
            this.$set(this.formFilter, 'status', status);
            this.getDataList(1);
        },
        statusLabel(s) {
            return { pending: 'Open', resolved: 'Resolved', reviewed: 'Dismissed' }[s] || s;
        },
        reload() {
            this.getDataList(this.currentPagination || 1);
            this.getConfigurations();
        },
        resolve(report) {
            this.busyId = report.id;
            this.adminRequest('post', `api/v1/admin/reports/${report.id}/resolve`)
                .then(() => this.reload())
                .catch(() => {})
                .finally(() => { this.busyId = null; });
        },
        dismiss(report) {
            this.busyId = report.id;
            this.adminRequest('post', `api/v1/admin/reports/${report.id}/dismiss`)
                .then(() => this.reload())
                .catch(() => {})
                .finally(() => { this.busyId = null; });
        },
        openTakeDown(report) {
            this.target = report;
            this.reason = `Removed after a report: ${report.reason}.`;
            this.openModal('takeDownModal', 'Take down listing', false, false);
        },
        submitTakeDown() {
            const reason = this.reason.trim();
            if (reason.length < 5) {
                this.$toastr('error', 'Please give the seller a reason.', 'Reason needed');
                return;
            }
            this.$store.commit('httpRequest', true);
            this.adminRequest('post', `api/v1/admin/reports/${this.target.id}/resolve`, { take_down: true, reason })
                .then(() => {
                    this.closeModal('takeDownModal', false);
                    this.reload();
                })
                .catch(() => {})
                .finally(() => this.$store.commit('httpRequest', false));
        },
    },
    mounted() {
        this.filterStatus('pending');
    },
}
</script>
