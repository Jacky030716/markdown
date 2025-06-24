<template>
  <div class="container">
    <!-- Header Section -->
    <div class="header">
      <h1>Remark Requests</h1>
    </div>

    <!-- Info Box -->
    <InfoBox />

    <!-- Main Content -->
    <div class="main-content">
      <!-- Remark Request Form -->
      <RemarkForm :courses="courses" @submitRequest="submitRequest" />
    </div>

    <!-- Request History -->
    <RequestHistory :requests="requests" />
  </div>
</template>

<script>
import InfoBox from '../../components/student/remark-request/InfoBox.vue';
import RemarkForm from '../../components/student/remark-request/RemarkForm.vue';
import RequestHistory from '../../components/student/remark-request/RequestHistory.vue';
import studentsApi from "../../api/students";  // API calls

export default {
  name: 'RemarkRequests',
  components: {
    InfoBox,
    RemarkForm,
    RequestHistory
  },
  data() {
    return {
      courses: [],    // To hold courses data
      requests: [],   // To hold request history
      studentId: 4,   // Match the PHP hardcoded value
      loading: false,
      error: null
    };
  },
  created() {
    this.fetchCourses();          // Fetch courses data on component mount
    this.fetchRemarkRequests();   // Fetch remark requests data on component mount
  },
  methods: {
    // Fetch all courses with components for the student (NEW METHOD)
    async fetchCourses() {
      this.loading = true;
      this.error = null;
      
      try {
        console.log('Fetching courses with components for student:', this.studentId);
        //  API method that to fetch course of students includes the components
        const response = await studentsApi.getCoursesWithComponents(this.studentId);
        console.log('API Response:', response);
        
        this.courses = response.data || [];
        console.log('Courses loaded:', this.courses);
        
        if (this.courses.length === 0) {
          this.error = 'No courses found. Please contact your administrator.';
        } else {
          // Verify that components are loaded
          this.courses.forEach(course => {
            console.log(`Course ${course.name} has ${course.components?.length || 0} components`);
          });
        }
      } catch (error) {
        console.error('Error fetching courses with components:', error);
        this.error = error.message || 'Failed to load courses';
        this.courses = [];
      } finally {
        this.loading = false;
      }
    },

    // Fetch all remark requests for the student
    async fetchRemarkRequests() {
      try {
        const response = await studentsApi.getRemarkRequests(this.studentId);
        this.requests = response.data || [];
      } catch (error) {
        console.error('Error fetching remark requests:', error);
        // Don't show error for requests as it's less critical
      }
    },

    async submitRequest(remarkData) {
      try {
        console.log('Submitting remark request:', remarkData);
        
        // Send the data to the backend API for saving the remark request
        const response = await studentsApi.submitRemarkRequest(
          this.studentId, 
          remarkData.courseId, 
          remarkData
        );
        console.log('Remark request submitted:', response.data);
        
        // After successful submission, refresh the request history
        this.fetchRemarkRequests();
        
        // You might want to show a success message here
        alert('Remark request submitted successfully!');
        
      } catch (error) {
        console.error('Error submitting remark request:', error);
        alert('Failed to submit remark request. Please try again.');
      }
    }
  }
};
</script>

<style scoped>
.container {
  max-width: 2000px;
  margin: 0 auto;
  padding: 30px;
}

h1 {
  font-size: xx-large;
  font-weight: 700;
  color: #1e293b;
}
</style>