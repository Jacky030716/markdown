<template>
  <div class="flex flex-wrap items-center gap-4">
    <div v-if="loading" class="flex items-center text-gray-600">
      <span>Loading courses...</span>
    </div>
    
    <template v-else>
      <div class="flex-1 min-w-[150px]">
        <label for="academicYear" class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
        <select id="academicYear" v-model="selectedYear" @change="handleFilterChange"
          class="mt-1 block w-full pl-3 pr-10 py-4 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
          <option value="">All Years</option>
          <option v-for="year in uniqueAcademicYears" :key="year" :value="year">{{ year }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[150px]">
        <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
        <select id="semester" v-model="selectedSemester" @change="handleFilterChange"
          class="mt-1 block w-full pl-3 pr-10 py-4 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
          <option value="">All Semesters</option>
          <option v-for="semester in uniqueSemesters" :key="semester" :value="semester">{{ semester }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[250px] md:flex-grow-0">
        <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Select Course</label>
        <select id="course" v-model="selectedCourseId" @change="handleCourseChange"
          class="mt-1 block w-full pl-3 pr-10 py-4 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm"
          :disabled="filteredCourses.length === 0">
          <option :value="null">
            {{ filteredCourses.length === 0 ? '-- No Courses Available --' : '-- Select a Course --' }}
          </option>
          <option v-for="course in filteredCourses" :key="course.id" :value="course.id">
            {{ course.course_code }} - {{ course.course_name }}
            <span v-if="course.credit_hours">({{ course.credit_hours }} credit hours)</span>
          </option>
        </select>
      </div>
    </template>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';

export default {
  name: 'CourseSelector',
  props: {
    courses: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['course-selected'],
  setup(props, { emit }) {
    const selectedYear = ref('');
    const selectedSemester = ref('');
    const selectedCourseId = ref(null);

    const uniqueAcademicYears = computed(() => {
      if (!props.courses || props.courses.length === 0) return [];
      
      const years = new Set(props.courses.map(c => c.academic_year).filter(Boolean));
      return Array.from(years).sort((a, b) => b - a); // Sort years in descending order numerically
    });

    const uniqueSemesters = computed(() => {
      if (!props.courses || props.courses.length === 0) return [];
      
      const semesters = new Set(props.courses.map(c => c.semester).filter(Boolean));
      return Array.from(semesters).sort(); // Sort semesters alphabetically
    });

    const filteredCourses = computed(() => {
      if (!props.courses || props.courses.length === 0) return [];
      
      return props.courses.filter(course => {
        const yearMatch = selectedYear.value === '' || course.academic_year === selectedYear.value;
        const semesterMatch = selectedSemester.value === '' || course.semester === selectedSemester.value;
        return yearMatch && semesterMatch;
      });
    });

    // Emits the currently selected course object or null
    const emitSelectedCourse = () => {
      const selectedCourse = props.courses.find(c => c.id === selectedCourseId.value);
      console.log('CourseSelector: Emitting course:', selectedCourse || null);
      emit('course-selected', selectedCourse || null);
    };

    const handleFilterChange = () => {
      console.log('CourseSelector: Filter changed, checking selected course validity.');
      // Check if the currently selected course is still in the filtered list
      const isSelectedCourseStillValid = filteredCourses.value.some(c => c.id === selectedCourseId.value);
      
      if (!isSelectedCourseStillValid) {
        selectedCourseId.value = null; // Clear if not valid
      }
      // Always emit to ensure parent is updated, even if selection becomes null
      emitSelectedCourse(); 
    };

    const handleCourseChange = () => {
      console.log('CourseSelector: Course selection changed to:', selectedCourseId.value);
      emitSelectedCourse();
    };

    // Watch for changes in filteredCourses to ensure selectedCourseId remains valid
    watch(filteredCourses, (newFilteredCourses) => {
      // If selectedCourseId is set but no longer in the new filtered list, reset it.
      // This also handles cases where filters are applied, and the previously selected
      // course is no longer available.
      if (selectedCourseId.value !== null && !newFilteredCourses.some(c => c.id === selectedCourseId.value)) {
        selectedCourseId.value = null;
        emitSelectedCourse(); // Emit null if the course becomes invalid
      }
      // If there's only one course left after filtering, auto-select it.
      if (newFilteredCourses.length === 1 && selectedCourseId.value === null) {
        selectedCourseId.value = newFilteredCourses[0].id;
        emitSelectedCourse();
      }
    }, { immediate: true }); // Run immediately to catch initial filteredCourses

    // Watch for changes in courses prop to reset selections if needed
    watch(() => props.courses, (newCourses) => {
      console.log('CourseSelector: `courses` prop changed.');
      if (!newCourses || newCourses.length === 0) {
        selectedYear.value = '';
        selectedSemester.value = '';
        selectedCourseId.value = null;
        emitSelectedCourse(); // Emit null to clear parent's state
      } else {
        // If courses are loaded for the first time or reloaded, and no course is selected,
        // try to automatically select the first one if only one exists or if specific logic applies.
        // For now, we'll keep it simple and rely on filtering for selection.
      }
    }, { immediate: true }); // Run immediately on component mount

    return {
      selectedYear,
      selectedSemester,
      selectedCourseId,
      uniqueAcademicYears,
      uniqueSemesters,
      filteredCourses,
      handleFilterChange,
      handleCourseChange,
    };
  }
};
</script>

<style scoped>
/* Styling is handled by Tailwind CSS */
</style>