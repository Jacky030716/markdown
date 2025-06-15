export default [
  {
    path: "/lecturer",
    component: () => import("../layouts/LecturerLayout.vue"),
    children: [
      {
        path: "dashboard",
        name: "LecturerDashboard",
        component: () => import("../views/lecturer/Dashboard.vue"),
      },
      {
        path: "students",
        name: "ManageStudentsMark",
        component: () => import("../views/lecturer/ManageStudentsMark.vue"),
      },
      {
        path: "courses",
        name: "Courses",
        component: () => import("../views/lecturer/Courses.vue"),
      },
      {
        path: "course/:courseId",
        name: "CourseManagement",
        component: () => import("../views/lecturer/CourseManagement.vue"),
      },
      // {
      //   path: "exam",
      //   name: "FinalExam",
      //   component: () => import("../views/lecturer/FinalExam.vue"),
      // },
      {
        path: "analytics",
        name: "Analytics",
        component: () => import("../views/lecturer/Analytics.vue"),
      },
      // {
      //   path: "notifications",
      //   name: "Notifications",
      //   component: () => import("@/views/lecturer/Notifications.vue"),
      // },
    ],
  },
];
