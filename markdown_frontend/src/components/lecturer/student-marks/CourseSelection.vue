<template>
  <div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Course Selection</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!--
          The selectedCourse prop itself is an object,
          but the v-model for EnhancedSelect needs the ID.
          We bind selectedCourseId to v-model, which is a computed property
          that bridges the ID for the select component and the full object
          for the parent component.
        -->
        <EnhancedSelect v-model="selectedCourseId"
          :options="courses.map(course => ({ value: course.id, label: `${course.course_code} - ${course.course_name}` }))"
          placeholder="Select Course" label="Select Course" />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, computed } from 'vue';
import EnhancedSelect from '../../../components/common/EnhancedSelect.vue'; // Ensure this path is correct

export default {
  name: 'CourseSelection',
  components: {
    EnhancedSelect
  },
  props: {
    courses: {
      type: Array,
      default: () => []
    },

    selectedCourse: {
      type: Object,
      default: null
    }
  },
  // Declare the event this component emits
  emits: ['course-selected'],
  setup(props, { emit }) {
    // A computed property that acts as a two-way bridge between
    // the EnhancedSelect's expected `modelValue` (the course ID)
    // and the parent's `selectedCourse` prop (the full course object).
    const selectedCourseId = computed({
      // Getter: Returns the ID of the currently selected course object, or null if none.
      get: () => props.selectedCourse ? props.selectedCourse.id : null,
      // Setter: Called when EnhancedSelect emits an update (a new ID is selected).
      set: (newValue) => {
        // Find the full course object from the 'courses' prop based on the new ID.
        const course = props.courses.find(c => c.id === newValue);
        // Emit the found course object back to the parent component.
        // The parent component should listen for 'course-selected' to update its own state.
        emit('course-selected', course);
      }
    });

    return {
      selectedCourseId
    };
  }
};
</script>