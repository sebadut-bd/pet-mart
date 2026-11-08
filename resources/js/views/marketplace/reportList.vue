<template :key="$route.meta.name">
    <div class="main_component">
        <div class="row">
            <div class="col">
                <data-table :table-heading="tableHeading" table-title="Product Reports & Abuse Queue">
                    <template v-slot:page_top>
                        <page-top topPageTitle="Abuse Reports Queue" :default-add-button="false"></page-top>
                    </template>
                    <template v-slot:data>
                        <tr v-for="(data, index) in dataList.data" :key="data.id">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <strong>{{ data.product ? data.product.title : 'Deleted Product' }}</strong>
                            </td>
                            <td>{{ data.reporter ? data.reporter.name : 'Anonymous' }}</td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger p-2">{{ data.reason }}</span>
                            </td>
                            <td>{{ new Date(data.created_at).toLocaleDateString() }}</td>
                            <td>
                                <span class="badge bg-warning text-dark" v-if="data.status === 'pending'">Pending Review</span>
                                <span class="badge bg-success" v-else-if="data.status === 'resolved'">Resolved</span>
                                <span class="badge bg-secondary" v-else>Dismissed</span>
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
import PageTop from "../../components/pageTop";

export default {
    name: "reportList",
    components: { PageTop, DataTable },
    data() {
        return {
            tableHeading: ['Sl', 'Reported Listing', 'Reporter User', 'Abuse Reason', 'Report Date', 'Status'],
        }
    },
    mounted() {
        this.getDataList();
    }
}
</script>
