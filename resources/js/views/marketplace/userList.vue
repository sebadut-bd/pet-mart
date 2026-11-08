<template :key="$route.meta.name">
    <div class="main_component">
        <div class="row">
            <div class="col">
                <data-table :table-heading="tableHeading" table-title="Users & Sellers Directory">
                    <template v-slot:data>
                        <tr v-for="(data, index) in dataList.data" :key="data.id">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <strong>{{ data.name }}</strong><br>
                                <small class="text-muted">{{ data.email || 'No email' }}</small>
                            </td>
                            <td>{{ data.phone }}</td>
                            <td>
                                <span class="badge bg-primary" v-if="data.type === 1">Admin</span>
                                <span class="badge bg-info text-dark" v-else-if="data.type === 2">Seller</span>
                                <span class="badge bg-secondary" v-else>Buyer</span>
                            </td>
                            <td>
                                <span class="badge bg-success" v-if="data.is_verified"><i class="fa fa-check-circle"></i> Verified Seller</span>
                                <span class="badge bg-warning text-dark" v-else>Unverified</span>
                            </td>
                            <td>
                                <span class="badge bg-success" v-if="data.status">Active</span>
                                <span class="badge bg-danger" v-else>Inactive</span>
                            </td>
                            <td class="action-buttons">
                                <button class="btn btn-sm" :class="data.is_verified ? 'btn-outline-warning' : 'btn-outline-success'" @click="toggleVerification(data.id)">
                                    {{ data.is_verified ? 'Revoke Badge' : 'Verify Seller' }}
                                </button>
                                <button class="btn btn-sm" :class="data.status ? 'btn-outline-danger' : 'btn-outline-success'" @click="toggleStatus(data.id)">
                                    {{ data.status ? 'Ban User' : 'Activate' }}
                                </button>
                            </td>
                        </tr>
                    </template>
                </data-table>
            </div>
        </div>
    </div>
</template>

<script>
    import DataTable from "../../components/dataTable";

    export default {
        name: "userList",
        components: { DataTable },
        data() {
            return {
                tableHeading: ['Sl', 'User Name & Email', 'Phone Number', 'Account Type', 'Seller Verified', 'Status', 'Action'],
            }
        },
        methods: {
            toggleVerification(id) {
                this.http().post(`v1/admin/users/${id}/toggle-verification`).then(res => {
                    this.toastSuccess('Seller verification status updated!');
                    this.getDataList('v1/admin/users');
                });
            },
            toggleStatus(id) {
                this.http().post(`v1/admin/users/${id}/toggle-status`).then(res => {
                    this.toastSuccess('User status updated!');
                    this.getDataList('v1/admin/users');
                });
            }
        },
        mounted() {
            this.getDataList('v1/admin/users');
        }
    }
</script>

<style scoped>
    .action-buttons {
        display: flex;
        gap: 8px;
    }
</style>
