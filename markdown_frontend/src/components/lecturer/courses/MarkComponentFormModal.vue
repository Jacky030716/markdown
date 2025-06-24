<template>
  <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all duration-300 ease-out">
      <!-- Header -->
      <div class="relative px-6 pt-6 pb-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-gray-900">
              {{ isEditMode ? 'Edit Component' : 'Add New Component' }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">
              {{ isEditMode ? 'Update component details' : 'Create a new grading component' }}
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
        <!-- Component Name -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            Component Name
            <span class="text-red-500">*</span>
          </label>
          <input v-model="form.name" type="text" required :readonly="isFinalExam"
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
            :class="{ 'bg-gray-50 cursor-not-allowed': isFinalExam }" placeholder="e.g., Quiz 1, Assignment 1">
        </div>

        <!-- Type Selection -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">
            Component Type
            <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <select v-model="form.type" required :disabled="isFinalExam"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 text-gray-900 bg-white appearance-none cursor-pointer"
              :class="{ 'bg-gray-50 cursor-not-allowed': isFinalExam }">
              <option value="" disabled class="text-gray-400">Select component type</option>
              <option value="quiz" class="py-2">📝 Quiz</option>
              <option value="assignment" class="py-2">📄 Assignment</option>
              <option value="test" class="py-2">📊 Test</option>
              <option value="lab" class="py-2">🔬 Lab</option>
              <option value="project" class="py-2">🚀 Project</option>
              <option value="final" class="py-2">🎓 Final Exam</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Max Marks and Weight Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Max Marks -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">
              Max Marks (%)
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input v-model.number="form.max_mark" type="number" min="1" required :readonly="isFinalExam"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
                :class="{ 'bg-gray-50 cursor-not-allowed': isFinalExam }" placeholder="100">
            </div>
          </div>

          <!-- Weight -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">
              Weight (%)
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input v-model.number="form.weight" type="number" min="1" :max="Math.floor(availableWeight + form.weight)"
                required
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
                placeholder="15">
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="text-gray-500">Available weight</span>
              <span class="font-semibold text-sky-600 bg-sky-50 px-2 py-1 rounded-full">
                {{ availableWeight.toFixed(1) }}%
              </span>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="flex items-center space-x-2 p-4 bg-red-50 border border-red-200 rounded-xl">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-red-700 text-sm font-medium">{{ error }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
          <button type="button" @click="$emit('close')"
            class="w-full sm:w-auto px-6 py-3 text-gray-700 font-semibold border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:ring-4 focus:ring-gray-100">
            Cancel
          </button>
          <button type="submit"
            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-sky-500 to-sky-600 text-white font-semibold rounded-xl hover:from-sky-600 hover:to-sky-700 focus:ring-4 focus:ring-sky-200 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
            <span class="flex items-center justify-center space-x-2">
              <PlusIcon v-if="!isEditMode" class="w-5 h-5" />
              <Edit2Icon v-else class="w-5 h-5" />
              <span>{{ isEditMode ? 'Update Component' : 'Add Component' }}</span>
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Edit2Icon, PlusIcon } from 'lucide-vue-next';

export default {
  components: {
    Edit2Icon,
    PlusIcon
  },
  props: {
    component: { // The component being edited (null if adding new)
      type: Object,
      default: null
    },
    availableWeight: { // Maximum weight allowed for the current operation
      type: Number,
      required: true
    },
    isFinalExam: { // Flag to indicate if the component is the Final Exam
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'save'],
  data() {
    return {
      form: {
        name: '',
        type: '',
        max_mark: '',
        weight: ''
      },
      error: ''
    };
  },
  computed: {
    isEditMode() {
      return !!this.component;
    }
  },
  watch: {
    component: {
      immediate: true,
      handler(newVal) {
        this.form = newVal ? { ...newVal } : {
          name: '',
          type: '',
          max_mark: '',
          weight: ''
        };
        // Set default for new final exam if applicable
        if (!newVal && this.isFinalExam) {
          this.form.name = 'Final Exam';
          this.form.type = 'final_exam';
          this.form.max_mark = 100;
          this.form.weight = 30; // Default weight for auto-created final exam
        }
        this.error = ''; // Clear error on component change
      }
    },
    availableWeight(newVal) {
      if (this.form.weight > newVal && !this.isEditMode) {
        this.form.weight = newVal; // Clamp new component weight
      }
    }
  },
  methods: {
    handleSubmit() {
      this.error = ''; // Clear previous errors

      const submittedWeight = parseFloat(this.form.weight);

      if (submittedWeight > this.availableWeight + (this.component ? parseFloat(this.component.weight) : 0)) {
        this.error = `Weight cannot exceed ${this.availableWeight.toFixed(2)}%.`;
        return;
      }
      this.$emit('save', { ...this.form });
    }
  }
};
</script>
