<template>
    <header id="page-topbar">
        <div class="layout-width">
            <div class="navbar-header">
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                        id="topnav-hamburger-icon" @click="toggleAppMenu()">
                        <span class="menu_colups_icon">
                            <i v-if="httpRequest" class="fa fa-spin fa-spinner fs-18"></i>
                            <i v-else class="fa fa-bars fs-18"></i>
                        </span>
                    </button>

                    <div class="ms-2">
                        <h4 class="mb-0 fw-bold text-dark">
                            <i class="fa fa-paw text-primary me-2"></i>Pet-Mart Administration
                        </h4>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <div class="dropdown ms-sm-3 header-item topbar-user">
                        <button type="button" class="btn d-flex align-items-center" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="d-flex align-items-center">
                                <img v-if="userImage" class="rounded-circle header-profile-user me-2" :src="userImage"
                                    alt="User Image" />
                                <div v-else
                                    class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center header-profile-user me-2"
                                    style="width: 36px; height: 36px; font-weight: 600; font-size: 13px;">
                                    {{ getInitials(getUserName) }}
                                </div>

                                <span class="text-start d-none d-xl-inline-block">
                                    <span class="fw-semibold user-name-text d-block">{{ getUserName }}</span>
                                    <small class="text-muted d-block" style="font-size: 11px;">Administrator</small>
                                </span>
                                <i class="fa fa-chevron-down ms-2 fs-12 text-muted"></i>
                            </span>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end shadow">
                            <h6 class="dropdown-header">Welcome, {{ getUserName }}!</h6>
                            <router-link class="dropdown-item" to="/admin/profile">
                                <i class="fa fa-user text-muted me-2"></i> Profile
                            </router-link>
                            <a class="dropdown-item text-danger" :href="`${baseUrl}/logout`">
                                <i class="fa fa-sign-out text-danger me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
export default {
    name: "topNavBar",
    data() {
        return {}
    },

    computed: {
        getUserName() {
            if (this.Config && this.Config.user) {
                return this.Config.user.name || this.Config.user.username || 'Admin';
            }
            return 'Super Admin';
        },
        userImage() {
            const img = this.showData(this.Config ? this.Config.user : null, 'avatar');
            if (!img || typeof img !== 'string' || img.trim() === '' || img === 'null' || img === 'NULL') {
                return null;
            }
            return this.getImage(img);
        },
    },

    methods: {
        toggleAppMenu: function () {
            $('body').toggleClass('vertical-sidebar-enable');
        },

        getInitials(username) {
            if (!username || typeof username !== 'string' || !username.trim()) return 'SA';
            const parts = username.trim().split(' ');
            if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        },
    },
}
</script>

<style scoped>
.header-profile-user {
    width: 36px;
    height: 36px;
    object-fit: cover;
}
</style>
