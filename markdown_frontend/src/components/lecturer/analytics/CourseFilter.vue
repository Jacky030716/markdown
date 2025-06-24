<template>
  <!-- Clear Filters -->
  <div class="flex justify-end mb-2">
    <button @click="clearFilters" v-if="selectedYear || selectedSemester || internalSelectedCourseId"
      class="inline-flex items-center gap-1 border border-gray-100 px-4 py-1.5 rounded-lg text-sm font-medium text-rose-700 hover:text-rose-600 hover:bg-gray-50 transition-all duration-200 cursor-pointer hover:scale-105">
      <XIcon />
      Clear Filters
    </button>
  </div>
  <div class="flex flex-wrap items-center space-y-4 md:space-y-0 md:space-x-4">
    <div class="w-full md:w-auto flex-grow">
      <EnhancedSelect v-model="selectedYear" :options="uniqueAcademicYears.map(year => ({ value: year, label: year }))"
        placeholder="Select Academic Year" @change="filterCourses" label="Academic Year" />
    </div>

    <div class="w-full md:w-auto flex-grow">
      <EnhancedSelect v-model="selectedSemester"
        :options="uniqueSemesters.map(semester => ({ value: semester, label: semester }))" placeholder="Select Semester"
        @change="filterCourses" label="Semester" />
    </div>

    <div class="w-full md:w-auto flex-grow md:flex-grow-0 md:min-w-[200px]">
      <EnhancedSelect v-model="internalSelectedCourseId"
        :options="filteredCourses.map(course => ({ value: course.id, label: `${course.course_code} - ${course.course_name}` }))"
        placeholder="Select Course" @change="emitSelectedCourse" label="Select Course" />
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import EnhancedSelect from '../../common/EnhancedSelect.vue';
import { XIcon } from 'lucide-vue-next';

export default {
  name: 'CourseFilter',
  components: {
    EnhancedSelect,
    XIcon
  },
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

    const clearFilters = () => {
      selectedYear.value = '';
      selectedSemester.value = '';
      internalSelectedCourseId.value = null; // Reset course selection
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
      emitSelectedCourse,
      clearFilters
    };
  }
};
</script>