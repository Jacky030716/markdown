export default [
  {
    path: "/admin",
    component: () => import("../layouts/AdminLayout.vue"),
    children: [
      {
        path: "user-management",
        name: "UserManagement",
        component: () => import("../views/admin/UserManagement.vue"),
      },
      {
        path: "course-registration",
        name: "CourseRegistration",
        component: () => import("../views/admin/CourseRegistration.vue"),
      },
    ],
  },
];
