<template>
  <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all duration-300 ease-out">
      <!-- Header -->
      <div class="relative px-6 pt-6 pb-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-gray-900">Manage Students for Course</h3>
            <p class="text-sm text-gray-500 mt-1">
              {{ course.course_code }} - {{ course.course_name }}
            </p>
          </div>
          <button @click="$emit('close')"
            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content: Two columns for enrolled and available students -->
      <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Enrolled Students Column -->
        <div class="space-y-4">
          <h4 class="text-lg font-semibold text-gray-800">Enrolled Students ({{ enrolledStudents.length }})</h4>
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 h-80 overflow-y-auto">
            <p v-if="enrolledStudents.length === 0" class="text-gray-500 text-sm text-center py-4">No students enrolled
              yet.</p>
            <ul v-else class="space-y-2">
              <li v-for="student in enrolledStudents" :key="student.id"
                class="flex items-center justify-between bg-white p-2 rounded-md shadow-sm border border-gray-100">
                <span class="text-gray-800 text-sm font-medium">{{ student.name }} ({{ student.matric_no }})</span>
                <button @click="moveToAvailable(student)"
                  class="p-1 rounded-full text-red-600 hover:bg-red-100 transition-colors" title="Remove from course">
                  <MinusCircle class="w-5 h-5" />
                </button>
              </li>
            </ul>
          </div>
        </div>

        <!-- Available Students Column -->
        <div class="space-y-4">
          <h4 class="text-lg font-semibold text-gray-800">Available Students ({{ availableStudents.length }})</h4>
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 h-80 overflow-y-auto">
            <p v-if="availableStudents.length === 0" class="text-gray-500 text-sm text-center py-4">No more students
              available.</p>
            <ul v-else class="space-y-2">
              <li v-for="student in availableStudents" :key="student.id"
                class="flex items-center justify-between bg-white p-2 rounded-md shadow-sm border border-gray-100">
                <span class="text-gray-800 text-sm font-medium">{{ student.name }} ({{ student.matric_no }})</span>
                <button @click="moveToEnrolled(student)"
                  class="p-1 rounded-full text-green-600 hover:bg-green-100 transition-colors" title="Add to course">
                  <PlusCircle class="w-5 h-5" />
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="px-6 pb-6 text-red-600 text-sm text-center">{{ errorMessage }}</div>

      <!-- Action Buttons -->
      <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 px-6 pb-6 pt-4 border-t border-gray-100">
        <button type="button" @click="$emit('close')"
          class="w-full sm:w-auto px-6 py-3 text-gray-700 font-semibold border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:ring-4 focus:ring-gray-100">
          Cancel
        </button>
        <button @click="handleSaveEnrollments" :disabled="isSaving"
          class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-sky-500 to-sky-600 text-white font-semibold rounded-xl hover:from-sky-600 hover:to-sky-700 focus:ring-4 focus:ring-sky-200 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
          :class="{ 'opacity-50 cursor-not-allowed': isSaving }">
          <svg v-if="isSaving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <Save class="w-5 h-5" v-else />
          <span>{{ isSaving ? 'Saving...' : 'Save Changes' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { PlusCircle, MinusCircle, Save } from 'lucide-vue-next';
import adminApi from '../../../api/admin';

const props = defineProps({
  course: {
    type: Object,
    required: true
  },
  allStudents: { // All possible students in the system
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'enrollments-updated']);

const enrolledStudents = ref([]); // Students currently in this course
const availableStudents = ref([]); // Students not in this course
const errorMessage = ref('');
const isSaving = ref(false);

// Stores initial state to calculate changes
let initialEnrolledStudentIds = new Set();

const fetchCourseEnrollments = async () => {
  errorMessage.value = '';
  try {
    const enrollments = await adminApi.getCourseEnrollments(props.course.id);
    const enrolledIds = new Set(enrollments.map(s => s.id));
    initialEnrolledStudentIds = new Set(enrolledIds); // Store initial IDs

    enrolledStudents.value = props.allStudents.filter(student => enrolledIds.has(student.id));
    availableStudents.value = props.allStudents.filter(student => !enrolledIds.has(student.id));

  } catch (error) {
    console.error("Error fetching course enrollments:", error);
    errorMessage.value = error.message || 'Failed to load course enrollments.';
    enrolledStudents.value = [];
    availableStudents.value = props.allStudents; // Assume all available if error
  }
};

onMounted(() => {
  fetchCourseEnrollments();
});

// Watch for course changes to re-fetch enrollments
watch(() => props.course, (newCourse) => {
  if (newCourse) {
    fetchCourseEnrollments();
  }
}, { deep: true }); // Deep watch is good if course object itself changes

const moveToAvailable = (studentToRemove) => {
  enrolledStudents.value = enrolledStudents.value.filter(s => s.id !== studentToRemove.id);
  availableStudents.value.push(studentToRemove);
  availableStudents.value.sort((a, b) => a.name.localeCompare(b.name)); // Keep sorted
};

const moveToEnrolled = (studentToAdd) => {
  availableStudents.value = availableStudents.value.filter(s => s.id !== studentToAdd.id);
  enrolledStudents.value.push(studentToAdd);
  enrolledStudents.value.sort((a, b) => a.name.localeCompare(b.name)); // Keep sorted
};

const handleSaveEnrollments = async () => {
  errorMessage.value = '';
  isSaving.value = true;

  try {
    const finalEnrolledIds = new Set(enrolledStudents.value.map(s => s.id));

    const studentsToEnroll = [];
    const studentsToUnenroll = [];

    // Determine who needs to be enrolled
    finalEnrolledIds.forEach(id => {
      if (!initialEnrolledStudentIds.has(id)) {
        studentsToEnroll.push(id);
      }
    });

    // Determine who needs to be unenrolled
    initialEnrolledStudentIds.forEach(id => {
      if (!finalEnrolledIds.has(id)) {
        studentsToUnenroll.push(id);
      }
    });

    // Perform API calls
    if (studentsToEnroll.length > 0) {
      await adminApi.enrollStudents(props.course.id, studentsToEnroll);
    }
    if (studentsToUnenroll.length > 0) {
      await adminApi.unenrollStudents(props.course.id, studentsToUnenroll);
    }

    emit('enrollments-updated'); // Notify parent of changes
  } catch (error) {
    console.error("Error saving enrollments:", error);
    errorMessage.value = error.message || 'An unexpected error occurred while saving enrollments.';
  } finally {
    isSaving.value = false;
  }
};
</script>