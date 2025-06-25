<template>
  <div class="bg-gray-100 min-h-screen font-sans">
    <div class="header">
      <h1>Course Marks</h1>
      <CourseSelector
        :courses="allCourses"
        :loading="coursesLoading"
        @course-selected="handleCourseSelection"
      />
    </div>
    <div class="main-content">
      <!-- Show loading state -->
      <div v-if="coursesLoading" class="loading-container">
        <p>Loading courses...</p>
      </div>
      
      <!-- Show message when no course is selected -->
      <div v-else-if="!selectedCourse" class="no-selection-container">
        <p class="text-gray-600 text-center py-8">Please select a course to view your marks and progress.</p>
      </div>
      
      <!-- Show course content when course is selected -->
      <div v-else>
        <!-- Loading state for marks -->
        <div v-if="marksLoading" class="loading-container">
          <p>Loading course data...</p>
        </div>
        
        <!-- Course content - only show when not loading and has data -->
        <div v-else-if="!marksLoading && selectedCourse">
          <ProgressSection :progressData="progressData" />

          <!-- Wrapper for the new grid layout -->
          <div class="grid-wrapper">
            <div class="grid-row grid-60-40">
              <MarksBreakdown :marksData="marksData" />
              <PerformanceAnalytics :marksData="marksData" />
            </div>

            <div class="grid-row grid-30-70">
              <ClassRanking />
              <PerformanceComparison :comparisonData="comparisonData" />
            </div>

            <!-- This component will take the full width -->
            <div class="full-width-component">
              <!-- Passing the necessary data to WhatIfSimulator.vue -->
              <WhatIfSimulator
                :currentMarks="currentMarks"
                :remainingWeight="remainingWeight"
                :quiz1Score="quiz1Score"
                :assignment2Score="assignment2Score"
                @update:quiz1Score="quiz1Score = $event"
                @update:assignment2Score="assignment2Score = $event"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from "vue";
import studentsApi from "../../api/students"; // Adjust path if needed

// Assuming these are valid paths to your components
import CourseSelector from "../../components/student/course-marks/CourseSelector.vue";
import PerformanceAnalytics from "../../components/student/course-marks/PerformanceAnalytics.vue";
import ProgressSection from "../../components/student/course-marks/ProgressSection.vue";
import MarksBreakdown from "../../components/student/course-marks/MarksBreakdown.vue";
import PerformanceComparison from "../../components/student/course-marks/PerformanceComparison.vue";
import ClassRanking from "../../components/student/course-marks/ClassRanking.vue";
import WhatIfSimulator from "../../components/student/course-marks/WhatIfSimulator.vue";

export default {
  name: "CourseMarks",
  components: {
    CourseSelector,
    ProgressSection,
    MarksBreakdown,
    PerformanceComparison,
    ClassRanking,
    PerformanceAnalytics,
    WhatIfSimulator,
  },
  setup() {
    // Reactive states
    const allCourses = ref([]);
    const selectedCourse = ref(null);
    const marksData = ref([]);
    const coursesLoading = ref(false);
    const marksLoading = ref(false);
    const error = ref(null);
    
    // Student ID - hardcoded for now since login isn't implemented
    const studentId = ref(5);

    // Dummy data for components that don't have API endpoints yet
    const comparisonData = ref([
      {
        student: "Student A",
        quiz1: "95%",
        assignment1: "92%",
        lab1: "100%",
        quiz2: "90%",
        final: "95%",
        total: "94.2%",
      },
      {
        student: "You",
        quiz1: "100%",
        assignment1: "100%",
        lab1: "100%",
        quiz2: "100%",
        final: "88%",
        total: "94.8%",
      },
      {
        student: "Student C",
        quiz1: "85%",
        assignment1: "88%",
        lab1: "95%",
        quiz2: "80%",
        final: "82%",
        total: "85.1%",
      },
      {
        student: "Student D",
        quiz1: "85%",
        assignment1: "88%",
        lab1: "95%",
        quiz2: "80%",
        final: "82%",
        total: "85.1%",
      },
    ]);

    const currentMarks = ref({
      total: 94.8,
      weight: 70,
    });

    const remainingWeight = ref({
      quiz1: 30,
      assignment2: 16,
    });

    const quiz1Score = ref(85);
    const assignment2Score = ref(90);

    // Computed property for progress data
    const progressData = computed(() => {
      if (!marksData.value || marksData.value.length === 0) {
        return {
          "Components Completed": { value: "0/0", label: "Assessments" },
          "Total Weight Completed": { value: "0/100", label: "Percentage" },
          "Current Total Mark": { value: "0%", label: "Average Score" }
        };
      }

      const totalComponents = marksData.value.length;
      const completedComponents = marksData.value.filter(item => item.mark !== null).length;
      
      const totalWeight = marksData.value.reduce((sum, item) => sum + parseFloat(item.weight), 0);
      const completedWeight = marksData.value
        .filter(item => item.mark !== null)
        .reduce((sum, item) => sum + parseFloat(item.weight), 0);
      
      // Calculate weighted average of completed components
      let weightedSum = 0;
      let totalWeightForCompleted = 0;
      
      marksData.value.forEach(item => {
        if (item.mark !== null) {
          const percentage = (parseFloat(item.mark) / parseFloat(item.max_mark)) * 100;
          weightedSum += percentage * parseFloat(item.weight);
          totalWeightForCompleted += parseFloat(item.weight);
        }
      });
      
      const currentAverage = totalWeightForCompleted > 0 ? (weightedSum / totalWeightForCompleted).toFixed(1) : 0;

      return {
        "Components Completed": { 
          value: `${completedComponents}/${totalComponents}`, 
          label: "Assessments" 
        },
        "Total Weight Completed": { 
          value: `${completedWeight.toFixed(0)}/${totalWeight.toFixed(0)}`, 
          label: "Percentage" 
        },
        "Current Total Mark": { 
          value: `${currentAverage}%`, 
          label: "Average Score" 
        }
      };
    });

    // Fetch all courses
    const fetchCourses = async () => {
      coursesLoading.value = true;
      error.value = null;
      
      try {
        const response = await studentsApi.getAllCourses(studentId.value);
        
        if (response.status === 'success' && response.data) {
          allCourses.value = response.data;
        } else {
          console.warn('No courses found or unexpected response structure:', response);
          allCourses.value = [];
        }
      } catch (err) {
        console.error('Error fetching courses:', err);
        error.value = 'Failed to load courses. Please try again.';
        allCourses.value = [];
      } finally {
        coursesLoading.value = false;
      }
    };

    // Fetch marks for selected course
    const fetchMarks = async (courseId) => {
      if (!courseId) return;
      
      marksLoading.value = true;
      error.value = null;
      
      try {
        const response = await studentsApi.getMarks(studentId.value, courseId);
        
        if (response.status === 'success' && response.data) {
          marksData.value = response.data;
        } else {
          console.warn('No marks found or unexpected response structure:', response);
          marksData.value = [];
        }
      } catch (err) {
        console.error('Error fetching marks:', err);
        error.value = 'Failed to load marks. Please try again.';
        marksData.value = [];
      } finally {
        marksLoading.value = false;
      }
    };

    // Handle course selection
    const handleCourseSelection = async (course) => {
      // Clear previous data immediately when switching courses
      marksData.value = [];
      
      selectedCourse.value = course;
      console.log("Selected Course:", selectedCourse.value);
      
      if (course && course.id) {
        await fetchMarks(course.id);
      }
    };

    // Watch for changes in selectedCourse to ensure data is cleared
    watch(selectedCourse, (newCourse, oldCourse) => {
      // If the course actually changed, clear the marks data
      if (oldCourse && newCourse && oldCourse.id !== newCourse.id) {
        marksData.value = [];
      }
      // If no course is selected, clear the data
      if (!newCourse) {
        marksData.value = [];
      }
    });

    // Fetch courses on component mount
    onMounted(() => {
      fetchCourses();
    });

    return {
      allCourses,
      selectedCourse,
      marksData,
      progressData,
      comparisonData,
      currentMarks,
      remainingWeight,
      quiz1Score,
      assignment2Score,
      coursesLoading,
      marksLoading,
      error,
      handleCourseSelection,
    };
  },
};
</script>

<style scoped>
.container {
  max-width: 2000px;
  margin: 0 auto;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  padding: 20px;
}

h1 {
  font-size: xx-large;
  font-weight: 700;
  color: #1e293b;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 40px;
  flex-wrap: wrap;
}

.main-content {
  padding: 0 40px 40px 40px;
}

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
  color: #6b7280;
  font-size: 16px;
}

.no-selection-container {
  padding: 40px;
}

.grid-wrapper {
  margin-top: 40px;
}

.grid-row {
  display: grid;
  gap: 40px;
}

.grid-row + .grid-row,
.grid-row + .full-width-component {
  margin-top: 40px;
}

.grid-60-40 {
  grid-template-columns: 3fr 2fr;
}

.grid-30-70 {
  grid-template-columns: 3fr 7fr;
}

@media (max-width: 1024px) {
  .grid-60-40,
  .grid-30-70 {
    grid-template-columns: 1fr;
  }

  .header {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
  }
}

@media (max-width: 768px) {
  .header,
  .main-content {
    padding-left: 20px;
    padding-right: 20px;
  }
}
</style>