<template>
    <div class="page-wrapper">
        <i v-if="httpRequest" class="fa fa-spin fa-spinner" style="font-size: 2rem; display: block; margin: 3rem auto; text-align: center; color: #10b981;"></i>
        <div class="page-content" v-else>
            
            <!-- Top Header Welcome -->
            <div class="row mb-4 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column justify-content-between">
                        <div>
                            <h3 class="fs-22 fw-bold mb-1">
                                Welcome, <span class="text-success">{{ showData(Config.user, 'name') || 'Admin' }}</span>
                            </h3>
                            <p class="text-muted mb-0">Pet & Animal Marketplace Admin Overview</p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <router-link to="/admin/moderation" class="btn btn-emerald me-2">
                                <i class="fa fa-clock me-1"></i> Moderation Queue ({{ (stats && stats.pending_approvals) || 0 }})
                            </router-link>
                            <router-link to="/admin/categories" class="btn btn-outline-secondary">
                                <i class="fa fa-tags me-1"></i> Manage Categories
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Metric Cards -->
            <div class="row mb-4">
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-shape bg-emerald-light text-emerald rounded-4 me-3">
                                <i class="fa fa-paw fs-20"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-semibold text-uppercase fs-12 mb-1">Active Listings</h6>
                                <h3 class="fw-bold mb-0 text-dark">{{ (stats && stats.active_listings) || 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-shape bg-amber-light text-amber rounded-4 me-3">
                                <i class="fa fa-exclamation-circle fs-20"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-semibold text-uppercase fs-12 mb-1">Pending Review</h6>
                                <h3 class="fw-bold mb-0 text-dark">{{ (stats && stats.pending_approvals) || 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-shape bg-blue-light text-blue rounded-4 me-3">
                                <i class="fa fa-users fs-20"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-semibold text-uppercase fs-12 mb-1">Total Users</h6>
                                <h3 class="fw-bold mb-0 text-dark">{{ (stats && stats.total_users) || 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-shape bg-purple-light text-purple rounded-4 me-3">
                                <i class="fa fa-user-shield fs-20"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-semibold text-uppercase fs-12 mb-1">Verified Breeders</h6>
                                <h3 class="fw-bold mb-0 text-dark">{{ (stats && stats.verified_sellers) || 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Charts Section -->
            <div class="row mb-4">
                
                <!-- Listing Status Breakdown Bar Chart -->
                <div class="col-lg-7 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Listing Status Overview</h5>
                            <span class="badge bg-light text-dark fw-semibold">Live Data</span>
                        </div>
                        <div class="card-body px-4">
                            <apexchart
                                type="bar"
                                height="320"
                                :options="statusChartOptions"
                                :series="statusChartSeries">
                            </apexchart>
                        </div>
                    </div>
                </div>

                <!-- Category Distribution Donut Chart -->
                <div class="col-lg-5 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-0">Top Categories</h5>
                        </div>
                        <div class="card-body px-4 d-flex align-items-center justify-content-center">
                            <apexchart
                                type="donut"
                                height="300"
                                :options="categoryChartOptions"
                                :series="categoryChartSeries">
                            </apexchart>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Recent Listings Activity Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Recent Pet Listings</h5>
                            <router-link to="/admin/moderation" class="text-emerald text-decoration-none fw-semibold fs-14">
                                View Moderation Queue →
                            </router-link>
                        </div>
                        <div class="card-body px-0 pt-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Title</th>
                                            <th>Seller</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th class="pe-4 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in ((stats && stats.recent_products) || [])" :key="item.id">
                                            <td class="ps-4 fw-semibold">{{ item.title }}</td>
                                            <td>{{ item.user ? item.user.name : '—' }}</td>
                                            <td>
                                                <span class="badge bg-light text-secondary rounded-pill px-3 py-1">
                                                    {{ item.category ? item.category.name : '—' }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success">
                                                {{ item.is_free ? 'Free Adoption' : (item.price ? '৳' + Number(item.price).toLocaleString() : 'Negotiable') }}
                                            </td>
                                            <td>
                                                <span :class="getStatusBadgeClass(item.status)">
                                                    {{ item.status }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <router-link to="/admin/moderation" class="btn btn-sm btn-outline-emerald">
                                                    Review
                                                </router-link>
                                            </td>
                                        </tr>
                                        <tr v-if="!stats || !stats.recent_products || stats.recent_products.length === 0">
                                            <td colspan="6" class="text-center py-4 text-muted">No recent listings available.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
import VueApexCharts from "vue-apexcharts";

export default {
    name: "DashboardPage",
    components: {
        apexchart: VueApexCharts,
    },
    data() {
        return {
            stats: {
                active_listings: 0,
                pending_approvals: 0,
                total_users: 0,
                verified_sellers: 0,
                recent_products: []
            },
            statusChartSeries: [{
                name: 'Listings',
                data: [0, 0, 0, 0]
            }],
            statusChartOptions: {
                chart: { id: 'status-chart', toolbar: { show: false } },
                colors: ['#10b981', '#f59e0b', '#64748b', '#ef4444'],
                plotOptions: { bar: { borderRadius: 8, distributed: true } },
                xaxis: { categories: ['Active', 'Pending', 'Sold', 'Rejected'] },
                legend: { show: false }
            },
            categoryChartSeries: [1],
            categoryChartOptions: {
                chart: { id: 'category-chart' },
                labels: ['Loading...'],
                colors: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'],
                legend: { position: 'bottom' }
            }
        };
    },
    methods: {
        getDashboardData() {
            const _this = this;
            _this.$store.commit('httpRequest', true);

            _this.httpReq('/api/v1/admin/dashboard-stats', 'get', {}, {}, function (res) {
                _this.$store.commit('httpRequest', false);
                if (res) {
                    const dataObj = res.data || res;
                    _this.stats = Object.assign({}, _this.stats, dataObj);
                    
                    const statusChart = dataObj.status_chart || res.status_chart;
                    if (statusChart && statusChart.series) {
                        _this.statusChartSeries = statusChart.series;
                        _this.statusChartOptions = {
                            ..._this.statusChartOptions,
                            xaxis: { categories: statusChart.categories || ['Active', 'Pending', 'Sold', 'Rejected'] }
                        };
                    }

                    const catChart = dataObj.category_chart || res.category_chart;
                    if (catChart && catChart.series && catChart.series.length > 0) {
                        _this.categoryChartSeries = catChart.series;
                        _this.categoryChartOptions = {
                            ..._this.categoryChartOptions,
                            labels: catChart.labels || []
                        };
                    }
                }
            });
        },
        getStatusBadgeClass(status) {
            if (status === 'active') return 'badge bg-success-subtle text-success rounded-pill px-3';
            if (status === 'pending') return 'badge bg-warning-subtle text-warning rounded-pill px-3';
            if (status === 'rejected') return 'badge bg-danger-subtle text-danger rounded-pill px-3';
            return 'badge bg-secondary-subtle text-secondary rounded-pill px-3';
        }
    },
    mounted() {
        this.getDashboardData();
    }
};
</script>

<style scoped>
.icon-shape {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-emerald-light { background: #ecfdf5; }
.text-emerald { color: #10b981; }
.btn-emerald { background: #10b981; color: #fff; border: none; }
.btn-emerald:hover { background: #059669; color: #fff; }
.btn-outline-emerald { color: #10b981; border-color: #10b981; }
.btn-outline-emerald:hover { background: #10b981; color: #fff; }

.bg-amber-light { background: #fffbebf; }
.text-amber { color: #f59e0b; }

.bg-blue-light { background: #eff6ff; }
.text-blue { color: #3b82f6; }

.bg-purple-light { background: #faf5ff; }
.text-purple { color: #8b5cf6; }

.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.06) !important;
}
</style>
