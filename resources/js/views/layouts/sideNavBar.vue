<template>
    <aside class="pm-sidebar" aria-label="Main navigation">
        <router-link to="/admin/dashboard" class="pm-brand" title="PetMart Admin">
            <span class="pm-brand-mark"><i class="fa-solid fa-paw"></i></span>
            <span class="pm-brand-text">
                <strong>PetMart</strong>
                <small>Admin panel</small>
            </span>
        </router-link>

        <div class="pm-side-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input v-model="keyword" type="search" placeholder="Search menu…" aria-label="Search menu">
        </div>

        <nav class="pm-nav">
            <template v-for="section in sections">
                <div v-if="section.label" class="pm-nav-label" :key="'l' + section.key">{{ section.label }}</div>

                <template v-for="item in section.items">
                    <router-link
                        v-if="!item.children.length"
                        :key="item.key"
                        :to="item.link"
                        class="pm-nav-link"
                        :class="{ 'is-active': isActive(item.link) }"
                        :style="{ '--accent': item.color }"
                        :title="item.label"
                    >
                        <span class="pm-nav-icon"><i :class="item.icon"></i></span>
                        <span class="pm-nav-text">{{ item.label }}</span>
                        <span v-if="item.badge" class="pm-nav-badge">{{ item.badge > 99 ? '99+' : item.badge }}</span>
                    </router-link>

                    <div v-else :key="item.key" class="pm-nav-group" :class="{ 'is-open': isOpen(item) }">
                        <button
                            type="button"
                            class="pm-nav-link"
                            :class="{ 'is-active': collapsed && groupActive(item) }"
                            :style="{ '--accent': item.color }"
                            :title="item.label"
                            :aria-expanded="isOpen(item) ? 'true' : 'false'"
                            @click="toggleGroup(item)"
                        >
                            <span class="pm-nav-icon"><i :class="item.icon"></i></span>
                            <span class="pm-nav-text">{{ item.label }}</span>
                            <span v-if="!isOpen(item) && groupBadge(item)" class="pm-nav-badge">{{ groupBadge(item) }}</span>
                            <i class="fa-solid fa-chevron-right pm-nav-chevron"></i>
                        </button>
                        <ul v-show="isOpen(item)" class="pm-nav-sub">
                            <li v-for="child in item.children" :key="child.key">
                                <router-link
                                    :to="child.link"
                                    class="pm-nav-link"
                                    :class="{ 'is-active': isActive(child.link) }"
                                    :style="{ '--accent': child.color }"
                                >
                                    <span class="pm-nav-text">{{ child.label }}</span>
                                    <span v-if="child.badge" class="pm-nav-badge">{{ child.badge > 99 ? '99+' : child.badge }}</span>
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </template>
            </template>

            <p v-if="!sections.length && keyword" class="pm-nav-empty">No menu matches “{{ keyword }}”.</p>
            <p v-else-if="!sections.length" class="pm-nav-empty"><i class="fa fa-spinner fa-spin me-1"></i> Loading menu…</p>
        </nav>

        <div class="pm-side-footer">
            <div class="pm-side-user">
                <span class="pm-avatar">{{ initials }}</span>
                <span class="pm-side-user-text">
                    <strong>{{ userName }}</strong>
                    <small>Administrator</small>
                </span>
            </div>
        </div>
    </aside>
</template>

<script>
const META = {
    dashboard:       { icon: 'fa-solid fa-gauge-high', color: 'var(--pm-c-emerald)' },
    marketplace:     { icon: 'fa-solid fa-store', color: 'var(--pm-c-sky)', section: 'Marketplace' },
    moderation:      { icon: 'fa-solid fa-clipboard-check', color: 'var(--pm-c-amber)' },
    category:        { icon: 'fa-solid fa-layer-group', color: 'var(--pm-c-sky)' },
    user:            { icon: 'fa-solid fa-users', color: 'var(--pm-c-violet)' },
    admin_user:      { icon: 'fa-solid fa-user-gear', color: 'var(--pm-c-sky)' },
    report:          { icon: 'fa-solid fa-flag', color: 'var(--pm-c-rose)' },
    contact_message: { icon: 'fa-solid fa-envelope-open-text', color: 'var(--pm-c-teal)' },
    banner:          { icon: 'fa-solid fa-image', color: 'var(--pm-c-pink)' },
    accesses:        { icon: 'fa-solid fa-shield-halved', color: 'var(--pm-c-indigo)', section: 'Access control' },
    module:          { icon: 'fa-solid fa-cubes', color: 'var(--pm-c-indigo)' },
    role:            { icon: 'fa-solid fa-user-shield', color: 'var(--pm-c-violet)' },
    role_permission: { icon: 'fa-solid fa-key', color: 'var(--pm-c-orange)' },
    setting:         { icon: 'fa-solid fa-gear', color: 'var(--pm-c-slate)', section: 'Settings' },
    general_setting: { icon: 'fa-solid fa-sliders', color: 'var(--pm-c-emerald)' },
};
const FALLBACK_COLORS = ['var(--pm-c-emerald)', 'var(--pm-c-sky)', 'var(--pm-c-violet)', 'var(--pm-c-amber)', 'var(--pm-c-pink)', 'var(--pm-c-teal)'];

export default {
    name: "sideNavBar",
    data() {
        return {
            keyword: '',
            open: {},
        };
    },
    computed: {
        collapsed() {
            return this.$store.getters.sidebarCollapsed && window.innerWidth >= 992;
        },
        badges() {
            return (this.Config && this.Config.badges) || {};
        },
        menuTree() {
            const menus = (this.Config && this.Config.menus) || [];
            return menus.map((m, i) => this.toItem(m, i));
        },
        sections() {
            const q = this.keyword.trim().toLowerCase();
            const items = this.menuTree
                .map((item) => {
                    if (!q) return item;
                    const children = item.children.filter((c) => c.label.toLowerCase().includes(q));
                    if (item.label.toLowerCase().includes(q)) return item;
                    return children.length ? { ...item, children, forceOpen: true } : null;
                })
                .filter(Boolean);

            const sections = [];
            items.forEach((item) => {
                if (!sections.length || item.section) {
                    sections.push({ key: item.key, label: item.section || '', items: [] });
                }
                sections[sections.length - 1].items.push(item);
            });
            return sections;
        },
        userName() {
            return (this.Config && this.Config.user && this.Config.user.name) || 'Admin';
        },
        initials() {
            return this.nameInitials(this.userName);
        },
    },
    watch: {
        '$route.path': {
            handler() {
                this.openActiveGroup();
            },
        },
        menuTree() {
            this.openActiveGroup();
        },
    },
    methods: {
        toItem(menu, index, depth = 0) {
            const meta = META[menu.name] || {};
            const children = (menu.submenus || []).filter((s) => s.link && s.link !== '#');
            return {
                key: menu.id + '-' + menu.name,
                name: menu.name,
                label: menu.display_name || menu.name,
                link: menu.link,
                icon: meta.icon || ('fa ' + (menu.icon || 'fa-circle')),
                color: meta.color || FALLBACK_COLORS[index % FALLBACK_COLORS.length],
                section: depth === 0 ? meta.section : undefined,
                badge: this.badges[menu.name] || 0,
                children: depth === 0 ? children.map((c, i) => this.toItem(c, index + i + 1, 1)) : [],
            };
        },
        isActive(link) {
            if (!link || link === '#') return false;
            const path = this.$route.path.replace(/\/+$/, '');
            const target = link.replace(/\/+$/, '');
            return path === target || path.startsWith(target + '/');
        },
        groupActive(item) {
            return item.children.some((c) => this.isActive(c.link));
        },
        groupBadge(item) {
            return item.children.reduce((sum, c) => sum + (c.badge || 0), 0);
        },
        isOpen(item) {
            return !!item.forceOpen || !!this.open[item.key];
        },
        toggleGroup(item) {
            if (this.collapsed) {
                this.$store.commit('sidebarCollapsed', false);
                this.$set(this.open, item.key, true);
                return;
            }
            this.$set(this.open, item.key, !this.open[item.key]);
        },
        openActiveGroup() {
            this.menuTree.forEach((item) => {
                if (this.groupActive(item)) this.$set(this.open, item.key, true);
            });
        },
    },
    mounted() {
        this.getConfigurations();
    },
}
</script>
