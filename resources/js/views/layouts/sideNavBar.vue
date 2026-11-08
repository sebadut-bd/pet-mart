<template>
    <div class="app-menu navbar-menu">
        <!-- LOGO -->
        <div class="navbar-brand-box py-3 text-center bg-dark">
            <router-link to="/admin/dashboard" class="logo text-white text-decoration-none">
                <span class="logo-sm">
                    <i class="fa fa-paw text-warning fs-3"></i>
                </span>
                <span class="logo-lg fw-bold fs-4 text-white">
                    <i class="fa fa-paw text-warning me-2 fs-3"></i>PET-MART
                </span>
            </router-link>
            <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover text-white" id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
        </div>

        <div id="scrollbar">
            <div class="container-fluid">

                <div id="two-column-menu">
                </div>
                <ul class="navbar-nav" id="navbar-nav">
                    <li class="nav-item hide_menu">
                        <a class="nav-link menu-link pointer" @click="hideMenu()">
                            <i class="fa fa-chevron-left"></i> Hide Menu
                        </a>
                        <hr class="my-1">
                    </li>
                    <li class="nav-item px-2 my-2">
                        <div class="input-group filter input-group-sm">
                            <span class="input-group-text bg-transparent border-secondary text-muted">
                                <i @click="getConfigurations()" class="fa fa-refresh pointer" title="Refresh Menus"></i>
                            </span>
                            <input v-model="menu_keyword" placeholder="Search menu..." class="form-control border-secondary bg-transparent text-white">
                            <span v-if="menu_keyword" @click="(()=>{menu_keyword=''})" class="input-group-text bg-transparent border-secondary text-muted pointer">
                                <i class="fa fa-close"></i>
                            </span>
                        </div>
                    </li>
                    <template v-for="(menu, index) in allMenus">
                        <template v-if="arrLength(menu.submenus) === 0">
                            <li class="nav-item" :key="menu.id">
                                <router-link class="nav-link menu-link" :to="menu.link">
                                    <i :class="'fa ' + menu.icon" style="font-size: 16px"></i> <span data-key="t-widgets">{{menu.display_name}}</span>
                                </router-link>
                            </li>
                        </template>
                        <template v-else>
                            <li class="nav-item" :key="menu.id">
                                <a class="nav-link menu-link" :href="`#${menu.name}_${index}`" data-bs-toggle="collapse" role="button" aria-expanded="false" :aria-controls="`${menu.name}_${index}`">
                                    <i :class="'fa ' + menu.icon" style="font-size: 16px"></i><span data-key="t-dashboards">{{menu.display_name}}</span>
                                </a>
                                <div class="collapse menu-dropdown" :id="`${menu.name}_${index}`">
                                    <ul class="nav nav-sm flex-column">
                                        <template v-for="(submenu, sIndex) in menu.submenus">
                                            <template v-if="arrLength(submenu.submenus) === 0">
                                                <li class="nav-item" :key="submenu.id">
                                                    <router-link :to="submenu.link" class="nav-link"> {{submenu.display_name}} </router-link>
                                                </li>
                                            </template>
                                            <template v-else>
                                                <li class="nav-item" :key="submenu.id">
                                                    <a :href="`#${submenu.name}_${sIndex}`" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" :aria-controls="`${submenu.name}_${sIndex}`" data-key="t-email">{{submenu.display_name}}</a>
                                                    <div class="collapse menu-dropdown" :id="`${submenu.name}_${sIndex}`">
                                                        <ul class="nav nav-sm flex-column">
                                                            <template v-for="subSubMenu in submenu.submenus">
                                                                <li class="nav-item" :key="subSubMenu.id">
                                                                    <router-link :to="subSubMenu.link" class="nav-link" data-key="t-mailbox"> {{subSubMenu.display_name}} </router-link>
                                                                </li>
                                                            </template>
                                                        </ul>
                                                    </div>
                                                </li>
                                            </template>
                                        </template>
                                    </ul>
                                </div>
                            </li>
                        </template>
                    </template>
                </ul>
            </div>
            <!-- Sidebar -->
        </div>

        <div class="sidebar-background"></div>
    </div>
</template>

<script>
    export default {
        name: "sideNavBar",
        data(){
            return{
                menu_keyword : '',
                menus : [],
            }
        },
        watch : {
            menu_keyword : function (oldVal, newVal) {
                const _this = this;
                _this.menus = [];
                _this.$store.commit('allMenus', _this.Config.menus);
                if (_this.menu_keyword !== ''){
                    $.each(_this.Config.menus, function (pIndex, pMenu) {
                        _this.addMenu(pMenu);
                        $.each(pMenu.submenus, function (sIndex, sMenu) {
                            _this.addMenu(sMenu);
                            $.each(sMenu.submenus, function (ssIndex, ssMenu) {
                                _this.addMenu(ssMenu);
                            });
                        });
                    });
                    var foundValue = _this.menus.filter(function(eachData){
                        var display_name = eachData.display_name.toLowerCase();
                        return display_name.includes(_this.menu_keyword.toLowerCase());
                    });
                    _this.$store.commit('allMenus', foundValue);
                }else{
                    _this.$store.commit('allMenus', _this.Config.menus);
                }
            }
        },
        methods : {
            hideMenu : function(){
                $('body').removeClass('vertical-sidebar-enable');
            },
            addMenu : function (menu) {
                const _this = this;
                _this.menus.push({
                    display_name : menu.display_name,
                    id : menu.id,
                    link : menu.link,
                    name : menu.name,
                    status : menu.status,
                    submenus : [],
                });
            }
        },
        mounted() {
            this.getConfigurations();
        }
    }
</script>

<style scoped>
.pointer {
    cursor: pointer;
}
</style>
