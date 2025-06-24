<template>
  <div class="flex flex-wrap items-center gap-4">
    <!-- Academic Year Selector -->
    <div class="flex-1 min-w-[150px]">
      <label for="academicYear" class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
      <select id="academicYear" v-model="selectedYear" @change="filterAndEmitCourse"
        class="mt-1 block w-full pl-3 pr-10 py-4 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
        <option value="">All Years</option>
        <option v-for="year in uniqueAcademicYears" :key="year" :value="year">{{ year }}</option>
      </select>
    </div>

    <!-- Semester Selector -->
    <div class="flex-1 min-w-[150px]">
      <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
      <select id="semester" v-model="selectedSemester" @change="filterAndEmitCourse"
        class="mt-1 block w-full pl-3 pr-10 py-4 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
        <option value="">All Semesters</option>
        <option v-for="semester in uniqueSemesters" :key="semester" :value="semester">{{ semester }}</option>
      </select>
    </div>

    <!-- Course Selector -->
    <div class="flex-1 min-w-[250px] md:flex-grow-0">
      <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Select Course</label>
      <select id="course" v-model="internalSelectedCourseId" @change="emitSelectedCourse"
        class="mt-1 block w-full pl-3 pr-10 py-4 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
        <option :value="null">-- Select a Course --</option>
        <option v-for="course in filteredCourses" :key="course.id" :value="course.id">
          {{ course.course_code }} - {{ course.course_name }}
        </option>
      </select>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'CourseSelector',
  props: {
    courses: {
      type: Array,
      default: () => []
    }
  },
  emits: ['course-selected'],
  setup(props, { emit }) {
    const selectedYear = ref('');
    const selectedSemester = ref('');
    const internalSelectedCourseId = ref(null);

    const uniqueAcademicYears = computed(() => {
      const years = new Set(props.courses.map(c => c.academic_year));
      return Array.from(years).sort().reverse(); // Sort years in descending order
    });

    const uniqueSemesters = computed(() => {
      const semesters = new Set(props.courses.map(c => c.semester));
      return Array.from(semesters).sort(); // Sort semesters alphabetically
    });

    const filteredCourses = computed(() => {
      return props.courses.filter(course => {
        const yearMatch = selectedYear.value === '' || course.academic_year === selectedYear.value;
        const semesterMatch = selectedSemester.value === '' || course.semester === selectedSemester.value;
        return yearMatch && semesterMatch;
      });
    });

    const emitSelectedCourse = () => {
      const selectedCourse = props.courses.find(c => c.id === internalSelectedCourseId.value);
      emit('course-selected', selectedCourse || null);
    };

    const filterAndEmitCourse = () => {
      // Reset course selection when filters change
      internalSelectedCourseId.value = null;
      emitSelectedCourse();
    };

    return {
      selectedYear,
      selectedSemester,
      internalSelectedCourseId,
      uniqueAcademicYears,
      uniqueSemesters,
      filteredCourses,
      filterAndEmitCourse,
      emitSelectedCourse
    };
  }
};
</script>

<style scoped>
/* Styling is handled by Tailwind CSS */
</style>
