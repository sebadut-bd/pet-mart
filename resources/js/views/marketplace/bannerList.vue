<template :key="$route.meta.name">
    <div class="main_component">
        <div class="row">
            <div class="col">
                <data-table :table-heading="tableHeading" table-title="Marketplace Banners & Promos">
                    <template v-slot:page_top>
                        <page-top topPageTitle="Banner Management" :default-add-button="can('banner_add')" page-modal-title="Add / Edit Banner" :default-object="{title:'', image_path: '', link_url:'', sort_order:0, status:1}"></page-top>
                    </template>
                    <template v-slot:data>
                        <tr v-for="(data, index) in dataList.data" :key="data.id">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <strong>{{ data.title }}</strong>
                            </td>
                            <td>
                                <img :src="data.image_path" alt="Banner" style="width: 100px; height: 50px; object-fit: cover; border-radius: 6px;" />
                            </td>
                            <td><a :href="data.link_url" target="_blank" v-if="data.link_url">{{ data.link_url }}</a><span v-else>—</span></td>
                            <td>{{ data.sort_order }}</td>
                            <td>
                                <span class="badge bg-success" v-if="data.status">Active</span>
                                <span class="badge bg-secondary" v-else>Disabled</span>
                            </td>
                            <td class="action-buttons">
                                <div class="hstack gap-2 fs-15">
                                    <button class="btn btn-sm btn-outline-primary" @click="editData(data, data.id)"><i class="fa fa-edit"></i> Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" @click="deleteInformation(index, data.id)"><i class="fa fa-trash"></i> Delete</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </data-table>
            </div>
        </div>

        <!-- Add/Edit Banner Modal -->
        <formModal modalSize="modal-md" @submit="submitForm(formObject, 'formModal')">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Banner Title</label>
                    <input v-validate="'required'" name="title" type="text" v-model="formObject.title" class="form-control" placeholder="e.g. Monsoon Pet Supplies Offer 20%">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Image URL</label>
                    <input v-validate="'required'" name="image_path" type="url" v-model="formObject.image_path" class="form-control" placeholder="https://images.unsplash.com/...">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Link URL (Optional)</label>
                    <input name="link_url" type="text" v-model="formObject.link_url" class="form-control" placeholder="/browse?category_slug=pet-supplies">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input name="sort_order" type="number" v-model="formObject.sort_order" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" v-model="formObject.status" class="form-control">
                        <option :value="1">Active</option>
                        <option :value="0">Disabled</option>
                    </select>
                </div>
            </div>
        </formModal>
    </div>
</template>

<script>
import DataTable from "../../components/dataTable";
import PageTop from "../../components/pageTop";
import formModal from "../../components/formModal";

export default {
    name: "bannerList",
    components: { PageTop, DataTable, formModal },
    data() {
        return {
            tableHeading: ['Sl', 'Title', 'Banner Image', 'Target Link', 'Order', 'Status', 'Action'],
        }
    },
    mounted() {
        this.getDataList();
    }
}
</script>
