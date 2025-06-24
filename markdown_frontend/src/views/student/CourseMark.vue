<template>
  <div class=" bg-gray-100 min-h-screen font-sans">
    <div class="header">
      <h1>📊 Course Marks</h1>
      <CourseSelector
        :courses="allCourses"
        @course-selected="handleCourseSelection"
      />
    </div>
    <div class="main-content">
      <ProgressSection :marksData="marksData" />

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
</template>

<script>
import { ref } from "vue"; // Import necessary functions from Vue

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
    const allCourses = ref([
      {
        id: 1,
        course_code: "CS101",
        course_name: "Software Quality Assurance",
        academic_year: "2024/2025",
        semester: "Semester 1",
        credit_hours: 3,
      },
      {
        id: 2,
        course_code: "CS201",
        course_name: "Data Structures & Algorithms",
        academic_year: "2024/2025",
        semester: "Semester 1",
        credit_hours: 3,
      },
      {
        id: 3,
        course_code: "CS301",
        course_name: "Web Technology",
        academic_year: "2024/2025",
        semester: "Semester 2",
        credit_hours: 3,
      },
    ]); // Dummy courses data

    const selectedCourse = ref(null); // Reactive state for the selected course
    const marksData = ref([
      // Dummy marks data for performance analytics
      { name: "Quiz 1", type: "quiz", mark: "18", max_mark: "20", weight: 5 },
      {
        name: "Assignment 1",
        type: "assignment",
        mark: "60",
        max_mark: "60",
        weight: 10,
      },
      { name: "Lab 1", type: "lab", mark: "30", max_mark: "30", weight: 4 },
      { name: "Quiz 2", type: "quiz", mark: "16", max_mark: "20", weight: 5 },
      {
        name: "Final Exam",
        type: "exam",
        mark: "88",
        max_mark: "100",
        weight: 30,
      },
    ]); // Dummy marks data

    // Dummy ranking data for ClassRanking.vue
    const rankingData = ref({
      studentRank: 5, // The student's rank
      totalStudents: 30, // Total number of students
      studentName: "John Doe", // Student's name
    });

    // Dummy performance comparison data for PerformanceComparison.vue
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
    ]); // Dummy comparison data

    // Dummy data for WhatIfSimulator.vue
    const currentMarks = ref({
      total: 94.8, // Current total grade (as a percentage)
      weight: 70, // The weight of the completed components
    });

    const remainingWeight = ref({
      quiz1: 30, // Weight of the remaining quiz
      assignment2: 16, // Weight of the remaining assignment
    });

    // Default scores for the remaining components
    const quiz1Score = ref(85); // Default score for Quiz 1
    const assignment2Score = ref(90); // Default score for Assignment 2

    // Handle course selection
    const handleCourseSelection = (course) => {
      selectedCourse.value = course;
      console.log("Selected Course:", selectedCourse.value);
    };

    return {
      allCourses,
      selectedCourse,
      marksData,
      comparisonData, // Passing comparisonData to PerformanceComparison.vue
      rankingData, // Passing rankingData to ClassRanking.vue
      currentMarks, // Passing currentMarks to WhatIfSimulator.vue
      remainingWeight, // Passing remainingWeight to WhatIfSimulator.vue
      quiz1Score, // Passing quiz1Score to WhatIfSimulator.vue
      assignment2Score, // Passing assignment2Score to WhatIfSimulator.vue
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
  padding: 20px; /* Added padding for better spacing on smaller screens */
}

h1 {
  font-size: xx-large;
  font-weight: 700;
  color: #1e293b;
}

/* Added styles for the header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 40px;
  flex-wrap: wrap; /* Allows items to wrap on smaller screens */
}

.main-content {
  padding: 0 40px 40px 40px; /* Adjusted padding */
}

/* --- New Grid Styles --- */
.content-grid {
  display: grid;
  /* Creates two columns of equal width */
  grid-template-columns: repeat(2, 1fr);
  /* Adds space between grid items */
  gap: 40px;
  margin-top: 40px; /* Adds space above the grid */
}

/* --- Responsive Media Query --- */
/* For screens smaller than 1024px */
@media (max-width: 1024px) {
  .content-grid {
    /* Switches to a single column layout */
    grid-template-columns: 1fr;
  }

  .header {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
  }
}

/* --- New Grid Styles --- */
.grid-wrapper {
  margin-top: 40px; /* Adds space above the grid layouts */
}

.grid-row {
  display: grid;
  gap: 40px; /* Adds space between grid items */
}

/* Adds space between the grid rows and the full-width component */
.grid-row + .grid-row,
.grid-row + .full-width-component {
  margin-top: 40px;
}

/* First row: 60% and 40% split */
.grid-60-40 {
  grid-template-columns: 3fr 2fr; /* Creates a 60%/40% split */
}

/* Second row: 30% and 70% split */
.grid-30-70 {
  grid-template-columns: 3fr 7fr; /* Creates a 30%/70% split */
}

/* --- Responsive Media Query --- */
/* For screens smaller than 1024px */
@media (max-width: 1024px) {
  .grid-60-40,
  .grid-30-70 {
    /* Switches to a single column layout */
    grid-template-columns: 1fr;
  }

  .header {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
  }
}

/* For screens smaller than 768px */
@media (max-width: 768px) {
  .header,
  .main-content {
    padding-left: 20px;
    padding-right: 20px;
  }
}
</style>
