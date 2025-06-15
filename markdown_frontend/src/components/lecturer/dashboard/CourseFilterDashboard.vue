<template>
  <div class="flex flex-wrap items-center space-y-4 md:space-y-0 md:space-x-4">
    <div class="w-full md:w-auto flex-grow">
      <label for="academicYear" class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
      <select id="academicYear" v-model="selectedYear" @change="filterAndEmitCourse"
        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm">
        <option value="">All Years</option>
        <option v-for="year in uniqueAcademicYears" :key="year" :value="year">{{ year }}</option>
      </select>
    </div>

    <div class="w-full md:w-auto flex-grow">
      <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
      <select id="semester" v-model="selectedSemester" @change="filterAndEmitCourse"
        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm">
        <option value="">All Semesters</option>
        <option v-for="semester in uniqueSemesters" :key="semester" :value="semester">{{ semester }}</option>
      </select>
    </div>

    <div class="w-full md:w-auto flex-grow md:flex-grow-0 md:min-w-[200px]">
      <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Select Course</label>
      <select id="course" v-model="internalSelectedCourseId" @change="emitSelectedCourse"
        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm">
        <option :value="null">-- Select a Course --</option>
        <option v-for="course in filteredCourses" :key="course.id" :value="course.id">
          {{ course.course_code }} - {{ course.course_name }}
        </option>
      </select>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';

export default {
  name: 'CourseFilterDashboard',
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
      return Array.from(years).sort().reverse();
    });

    const uniqueSemesters = computed(() => {
      const semesters = new Set(props.courses.map(c => c.semester));
      return Array.from(semesters).sort();
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
      emit('course-selected', selectedCourse);
    };

    const filterAndEmitCourse = () => {
      // If the currently selected course is no longer in the filtered list, clear selection
      const currentSelectedCourse = props.courses.find(c => c.id === internalSelectedCourseId.value);
      if (currentSelectedCourse && !filteredCourses.value.includes(currentSelectedCourse)) {
        internalSelectedCourseId.value = null;
      }
      // Always emit the current selection (which might be null after filtering)
      emitSelectedCourse();
    };

    watch(() => props.courses, (newCourses) => {
      // If the previously selected course is no longer in the new list of courses, reset selection
      if (internalSelectedCourseId.value && !newCourses.some(c => c.id === internalSelectedCourseId.value)) {
        internalSelectedCourseId.value = null;
      }
      // If there's no selection and new courses are available, attempt to pre-select the first one
      if (internalSelectedCourseId.value === null && newCourses.length > 0) {
        internalSelectedCourseId.value = newCourses[0].id;
        emitSelectedCourse();
      }
    }, { immediate: true });

    return {
      selectedYear,
      selectedSemester,
      internalSelectedCourseId,
      uniqueAcademicYears,
      uniqueSemesters,
      filteredCourses,
      filterAndEmitCourse, // Use this for filter changes
      emitSelectedCourse // Use this for direct course select changes
    };
  }
};
</script>
