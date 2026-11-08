<template :key="$route.meta.name">
    <div class="main_component">
        <div class="row">
            <div class="col">
                <data-table :table-heading="tableHeading" table-title="Marketplace Categories">
                    <template v-slot:page_top>
                        <page-top topPageTitle="Category & Attributes Management" :default-add-button="can('category_add')" page-modal-title="Add / Edit Category" :default-object="{name:'', parent_id: '', icon:'', description:''}"></page-top>
                    </template>
                    <template v-slot:data>
                        <tr v-for="(data, index) in dataList.data" :key="data.id">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <strong>{{ data.name }}</strong>
                            </td>
                            <td>{{ data.slug }}</td>
                            <td>{{ data.parent ? data.parent.name : 'Root Category' }}</td>
                            <td>
                                <span class="badge bg-info text-dark" v-if="data.attributes">{{ data.attributes.length }} Attributes</span>
                                <span v-else>0</span>
                            </td>
                            <td class="action-buttons">
                                <div class="hstack gap-2 fs-15">
                                    <button class="btn btn-sm btn-outline-primary" @click="editData(data, data.id)"><i class="fa fa-edit"></i> Edit</button>
                                    <button class="btn btn-sm btn-outline-info" @click="openAttributeModal(data)"><i class="fa fa-plus-circle"></i> Add Attr</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </data-table>
            </div>
        </div>

        <!-- Add/Edit Category Modal -->
        <formModal modalSize="modal-md" @submit="submitForm(formObject, 'formModal')">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Category Name</label>
                    <input v-validate="'required'" name="name" type="text" v-model="formObject.name" class="form-control" placeholder="e.g. Dogs & Puppies">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Parent Category</label>
                    <select name="parent_id" v-model="formObject.parent_id" class="form-control">
                        <option value="">None (Root Category)</option>
                        <option v-for="cat in dataList.data" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Icon (FontAwesome)</label>
                    <input name="icon" type="text" v-model="formObject.icon" class="form-control" placeholder="e.g. dog, cat, paw">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" v-model="formObject.description" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </formModal>

        <!-- Add Category Attribute Modal -->
        <div class="modal fade" id="attrModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Attribute to {{ selectedCategory ? selectedCategory.name : '' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Attribute Name</label>
                            <input type="text" v-model="attrForm.name" class="form-control" placeholder="e.g. Vaccination Status">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attribute Key</label>
                            <input type="text" v-model="attrForm.attribute_key" class="form-control" placeholder="e.g. vaccination_status">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Input Type</label>
                            <select v-model="attrForm.type" class="form-control">
                                <option value="text">Text Input</option>
                                <option value="number">Number</option>
                                <option value="select">Dropdown Select</option>
                                <option value="boolean">Yes / No</option>
                            </select>
                        </div>
                        <div class="mb-3" v-if="attrForm.type === 'select'">
                            <label class="form-label">Dropdown Options (Comma separated)</label>
                            <input type="text" v-model="optionsString" class="form-control" placeholder="Fully Vaccinated, Partially Vaccinated, Not Vaccinated">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" v-model="attrForm.is_required" id="reqCheck">
                            <label class="form-check-label" for="reqCheck">Required Field</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="saveAttribute">Save Attribute</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DataTable from "../../components/dataTable";
    import PageTop from "../../components/pageTop";
    import formModal from "../../components/formModal";

    export default {
        name: "categoryList",
        components: { PageTop, DataTable, formModal },
        data() {
            return {
                tableHeading: ['Sl', 'Category Name', 'Slug', 'Parent Category', 'Attributes', 'Action'],
                selectedCategory: null,
                optionsString: '',
                attrForm: {
                    name: '',
                    attribute_key: '',
                    type: 'text',
                    options: [],
                    is_required: false,
                }
            }
        },
        methods: {
            openAttributeModal(category) {
                this.selectedCategory = category;
                this.attrForm = { name: '', attribute_key: '', type: 'text', options: [], is_required: false };
                this.optionsString = '';
                $('#attrModal').modal('show');
            },
            saveAttribute() {
                if (!this.selectedCategory) return;
                if (this.attrForm.type === 'select' && this.optionsString) {
                    this.attrForm.options = this.optionsString.split(',').map(s => s.trim());
                }
                const url = `v1/admin/categories/${this.selectedCategory.id}/attributes`;
                this.http().post(url, this.attrForm).then(res => {
                    this.toastSuccess('Attribute saved successfully');
                    $('#attrModal').modal('hide');
                    this.getDataList();
                }).catch(err => {
                    this.toastError('Failed to save attribute');
                });
            }
        },
        mounted() {
            this.getDataList();
        }
    }
</script>

<style scoped>
    .action-buttons {
        display: flex;
        gap: 8px;
    }
</style>
