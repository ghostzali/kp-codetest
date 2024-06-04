import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        component: () => import("./pages/MedicineOutgoingList.vue"),
    },
    {
        path: "/login",
        component: () => import("./pages/Login.vue"),
    },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
