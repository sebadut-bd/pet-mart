<template>
    <div>
        <div class="pm-page-head">
            <div>
                <h2>Listing moderation</h2>
                <p>Every listing is checked here before it appears on the site.</p>
            </div>
        </div>

        <data-table
            :table-heading="tableHeading"
            search-placeholder="Search title, seller name or phone…"
            :empty-icon="activeStatus === 'pending' ? 'fa-solid fa-check' : 'fa-regular fa-folder-open'"
            :empty-title="activeStatus === 'pending' ? 'All caught up' : 'No listings here'"
            :empty-text="activeStatus === 'pending' ? 'There are no listings waiting for review.' : 'Try another tab or search term.'"
        >
            <template v-slot:page_top>
                <div class="pm-tabs" role="tablist" aria-label="Listing status">
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
                <tr v-for="item in (dataList.data || [])" :key="item.id">
                    <td>
                        <div class="d-flex align-items-center gap-12">
                            <img v-if="item.images.length" :src="item.images[0]" class="pm-thumb" alt="" loading="lazy">
                            <span v-else class="pm-thumb"><i class="fa-regular fa-image"></i></span>
                            <div class="min-w-0">
                                <a href="#" class="pm-cell-title text-decoration-none" @click.prevent="view(item)">
                                    <i v-if="item.is_featured" class="fa-solid fa-star text-warning me-1" title="Featured"></i>{{ item.title }}
                                </a>
                                <span class="pm-cell-sub">{{ categoryName(item) }}<template v-if="item.location"> · {{ item.location }}</template></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-600 text-dark">{{ item.seller ? item.seller.name : 'Unknown' }}</span>
                            <i v-if="item.seller && item.seller.is_verified" class="fa-solid fa-circle-check text-success" title="Verified seller"></i>
                        </div>
                        <span class="pm-cell-sub">{{ item.seller ? item.seller.phone : '' }}</span>
                    </td>
                    <td class="text-nowrap">
                        <span class="fw-600 text-dark">{{ money(item.price, item.is_free) }}</span>
                        <div><span class="pm-badge no-dot is-neutral mt-1">{{ typeLabel(item.listing_type) }}</span></div>
                    </td>
                    <td class="text-nowrap">
                        <span>{{ timeAgo(item.created_at) }}</span>
                        <div class="pm-cell-sub">{{ dateTime(item.created_at, false) }}</div>
                    </td>
                    <td><span class="pm-badge" :class="statusTone(item.status)">{{ statusLabel(item.status) }}</span></td>
                    <td>
                        <div class="pm-actions">
                            <button type="button" class="btn btn-sm btn-light" @click="view(item)" title="View details">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <button v-if="canApprove(item)" type="button" class="btn btn-sm btn-primary" :disabled="busyId === item.id" @click="approve(item)">
                                <i class="fa-solid fa-check"></i> Approve
                            </button>
                            <button v-if="canReject(item)" type="button" class="btn btn-sm btn-outline-danger" @click="openReject(item)">
                                <i class="fa-solid fa-xmark"></i> Reject
                            </button>
                            <button
                                v-if="item.status === 'active'"
                                type="button"
                                class="btn btn-sm"
                                :class="item.is_featured ? 'btn-outline-warning' : 'btn-light'"
                                :disabled="busyId === item.id"
                                @click="toggleFeatured(item)"
                                :title="item.is_featured ? 'Remove from featured' : 'Feature on the homepage'"
                            >
                                <i :class="item.is_featured ? 'fa-solid fa-star' : 'fa-regular fa-star'"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </data-table>

        <general-modal modal-id="listingModal" modal-size="modal-xl" :is-enable-footer="true">
            <template v-slot:title>
                <span v-if="selected">{{ selected.title }}</span>
            </template>
            <template v-slot:body>
                <div v-if="selected" class="row g-4">
                    <div class="col-lg-7">
                        <div v-if="selected.images.length" class="pm-gallery mb-3">
                            <a v-for="(src, i) in selected.images" :key="i" :href="src" target="_blank" rel="noopener">
                                <img :src="src" :alt="`Photo ${i + 1}`" loading="lazy">
                            </a>
                        </div>
                        <p class="pm-section-title">Description</p>
                        <div class="pm-quote">{{ selected.description || 'No description.' }}</div>

                        <template v-if="selected.attributes.length">
                            <p class="pm-section-title">Details</p>
                            <dl class="pm-dl">
                                <template v-for="a in selected.attributes">
                                    <dt :key="'k' + a.key">{{ indexToLabel(a.key) }}</dt>
                                    <dd :key="'v' + a.key">{{ a.value }}</dd>
                                </template>
                            </dl>
                        </template>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="pm-badge" :class="statusTone(selected.status)">{{ statusLabel(selected.status) }}</span>
                            <span class="fs-5 fw-bold text-dark">{{ money(selected.price, selected.is_free) }}</span>
                        </div>
                        <div v-if="selected.rejection_reason" class="alert alert-danger py-2 px-3 small">
                            <strong>Rejected:</strong> {{ selected.rejection_reason }}
                        </div>

                        <p class="pm-section-title">Listing</p>
                        <dl class="pm-dl">
                            <dt>Type</dt><dd>{{ typeLabel(selected.listing_type) }}</dd>
                            <dt>Category</dt><dd>{{ categoryName(selected) }}</dd>
                            <dt v-if="selected.condition">Condition</dt><dd v-if="selected.condition">{{ capitalize(selected.condition) }}</dd>
                            <dt>Location</dt><dd>{{ selected.location || '—' }}<div v-if="selected.address" class="pm-cell-sub">{{ selected.address }}</div></dd>
                            <dt>Negotiable</dt><dd>{{ selected.is_negotiable ? 'Yes' : 'No' }}</dd>
                            <dt>Submitted</dt><dd>{{ dateTime(selected.created_at) }}</dd>
                            <dt>Views</dt><dd>{{ selected.views }}</dd>
                        </dl>

                        <p class="pm-section-title">Seller</p>
                        <div v-if="selected.seller" class="d-flex align-items-center gap-12 mb-2">
                            <span class="pm-avatar" :class="avatarTone(selected.seller.id)">{{ nameInitials(selected.seller.name) }}</span>
                            <div class="min-w-0">
                                <div class="fw-600 text-dark">
                                    {{ selected.seller.name }}
                                    <i v-if="selected.seller.is_verified" class="fa-solid fa-circle-check text-success" title="Verified seller"></i>
                                </div>
                                <div class="pm-cell-sub">{{ selected.seller.phone }}<template v-if="selected.seller.email"> · {{ selected.seller.email }}</template></div>
                                <div class="pm-cell-sub">Member since {{ dateTime(selected.seller.joined_at, false) }}</div>
                            </div>
                        </div>
                        <span v-if="selected.seller && !selected.seller.status" class="pm-badge is-danger">Account deactivated</span>

                        <template v-if="selected.history.length">
                            <p class="pm-section-title">History</p>
                            <ul class="pm-timeline">
                                <li v-for="(h, i) in selected.history" :key="i">
                                    <strong class="text-dark">{{ indexToLabel(h.action) }}</strong>
                                    <span class="text-muted"> by {{ h.admin || 'admin' }} · {{ timeAgo(h.created_at) }}</span>
                                    <div v-if="h.reason" class="pm-cell-sub">{{ h.reason }}</div>
                                </li>
                            </ul>
                        </template>
                    </div>
                </div>
            </template>
            <template v-slot:buttons>
                <template v-if="selected">
                    <a v-if="selected.status === 'active' || selected.status === 'sold'" :href="siteUrl(`/product/${selected.slug}`)" target="_blank" rel="noopener" class="btn btn-light me-auto">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View on site
                    </a>
                    <button v-if="canReject(selected)" type="button" class="btn btn-outline-danger" @click="openReject(selected)">
                        <i class="fa-solid fa-xmark"></i> Reject
                    </button>
                    <button v-if="canApprove(selected)" type="button" class="btn btn-primary" :disabled="busyId === selected.id" @click="approve(selected)">
                        <i class="fa-solid fa-check"></i> Approve
                    </button>
                </template>
            </template>
        </general-modal>

        <form-modal modal-id="rejectModal" modal-size="modal-md" title="Reject listing" submit-text="Reject listing" :reset-form-data="false" :reset-form-data-on-close-icon="false" @submit="submitReject">
            <p class="text-muted mb-3" v-if="rejectTarget">
                <strong class="text-dark">{{ rejectTarget.title }}</strong> will be hidden from the site. The seller sees your reason and can edit and resubmit.
            </p>
            <label class="form-label" for="reject-reason">Reason <span class="req">*</span></label>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <button v-for="r in quickReasons" :key="r" type="button" class="btn btn-sm btn-light" @click="rejectReason = r">{{ r }}</button>
            </div>
            <textarea id="reject-reason" v-model="rejectReason" class="form-control" rows="4" maxlength="500" placeholder="Explain what the seller needs to fix…"></textarea>
            <div class="form-text text-end">{{ rejectReason.length }}/500</div>
        </form-modal>
    </div>
</template>

<script>
import DataTable from "../../components/dataTable";
import GeneralModal from "../../components/generalModal";
import FormModal from "../../components/formModal";

const TYPES = { sell: 'For sale', adopt: 'Adoption', stud_service: 'Stud service', lost: 'Lost pet', found: 'Found pet' };

export default {
    name: "moderationList",
    components: { DataTable, GeneralModal, FormModal },
    data() {
        return {
            tableHeading: ['Listing', 'Seller', 'Price', 'Submitted', 'Status', 'Actions'],
            tabs: [
                { value: 'pending', label: 'Pending' },
                { value: 'active', label: 'Live' },
                { value: 'rejected', label: 'Rejected' },
                { value: 'sold', label: 'Sold' },
            ],
            activeStatus: 'pending',
            selected: null,
            rejectTarget: null,
            rejectReason: '',
            busyId: null,
            quickReasons: [
                'Photos are unclear or not of the actual animal/item.',
                'Price or description is misleading.',
                'Vaccination or health details are missing.',
                'This breed/item is not allowed on PetMart.',
                'Duplicate listing.',
            ],
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
        reload() {
            this.getDataList(this.currentPagination || 1);
            this.getConfigurations();
        },
        typeLabel(t) {
            return TYPES[t] || t;
        },
        statusLabel(s) {
            return { active: 'Live', pending: 'Pending', rejected: 'Rejected', sold: 'Sold', expired: 'Expired' }[s] || s;
        },
        categoryName(item) {
            if (!item.category) return '—';
            return item.category.parent ? `${item.category.parent} › ${item.category.name}` : item.category.name;
        },
        canApprove(item) {
            return ['pending', 'rejected'].includes(item.status);
        },
        canReject(item) {
            return ['pending', 'active'].includes(item.status);
        },
        view(item) {
            this.selected = item;
            this.openModal('listingModal', item.title, false, false);
        },
        approve(item) {
            this.busyId = item.id;
            this.adminRequest('post', `api/v1/admin/moderation/${item.id}/approve`)
                .then(() => {
                    this.closeModal('listingModal', false);
                    this.reload();
                })
                .catch(() => {})
                .finally(() => { this.busyId = null; });
        },
        openReject(item) {
            this.rejectTarget = item;
            this.rejectReason = '';
            const fromDetails = $('#listingModal').hasClass('show');
            this.closeModal('listingModal', false);
            setTimeout(() => this.openModal('rejectModal', 'Reject listing', false, false), fromDetails ? 300 : 0);
        },
        submitReject() {
            const reason = this.rejectReason.trim();
            if (reason.length < 5) {
                this.$toastr('error', 'Please give the seller a reason (at least 5 characters).', 'Reason needed');
                return;
            }
            this.$store.commit('httpRequest', true);
            this.adminRequest('post', `api/v1/admin/moderation/${this.rejectTarget.id}/reject`, { reason })
                .then(() => {
                    this.closeModal('rejectModal', false);
                    this.reload();
                })
                .catch(() => {})
                .finally(() => this.$store.commit('httpRequest', false));
        },
        toggleFeatured(item) {
            this.busyId = item.id;
            this.adminRequest('post', `api/v1/admin/moderation/${item.id}/feature`)
                .then((res) => { item.is_featured = res.is_featured; })
                .catch(() => {})
                .finally(() => { this.busyId = null; });
        },
    },
    mounted() {
        this.filterStatus('pending');
    },
}
</script>
