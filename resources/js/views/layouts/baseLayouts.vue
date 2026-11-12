<template>
    <div class="pm-shell" :class="{ 'is-collapsed': sidebarCollapsed, 'is-mobile-open': sidebarOpen }">
        <side-nav-bar></side-nav-bar>
        <div class="pm-overlay" @click="$store.commit('sidebarOpen', false)" aria-hidden="true"></div>

        <div class="pm-main">
            <top-nav-bar></top-nav-bar>
            <main class="pm-content" :key="$route.path">
                <router-view></router-view>
            </main>
            <admin-footer></admin-footer>
        </div>

    </div>
</template>

<script>
import TopNavBar from "./topNavBar";
import SideNavBar from "./sideNavBar";
import AdminFooter from "./adminFooter";

export default {
    name: "baseLayouts",
    components: { SideNavBar, TopNavBar, AdminFooter },
    computed: {
        sidebarCollapsed() {
            return this.$store.getters.sidebarCollapsed;
        },
        sidebarOpen() {
            return this.$store.getters.sidebarOpen;
        },
    },
    watch: {
        '$route.path'() {
            this.$store.commit('sidebarOpen', false);
        },
    },
    mounted() {
        document.addEventListener('keydown', this.onKey);
    },
    beforeDestroy() {
        document.removeEventListener('keydown', this.onKey);
    },
    methods: {
        onKey(e) {
            if (e.key === 'Escape' && this.sidebarOpen) this.$store.commit('sidebarOpen', false);
        },
    },
}
</script>
