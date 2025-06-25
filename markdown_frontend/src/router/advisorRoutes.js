export default [
  {
    path: "/advisor",
    component: () => import("../layouts/AdvisorLayout.vue"),
    children: [
      {
        path: "dashboard",
        name: "AdvisorDashboard",
        component: () => import("../views/advisor/Dashboard.vue"),
      },
      {
        path: "advisee/:studentId",
        name: "AdviseeProfile",
        component: () => import("../views/advisor/AdviseeProfile.vue"),
        props: true,
      },
      {
        path: "at-risk",
        name: "AtRiskStudents",
        component: () => import("../views/advisor/AtRiskStudents.vue"),
      },
      {
        path: "meetings",
        name: "MeetingNotes",
        component: () => import("../views/advisor/MeetingNotes.vue"),
      },
      {
        path: "meetings/:studentId",
        name: "StudentMeetingHistory",
        component: () => import("../views/advisor/StudentMeetingHistory.vue"),
        props: true,
      },
      {
        path: "analytics",
        name: "AdvisorAnalytics",
        component: () => import("../views/advisor/Analytics.vue"),
      }
    ],
  },
];