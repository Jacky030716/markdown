export default [
    {
      path: "/student",
      component: () => import("../layouts/StudentLayout.vue"),
      children: [
        {
          path: "dashboard",
          name: "StudentDashboard",
          component: () => import("../views/student/Dashboard.vue"),
        },
        {
          path: "marks",
          name: "MarksBreakdown",
          component: () => import("../views/student/MarksBreakdown.vue"),
        },
        {
          path: "remark",
          name: "RemarkRequest",
          component: () => import("../views/student/RemarkRequest.vue"),
        },
      ],
    },
  ];
  