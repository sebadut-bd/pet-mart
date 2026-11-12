export const state = {
    httpRequest: false,
    dataList: [],
    Config: [],
    allMenus: [],
    requiredData: {},
    formType: 1,
    updateId: '',
    formObject: {},
    formFilter: {
        keyword: '',
        perPage: 15,
        status: '',
    },
    modalTitle: '',
    currentPagination: 1,
    sidebarCollapsed: (() => { try { return localStorage.getItem('pm_sidebar_collapsed') === '1'; } catch (e) { return false; } })(),
    sidebarOpen: false,
};
