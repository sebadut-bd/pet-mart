<template>
    <div>
        <div class="pm-page-head">
            <div>
                <h2>Members</h2>
                <p>People who buy and sell on PetMart. Dashboard accounts are under Access control › Users.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-6 col-xl-3">
                <stat-card label="All members" :value="counts.total || 0" icon="fa-solid fa-users" color="var(--pm-c-violet)"></stat-card>
            </div>
            <div class="col-6 col-xl-3">
                <stat-card label="Active" :value="counts.active || 0" icon="fa-solid fa-user-check" color="var(--pm-c-emerald)"></stat-card>
            </div>
            <div class="col-6 col-xl-3">
                <stat-card label="Inactive" :value="counts.inactive || 0" icon="fa-solid fa-user-slash" color="var(--pm-c-rose)"></stat-card>
            </div>
            <div class="col-6 col-xl-3">
                <stat-card label="Verified sellers" :value="counts.verified || 0" icon="fa-solid fa-circle-check" color="var(--pm-c-sky)"></stat-card>
            </div>
        </div>

        <data-table :table-heading="tableHeading" search-placeholder="Search name, phone or email…" empty-icon="fa-solid fa-user" empty-title="No members found">
            <template v-slot:filter>
                <select v-model="formFilter.status" class="form-select" aria-label="Status" @change="getDataList(1)">
                    <option value="">Any status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <select v-model="formFilter.is_verified" class="form-select" aria-label="Seller badge" @change="getDataList(1)">
                    <option value="">Any badge</option>
                    <option value="1">Verified</option>
                    <option value="0">Not verified</option>
                </select>
            </template>

            <template v-slot:data>
                <tr v-for="member in (dataList.data || [])" :key="member.id">
                    <td>
                        <div class="d-flex align-items-center gap-12">
                            <img v-if="member.image" :src="member.image" class="pm-avatar" alt="">
                            <span v-else class="pm-avatar" :class="avatarTone(member.id)">{{ nameInitials(member.name) }}</span>
                            <div class="min-w-0">
                                <span class="pm-cell-title">
                                    {{ member.name }}
                                    <i v-if="member.is_verified" class="fa-solid fa-circle-check text-success" title="Verified seller"></i>
                                </span>
                                <span class="pm-cell-sub">{{ member.email || 'No email' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="text-nowrap">{{ member.phone }}</td>
                    <td class="text-nowrap">
                        <span class="fw-600 text-dark">{{ member.active_products_count }}</span>
                        <span class="text-muted"> live / {{ member.products_count }}</span>
                    </td>
                    <td class="text-nowrap">{{ dateTime(member.created_at, false) }}</td>
                    <td>
                        <button type="button" class="btn btn-sm p-0 border-0" :disabled="busyId === member.id" @click="toggleVerification(member)" :title="member.is_verified ? 'Remove seller badge' : 'Give seller badge'">
                            <span class="pm-badge" :class="member.is_verified ? 'is-info' : 'is-neutral'">{{ member.is_verified ? 'Verified' : 'Not verified' }}</span>
                        </button>
                    </td>
                    <td>
                        <div class="form-check form-switch m-0" :title="member.status ? 'Active – click to deactivate' : 'Inactive – click to activate'">
                            <input class="form-check-input" type="checkbox" role="switch" :checked="member.status" :disabled="busyId === member.id" :aria-label="`${member.name} active`" @click.prevent="toggleStatus(member)">
                        </div>
                    </td>
                    <td>
                        <div class="pm-actions">
                            <button type="button" class="btn btn-sm btn-light btn-icon" title="Edit" @click="edit(member)"><i class="fa-regular fa-pen-to-square"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Delete" @click="remove(member)"><i class="fa-regular fa-trash-can"></i></button>
                        </div>
                    </td>
                </tr>
            </template>
        </data-table>

        <form-modal modal-size="modal-lg" title="Edit member" submit-text="Save changes" @submit="save">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="m-name">Name <span class="req">*</span></label>
                    <input id="m-name" v-model="form.name" class="form-control" maxlength="100" placeholder="Full name" :class="{ 'is-invalid': errors.name }">
                    <span v-if="errors.name" class="error_message">{{ errors.name }}</span>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="m-phone">Mobile number <span class="req">*</span></label>
                    <input id="m-phone" v-model="form.phone" class="form-control" inputmode="tel" maxlength="20" placeholder="01XXXXXXXXX" :class="{ 'is-invalid': errors.phone }">
                    <span v-if="errors.phone" class="error_message">{{ errors.phone }}</span>
                </div>
                <div class="col-12">
                    <label class="form-label" for="m-email">Email</label>
                    <input id="m-email" v-model="form.email" type="email" class="form-control" maxlength="150" placeholder="name@example.com (optional)" :class="{ 'is-invalid': errors.email }">
                    <span v-if="errors.email" class="error_message">{{ errors.email }}</span>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input id="m-status" class="form-check-input" type="checkbox" v-model="form.status">
                        <label class="form-check-label" for="m-status">Active <span class="form-text d-block">Inactive members are signed out and cannot log in.</span></label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input id="m-verified" class="form-check-input" type="checkbox" v-model="form.is_verified">
                        <label class="form-check-label" for="m-verified">Verified seller <span class="form-text d-block">Shows a badge on their listings.</span></label>
                    </div>
                </div>
            </div>
        </form-modal>
    </div>
</template>

<script>
import DataTable from "../../components/dataTable";
import FormModal from "../../components/formModal";
import StatCard from "../../components/statCard";

export default {
    name: "memberList",
    components: { DataTable, FormModal, StatCard },
    data() {
        return {
            tableHeading: ['Member', 'Phone', 'Listings', 'Joined', 'Seller badge', 'Active', 'Actions'],
            busyId: null,
            editingId: null,
            form: {},
            errors: {},
        };
    },
    computed: {
        counts() {
            return (this.dataList && this.dataList.counts) || {};
        },
    },
    methods: {
        reload() {
            this.getDataList(this.currentPagination || 1);
        },
        run(member, request) {
            this.busyId = member.id;
            request.then(() => this.reload()).catch(() => {}).finally(() => { this.busyId = null; });
        },
        toggleVerification(member) {
            this.run(member, this.adminRequest('post', `api/v1/admin/members/${member.id}/toggle-verification`));
        },
        toggleStatus(member) {
            if (!member.status) {
                this.run(member, this.adminRequest('post', `api/v1/admin/members/${member.id}/toggle-status`));
                return;
            }
            this.confirmBox((ok) => {
                if (ok) this.run(member, this.adminRequest('post', `api/v1/admin/members/${member.id}/toggle-status`));
            }, `Deactivate ${member.name}?`, 'They will be signed out everywhere and cannot log in until activated again. Their listings stay as they are.');
        },
        edit(member) {
            this.editingId = member.id;
            this.errors = {};
            this.form = {
                name: member.name,
                phone: member.phone.replace(/^\+88/, ''),
                email: member.email || '',
                status: member.status,
                is_verified: member.is_verified,
            };
            this.openModal('formModal', 'Edit member', false, false);
        },
        save() {
            this.errors = {};
            this.$store.commit('httpRequest', true);
            this.adminRequest('put', `api/v1/admin/members/${this.editingId}`, { ...this.form, email: this.form.email || null })
                .then(() => {
                    this.closeModal('formModal', false);
                    this.reload();
                })
                .catch((err) => {
                    const bag = (err.response && err.response.data && err.response.data.errors) || {};
                    this.errors = Object.fromEntries(Object.entries(bag).map(([k, v]) => [k, v[0]]));
                })
                .finally(() => this.$store.commit('httpRequest', false));
        },
        remove(member) {
            const listings = member.products_count ? ` and their ${member.products_count} listing(s)` : '';
            this.confirmBox((ok) => {
                if (ok) this.run(member, this.adminRequest('delete', `api/v1/admin/members/${member.id}`));
            }, `Delete ${member.name}?`, `This permanently removes the account${listings}, messages and saved items. It cannot be undone. To keep the history, deactivate the member instead.`);
        },
    },
    mounted() {
        ['is_verified', 'status'].forEach((k) => this.$set(this.formFilter, k, ''));
        this.getDataList(1);
    },
}
</script>
