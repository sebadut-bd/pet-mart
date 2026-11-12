import baseLayouts from "../views/layouts/baseLayouts";
import pageNotFound from "../views/pageNotFound";
import dashboardPage from "../views/dashboardPage";
import profile from "../views/users/profile";
import moduleList from "../views/rbac/moduleList";
import roleList from "../views/rbac/roleList";
import rolePermissions from "../views/rbac/rolePermissions";
import generalSettingForm from "../views/componentConfigure/generalSettingForm";
import categoryList from "../views/marketplace/categoryList";
import moderationList from "../views/marketplace/moderationList";
import memberList from "../views/marketplace/memberList";
import userList from "../views/users/userList";
import bannerList from "../views/marketplace/bannerList";
import reportList from "../views/marketplace/reportList";
import contactMessageList from "../views/marketplace/contactMessageList";

const routes = [
    {
        path: "/admin/",
        component: baseLayouts,
        children: [
            { path: "", redirect: "dashboard" },
            { path: "dashboard", name: "dashboard", component: dashboardPage, meta: { pageTitle: "Dashboard" } },
            { path: "profile", name: "profile", component: profile, meta: { pageTitle: "My profile" } },

            { path: "marketplace/moderation", name: "marketplace_moderation", component: moderationList, meta: { dataUrl: "api/v1/admin/moderation", pageTitle: "Listing moderation" } },
            { path: "marketplace/reports", name: "marketplace_reports", component: reportList, meta: { dataUrl: "api/v1/admin/reports", pageTitle: "Listing reports" } },
            { path: "marketplace/messages", name: "marketplace_messages", component: contactMessageList, meta: { dataUrl: "api/v1/admin/contact-messages", pageTitle: "Contact messages" } },
            { path: "marketplace/members", name: "marketplace_members", component: memberList, meta: { dataUrl: "api/v1/admin/members", pageTitle: "Members" } },
            { path: "marketplace/users", redirect: "/admin/marketplace/members" },
            { path: "marketplace/categories", name: "marketplace_categories", component: categoryList, meta: { dataUrl: "api/v1/admin/categories", pageTitle: "Categories" } },
            { path: "marketplace/banners", name: "marketplace_banners", component: bannerList, meta: { dataUrl: "api/v1/admin/banners", pageTitle: "Banners" } },

            { path: "users", name: "users", component: userList, meta: { dataUrl: "api/v1/admin/users", pageTitle: "Users" } },
            { path: "role", name: "role", component: roleList, meta: { dataUrl: "api/roles", pageTitle: "Roles" } },
            { path: "role_permissions", name: "role_permissions", component: rolePermissions, meta: { dataUrl: "api/role_permissions", pageTitle: "Role permissions" } },
            { path: "module", name: "module", component: moduleList, meta: { dataUrl: "api/modules", pageTitle: "Modules" } },
            { path: "general_setting", name: "general_setting", component: generalSettingForm, meta: { dataUrl: "api/general_setting", pageTitle: "General settings" } },

            { path: "404", name: "404", component: pageNotFound, meta: { pageTitle: "Page not found" } },
        ],
    },
    { path: "*", redirect: "/admin/404" },
];

export default routes;
