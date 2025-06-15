<template>
  <div class="p-6 bg-gray-100 min-h-screen font-sans">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Course Performance Analysis</h1>

    <!-- Course/Semester/Academic Year Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
      <CourseFilter :courses="allCourses" @course-selected="handleCourseSelection" />
    </div>

    <div v-if="selectedCourse" class="space-y-8">
      <!-- Performance Summary Cards -->
      <PerformanceSummaryCards :summary-data="performanceSummary" />

      <!-- Grade Distribution Chart -->
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Grade Distribution</h2>
        <div class="h-80">
          <GradeDistributionChart :grades-data="gradeDistribution" />
        </div>
      </div>

      <!-- Student Performance Table -->
      <div class="bg-white shadow-md rounded-lg p-6 overflow-hidden">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Student Performance Overview</h2>
        <StudentPerformanceTable :students-data="processedStudents" :at-risk-threshold="2.0"
          @view-student-details="openStudentBreakdownModal" />
      </div>

      <!-- What-If Simulator Trigger -->
      <div class="bg-white shadow-md rounded-lg p-6 text-center">
        <h2 class="text-2xl font-semibold text-gray-800 mb-1">"What-If" Scenario Simulator</h2>
        <p class="text-gray-600 mb-4">Experiment with hypothetical marks and see their impact on student grades.</p>
        <button @click="openWhatIfSimulator"
          class="bg-sky-600 hover:bg-sky-700 cursor-pointer text-white px-8 py-3 rounded-full font-medium shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
          Launch Simulator
        </button>
      </div>
    </div>

    <div v-else class="text-center py-20 text-gray-500 text-xl">
      Please select a course to view analysis.
    </div>

    <!-- Student Mark Breakdown Modal -->
    <StudentMarkBreakdownModal :show="showBreakdownModal" :student="selectedStudentForBreakdown"
      :assessment-components="assessmentComponents" @close="closeStudentBreakdownModal" />

    <!-- What-If Simulator Modal -->
    <WhatIfSimulator :show="showWhatIfSimulatorModal" :student="selectedStudentForSimulator"
      :assessment-components="assessmentComponents" @close="closeWhatIfSimulator"
      @simulate-change="handleSimulationChange" />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import lecturersApi from "../../api/lecturers"

import CourseFilter from '../../components/lecturer/analytics/CourseFilter.vue';
import StudentPerformanceTable from '../../components/lecturer/analytics/StudentPerformanceTable.vue';
import StudentMarkBreakdownModal from '../../components/lecturer/analytics/StudentMarkBreakdownModal.vue';
import GradeDistributionChart from '../../components/lecturer/analytics/GradeDistributionChart.vue';
import PerformanceSummaryCards from '../../components/lecturer/analytics/PerformanceSummaryCards.vue';
import WhatIfSimulator from '../../components/lecturer/analytics/WhatIfSimulator.vue';
import { toast } from 'vue-sonner';

export default {
  name: 'CourseAnalysisPage',
  components: {
    CourseFilter,
    StudentPerformanceTable,
    StudentMarkBreakdownModal,
    GradeDistributionChart,
    PerformanceSummaryCards,
    WhatIfSimulator
  },
  setup() {
    // --- Reactive State ---
    const allCourses = ref([]); // All courses available across all semesters/years
    const selectedCourse = ref(null); // The currently selected course for analysis
    const studentsInSelectedCourse = ref([]); // Students data for the selected course
    const assessmentComponents = ref([]); // Components for the selected course
    const showBreakdownModal = ref(false);
    const selectedStudentForBreakdown = ref(null);
    const showWhatIfSimulatorModal = ref(false);
    const selectedStudentForSimulator = ref(null);

    // --- Lifecycle Hook ---
    onMounted(async () => {
      const allCoursesResponse = await lecturersApi.getTaughtCourses(1); // Replace 1 with actual lecturer ID

      if (allCoursesResponse && allCoursesResponse.data) {
        allCourses.value = allCoursesResponse.data;
      } else {
        console.error('Failed to fetch courses:', allCoursesResponse);
      }
      // Optionally, pre-select the first course for immediate display
      if (allCourses.value.length > 0) {
        handleCourseSelection(allCourses.value[0]);
      }
    });

    /**
     * Processes students data to add risk status.
     * In a real app, 'allMarksGiven' might be determined by checking if all components
     * have non-null marks for a student. For mock, it's part of generation.
     */
    const processedStudents = computed(() => {
      return studentsInSelectedCourse.value.map(student => ({
        ...student,
        isAtRisk: student.allMarksGiven && (student.cgpa < 2.0)
      }));
    });

    /**
     * Calculates the grade distribution for the selected course.
     */
    const gradeDistribution = computed(() => {
      const grades = { 'A+': 0, 'A': 0, 'B': 0, 'C': 0, 'D': 0, 'E': 0 };
      processedStudents.value.forEach(student => {
        if (grades[student.grade] !== undefined) {
          grades[student.grade]++;
        }
      });
      return grades;
    });

    /**
     * Calculates overall performance summary data.
     */
    const performanceSummary = computed(() => {
      const totalStudents = processedStudents.value.length;
      if (totalStudents === 0) {
        return {
          totalStudents: 0,
          classAverage: 0,
          passCount: 0,
          failCount: 0,
          highestMark: 0,
          lowestMark: 0
        };
      }

      const totalMarksSum = processedStudents.value.reduce((sum, s) => sum + (s.totalMark || 0), 0);
      const classAvg = totalMarksSum / totalStudents;
      const passCount = processedStudents.value.filter(s => (s.totalMark || 0) >= 40).length;
      const failCount = totalStudents - passCount;
      const highestMark = Math.max(...processedStudents.value.map(s => s.totalMark || 0));
      const lowestMark = Math.min(...processedStudents.value.map(s => s.totalMark || 0));

      return {
        totalStudents: totalStudents,
        classAverage: parseFloat(classAvg.toFixed(1)),
        passCount: passCount,
        failCount: failCount,
        highestMark: parseFloat(highestMark.toFixed(1)),
        lowestMark: parseFloat(lowestMark.toFixed(1))
      };
    });

    // --- Methods ---

    /**
     * Handles course selection and loads relevant student data.
     * @param {Object} course The selected course object.
     */
    const handleCourseSelection = async (course) => {
      selectedCourse.value = course;
      if (course) {
        try {
          const studentsAnalysisResponse = await lecturersApi.getStudentsAnalysis(1, course.id);
          const studentsAnalysis = studentsAnalysisResponse.data;
          studentsInSelectedCourse.value = studentsAnalysis || []; // Ensure we have an array

          // Extract assessment components from the first student's marks object
          if (studentsAnalysis.length > 0) {
            const firstStudentMarks = studentsAnalysis[0].marks;
            const extractedComponents = Object.values(firstStudentMarks).map(markEntry => ({
              component_id: markEntry.component_id,
              component_name: markEntry.component_name,
              component_type: markEntry.component_type,
              max_mark: markEntry.max_mark,
              weight: markEntry.weight
            }));

            // Sort components by their ID for consistent display order
            assessmentComponents.value = extractedComponents.sort((a, b) => a.component_id - b.component_id);
          } else {
            assessmentComponents.value = []; // No students, so no components to display
          }

        } catch (error) {
          console.error("Error loading student data for course:", error);
          toast.error("Failed to load student data for this course. Please try again.");
          studentsInSelectedCourse.value = [];
          assessmentComponents.value = [];
        }
      } else {
        // If no course is selected, clear student data
        studentsInSelectedCourse.value = [];
        assessmentComponents.value = [];
      }
    };

    /**
     * Opens the student mark breakdown modal.
     * @param {Object} student The student object to display in the modal.
     */
    const openStudentBreakdownModal = (student) => {
      selectedStudentForBreakdown.value = student;
      showBreakdownModal.value = true;
    };

    /**
     * Closes the student mark breakdown modal.
     */
    const closeStudentBreakdownModal = () => {
      showBreakdownModal.value = false;
      selectedStudentForBreakdown.value = null;
    };

    /**
     * Opens the "What-If" simulator modal.
     * For simplicity, let's default to the first student for the simulator if available.
     * In a real app, you might allow selecting a student first or choose a specific student.
     */
    const openWhatIfSimulator = () => {
      if (processedStudents.value.length > 0) {
        selectedStudentForSimulator.value = processedStudents.value[0]; // Default to first student
        showWhatIfSimulatorModal.value = true;
      } else {
        alert("No students available to run simulator.");
      }
    };

    /**
     * Closes the "What-If" simulator modal.
     */
    const closeWhatIfSimulator = () => {
      showWhatIfSimulatorModal.value = false;
      selectedStudentForSimulator.value = null;
    };

    /**
     * Handles changes from the "What-If" simulator (e.g., updates local student data temporarily).
     * @param {Object} simulationResult Object containing studentId and simulated marks/totals.
     */
    const handleSimulationChange = (simulationResult) => {
      // This method would typically update the local student data (e.g., student.totalMark, student.grade)
      // for the selectedStudentForSimulator based on the simulationResult.
      // This is for display purposes in the simulator, not for saving to the backend.
      console.log('Simulator result:', simulationResult);
      // Example: Update the student in localStudents for live feedback in simulator
      const studentIndex = studentsInSelectedCourse.value.findIndex(s => s.id === simulationResult.studentId);
      if (studentIndex !== -1) {
        // This is a temporary update for the simulator's display.
        // You might have a separate variable for 'simulatedStudent' instead.
        studentsInSelectedCourse.value[studentIndex].totalMark = simulationResult.simulatedTotalMark;
        studentsInSelectedCourse.value[studentIndex].grade = simulationResult.simulatedGrade;
        // Also update individual component marks if simulation affects them
        // studentsInSelectedCourse.value[studentIndex].marks = simulationResult.simulatedMarks;
      }
    };

    /**
     * Helper function to calculate grade based on total mark.
     * Replicated here for clarity, but should be consistent with CourseMarkManagement.
     */
    const calculateGrade = (totalMark) => {
      if (totalMark >= 90) return 'A+';
      if (totalMark >= 80) return 'A';
      if (totalMark >= 70) return 'B';
      if (totalMark >= 60) return 'C';
      if (totalMark >= 50) return 'D';
      if (totalMark >= 40) return 'E';
      return 'F';
    };

    return {
      allCourses,
      selectedCourse,
      processedStudents,
      performanceSummary,
      gradeDistribution,
      handleCourseSelection,
      showBreakdownModal,
      selectedStudentForBreakdown,
      openStudentBreakdownModal,
      closeStudentBreakdownModal,
      assessmentComponents, // Make sure assessmentComponents is returned
      showWhatIfSimulatorModal,
      selectedStudentForSimulator,
      openWhatIfSimulator,
      closeWhatIfSimulator,
      handleSimulationChange
    };
  }
};
</script>