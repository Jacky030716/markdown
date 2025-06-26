<template>
  <div class="bg-gray-100 min-h-screen font-sans p-4 sm:p-6 lg:p-8">
    <div class="max-w-screen-xxl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">
      <!-- Header Section -->
      <div
        class="flex flex-col lg:flex-row justify-between items-start lg:items-center p-6 md:p-10 gap-5 border-b border-gray-200">
        <div>
          <h1 class="text-3xl font-semibold text-slate-800">Student Analytics</h1>
          <p class="text-gray-600 mt-2">Analyze your advisees' academic performance and progress</p>
        </div>

        <!-- Student Selector -->
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-end">
          <div class="min-w-[250px]">
            <label for="student" class="block text-sm font-medium text-gray-700 mb-1">Select Student</label>
            <select id="student" v-model="selectedStudentId" @change="handleStudentSelection"
              class="mt-1 block w-full pl-3 pr-10 py-4 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm"
              :disabled="adviseesLoading || advisees.length === 0">
              <option :value="null">
                {{ adviseesLoading ? 'Loading students...' :
                  advisees.length === 0 ? '-- No Students Available --' :
                    '-- Select a Student --' }}
              </option>
              <option v-for="advisee in advisees" :key="advisee.id" :value="advisee.id">
                {{ advisee.name }} ({{ advisee.matric_no }})
              </option>
            </select>
          </div>

          <!-- Course Selector - Only show when student is selected -->
          <CourseSelector v-if="selectedStudentId && allCourses.length > 0" :courses="allCourses"
            :loading="coursesLoading" @course-selected="handleCourseSelection" />
        </div>
      </div>

      <!-- Main Content Section -->
      <div class="px-6 md:px-10 py-10">
        <!-- Loading States -->
        <div v-if="adviseesLoading" class="flex justify-center items-center p-10 text-gray-500">
          <p>Loading students...</p>
        </div>

        <!-- No Student Selected -->
        <div v-else-if="!selectedStudentId" class="p-10">
          <div class="text-center py-8">
            <div class="text-6xl mb-4">📊</div>
            <p class="text-gray-600 text-lg">Please select a student to view their analytics and course performance.</p>
            <p class="text-gray-500 text-sm mt-2" v-if="advisees.length === 0">No advisees found for your account.</p>
          </div>
        </div>

        <!-- Student Selected but Loading Courses -->
        <div v-else-if="coursesLoading" class="flex justify-center items-center p-10 text-gray-500">
          <p>Loading {{ selectedStudentName }}'s courses...</p>
        </div>

        <!-- Student Selected but No Course Selected -->
        <div v-else-if="selectedStudentId && !selectedCourse" class="p-10">
          <div class="text-center py-8">
            <div class="text-6xl mb-4">📚</div>
            <p class="text-gray-600 text-lg">
              {{ allCourses.length === 0 ?
                `No courses found for ${selectedStudentName}.` :
                `Please select a course to view ${selectedStudentName}'s performance.` }}
            </p>
          </div>
        </div>

        <!-- Course Selected - Show Analytics -->
        <div v-else-if="selectedCourse">
          <div v-if="marksLoading" class="flex justify-center items-center p-10 text-gray-500">
            <p>Loading course data for {{ selectedStudentName }}...</p>
          </div>

          <div v-else>
            <!-- Student and Course Info Banner -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-6 mb-8">
              <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                  <h2 class="text-xl font-semibold text-gray-800">
                    {{ selectedStudentName }} - {{ selectedCourse.course_code }}
                  </h2>
                  <p class="text-gray-600">{{ selectedCourse.course_name }}</p>
                  <p class="text-sm text-gray-500">
                    {{ selectedCourse.academic_year }} • {{ selectedCourse.semester }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-500">Student ID</p>
                  <p class="font-mono text-lg text-gray-800">{{ selectedStudentId }}</p>
                </div>
              </div>
            </div>

            <!-- Analytics Components -->
            <div class="mt-10 flex flex-col gap-10">
              <div class="grid grid-cols-1 lg:grid-cols-[3fr_2fr] gap-10">
                <MarksBreakdown :marksData="marksData" :studentId="selectedStudentId" :courseId="selectedCourse.id" />
                <PerformanceAnalytics :analyticsData="analyticsData" :loading="analyticsLoading"
                  :error="analyticsError" />
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-[4fr_6fr] gap-10">
                <ClassRanking :studentId="selectedStudentId" :courseId="selectedCourse.id" />
                <PerformanceComparison :analyticsData="analyticsData" :loading="analyticsLoading"
                  :error="analyticsError" />
              </div>
            </div>

            <!-- Request History -->
            <div class="mt-10 space-y-8">
              <!-- Pending Requests Section -->
              <div>
                <div class="mb-4">
                  <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-clock mr-2 text-yellow-500"></i>
                    Pending Requests
                  </h3>
                </div>
                <RequestHistory key="pending-requests" :requests="pendingRequests" />
              </div>

              <!-- Completed Requests Section -->
              <div>
                <div class="mb-4">
                  <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500"></i>
                    Completed Requests
                  </h3>
                </div>
                <RequestHistory key="completed-requests" :requests="completedRequests" />
              </div>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-10 text-center">
          <div class="text-red-600 bg-red-50 border border-red-200 rounded-lg p-6">
            <p class="font-medium">{{ error }}</p>
            <button @click="retryLoadData"
              class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
              Retry
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from "vue";
import advisorsApi from "../../api/advisors"; // Adjust path if needed
import studentsApi from "../../api/students"; // Adjust path if needed

// Import the same components used in CourseMark.vue
import CourseSelector from "../../components/student/course-marks/CourseSelector.vue";
import PerformanceAnalytics from "../../components/student/course-marks/PerformanceAnalytics.vue";
import MarksBreakdown from "../../components/student/course-marks/MarksBreakdown.vue";
import PerformanceComparison from "../../components/student/course-marks/PerformanceComparison.vue";
import ClassRanking from "../../components/student/course-marks/ClassRanking.vue";
import RequestHistory from '../../components/student/remark-request/RequestHistory.vue';

export default {
  name: "Analytics",
  components: {
    CourseSelector,
    MarksBreakdown,
    PerformanceComparison,
    ClassRanking,
    PerformanceAnalytics,
    RequestHistory
  },
  setup() {
    // Advisor and advisees states
    const currentAdvisorId = ref(1); // This should come from your authentication context
    const advisees = ref([]);
    const adviseesLoading = ref(false);
    const selectedStudentId = ref(null);
    const requests = ref([]);

    // Course and marks states (similar to CourseMark.vue)
    const allCourses = ref([]);
    const selectedCourse = ref(null);
    const marksData = ref([]);
    const coursesLoading = ref(false);
    const marksLoading = ref(false);
    const error = ref(null);

    // Analytics data states
    const analyticsData = ref(null);
    const analyticsLoading = ref(false);
    const analyticsError = ref(null);

    // Computed properties
    const selectedStudentName = computed(() => {
      if (!selectedStudentId.value || !advisees.value.length) return '';
      const student = advisees.value.find(advisee => advisee.id === selectedStudentId.value);
      return student ? student.name : '';
    });

    const pendingRequests = computed(() => {
      return requests.value.filter(request => request.status === 'pending');
    });

    const completedRequests = computed(() => {
      return requests.value.filter(request => request.status !== 'pending');
    });


    const fetchRemarkRequests = async (studentId) => {
      if (!studentId) {
        requests.value = [];
        return;
      }

      try {
        const response = await studentsApi.getRemarkRequests(studentId);
        requests.value = response.data || [];
      } catch (error) {
        console.error('Error fetching remark requests:', error);
        // Don't show error for requests as it's less critical
      }
    };

    // Fetch advisees for dropdown
    const fetchAdviseesForDropdown = async () => {
      adviseesLoading.value = true;
      error.value = null;
      try {
        const response = await advisorsApi.getAdviseesForDropdown(currentAdvisorId.value);
        if (response.status === 'success') {
          advisees.value = response.data;
          console.log('Advisees loaded for dropdown:', advisees.value);

        } else {
          console.error('Failed to fetch advisees for dropdown:', response.message);
          advisees.value = [];
          error.value = response.message || 'Failed to fetch students.';
        }
      } catch (err) {
        console.error('Error fetching advisees dropdown:', err);
        error.value = 'Failed to load students. Please try again.';
        advisees.value = [];
      } finally {
        adviseesLoading.value = false;
      }
    };

    // Fetch all courses for selected student
    const fetchCourses = async (studentId) => {
      if (!studentId) {
        allCourses.value = [];
        return;
      }

      coursesLoading.value = true;
      error.value = null;
      try {
        const response = await studentsApi.getAllCourses(studentId);
        if (response.status === "success" && response.data) {
          allCourses.value = response.data;
        } else {
          console.warn("No courses found or unexpected response structure:", response);
          allCourses.value = [];
        }
      } catch (err) {
        console.error("Error fetching courses:", err);
        error.value = "Failed to load courses. Please try again.";
        allCourses.value = [];
      } finally {
        coursesLoading.value = false;
      }
    };

    // Fetch marks for selected course
    const fetchMarks = async (studentId, courseId) => {
      if (!studentId || !courseId) {
        marksData.value = [];
        return;
      }

      marksLoading.value = true;
      error.value = null;

      try {
        console.log(`Fetching marks for student ${studentId}, course ${courseId}`);
        const response = await studentsApi.getMarks(studentId, courseId);

        if (response.status === "success" && response.data) {
          marksData.value = response.data;
          console.log("Marks data loaded:", response.data);
        } else {
          console.warn("No marks found or unexpected response structure:", response);
          marksData.value = [];
        }
      } catch (err) {
        console.error("Error fetching marks:", err);
        error.value = "Failed to load marks. Please try again.";
        marksData.value = [];
      } finally {
        marksLoading.value = false;
      }
    };

    // Fetch analytics data for selected course
    const fetchAnalytics = async (studentId, courseId) => {
      if (!studentId || !courseId) {
        analyticsData.value = null;
        analyticsLoading.value = false;
        return;
      }

      analyticsLoading.value = true;
      analyticsError.value = null;

      try {
        console.log(`Fetching analytics for student ${studentId}, course ${courseId}`);
        const response = await studentsApi.getAnalytics(studentId, courseId);

        if (response.status === "success" && response.data) {
          analyticsData.value = response.data;
          console.log("Analytics data loaded:", response.data);
        } else {
          console.warn("No analytics found or unexpected response structure:", response);
          analyticsData.value = null;
        }
      } catch (err) {
        console.error("Error fetching analytics:", err);
        analyticsError.value = err.message || "Failed to load analytics. Please try again.";
        analyticsData.value = null;
      } finally {
        analyticsLoading.value = false;
        console.log("Analytics loading finished, loading state:", analyticsLoading.value);
      }
    };

    // Handle student selection
    const handleStudentSelection = async () => {
      console.log("Student selection changed:", selectedStudentId.value);

      // Clear all previous data
      allCourses.value = [];
      selectedCourse.value = null;
      marksData.value = [];
      analyticsData.value = null;
      analyticsError.value = null;
      requests.value = []; // Clear requests

      // Fetch new data for selected student
      if (selectedStudentId.value) {
        await Promise.all([
          fetchCourses(selectedStudentId.value),
          fetchRemarkRequests(selectedStudentId.value)
        ]);
      }
    };

    // Handle course selection
    const handleCourseSelection = async (course) => {
      console.log("Course selection changed:", course);

      // Always clear previous data first
      marksData.value = [];
      analyticsData.value = null;
      analyticsError.value = null;

      // Update selected course
      selectedCourse.value = course;

      // Fetch new data if course is selected and student is selected
      if (course && course.id && selectedStudentId.value) {
        // Fetch both marks and analytics data concurrently
        await Promise.all([
          fetchMarks(selectedStudentId.value, course.id),
          fetchAnalytics(selectedStudentId.value, course.id)
        ]);
      }
    };

    // Retry function for error states
    const retryLoadData = async () => {
      error.value = null;
      if (selectedStudentId.value) {
        await fetchCourses(selectedStudentId.value);
      } else {
        await fetchAdviseesForDropdown();
      }
    };

    // Watch for changes in selectedStudentId to ensure data consistency
    watch(
      selectedStudentId,
      (newStudentId, oldStudentId) => {
        console.log("Selected student ID changed:", { oldStudentId, newStudentId });

        // If student changed, ensure all course-related data is cleared
        if (oldStudentId !== newStudentId) {
          allCourses.value = [];
          selectedCourse.value = null;
          marksData.value = [];
          analyticsData.value = null;
          analyticsError.value = null;
        }
      },
      { immediate: true }
    );

    // Watch for changes in selectedCourse to ensure data consistency
    watch(
      selectedCourse,
      (newCourse, oldCourse) => {
        console.log("Selected course changed:", { oldCourse, newCourse });

        // If course changed or no course selected, ensure data is cleared
        if (!newCourse || (oldCourse && newCourse && oldCourse.id !== newCourse.id)) {
          if (marksData.value.length > 0 || analyticsData.value !== null) {
            console.log("Clearing data due to course change");
            marksData.value = [];
            analyticsData.value = null;
            analyticsError.value = null;
          }
        }
      },
      { immediate: true }
    );

    // Fetch advisees on component mount
    onMounted(() => {
      fetchAdviseesForDropdown();
    });

    return {
      // Advisor and advisees data
      currentAdvisorId,
      advisees,
      adviseesLoading,
      selectedStudentId,
      selectedStudentName,

      // Course and marks data (same as CourseMark.vue)
      allCourses,
      selectedCourse,
      marksData,
      coursesLoading,
      marksLoading,
      error,

      // Analytics data
      analyticsData,
      analyticsLoading,
      analyticsError,

      // Methods
      handleStudentSelection,
      handleCourseSelection,
      retryLoadData,

      requests,

      pendingRequests,
      completedRequests,
    };
  }
};
</script>

<style scoped>
/* Additional custom styles if needed */
.max-w-screen-xxl {
  max-width: 1536px;
}
</style>
