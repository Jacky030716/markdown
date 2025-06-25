<template>
  <div class="bg-gray-100 min-h-screen font-sans p-4 sm:p-6 lg:p-8">
    <div
      class="max-w-screen-xxl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden"
    >
      <div
        class="flex flex-col lg:flex-row justify-between items-start lg:items-center p-6 md:p-10 gap-5 border-b border-gray-200"
      >
        <h1 class="text-3xl font-semibold text-slate-800">Course Marks</h1>
        <CourseSelector
          :courses="allCourses"
          :loading="coursesLoading"
          @course-selected="handleCourseSelection"
        />
      </div>

      <div class="px-6 md:px-10 py-10">
        <div
          v-if="coursesLoading"
          class="flex justify-center items-center p-10 text-gray-500"
        >
          <p>Loading courses...</p>
        </div>

        <div v-else-if="!selectedCourse" class="p-10">
          <p class="text-gray-600 text-center py-8">
            Please select a course to view your marks and progress.
          </p>
        </div>

        <div v-else>
          <div
            v-if="marksLoading"
            class="flex justify-center items-center p-10 text-gray-500"
          >
            <p>Loading course data...</p>
          </div>

          <div v-else-if="!marksLoading && selectedCourse">
            <ProgressSection :progressData="progressData" />

            <div class="mt-10 flex flex-col gap-10">
              <div class="grid grid-cols-1 lg:grid-cols-[3fr_2fr] gap-10">
                <MarksBreakdown
                  :marksData="marksData"
                  :studentId="studentId"
                  :courseId="selectedCourse.id"
                />
                <PerformanceAnalytics
                  :analyticsData="analyticsData"
                  :loading="analyticsLoading"
                  :error="analyticsError"
                />
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-[4fr_6fr] gap-10">
                <ClassRanking
                  :studentId="studentId"
                  :courseId="selectedCourse.id"
                />
                <PerformanceComparison
                  :analyticsData="analyticsData"
                  :loading="analyticsLoading"
                  :error="analyticsError"
                />
              </div>

              <div>
                <WhatIfSimulator
                  :marksData="marksData"
                  :loading="marksLoading"
                  :error="error"
                />
              </div>
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

    // Analytics data states
    const analyticsData = ref(null);
    const analyticsLoading = ref(false);
    const analyticsError = ref(null);

    // Student ID - hardcoded for now since login isn't implemented
    const studentId = ref(4);

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

    // const currentMarks = ref({
    //   total: 94.8,
    //   weight: 70,
    // });

    // const remainingWeight = ref({
    //   quiz1: 30,
    //   assignment2: 16,
    // });

    // const quiz1Score = ref(85);
    // const assignment2Score = ref(90);

    // Computed property for progress data
    const progressData = computed(() => {
      if (!marksData.value || marksData.value.length === 0) {
        return {
          "Components Completed": { value: "0/0", label: "" },
          "Total Weight Completed": { value: "0/100", label: "" },
          "Current Total Mark": { value: "0%", label: "" },
        };
      }

      const totalComponents = marksData.value.length;
      const completedComponents = marksData.value.filter(
        (item) => item.mark !== null && item.mark !== undefined
      ).length;

      const totalWeight = marksData.value.reduce(
        (sum, item) => sum + parseFloat(item.weight || 0),
        0
      );

      const completedWeight = marksData.value
        .filter((item) => item.mark !== null && item.mark !== undefined)
        .reduce((sum, item) => sum + parseFloat(item.weight || 0), 0);

      // Calculate weighted total score
      let weightedSum = 0;
      marksData.value.forEach((item) => {
        if (item.mark !== null && item.mark !== undefined) {
          const percentage =
            (parseFloat(item.mark) / parseFloat(item.max_mark)) * 100;
          const weightedScore = (percentage * parseFloat(item.weight)) / 100;
          weightedSum += weightedScore;
        }
      });

      return {
        "Components Completed": {
          value: `${completedComponents}/${totalComponents}`,
          label: "",
        },
        "Total Weight Completed": {
          value: `${completedWeight.toFixed(0)}/${totalWeight.toFixed(0)}`,
          label: "",
        },
        "Current Total Mark": {
          value: `${weightedSum.toFixed(2)}%`,
          label: "",
        },
      };
    });

    // Fetch all courses
    const fetchCourses = async () => {
      coursesLoading.value = true;
      error.value = null;
      try {
        const response = await studentsApi.getAllCourses(studentId.value);
        if (response.status === "success" && response.data) {
          allCourses.value = response.data;
        } else {
          console.warn(
            "No courses found or unexpected response structure:",
            response
          );
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
    const fetchMarks = async (courseId) => {
      if (!courseId) {
        marksData.value = [];
        return;
      }

      marksLoading.value = true;
      error.value = null;

      try {
        console.log(
          `Fetching marks for student ${studentId.value}, course ${courseId}`
        );
        const response = await studentsApi.getMarks(studentId.value, courseId);

        if (response.status === "success" && response.data) {
          marksData.value = response.data;
          console.log("Marks data loaded:", response.data);
        } else {
          console.warn(
            "No marks found or unexpected response structure:",
            response
          );
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
    const fetchAnalytics = async (courseId) => {
      if (!courseId) {
        analyticsData.value = null;
        analyticsLoading.value = false; // Make sure to set loading to false
        return;
      }

      analyticsLoading.value = true;
      analyticsError.value = null;

      try {
        console.log(
          `Fetching analytics for student ${studentId.value}, course ${courseId}`
        );
        const response = await studentsApi.getAnalytics(
          studentId.value,
          courseId
        );

        if (response.status === "success" && response.data) {
          analyticsData.value = response.data;
          console.log("Analytics data loaded:", response.data);
        } else {
          console.warn(
            "No analytics found or unexpected response structure:",
            response
          );
          analyticsData.value = null;
        }
      } catch (err) {
        console.error("Error fetching analytics:", err);
        analyticsError.value =
          err.message || "Failed to load analytics. Please try again.";
        analyticsData.value = null;
      } finally {
        // IMPORTANT: Always set loading to false in finally block
        analyticsLoading.value = false;
        console.log(
          "Analytics loading finished, loading state:",
          analyticsLoading.value
        );
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

      // Fetch new data if course is selected
      if (course && course.id) {
        // Fetch both marks and analytics data concurrently
        await Promise.all([fetchMarks(course.id), fetchAnalytics(course.id)]);
      }
    };

    // Watch for changes in selectedCourse to ensure data consistency
    watch(
      selectedCourse,
      (newCourse, oldCourse) => {
        console.log("Selected course changed:", { oldCourse, newCourse });

        // If course changed or no course selected, ensure data is cleared
        if (
          !newCourse ||
          (oldCourse && newCourse && oldCourse.id !== newCourse.id)
        ) {
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
      coursesLoading,
      marksLoading,
      error,
      studentId,
      // Analytics data
      analyticsData,
      analyticsLoading,
      analyticsError,
      handleCourseSelection,
    };
  },
};
</script>
