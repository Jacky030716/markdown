<template>
  <div class="p-6 bg-gray-100 min-h-screen font-sans">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Lecturer Dashboard</h1>

    <!-- Course Selection Filter (Header Removed) -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
      <CourseFilterDashboard :courses="allCourses" @course-selected="handleCourseSelection" />
    </div>

    <div v-if="selectedCourse" class="space-y-8">
      <!-- Course Overview Cards -->
      <CourseOverviewCards :course="selectedCourse" />

      <!-- Class Performance Summary (KPIs) -->
      <!-- <ClassPerformanceSummary :summary-data="performanceSummary" /> -->

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="bg-white shadow-md rounded-lg p-6 lg:col-span-2">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Grade Distribution</h2>
          <div class="h-80">
            <GradeDistributionDashboardChart :grades-data="gradeDistribution" />
          </div>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 lg:col-span-2">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Component Averages</h2>
          <div class="h-80">
            <ComponentAveragesChart :components-data="componentAverages" />
          </div>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 col-span-full lg:col-span-1">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Pass/Fail Distribution</h2>
          <div class="h-80">
            <PassFailPieChart :pass-count="performanceSummary.passCount" :fail-count="performanceSummary.failCount" />
          </div>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 lg:col-span-3 col-span-full">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Simplified Student Overview</h2>
            <RouterLink to="/lecturer/analytics">
              <button class="text-sm text-sky-600 hover:text-sky-700 transition-colors cursor-pointer">View Detailed
                Analysis</button>
            </RouterLink>
          </div>
          <SimplifiedStudentOverview :students-data="studentsInSelectedCourse" />
        </div>
      </div>

      <!-- At-Risk Students Quick View -->
      <!-- <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Students Currently At Risk (Current Course)</h2>
        <AtRiskStudentsList :students-data="atRiskStudents" />
      </div> -->
    </div>

    <div v-else class="text-center py-20 text-gray-500 text-xl">
      Please select a course to view the dashboard.
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import lecturersApi from '../../api/lecturers'; // Adjust path if needed

// Import Dashboard Components
import CourseFilterDashboard from '../../components/lecturer/dashboard/CourseFilterDashboard.vue';
import CourseOverviewCards from '../../components/lecturer/dashboard/CourseOverviewCards.vue';
import ClassPerformanceSummary from '../../components/lecturer/dashboard/ClassPerformanceSummary.vue';
import GradeDistributionDashboardChart from '../../components/lecturer/dashboard/GradeDistributionDashboardChart.vue';
import ComponentAveragesChart from '../../components/lecturer/dashboard/ComponentAveragesChart.vue';
import AtRiskStudentsList from '../../components/lecturer/dashboard/AtRiskStudentsList.vue';
import PassFailPieChart from '../../components/lecturer/dashboard/PassFailPieChart.vue';
import SimplifiedStudentOverview from '../../components/lecturer/dashboard/SimplifiedStudentOverview.vue';
import { RouterLink } from 'vue-router';

export default {
  name: 'LecturerDashboard',
  components: {
    CourseFilterDashboard,
    CourseOverviewCards,
    ClassPerformanceSummary,
    GradeDistributionDashboardChart,
    ComponentAveragesChart,
    AtRiskStudentsList,
    PassFailPieChart,
    SimplifiedStudentOverview
  },
  setup() {
    // --- Reactive State ---
    const allCourses = ref([]);
    const selectedCourse = ref(null);
    const studentsInSelectedCourse = ref([]); // Data from students/marks API
    const courseComponents = ref([]); // Derived from student marks for component averages

    // --- Mock Lecturer ID (Replace with actual ID from authentication) ---
    const lecturerId = 1;

    // --- Data Fetching ---
    const fetchAllCourses = async () => {
      try {
        const courses = await lecturersApi.getTaughtCourses(1);
        allCourses.value = courses.data;
        // Automatically select the first course if available
        if (allCourses.value.length > 0) {
          handleCourseSelection(allCourses.value[0]);
        }
      } catch (error) {
        console.error("Error fetching all courses for dashboard:", error);
        // Display user-friendly error
      }
    };

    const fetchStudentDataForCourse = async (courseId) => {
      try {
        const studentDataResponse = await lecturersApi.getStudentMarks(lecturerId, courseId);
        studentsInSelectedCourse.value = studentDataResponse.data || []; // Access the 'data' key

        // Extract and process components for ComponentAveragesChart and other uses
        if (studentsInSelectedCourse.value.length > 0) {
          const firstStudentMarks = studentsInSelectedCourse.value[0].marks;
          const extractedComponents = Object.values(firstStudentMarks).map(markEntry => ({
            component_id: markEntry.component_id,
            component_name: markEntry.component_name,
            max_mark: markEntry.max_mark,
            weight: markEntry.weight
          }));
          courseComponents.value = extractedComponents.sort((a, b) => a.component_id - b.component_id);
        } else {
          courseComponents.value = [];
        }

      } catch (error) {
        console.error(`Error fetching student data for course ${courseId}:`, error);
        studentsInSelectedCourse.value = [];
        courseComponents.value = [];
      }
    };

    // --- Lifecycle Hook ---
    onMounted(() => {
      fetchAllCourses();
    });

    // --- Computed Properties for Dashboard Data ---

    const performanceSummary = computed(() => {
      const totalStudents = studentsInSelectedCourse.value.length;
      if (totalStudents === 0) {
        return {
          totalStudents: 0,
          classAverage: 0,
          passCount: 0,
          failCount: 0,
          highestMark: 0,
          lowestMark: 0,
        };
      }

      const totalMarksSum = studentsInSelectedCourse.value.reduce((sum, s) => sum + (s.totalMark || 0), 0);
      const classAvg = totalMarksSum / totalStudents;
      const passCount = studentsInSelectedCourse.value.filter(s => (s.totalMark || 0) >= 40).length; // Assuming 40% is pass mark
      const failCount = totalStudents - passCount;
      const highestMark = Math.max(...studentsInSelectedCourse.value.map(s => s.totalMark || 0));
      const lowestMark = Math.min(...studentsInSelectedCourse.value.map(s => s.totalMark || 0));

      return {
        totalStudents,
        classAverage: parseFloat(classAvg.toFixed(1)),
        passCount,
        failCount,
        highestMark: parseFloat(highestMark.toFixed(1)),
        lowestMark: parseFloat(lowestMark.toFixed(1)),
      };
    });

    const gradeDistribution = computed(() => {
      const grades = { 'A+': 0, 'A': 0, 'B+': 0, 'B': 0, 'B-': 0, 'C+': 0, 'C': 0, 'C-': 0, 'D+': 0, 'D': 0, 'D-': 0, 'E': 0, 'F': 0 };
      studentsInSelectedCourse.value.forEach(student => {
        if (grades[student.grade] !== undefined) {
          grades[student.grade]++;
        } else {
          grades['F']++; // Default any unexpected grade to F
        }
      });
      return grades;
    });

    const componentAverages = computed(() => {
      if (!courseComponents.value.length || !studentsInSelectedCourse.value.length) {
        return [];
      }

      const averages = courseComponents.value.map(component => {
        let totalMarkForComponent = 0;
        let studentsWithMark = 0;

        const componentKey = component.component_name.toLowerCase().replace(/[\s-]/g, '');

        studentsInSelectedCourse.value.forEach(student => {
          const markEntry = student.marks[componentKey]; // Access using the generated key
          if (markEntry && markEntry.student_mark !== null && !isNaN(markEntry.student_mark)) {
            totalMarkForComponent += markEntry.student_mark;
            studentsWithMark++;
          }
        });

        const average = studentsWithMark > 0 ? (totalMarkForComponent / studentsWithMark) : 0;

        return {
          component_name: component.component_name,
          average_mark: parseFloat(average.toFixed(1)),
          max_mark: component.max_mark // Useful for context in chart
        };
      });
      return averages;
    });

    const atRiskStudents = computed(() => {
      // Students are "at risk" if their totalMark for the current course is < 40% AND all marks are given for the course.
      // The 'allMarksGiven' flag comes from the backend.
      return studentsInSelectedCourse.value.filter(student =>
        student.allMarksGiven && (student.totalMark || 0) < 40
      );
    });


    // --- Handlers ---
    const handleCourseSelection = async (course) => {
      selectedCourse.value = course;
      if (course) {
        await fetchStudentDataForCourse(course.id);
      } else {
        studentsInSelectedCourse.value = [];
        courseComponents.value = [];
      }
    };

    return {
      allCourses,
      selectedCourse,
      studentsInSelectedCourse,
      performanceSummary,
      gradeDistribution,
      componentAverages,
      atRiskStudents,
      handleCourseSelection,
    };
  }
};
</script>