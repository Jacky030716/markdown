<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 overflow-y-auto h-full w-full z-50">
    <div
      class="max-h-[90vh] overflow-y-auto relative top-1/2 -translate-y-1/2 mx-auto p-5 border w-[500px] shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">
          Edit Marks for {{ student?.name }} ({{ student?.matricId }})
        </h3>
        <form @submit.prevent="handleSubmit">
          <!-- Dynamic Mark Input Fields -->
          <div v-if="Object.keys(form.marks).length > 0">
            <div v-for="(markEntry, componentKey) in form.marks" :key="componentKey" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ markEntry.component_name }} (Max: {{ markEntry.max_mark }})
              </label>
              <input v-model="markEntry.student_mark" type="number" :min="0" :max="markEntry.max_mark" step="0.01"
                :placeholder="`Enter mark (0-${markEntry.max_mark})`"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
          </div>
          <div v-else class="text-center py-4 text-gray-500">
            No assessment components found for this course.
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button @click="$emit('close')" type="button"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 cursor-pointer">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-sky-600 rounded-md hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 cursor-pointer">
              Save Marks
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';

export default {
  name: 'StudentModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    student: {
      type: Object,
      default: null // The student object containing all marks
    },
    // The list of all assessment components for the course, used for initializing missing marks
    assessmentComponents: {
      type: Array,
      default: () => []
    },
    // isEditing prop is now less critical as this modal is always for editing marks of an existing student.
    // It can be kept for consistency if desired by the parent, but its internal use is minimal here.
    isEditing: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'save'], // 'save' emit will now send { studentId, updatedMarks }

  setup(props, { emit }) {
    // 'form' will now hold a deep copy of the student's marks for editing
    const form = ref({
      marks: {} // Initialize as an empty object for marks
    });

    /**
     * Helper to generate a consistent key for components (lowercase, no spaces/hyphens).
     * This must match the key generation in CourseMarkManagement and the backend's expected structure.
     * @param {string} componentName The original component name.
     * @returns {string} The formatted key.
     */
    const getComponentKey = (componentName) => {
      return componentName.toLowerCase().replace(/[\s-]/g, '');
    };

    // Watch for changes in the 'student' prop to populate the form
    watch(() => props.student, (newStudent) => {
      if (newStudent) {
        // Create a deep copy of the student's marks to avoid direct mutation of props
        const currentMarks = JSON.parse(JSON.stringify(newStudent.marks || {}));
        const initializedMarks = {};

        // Ensure all assessment components are present in the form, initializing missing ones
        props.assessmentComponents.forEach(component => {
          const componentKey = getComponentKey(component.component_name);
          if (currentMarks[componentKey]) {
            // If mark exists, use its data
            initializedMarks[componentKey] = { ...currentMarks[componentKey] };
          } else {
            // If mark is missing, initialize with component details and null mark
            initializedMarks[componentKey] = {
              component_id: component.component_id,
              component_name: component.component_name,
              component_type: component.component_type,
              max_mark: component.max_mark,
              weight: component.weight,
              student_mark: null // Default new marks to null
            };
          }
        });
        form.value.marks = initializedMarks;

      } else {
        // Reset form when no student is selected (e.g., when modal is closed)
        form.value.marks = {};
      }
    }, { immediate: true }); // Run immediately on component mount if student prop is already set

    // Optional: Reset form when modal is explicitly hidden
    watch(() => props.show, (newShow) => {
      if (!newShow) {
        form.value.marks = {}; // Clear form when modal closes
      }
    });

    /**
     * Handles form submission. Emits the updated marks back to the parent.
     */
    const handleSubmit = () => {
      // Basic validation for marks: ensure they are numbers and within max_mark range
      for (const componentKey in form.value.marks) {
        const markEntry = form.value.marks[componentKey];
        const mark = parseFloat(markEntry.student_mark);
        const max = parseFloat(markEntry.max_mark);

        // Allow null marks, but if a number is entered, validate it
        if (markEntry.student_mark !== null && markEntry.student_mark !== '') {
          if (isNaN(mark) || mark < 0 || mark > max) {
            alert(`Please enter a valid mark for ${markEntry.component_name} (0-${max}).`);
            return; // Prevent form submission
          }
          // Ensure it's stored as a number, not string
          markEntry.student_mark = mark;
        } else {
          // If empty string or non-numeric input for a mark, set it to null
          markEntry.student_mark = null;
        }
      }

      // Emit the student's ID and the updated marks object
      emit('save', { studentId: props.student.id, updatedMarks: form.value.marks });
    };

    return {
      form,
      handleSubmit,
      getComponentKey // Expose helper if needed in template, though it's internal here now
    };
  }
};
</script>