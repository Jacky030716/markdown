<template>
  <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all duration-300 ease-out">
      <!-- Header -->
      <div class="relative px-6 pt-6 pb-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-gray-900">Assign Lecturer to Course</h3>
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

      <!-- Form Content -->
      <form @submit.prevent="handleSubmit" class="px-6 py-6 space-y-6">
        <div class="space-y-2">
          <label for="lecturerSelect" class="block text-sm font-semibold text-gray-700">Select Lecturer:</label>
          <div class="relative">
            <select id="lecturerSelect" v-model="selectedLecturerId"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 text-gray-900 bg-white appearance-none cursor-pointer">
              <option :value="null">-- Unassign Lecturer --</option>
              <option v-for="lecturer in allLecturers" :key="lecturer.id" :value="lecturer.id">
                {{ lecturer.name }} ({{ lecturer.lecturer_id }})
              </option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="flex items-center space-x-2 p-4 bg-red-50 border border-red-200 rounded-xl">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-red-700 text-sm font-medium">{{ errorMessage }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
          <button type="button" @click="$emit('close')"
            class="w-full sm:w-auto px-6 py-3 text-gray-700 font-semibold border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:ring-4 focus:ring-gray-100">
            Cancel
          </button>
          <button type="submit" :disabled="isSaving"
            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-sky-500 to-sky-600 text-white font-semibold rounded-xl hover:from-sky-600 hover:to-sky-700 focus:ring-4 focus:ring-sky-200 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
            :class="{ 'opacity-50 cursor-not-allowed': isSaving }">
            <svg v-if="isSaving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
              fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <CheckCircle class="w-5 h-5" v-else />
            <span>{{ isSaving ? 'Assigning...' : 'Assign Lecturer' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CheckCircle } from 'lucide-vue-next';
import adminApi from '../../../api/admin'; // Adjust the import path as necessary

const props = defineProps({
  course: {
    type: Object,
    required: true
  },
  allLecturers: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'lecturer-assigned']);

const selectedLecturerId = ref(null);
const errorMessage = ref('');
const isSaving = ref(false);

watch(() => props.course, (newCourse) => {
  if (newCourse) {
    selectedLecturerId.value = newCourse.lecturer_id || null;
    errorMessage.value = '';
  }
}, { immediate: true });

const handleSubmit = async () => {
  errorMessage.value = '';
  isSaving.value = true;
  try {
    const response = await adminApi.updateCourseLecturer(props.course.id, selectedLecturerId.value);
    if (response.status === 'success') {
      emit('lecturer-assigned'); // Emit event to parent to re-fetch data
    } else {
      errorMessage.value = response.message || 'Failed to assign lecturer.';
    }
  } catch (error) {
    console.error("Error assigning lecturer:", error);
    errorMessage.value = error.message || 'An unexpected error occurred during assignment.';
  } finally {
    isSaving.value = false;
  }
};
</script>