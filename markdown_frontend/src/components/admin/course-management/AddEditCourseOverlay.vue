<template>
  <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all duration-300 ease-out scale-100 opacity-100">
      <!-- Header -->
      <div class="relative px-8 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between">
        <div>
          <h3 class="text-2xl font-bold text-gray-900">
            {{ isEditMode ? 'Edit Course Details' : 'Create New Course' }}
          </h3>
          <p class="text-sm text-gray-500 mt-1">
            {{ isEditMode
              ? 'Update the information for this course.'
              : 'Fill in the details to create a new academic course.' }}
          </p>
        </div>
        <button @click="$emit('close')"
          class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors duration-200">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form Content -->
      <form @submit.prevent="handleSubmit" class="px-8 py-8 space-y-7">
        <!-- Course Basic Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label for="course_code" class="block text-sm font-semibold text-gray-700">Course Code <span
                class="text-red-500">*</span></label>
            <input type="text" id="course_code" v-model="form.course_code" required :readonly="isEditMode"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              :class="{ 'bg-gray-50 cursor-not-allowed': isEditMode }" placeholder="e.g., CS101">
          </div>
          <div class="space-y-2">
            <label for="course_name" class="block text-sm font-semibold text-gray-700">Course Name <span
                class="text-red-500">*</span></label>
            <input type="text" id="course_name" v-model="form.course_name" required
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              placeholder="e.g., Software Quality Assurance">
          </div>
          <div class="space-y-2">
            <EnhancedSelect id="credit_hours" v-model="form.credit_hours" :options="[{
              value: 1, label: '1'
            }, {
              value: 2, label: '2'
            }, {
              value: 3, label: '3'
            }, {
              value: 4, label: '4'
            }, {
              value: 5, label: '5'
            }]" label="Credit Hours" placeholder="Select Credit Hours" required />
          </div>
          <div class="space-y-2">
            <EnhancedSelect id="semester" v-model="form.semester" :options="[{
              value: 'Semester 1', label: 'Semester 1'
            }, {
              value: 'Semester 2', label: 'Semester 2'
            }]" label="Semester" placeholder="Select Semester" />
          </div>
          <div class="space-y-2">
            <EnhancedSelect id="academic_year" v-model="form.academic_year" :options="[{
              value: '2024/2025', label: '2024/2025'
            }, {
              value: '2025/2026', label: '2025/2026'
            }]" label="Academic Year" placeholder="Select Academic Year" />
          </div>
          <div class="space-y-2 flex flex-col justify-end">
            <label for="is_active" class="block text-sm font-semibold text-gray-700">Status</label>
            <label class="relative inline-flex items-center cursor-pointer mt-2">
              <input type="checkbox" v-model="form.is_active" id="is_active" class="sr-only peer">
              <div
                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-sky-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600">
              </div>
              <span class="ml-3 text-sm font-medium text-gray-900">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
            </label>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="flex items-center space-x-2 p-4 bg-red-50 border border-red-200 rounded-xl">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-red-700 text-sm font-medium">{{ errorMessage }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-7 border-t border-gray-100">
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
            <BookPlus v-else-if="!isEditMode" class="w-5 h-5" />
            <Edit class="w-5 h-5" v-else />
            <span>{{ isSaving ? 'Saving...' : (isEditMode ? 'Update Course' : 'Create Course') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { BookPlus, Edit } from 'lucide-vue-next';
import EnhancedSelect from '../../../components/common/EnhancedSelect.vue';

const props = defineProps({
  course: {
    type: Object,
    default: null // Null when adding a new course
  },
  allLecturers: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'save']);

const form = ref({
  course_code: '',
  course_name: '',
  credit_hours: 3, // Default value
  lecturer_id: null, // Initially unassigned
  semester: '',
  academic_year: '',
  is_active: true // Default to active
});

const isEditMode = ref(false);
const errorMessage = ref('');
const isSaving = ref(false);

watch(() => props.course, (newCourse) => {
  if (newCourse) {
    isEditMode.value = true;
    form.value = {
      id: newCourse.id, // Keep ID for updates
      course_code: newCourse.course_code || '',
      course_name: newCourse.course_name || '',
      credit_hours: newCourse.credit_hours || 3,
      lecturer_id: newCourse.lecturer_id || null,
      semester: newCourse.semester || '',
      academic_year: newCourse.academic_year || '',
      is_active: newCourse.is_active === 1 // Convert tinyint to boolean
    };
  } else {
    isEditMode.value = false;
    // Reset form for new course
    form.value = {
      course_code: '',
      course_name: '',
      credit_hours: 3,
      lecturer_id: null,
      semester: '',
      academic_year: '',
      is_active: true
    };
  }
  errorMessage.value = ''; // Clear errors on modal open/course change
}, { immediate: true });

const validateForm = () => {
  errorMessage.value = '';
  if (!form.value.course_code || !form.value.course_name || !form.value.credit_hours) {
    errorMessage.value = 'Course Code, Course Name, and Credit Hours are required.';
    return false;
  }
  if (form.value.credit_hours <= 0) {
    errorMessage.value = 'Credit Hours must be a positive number.';
    return false;
  }
  return true;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  isSaving.value = true;
  try {
    // Prepare payload, converting is_active back to 0/1 if needed by backend
    const payload = {
      ...form.value,
      is_active: form.value.is_active ? 1 : 0
    };

    // Remove `id` from payload if adding a new course, as it's auto-generated
    if (!isEditMode.value) {
      delete payload.id;
    }

    emit('save', payload); // Emit to parent for API call
  } catch (err) {
    // This catch block is mostly for unexpected local errors.
    // API errors are typically handled in the parent component's handleSaveCourse.
    console.error("Form submission error:", err);
    errorMessage.value = "An unexpected error occurred during form submission.";
  } finally {
    isSaving.value = false;
  }
};
</script>