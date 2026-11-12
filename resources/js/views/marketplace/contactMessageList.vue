<template>
    <div>
        <div class="pm-page-head">
            <div>
                <h2>Contact messages</h2>
                <p>Messages sent from the Contact page on the customer site.</p>
            </div>
        </div>

        <data-table :table-heading="tableHeading" search-placeholder="Search name, phone or message…" empty-icon="fa-regular fa-envelope-open" :empty-title="activeStatus === 'new' ? 'Inbox zero' : 'No messages'" empty-text="New messages from the Contact page appear here.">
            <template v-slot:page_top>
                <div class="pm-tabs" role="tablist" aria-label="Message status">
                    <button
                        v-for="tab in tabs"
                        :key="tab.value"
                        type="button"
                        role="tab"
                        :aria-selected="activeStatus === tab.value ? 'true' : 'false'"
                        :class="{ 'is-active': activeStatus === tab.value }"
                        @click="filterStatus(tab.value)"
                    >
                        {{ tab.label }} <span v-if="tab.value" class="pm-count">{{ counts[tab.value] || 0 }}</span>
                    </button>
                </div>
            </template>

            <template v-slot:data>
                <tr v-for="m in (dataList.data || [])" :key="m.id" class="pointer" @click="view(m)">
                    <td>
                        <div class="d-flex align-items-center gap-12">
                            <span class="pm-avatar is-sm" :class="avatarTone(m.phone)">{{ nameInitials(m.name) }}</span>
                            <div class="min-w-0">
                                <span class="pm-cell-title" :class="{ 'fw-bold': m.status === 'new' }">{{ m.name }}</span>
                                <span class="pm-cell-sub">{{ m.phone }}<template v-if="m.email"> · {{ m.email }}</template></span>
                            </div>
                        </div>
                    </td>
                    <td style="min-width: 260px; white-space: normal" :class="{ 'text-dark fw-600': m.status === 'new' }">{{ m.message | truncate(120) }}</td>
                    <td class="text-nowrap">{{ timeAgo(m.created_at) }}</td>
                    <td><span class="pm-badge" :class="statusTone(m.status)">{{ statusLabel(m.status) }}</span></td>
                    <td @click.stop>
                        <div class="pm-actions">
                            <button type="button" class="btn btn-sm btn-light btn-icon" title="Open" @click="view(m)"><i class="fa-regular fa-eye"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Delete" @click="remove(m)"><i class="fa-regular fa-trash-can"></i></button>
                        </div>
                    </td>
                </tr>
            </template>
        </data-table>

        <general-modal modal-id="messageModal" modal-size="modal-lg">
            <template v-slot:title>Message from {{ selected ? selected.name : '' }}</template>
            <template v-slot:body>
                <div v-if="selected">
                    <dl class="pm-dl mb-3">
                        <dt>Phone</dt>
                        <dd><a :href="`tel:${selected.phone}`">{{ selected.phone }}</a></dd>
                        <template v-if="selected.email">
                            <dt>Email</dt>
                            <dd><a :href="`mailto:${selected.email}`">{{ selected.email }}</a></dd>
                        </template>
                        <dt>Received</dt>
                        <dd>{{ dateTime(selected.created_at) }}</dd>
                        <dt>Account</dt>
                        <dd>{{ selected.user_id ? 'Signed-in member #' + selected.user_id : 'Visitor (not signed in)' }}</dd>
                    </dl>
                    <p class="pm-section-title">Message</p>
                    <div class="pm-quote mb-3">{{ selected.message }}</div>

                    <label class="form-label" for="msg-note">Internal note</label>
                    <textarea id="msg-note" v-model="note" class="form-control" rows="3" maxlength="1000" placeholder="Only admins see this, e.g. “Called back on 12 Nov, issue fixed.”"></textarea>
                </div>
            </template>
            <template v-slot:buttons>
                <template v-if="selected">
                    <button type="button" class="btn btn-light me-auto" :disabled="saving" @click="save('new')" v-if="selected.status !== 'new'">Mark as new</button>
                    <button type="button" class="btn btn-outline-success" :disabled="saving" @click="save('read')">Save note</button>
                    <button type="button" class="btn btn-primary" :disabled="saving" @click="save('resolved')"><i class="fa-solid fa-check"></i> Mark resolved</button>
                </template>
            </template>
        </general-modal>
    </div>
</template>

<script>
import DataTable from "../../components/dataTable";
import GeneralModal from "../../components/generalModal";

export default {
    name: "contactMessageList",
    components: { DataTable, GeneralModal },
    data() {
        return {
            tableHeading: ['From', 'Message', 'Received', 'Status', 'Actions'],
            tabs: [
                { value: 'new', label: 'New' },
                { value: 'read', label: 'In progress' },
                { value: 'resolved', label: 'Resolved' },
                { value: '', label: 'All' },
            ],
            activeStatus: 'new',
            selected: null,
            note: '',
            saving: false,
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
            return { new: 'New', read: 'In progress', resolved: 'Resolved' }[s] || s;
        },
        view(m) {
            this.selected = m;
            this.note = m.admin_note || '';
            this.openModal('messageModal', 'Message', false, false);
            if (m.status === 'new') {
                this.adminRequest('put', `api/v1/admin/contact-messages/${m.id}`, { status: 'read', admin_note: m.admin_note }, { silent: true })
                    .then(() => {
                        m.status = 'read';
                        this.getConfigurations();
                    })
                    .catch(() => {});
            }
        },
        save(status) {
            this.saving = true;
            this.adminRequest('put', `api/v1/admin/contact-messages/${this.selected.id}`, { status, admin_note: this.note })
                .then(() => {
                    this.closeModal('messageModal', false);
                    this.getDataList(this.currentPagination || 1);
                    this.getConfigurations();
                })
                .catch(() => {})
                .finally(() => { this.saving = false; });
        },
        remove(m) {
            this.confirmBox((ok) => {
                if (!ok) return;
                this.adminRequest('delete', `api/v1/admin/contact-messages/${m.id}`)
                    .then(() => {
                        this.getDataList(this.currentPagination || 1);
                        this.getConfigurations();
                    })
                    .catch(() => {});
            }, 'Delete this message?', 'This cannot be undone.');
        },
    },
    mounted() {
        this.filterStatus('new');
    },
}
</script>
