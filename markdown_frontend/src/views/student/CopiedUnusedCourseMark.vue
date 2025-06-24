<template>
    <div class="space-y-6 p-6 bg-gray-100 min-h-screen font-sans">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Student Dashboard</h1>
  
      <!-- Course Filter Section -->
      <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <CourseFilterDashboard
          :courses="allCourses"
          @course-selected="handleCourseSelection"
        />
      </div>
  
      <!-- Loading state -->
      <div v-if="loading" class="text-center py-8">
        <p class="text-gray-600">Loading...</p>
      </div>
  
      <!-- Display course overview if a course is selected -->
      <div v-if="selectedCourse && !loading" class="space-y-8">
        <div class="bg-white p-6 shadow-md rounded-lg">
          <h2 class="text-xl font-semibold text-gray-800">Course Overview</h2>
          <p class="text-gray-600 mt-2">Course Code: {{ selectedCourse.course_code }}</p>
          <p class="text-gray-600">Course Name: {{ selectedCourse.course_name }}</p>
          <p class="text-gray-600">Semester: {{ selectedCourse.semester }}</p>
          <p class="text-gray-600">Academic Year: {{ selectedCourse.academic_year }}</p>
          <p class="text-gray-600">Credit Hours: {{ selectedCourse.credit_hours }}</p>
        </div>
  
        <!-- Marks Summary Section -->
        <div v-if="marksData.length > 0" class="bg-white p-6 shadow-md rounded-lg">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Marks Summary</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Component</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mark</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Mark</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="mark in marksData" :key="mark.name">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ mark.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ mark.type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ mark.mark || 'N/A' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ mark.max_mark }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ mark.grade || 'N/A' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Total Marks Display -->
          <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <p class="text-lg font-semibold text-blue-800">
              Total Marks: {{ totalMarks.toFixed(2) }} / {{ maxTotalMarks.toFixed(2) }}
            </p>
            <p class="text-sm text-blue-600">
              Percentage: {{ totalMarks > 0 ? ((totalMarks / maxTotalMarks) * 100).toFixed(1) : 0 }}%
            </p>
          </div>
        </div>
  
        <!-- No marks message -->
        <div v-else class="bg-white p-6 shadow-md rounded-lg">
          <p class="text-gray-600 text-center">No marks available for this course.</p>
        </div>
      </div>
  
      <!-- No course selected message -->
      <div v-if="!selectedCourse && !loading && allCourses.length > 0" class="bg-white p-6 shadow-md rounded-lg">
        <p class="text-gray-600 text-center">Please select a course to view your marks.</p>
      </div>
  
      <!-- No courses available message -->
      <div v-if="!loading && allCourses.length === 0" class="bg-white p-6 shadow-md rounded-lg">
        <p class="text-gray-600 text-center">No courses available.</p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, computed } from "vue";
  import studentsApi from "../../api/students"; // Adjust path if needed
  
  // Importing the course filter component
  import CourseFilterDashboard from "../../components/student/course-mark/CourseFilterDashboard.vue";
  
  // Reactive state
  const allCourses = ref([]);
  const selectedCourse = ref(null);
  const marksData = ref([]);
  const loading = ref(false);
  
  // Mock Student ID (replace with actual ID from authentication)
  const studentId = 1;
  
  // Computed properties for totals
  const totalMarks = computed(() => {
    return marksData.value.reduce((sum, component) => {
      return sum + (parseFloat(component.mark) || 0);
    }, 0);
  });
  
  const maxTotalMarks = computed(() => {
    return marksData.value.reduce((sum, component) => {
      return sum + (parseFloat(component.max_mark) || 0);
    }, 0);
  });
  
  const fetchAllCourses = async () => {
    try {
      loading.value = true;
      const response = await studentsApi.getAllCourses(studentId);
      
      // Check if response has the expected structure
      if (response && response.data) {
        allCourses.value = response.data;
      } else {
        console.error("Unexpected response structure:", response);
        allCourses.value = [];
      }
    } catch (error) {
      console.error("Error fetching courses:", error);
      allCourses.value = [];
    } finally {
      loading.value = false;
    }
  };
  
  // Handle course selection
  const handleCourseSelection = async (course) => {
    selectedCourse.value = course;
    
    if (course) {
      await fetchMarksForSelectedCourse(course.id);
    } else {
      // Clear marks when no course is selected
      marksData.value = [];
    }
  };
  
  // Fetch marks for the selected course
  const fetchMarksForSelectedCourse = async (courseId) => {
    try {
      loading.value = true;
      const response = await studentsApi.getMarks(studentId, courseId);
      
      // Check if response has the expected structure
      if (response && response.data) {
        marksData.value = response.data;
      } else {
        console.error("Unexpected marks response structure:", response);
        marksData.value = [];
      }
    } catch (error) {
      console.error("Error fetching marks for selected course:", error);
      marksData.value = [];
    } finally {
      loading.value = false;
    }
  };
  
  // Fetch all courses when component is mounted
  onMounted(() => {
    fetchAllCourses();
  });
  </script>
  
  <style scoped>
  /* Add your dashboard styles here if needed */
  </style>