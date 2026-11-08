import baseLayouts from "../views/layouts/baseLayouts";
import pageNotFound from "../views/pageNotFound";
import dashboardPage from "../views/dashboardPage";
import profile from "../views/users/profile";
import userList from "../views/users/userList";
import moduleList from "../views/rbac/moduleList";
import roleList from "../views/rbac/roleList";
import roleModules from "../views/rbac/roleModules";
import rolePermissions from "../views/rbac/rolePermissions";
import configurationList from "../views/settings/configurationList";
import generalSettingForm from "../views/componentConfigure/generalSettingForm";
import marketplaceCategory from "../views/marketplace/categoryList";
import marketplaceModeration from "../views/marketplace/moderationList";
import marketplaceUser from "../views/marketplace/userList";
import marketplaceBanner from "../views/marketplace/bannerList";
import marketplaceReport from "../views/marketplace/reportList";

const routes = [
    {
        path: "/admin/",
        component: baseLayouts,
        children: [
            { path: "404", name: "404", component: pageNotFound },
            {
                path: "dashboard",
                name: "dashboard",
                component: dashboardPage,
                meta: { dataUrl: "api/dashboard", pageTitle: "Dashboard" },
            },
            {
                path: "profile",
                name: "profile",
                component: profile,
                meta: { dataUrl: "api/profile", pageTitle: "Profile" },
            },
            {
                path: "users",
                name: "users",
                component: userList,
                meta: { dataUrl: "api/users", pageTitle: "Users" },
            },
            {
                path: "role",
                name: "role",
                component: roleList,
                meta: { dataUrl: "api/roles", pageTitle: "Roles & Access Controls" },
            },
            {
                path: "module",
                name: "module",
                component: moduleList,
                meta: { dataUrl: "api/modules", pageTitle: "Modules Management" },
            },
            {
                path: "configurations",
                name: "configurations",
                component: configurationList,
                meta: { dataUrl: "api/configurations", pageTitle: "System Configurations" },
            },
            {
                path: "module_permission",
                name: "module_permission",
                component: roleModules,
                meta: {
                    dataUrl: "api/module_permissions",
                    pageTitle: "Module Permissions Configuration",
                },
            },
            {
                path: "role_permissions",
                name: "role_permissions",
                component: rolePermissions,
                meta: {
                    dataUrl: "api/role_permissions",
                    pageTitle: "Role Permissions Management",
                },
            },
            {
                path: "general_setting",
                name: "general_setting",
                component: generalSettingForm,
                meta: { dataUrl: "api/general_setting", pageTitle: "General Settings" },
            },

            // Marketplace Specific Routes
            {
                path: "marketplace/categories",
                name: "marketplace_categories",
                component: marketplaceCategory,
                meta: { dataUrl: "api/v1/admin/categories", pageTitle: "Marketplace Categories & Specs" },
            },
            {
                path: "marketplace/moderation",
                name: "marketplace_moderation",
                component: marketplaceModeration,
                meta: { dataUrl: "api/v1/admin/moderation", pageTitle: "Product Approval Queue" },
            },
            {
                path: "marketplace/users",
                name: "marketplace_users",
                component: marketplaceUser,
                meta: { dataUrl: "api/v1/admin/users", pageTitle: "Marketplace Users & Sellers" },
            },
            {
                path: "marketplace/banners",
                name: "marketplace_banners",
                component: marketplaceBanner,
                meta: { dataUrl: "api/v1/admin/banners", pageTitle: "Homepage Banners" },
            },
            {
                path: "marketplace/reports",
                name: "marketplace_reports",
                component: marketplaceReport,
                meta: { dataUrl: "api/v1/admin/reports", pageTitle: "Abuse Reports Queue" },
            },
        ],
    },
    { path: "*", redirect: "/admin/404" },
];

export default routes;