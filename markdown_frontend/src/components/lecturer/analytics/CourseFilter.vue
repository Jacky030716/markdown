<template>
  <div class="flex flex-wrap items-center space-y-4 md:space-y-0 md:space-x-4">
    <div class="w-full md:w-auto flex-grow">
      <label for="academicYear" class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
      <select id="academicYear" v-model="selectedYear" @change="filterCourses"
        class="mt-1 block w-full pl-3 pr-10 py-3 text-base sm:text-sm rounded-md shadow-sm outline-none focus:ring-2 focus:ring-sky-500">
        <option value="">All Years</option>
        <option v-for="year in uniqueAcademicYears" :key="year" :value="year">{{ year }}</option>
      </select>
    </div>

    <div class="w-full md:w-auto flex-grow">
      <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
      <select id="semester" v-model="selectedSemester" @change="filterCourses"
        class="mt-1 block w-full pl-3 pr-10 py-3 text-base sm:text-sm rounded-md shadow-sm outline-none focus:ring-2 focus:ring-sky-500">
        <option value="">All Semesters</option>
        <option v-for="semester in uniqueSemesters" :key="semester" :value="semester">{{ semester }}</option>
      </select>
    </div>

    <div class="w-full md:w-auto flex-grow md:flex-grow-0 md:min-w-[200px]">
      <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Select Course</label>
      <select id="course" v-model="internalSelectedCourseId" @change="emitSelectedCourse"
        class="mt-1 block w-full pl-3 pr-10 py-3 text-base sm:text-sm rounded-md shadow-sm outline-none focus:ring-2 focus:ring-sky-500">
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
  name: 'CourseFilter',
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
    const internalSelectedCourseId = ref(null); // Local state for the selected course ID

    // Extract unique academic years from all available courses
    const uniqueAcademicYears = computed(() => {
      const years = new Set(props.courses.map(c => c.academic_year));
      return Array.from(years).sort().reverse();
    });

    // Extract unique semesters from all available courses
    const uniqueSemesters = computed(() => {
      const semesters = new Set(props.courses.map(c => c.semester));
      // You might want to sort these manually if they are not just alphanumeric (e.g., ['Fall', 'Spring', 'Summer'])
      return Array.from(semesters).sort();
    });

    // Filter courses based on selected year and semester
    const filteredCourses = computed(() => {
      return props.courses.filter(course => {
        const yearMatch = selectedYear.value === '' || course.academic_year === selectedYear.value;
        const semesterMatch = selectedSemester.value === '' || course.semester === selectedSemester.value;
        return yearMatch && semesterMatch;
      });
    });

    // When filters change, reset selected course if it's no longer in the filtered list
    const filterCourses = () => {
      const currentSelectedCourse = props.courses.find(c => c.id === internalSelectedCourseId.value);
      if (currentSelectedCourse && !filteredCourses.value.includes(currentSelectedCourse)) {
        internalSelectedCourseId.value = null; // Reset selection if current course is filtered out
        emitSelectedCourse(); // Emit null selection
      } else if (currentSelectedCourse) {
        emitSelectedCourse(); // Re-emit if current course is still valid
      }
    };

    // Emits the full selected course object to the parent
    const emitSelectedCourse = () => {
      const selectedCourse = props.courses.find(c => c.id === internalSelectedCourseId.value);
      emit('course-selected', selectedCourse);
    };

    // Watch for changes in props.courses to initialize or reset internal state if needed
    watch(() => props.courses, (newCourses) => {
      // If the previously selected course is no longer in the new list of courses, reset selection
      if (internalSelectedCourseId.value && !newCourses.some(c => c.id === internalSelectedCourseId.value)) {
        internalSelectedCourseId.value = null;
      }
    }, { immediate: true });


    return {
      selectedYear,
      selectedSemester,
      internalSelectedCourseId,
      uniqueAcademicYears,
      uniqueSemesters,
      filteredCourses,
      filterCourses,
      emitSelectedCourse
    };
  }
};
</script>