<template>
  <div class="max-w-[1440px] mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Show loading spinner while data is being fetched -->
    <div v-if="isLoading" class="flex items-center justify-center h-screen">
      <div class="flex items-center space-x-2">
        <Loader2Icon class="animate-spin text-gray-500" />
        <span class="text-gray-500 text-lg">Loading...</span>
      </div>
    </div>

    <!-- Show course mark management once data is loaded -->
    <CourseMarkManagement v-else :courses="courses" @refresh-data="refreshData" />
  </div>
</template>

<script>
import CourseMarkManagement from '../../components/lecturer/student-marks/CourseMarkManagement.vue';
import lecturersApi from '../../api/lecturers';
import { Loader2Icon } from 'lucide-vue-next';

export default {
  name: 'ManageStudentsMark',
  components: {
    Loader2Icon,
    CourseMarkManagement
  },
  data() {
    return {
      students: [],
      courses: [],
      isLoading: true
    };
  },
  async mounted() {
    await this.loadData();
  },
  methods: {
    async loadData() {
      try {
        this.isLoading = true;

        // Fetch data from API
        const [studentsResponse, coursesResponse] = await Promise.all([
          lecturersApi.getStudentMarks(1, 1),
          lecturersApi.getTaughtCourses(1)
        ]);

        this.students = studentsResponse.data;
        this.courses = coursesResponse.data;
      } catch (error) {
        console.error("Error fetching data:", error);
        // You might want to show an error message to the user
      } finally {
        this.isLoading = false;
      }
    },
    async refreshData() {
      await this.loadData();
    }
  }
};
</script>