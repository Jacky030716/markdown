<template>
  <div v-if="show"
    class="fixed inset-0 bg-black/50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div
      class="relative p-8 border w-11/12 md:w-3/4 lg:w-1/2 xl:w-2/5 shadow-lg rounded-md bg-white transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-2xl font-bold text-gray-900">
          Mark Breakdown for {{ student?.name }} ({{ student?.matricId }})
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl font-semibold">
          &times;
        </button>
      </div>

      <div v-if="student && assessmentComponents.length > 0" class="mt-4">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Component
                </th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Max Mark
                </th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (%)
                </th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Student
                  Mark</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="component in assessmentComponents" :key="component.component_id">
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ component.component_name }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center capitalize">
                  {{ component.component_type }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">
                  {{ component.max_mark }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">
                  {{ component.weight }}%
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">
                  {{ getStudentComponentMark(student, component.component_name) !== null ?
                    getStudentComponentMark(student, component.component_name) : 'N/A' }}
                </td>
              </tr>
              <!-- Summary Rows -->
              <tr class="bg-gray-50 font-semibold text-gray-900">
                <td colspan="4" class="px-4 py-3 text-right">Total Mark:</td>
                <td class="px-4 py-3 text-center">{{ (student.totalMark || 0).toFixed(1) }}%</td>
              </tr>
              <tr class="bg-gray-50 font-semibold text-gray-900">
                <td colspan="4" class="px-4 py-3 text-right">Grade:</td>
                <td class="px-4 py-3 text-center">{{ student.grade }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-else class="text-center py-8 text-gray-500">
        No mark breakdown available for this student.
      </div>

      <div class="mt-6 flex justify-end">
        <button @click="$emit('close')"
          class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 cursor-pointer">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'StudentMarkBreakdownModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    student: {
      type: Object,
      default: null // The student object with marks
    },
    assessmentComponents: {
      type: Array,
      default: () => [] // The list of components for the course
    }
  },
  emits: ['close'],
  setup(props) {
    /**
     * Helper to get the lower-cased, hyphen-removed key for a component name.
     * This must match the key generation in CourseMarkManagement and the backend.
     * @param {string} componentName The original component name.
     * @returns {string} The formatted key.
     */
    const getComponentKey = (componentName) => {
      return componentName.toLowerCase().replace(/[\s-]/g, '');
    };

    /**
     * Gets the mark for a specific component for a student.
     * Ensures robust access given the nested structure.
     * @param {Object} student The student object.
     * @param {string} componentName The name of the component (e.g., 'Quiz 1', 'Final Exam').
     * @returns {number|null} The student's mark for that component, or null if not found.
     */
    const getStudentComponentMark = (student, componentName) => {
      const componentKey = getComponentKey(componentName);
      return student.marks?.[componentKey]?.student_mark ?? null;
    };

    return {
      getStudentComponentMark
    };
  }
};
</script>