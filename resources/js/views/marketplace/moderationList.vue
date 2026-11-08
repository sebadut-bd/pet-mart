<template :key="$route.meta.name">
    <div class="main_component">
        <div class="row mb-3">
            <div class="col-md-6">
                <h4>Pet & Goods Listing Moderation Queue</h4>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group" role="group">
                    <button class="btn btn-sm" :class="activeStatus === 'pending' ? 'btn-primary' : 'btn-outline-primary'" @click="filterStatus('pending')">Pending Approvals</button>
                    <button class="btn btn-sm" :class="activeStatus === 'active' ? 'btn-success' : 'btn-outline-success'" @click="filterStatus('active')">Approved Listings</button>
                    <button class="btn btn-sm" :class="activeStatus === 'rejected' ? 'btn-danger' : 'btn-outline-danger'" @click="filterStatus('rejected')">Rejected Listings</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <data-table :table-heading="tableHeading" table-title="Listings Queue">
                    <template v-slot:data>
                        <tr v-for="(data, index) in dataList.data" :key="data.id">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <strong>{{ data.title }}</strong><br>
                                <small class="text-muted">{{ data.category ? data.category.name : '' }}</small>
                            </td>
                            <td>
                                <strong>{{ data.price ? '৳ ' + data.price : 'Negotiable' }}</strong><br>
                                <span class="badge bg-secondary">{{ data.listing_type }}</span>
                            </td>
                            <td>
                                <div>{{ data.user ? data.user.name : (data.seller ? data.seller.name : 'Unknown') }}</div>
                                <small class="text-muted">{{ data.user ? data.user.phone : '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark" v-if="data.status === 'pending'">Pending Approval</span>
                                <span class="badge bg-success" v-else-if="data.status === 'active'">Live / Approved</span>
                                <span class="badge bg-danger" v-else-if="data.status === 'rejected'">Rejected</span>
                                <span class="badge bg-secondary" v-else>{{ data.status }}</span>
                            </td>
                            <td class="action-buttons">
                                <div class="hstack gap-2">
                                    <button class="btn btn-sm btn-success" v-if="data.status !== 'active'" @click="approveListing(data.id)">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger" v-if="data.status !== 'rejected'" @click="openRejectModal(data)">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!dataList || !dataList.data || dataList.data.length === 0">
                            <td colspan="6" class="text-center py-4 text-muted">No listings found in {{ activeStatus }} queue.</td>
                        </tr>
                    </template>
                </data-table>
            </div>
        </div>

        <!-- Reject Reason Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Reject Listing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Specify the reason for rejecting <strong>{{ selectedListing ? selectedListing.title : '' }}</strong>:</p>
                        <div class="mb-3">
                            <textarea class="form-control" rows="3" v-model="rejectReason" placeholder="e.g. Vaccination card proof required, illegal breed sale, or invalid photos."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="submitReject">Confirm Rejection</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DataTable from "../../components/dataTable";

    export default {
        name: "moderationList",
        components: { DataTable },
        data() {
            return {
                tableHeading: ['Sl', 'Listing Title & Category', 'Price & Type', 'Seller Details', 'Status', 'Moderation Action'],
                activeStatus: 'pending',
                selectedListing: null,
                rejectReason: '',
            }
        },
        methods: {
            filterStatus(status) {
                this.activeStatus = status;
                this.$set(this.formFilter, 'status', status);
                this.getDataList(1);
            },
            approveListing(id) {
                const _this = this;
                _this.httpReq('/api/v1/admin/moderation/' + id + '/approve', 'post', {}, {}, function (res) {
                    _this.filterStatus(_this.activeStatus);
                });
            },
            openRejectModal(listing) {
                this.selectedListing = listing;
                this.rejectReason = '';
                $('#rejectModal').modal('show');
            },
            submitReject() {
                const _this = this;
                if (!_this.rejectReason.trim()) {
                    _this.$toastr('error', 'Please provide a rejection reason', 'Error');
                    return;
                }
                _this.httpReq('/api/v1/admin/moderation/' + _this.selectedListing.id + '/reject', 'post', {}, { reason: _this.rejectReason }, function (res) {
                    $('#rejectModal').modal('hide');
                    _this.filterStatus(_this.activeStatus);
                });
            }
        },
        mounted() {
            this.filterStatus('pending');
        }
    }
</script>

<style scoped>
    .action-buttons {
        display: flex;
        gap: 8px;
    }
</style>
