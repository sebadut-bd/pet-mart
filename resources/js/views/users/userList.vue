<template>
    <div>
        <div class="pm-page-head">
            <div>
                <h2>Users</h2>
                <p>Accounts that can sign in to this dashboard. Each user's role controls what they can see.</p>
            </div>
            <div class="pm-page-actions">
                <button type="button" class="btn btn-primary" @click="create"><i class="fa-solid fa-user-plus"></i> Add user</button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <stat-card label="Dashboard users" :value="counts.total || 0" icon="fa-solid fa-user-shield" color="var(--pm-c-indigo)"></stat-card>
            </div>
            <div class="col-sm-4">
                <stat-card label="Active" :value="counts.active || 0" icon="fa-solid fa-user-check" color="var(--pm-c-emerald)"></stat-card>
            </div>
            <div class="col-sm-4">
                <stat-card label="Inactive" :value="counts.inactive || 0" icon="fa-solid fa-user-lock" color="var(--pm-c-rose)"></stat-card>
            </div>
        </div>

        <data-table :table-heading="tableHeading" search-placeholder="Search name, email or phone…" empty-icon="fa-solid fa-user-shield" empty-title="No users found">
            <template v-slot:filter>
                <select v-model="formFilter.role_id" class="form-select" aria-label="Role" @change="getDataList(1)">
                    <option value="">All roles</option>
                    <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.display_name }}</option>
                </select>
                <select v-model="formFilter.status" class="form-select" aria-label="Status" @change="getDataList(1)">
                    <option value="">Any status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </template>

            <template v-slot:data>
                <tr v-for="user in (dataList.data || [])" :key="user.id">
                    <td>
                        <div class="d-flex align-items-center gap-12">
                            <span class="pm-avatar" :class="avatarTone(user.email)">{{ nameInitials(user.name) }}</span>
                            <div class="min-w-0">
                                <span class="pm-cell-title">
                                    {{ user.name }}
                                    <span v-if="user.is_me" class="pm-badge no-dot is-success ms-1" style="font-size: 11px">You</span>
                                </span>
                                <span class="pm-cell-sub">{{ user.email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="text-nowrap">{{ user.phone }}</td>
                    <td><span class="pm-badge no-dot is-violet">{{ user.role || 'No role' }}</span></td>
                    <td class="text-nowrap">{{ dateTime(user.created_at, false) }}</td>
                    <td>
                        <div class="form-check form-switch m-0" :title="user.is_me ? 'You cannot deactivate yourself' : (user.status ? 'Active – click to deactivate' : 'Inactive – click to activate')">
                            <input class="form-check-input" type="checkbox" role="switch" :checked="user.status" :disabled="user.is_me || busyId === user.id" :aria-label="`${user.name} active`" @click.prevent="toggleStatus(user)">
                        </div>
                    </td>
                    <td>
                        <div class="pm-actions">
                            <button type="button" class="btn btn-sm btn-light btn-icon" title="Edit" @click="edit(user)"><i class="fa-regular fa-pen-to-square"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Delete" :disabled="user.is_me" @click="remove(user)"><i class="fa-regular fa-trash-can"></i></button>
                        </div>
                    </td>
                </tr>
            </template>
        </data-table>

        <form-modal modal-size="modal-lg" :title="editingId ? 'Edit user' : 'Add user'" :submit-text="editingId ? 'Save changes' : 'Create user'" @submit="save">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="u-name">Name <span class="req">*</span></label>
                    <input id="u-name" v-model="form.name" class="form-control" maxlength="100" autocomplete="off" placeholder="e.g. Nusrat Jahan" :class="{ 'is-invalid': errors.name }">
                    <span v-if="errors.name" class="error_message">{{ errors.name }}</span>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="u-role">Role <span class="req">*</span></label>
                    <select id="u-role" v-model="form.role_id" class="form-select" :class="{ 'is-invalid': errors.role_id }">
                        <option :value="null" disabled>Choose a role</option>
                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.display_name }}</option>
                    </select>
                    <span v-if="errors.role_id" class="error_message">{{ errors.role_id }}</span>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="u-email">Email (sign-in) <span class="req">*</span></label>
                    <input id="u-email" v-model="form.email" type="email" class="form-control" maxlength="150" autocomplete="off" placeholder="name@example.com" :class="{ 'is-invalid': errors.email }">
                    <span v-if="errors.email" class="error_message">{{ errors.email }}</span>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="u-phone">Mobile number <span class="req">*</span></label>
                    <input id="u-phone" v-model="form.phone" class="form-control" inputmode="tel" maxlength="20" placeholder="01XXXXXXXXX" :class="{ 'is-invalid': errors.phone }">
                    <span v-if="errors.phone" class="error_message">{{ errors.phone }}</span>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="u-pass">{{ editingId ? 'New password' : 'Password' }} <span v-if="!editingId" class="req">*</span></label>
                    <input id="u-pass" v-model="form.password" type="password" class="form-control" autocomplete="new-password" :placeholder="editingId ? 'Leave blank to keep' : 'At least 8 characters'" :class="{ 'is-invalid': errors.password }">
                    <span v-if="errors.password" class="error_message">{{ errors.password }}</span>
                    <div v-else class="form-text">{{ editingId ? 'Leave blank to keep the current password.' : 'At least 8 characters.' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="u-pass2">Confirm password</label>
                    <input id="u-pass2" v-model="form.password_confirmation" type="password" class="form-control" autocomplete="new-password" placeholder="Type the password again">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input id="u-status" class="form-check-input" type="checkbox" v-model="form.status" :disabled="editingIsMe">
                        <label class="form-check-label" for="u-status">Active <span class="form-text d-block">Inactive users cannot sign in to the dashboard.</span></label>
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

const EMPTY = () => ({ name: '', email: '', phone: '', role_id: null, password: '', password_confirmation: '', status: true });

export default {
    name: "userList",
    components: { DataTable, FormModal, StatCard },
    data() {
        return {
            tableHeading: ['User', 'Phone', 'Role', 'Added', 'Active', 'Actions'],
            busyId: null,
            editingId: null,
            editingIsMe: false,
            form: EMPTY(),
            errors: {},
        };
    },
    computed: {
        counts() {
            return (this.dataList && this.dataList.counts) || {};
        },
        roles() {
            return (this.dataList && this.dataList.roles) || [];
        },
    },
    methods: {
        reload() {
            this.getDataList(this.currentPagination || 1);
        },
        create() {
            this.editingId = null;
            this.editingIsMe = false;
            this.errors = {};
            this.form = EMPTY();
            if (this.roles.length === 1) this.form.role_id = this.roles[0].id;
            this.openModal('formModal', 'Add user', false, false);
        },
        edit(user) {
            this.editingId = user.id;
            this.editingIsMe = user.is_me;
            this.errors = {};
            this.form = { ...EMPTY(), name: user.name, email: user.email, phone: (user.phone || '').replace(/^\+88/, ''), role_id: user.role_id, status: user.status };
            this.openModal('formModal', 'Edit user', false, false);
        },
        validate() {
            const e = {};
            if (!this.form.name.trim()) e.name = 'Name is required.';
            if (!/^\S+@\S+\.\S+$/.test(this.form.email)) e.email = 'Enter a valid email address.';
            if (!this.form.phone.trim()) e.phone = 'Mobile number is required.';
            if (!this.form.role_id) e.role_id = 'Choose a role.';
            if (!this.editingId || this.form.password) {
                if (this.form.password.length < 8) e.password = 'Use at least 8 characters.';
                else if (this.form.password !== this.form.password_confirmation) e.password = 'The passwords do not match.';
            }
            this.errors = e;
            return !Object.keys(e).length;
        },
        save() {
            if (!this.validate()) return;
            const payload = { ...this.form };
            if (this.editingId && !payload.password) {
                delete payload.password;
                delete payload.password_confirmation;
            }
            const request = this.editingId
                ? this.adminRequest('put', `api/v1/admin/users/${this.editingId}`, payload)
                : this.adminRequest('post', 'api/v1/admin/users', payload);

            this.$store.commit('httpRequest', true);
            request
                .then(() => {
                    this.closeModal('formModal', false);
                    this.reload();
                    if (this.editingIsMe) this.getConfigurations();
                })
                .catch((err) => {
                    const bag = (err.response && err.response.data && err.response.data.errors) || {};
                    this.errors = Object.fromEntries(Object.entries(bag).map(([k, v]) => [k, v[0]]));
                })
                .finally(() => this.$store.commit('httpRequest', false));
        },
        toggleStatus(user) {
            if (user.is_me) return;
            const go = () => {
                this.busyId = user.id;
                this.adminRequest('post', `api/v1/admin/users/${user.id}/toggle-status`)
                    .then(() => this.reload())
                    .catch(() => {})
                    .finally(() => { this.busyId = null; });
            };
            if (!user.status) return go();
            this.confirmBox((ok) => ok && go(), `Deactivate ${user.name}?`, 'They will be signed out and cannot use the dashboard until activated again.');
        },
        remove(user) {
            this.confirmBox((ok) => {
                if (!ok) return;
                this.adminRequest('delete', `api/v1/admin/users/${user.id}`).then(() => this.reload()).catch(() => {});
            }, `Delete ${user.name}?`, 'Their dashboard access is removed permanently. Moderation history they created is kept.');
        },
    },
    mounted() {
        ['role_id', 'status'].forEach((k) => this.$set(this.formFilter, k, ''));
        this.getDataList(1);
    },
}
</script>
