<template>
  <div class="container">
    <!-- Welcome Section -->
    <WelcomeCard :student="student" />

    <!-- Quick Stats -->
    <QuickStats :stats="stats" />

    <!-- Courses Section -->
    <CourseOverview :courses="courses" />

    <!-- Recent Updates -->
    <RecentUpdates :updates="updates" />
  </div>
</template>

<script>
import WelcomeCard from '../../components/student/dashboard/WelcomeCard.vue';
import QuickStats from '../../components/student/dashboard/QuickStats.vue';
import CourseOverview from '../../components/student/dashboard/CourseOverview.vue';
import RecentUpdates from '../../components/student/dashboard/RecentUpdates.vue';
import studentsApi from "../../api/students";  // Import the API calls

export default {
  name: 'Dashboard',
  components: {
    WelcomeCard,
    QuickStats,
    CourseOverview,
    RecentUpdates
  },
  data() {
    return {
      studentId: localStorage.getItem('student_id'), // Mock student ID, replace this when login feature is implemented
      student: {}, // Empty object to be populated with student details
      stats: [], // Initially empty, will be populated with progress summary stats
      courses: [], // Courses data will be populated here
      updates: [
        // Sample data, modify as needed
        {
          icon: '📊', title: 'New marks uploaded for Web Technology', desc: 'Assignment 2 marks have been updated', time: '2 hours ago'
        },
        {
          icon: '📝', title: 'Remark request approved', desc: 'Your Database Systems Quiz remark has been processed', time: '1 day ago'
        },
        {
          icon: '🎯', title: 'Rank improvement', desc: "You've moved up 2 positions in Software Engineering", time: '3 days ago'
        },
        {
          icon: '📈', title: 'Performance milestone reached', desc: 'Your GPA has improved to 3.45', time: '1 week ago'
        }
      ]
    };
  },
  created() {
    this.fetchStudentDetails(this.studentId);  // Pass the student ID to fetch details
    this.fetchProgressSummary(this.studentId);  // Pass student ID for progress summary
    this.fetchCourses(this.studentId);  // Fetch the courses for the student
  },
  methods: {
    async fetchStudentDetails(studentId) {
      try {
        const response = await studentsApi.getStudentDetails(studentId);
        this.student = response.data;  // Set student data
      } catch (error) {
        console.error("Error fetching student details:", error);
        // You could also display an error message to the user here
      }
    },

    async fetchProgressSummary(studentId) {
      try {
        const progress = await studentsApi.getProgressSummary(studentId);
        this.stats = [
          { icon: '⏰', value: progress.data.current_credit_hours, label: 'Current Credit Hours' },
          { icon: '📚', value: progress.data.total_courses, label: 'Total Courses' },
          { icon: '🏆', value: progress.data.completed_courses, label: 'Completed Courses' },
        ];
      } catch (error) {
        console.error("Error fetching progress summary:", error);
      }
    },

    async fetchCourses(studentId) {
      try {
        const response = await studentsApi.getAllCourses(studentId);  // Fetch courses
        
        // Check if the response has the expected structure
        if (response && response.data) {
          this.courses = response.data;  // Set the courses data directly
        } else {
          console.error("Unexpected API response structure:", response);
          this.courses = []; // Set empty array as fallback
        }
      } catch (error) {
        console.error("Error fetching courses:", error);
        this.courses = []; // Set empty array as fallback
        // You could also display an error message to the user here
      }
    }
  }
};
</script>

<style scoped>
.container {
  max-width: 1500px;
  margin: 0 auto;
  padding: 30px;
}
</style>