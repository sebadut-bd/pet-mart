export const mutations = {
    modalTitle(state, title) { state.modalTitle = title; },
    formObject(state, object) { state.formObject = object; },
    formFilter(state, object) { state.formFilter = object; },
    formType(state, type) { state.formType = type; },
    dataList(state, data) { state.dataList = data; },
    updateId(state, id) { state.updateId = id; },
    Config(state, data) { state.Config = data; },
    allMenus(state, data) { state.allMenus = data; },
    httpRequest(state, data) { state.httpRequest = data; },
    requiredData(state, data) { state.requiredData = data; },
    currentPagination(state, data) { state.currentPagination = data; },
    sidebarCollapsed(state, value) {
        state.sidebarCollapsed = !!value;
        try { localStorage.setItem('pm_sidebar_collapsed', value ? '1' : '0'); } catch (e) { /* storage unavailable */ }
    },
    sidebarOpen(state, value) { state.sidebarOpen = !!value; },
};
