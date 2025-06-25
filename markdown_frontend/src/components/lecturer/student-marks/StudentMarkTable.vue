<template>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <!-- Table Header (Search, Add, Export) -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">Student Marks Entry</h3>
        <div class="flex items-center space-x-4">
          <input :value="searchQuery" @input="$emit('update-search', $event.target.value)" type="text"
            placeholder="Search students..."
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
          <!-- <button @click="$emit('add-student')"
            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-md text-sm font-medium cursor-pointer">
            Add Student
          </button> -->
          <!-- Save All button is commented out as per your previous code, uncomment if needed -->
          <!-- <button @click="$emit('save-all-marks')"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Save All
          </button> -->
          <button @click="$emit('export-csv')"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium cursor-pointer">
            Export CSV
          </button>
        </div>
      </div>
    </div>

    <!-- Main Marks Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">
              Student Info
            </th>
            <!-- Dynamically render assessment components (including Final Exam if it's a component) -->
            <th v-for="component in assessmentComponents" :key="component.component_id"
              class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-32">
              {{ component.component_name }}<br>
              <span class="text-gray-400 text-xs normal-case">({{ component.max_mark }} marks)</span>
            </th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-32">
              Total<br>
              <span class="text-gray-400 text-xs normal-case">(100%)</span>
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Grade
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="student in students" :key="student.id" class="hover:bg-gray-50">
            <!-- Student Info -->
            <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white z-10 shadow-sm">
              <div class="flex items-center">
                <div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center">
                  <span class="text-sky-600 font-medium text-sm">
                    {{ getInitials(student.name) }}
                  </span>
                </div>
                <div class="ml-3">
                  <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                  <div class="text-sm text-gray-500">{{ student.matricId }}</div>
                </div>
              </div>
            </td>

            <!-- Assessment Marks Input Fields (Dynamically Rendered) -->
            <td v-for="component in assessmentComponents" :key="component.component_id" class="px-4 py-4 text-center">
              <!-- <input :value="getStudentComponentMark(student, component.component_name)"
                @input="updateMark(student.id, component.component_name, $event.target.value)" type="number" :min="0"
                :max="component.max_mark" :placeholder="`0-${component.max_mark}`"
                class="w-20 px-2 py-1 text-center border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"> -->

              <span class="font-semibold text-gray-900">
                {{ getStudentComponentMark(student, component.component_name) !== null ?
                  getStudentComponentMark(student, component.component_name).toFixed(1) : '-' }}
              </span>
            </td>

            <!-- Total Mark -->
            <td class="px-4 py-4 text-center">
              <span class="text-lg font-semibold" :class="getTotalColorClass(student.totalMark)">
                {{ (student.totalMark || 0).toFixed(1) }}%
              </span>
            </td>

            <!-- Grade -->
            <td class="px-6 py-4 text-center">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="getGradeColorClass(student.grade)">
                {{ student.grade }}
              </span>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4 text-center">
              <div class="flex justify-center space-x-2">
                <button @click="$emit('edit-student', student)"
                  class="text-sky-600 hover:bg-sky-200 text-sm cursor-pointer bg-sky-100 rounded-md p-1.5 transition-colors">
                  <Edit2Icon class="size-4" />
                </button>
                <button @click="$emit('delete-student', student.id)"
                  class="text-red-600 hover:bg-red-200 text-sm cursor-pointer bg-rose-100 rounded-md p-1.5 transition-colors">
                  <Trash2Icon class="size-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Class Statistics -->
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        <div>
          <div class="text-2xl font-bold text-sky-600">{{ students.length }}</div>
          <div class="text-sm text-gray-600">Total Students</div>
        </div>
        <div>
          <div class="text-2xl font-bold text-green-600">{{ classAverage }}%</div>
          <div class="text-sm text-gray-600">Class Average</div>
        </div>
        <div>
          <div class="text-2xl font-bold text-purple-600">{{ passCount }}</div>
          <div class="text-sm text-gray-600">Passed Students</div>
        </div>
        <div>
          <div class="text-2xl font-bold text-orange-600">{{ failCount }}</div>
          <div class="text-sm text-gray-600">Failed Students</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Edit2Icon, Trash2Icon } from 'lucide-vue-next';

export default {
  name: 'StudentMarkTable',
  components: {
    Edit2Icon,
    Trash2Icon
  },
  props: {
    students: {
      type: Array,
      required: true
    },
    assessmentComponents: {
      type: Array,
      required: true
    },
    selectedCourse: {
      type: Object,
      required: true
    },
    classAverage: {
      type: Number,
      required: true
    },
    passCount: {
      type: Number,
      required: true
    },
    failCount: {
      type: Number,
      required: true
    },
    searchQuery: {
      type: String,
      default: ''
    }
  },
  emits: [
    'update-search',
    'update-student-mark',
    'add-student',
    'edit-student',
    'delete-student',
    'save-all-marks',
    'export-csv'
  ],
  methods: {
    /**
     * Helper to get the lower-cased, hyphen-removed key for a component name.
     * This must match the key generation in CourseMarkManagement and the backend.
     * @param {string} componentName The original component name.
     * @returns {string} The formatted key.
     */
    getComponentKey(componentName) {
      return componentName.toLowerCase().replace(/[\s-]/g, '');
    },

    /**
     * Gets the mark for a specific component for a student.
     * Ensures robust access given the nested structure.
     * @param {Object} student The student object.
     * @param {string} componentName The name of the component (e.g., 'Quiz 1', 'Final Exam').
     * @returns {number|null} The student's mark for that component, or null if not found.
     */
    getStudentComponentMark(student, componentName) {
      const componentKey = this.getComponentKey(componentName);
      // Access the nested student_mark property using optional chaining for safety
      return student.marks?.[componentKey]?.student_mark ?? null;
    },

    /**
     * Emits an event to update a student's mark.
     * @param {number} studentId The ID of the student.
     * @param {string} componentName The original name of the component (e.g., 'Quiz 1', 'Final Exam').
     * @param {string} value The raw input value (string).
     */
    updateMark(studentId, componentName, value) {
      const numValue = parseFloat(value);
      // Pass the generated componentKey to the parent
      console.log(`Updating mark for student ${studentId} in component ${componentName}: ${numValue}`);
      this.$emit('update-student-mark', studentId, this.getComponentKey(componentName), numValue);
    },

    /**
     * Generates initials for a student's name.
     * @param {string} name The student's full name.
     * @returns {string} The initials.
     */
    getInitials(name) {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },

    /**
     * Returns CSS classes for the total mark based on its value.
     * @param {number} total The student's total mark.
     * @returns {string} CSS classes.
     */
    getTotalColorClass(total) {
      if (!total) return 'text-gray-400';
      if (total >= 80) return 'text-green-600';
      if (total >= 70) return 'text-sky-600';
      if (total >= 60) return 'text-yellow-600';
      if (total >= 50) return 'text-orange-600';
      return 'text-red-600';
    },

    /**
     * Returns CSS classes for the grade based on its value.
     * @param {string} grade The student's grade.
     * @returns {string} CSS classes.
     */
    getGradeColorClass(grade) {
      const gradeClasses = {
        'A+': 'bg-green-200 text-green-900',
        'A': 'bg-green-100 text-green-800',
        'A-': 'bg-green-50 text-green-700',
        'B+': 'bg-sky-200 text-sky-900',
        'B': 'bg-sky-100 text-sky-800',
        'B-': 'bg-sky-50 text-sky-700',
        'C+': 'bg-yellow-200 text-yellow-900',
        'C': 'bg-yellow-100 text-yellow-800',
        'C-': 'bg-yellow-50 text-yellow-700',
        'D+': 'bg-orange-200 text-orange-900',
        'D': 'bg-orange-100 text-orange-800',
        'D-': 'bg-orange-50 text-orange-700',
        'E': 'bg-red-100 text-red-800',
      };
      return gradeClasses[grade] || 'bg-gray-100 text-gray-800';
    }
  }
};
</script>