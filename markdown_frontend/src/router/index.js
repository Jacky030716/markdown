import { createRouter, createWebHistory } from "vue-router";

import lecturerRoutes from "./lecturerRoutes";
import studentRoutes from "./studentRoutes";
// import advisorRoutes from "./advisorRoutes";
// import adminRoutes from "./adminRoutes";

const routes = [
  {
    path: "/",
    name: "Login",
    component: () => import("../views/Login.vue"),
  },
  ...lecturerRoutes,
  ...studentRoutes,
  // ...advisorRoutes,
  // ...adminRoutes,
  {
    path: "/:catchAll(.*)",
    name: "NotFound",
    component: () => import("../views/NotFound.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
