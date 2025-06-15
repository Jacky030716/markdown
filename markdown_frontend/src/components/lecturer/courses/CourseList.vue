<template>
  <div>
    <main class="container mx-auto px-4 py-8">
      <!-- Course Grid View -->
      <div v-if="currentView === 'courses'" class="space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-3xl font-bold text-gray-800">My Courses</h2>
          <div class="text-sm text-gray-600">
            {{ courses.length }} courses assigned
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <CourseCard v-for="course in courses" :key="course.id" :course="course" />
        </div>
      </div>

      <!-- Course Management View -->
      <div v-if="currentView === 'course-detail'" class="space-y-6">
        <div class="flex items-center space-x-4 mb-6">
          <button @click="backToCourses"
            class="text-sky-600 hover:text-sky-800 cursor-pointer flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>Back to Courses</span>
          </button>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex justify-between items-center mb-6">
            <div>
              <h2 class="text-3xl font-bold text-gray-800">{{ selectedCourse.name }}</h2>
              <p class="text-sky-600 font-medium text-lg">{{ selectedCourse.code }}</p>
              <p class="text-gray-600">{{ selectedCourse.students }} students enrolled</p>
            </div>
            <button @click="openAddModal"
              class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
              </svg>
              <span>Add Component</span>
            </button>
          </div>

          <!-- Weight Summary -->
          <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold text-gray-800">Assessment Weight Summary</h3>
              <div class="text-right">
                <div class="text-2xl font-bold"
                  :class="getTotalWeight(selectedCourse.components) > 70 ? 'text-red-600' : 'text-blue-600'">
                  {{ getTotalWeight(selectedCourse.components) }}%
                </div>
                <div class="text-sm text-gray-600">
                  {{ 70 - getTotalWeight(selectedCourse.components) }}% remaining
                </div>
              </div>
            </div>
            <div v-if="getTotalWeight(selectedCourse.components) > 70" class="mt-2 text-red-600 text-sm">
              ⚠️ Warning: Total weight exceeds 70% limit for continuous assessment
            </div>
          </div>

          <!-- Components Table -->
          <div class="overflow-x-auto">
            <table class="w-full table-auto">
              <thead>
                <tr class="bg-gray-100">
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Component</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Max Marks</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Weight (%)</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="component in selectedCourse.components" :key="component.id"
                  class="border-b border-gray-200 hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ component.name }}</td>
                  <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getTypeClass(component.type)">
                      {{ component.type }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-800">{{ component.maxMarks }}</td>
                  <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ component.weight }}%</td>
                  <td class="px-4 py-3 text-sm">
                    <div class="flex space-x-2">
                      <button @click="editComponent(component)" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                          </path>
                        </svg>
                      </button>
                      <button @click="deleteComponent(component.id)" class="text-red-600 hover:text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                          </path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="selectedCourse.components.length === 0">
                  <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    No components added yet. Click "Add Component" to get started.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- Add/Edit Component Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">
            {{ editingComponent ? 'Edit Component' : 'Add New Component' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveComponent" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Component Name</label>
            <input v-model="componentForm.name" type="text" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., Quiz 1, Assignment 1">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="componentForm.type" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select type</option>
              <option value="Quiz">Quiz</option>
              <option value="Assignment">Assignment</option>
              <option value="Test">Test</option>
              <option value="Lab">Lab</option>
              <option value="Project">Project</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Marks</label>
            <input v-model.number="componentForm.maxMarks" type="number" min="1" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., 100">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Weight (%)</label>
            <input v-model.number="componentForm.weight" type="number" min="1" max="70" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., 15">
            <div class="mt-1 text-sm text-gray-600">
              Available weight: {{ getAvailableWeight() }}%
            </div>
          </div>

          <div v-if="weightError" class="text-red-600 text-sm">
            {{ weightError }}
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="closeModal"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
              {{ editingComponent ? 'Update' : 'Add' }} Component
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import CourseCard from './CourseCard.vue';

export default {
  components: {
    CourseCard
  },
  data() {
    return {
      currentView: 'courses',
      selectedCourse: null,
      showModal: false,
      editingComponent: null,
      weightError: '',
      componentForm: {
        name: '',
        type: '',
        maxMarks: '',
        weight: ''
      },
    }
  },
  props: {
    courses: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    selectCourse(course) {
      this.selectedCourse = course;
      this.currentView = 'course-detail';
    },
    backToCourses() {
      this.currentView = 'courses';
      this.selectedCourse = null;
    },
    openAddModal() {
      this.showModal = true;
      this.editingComponent = null;
      this.resetForm();
    },
    editComponent(component) {
      this.showModal = true;
      this.editingComponent = component;
      this.componentForm = { ...component };
    },
    closeModal() {
      this.showModal = false;
      this.editingComponent = null;
      this.resetForm();
      this.weightError = '';
    },
    resetForm() {
      this.componentForm = {
        name: '',
        type: '',
        maxMarks: '',
        weight: ''
      };
    },
    saveComponent() {
      const totalCurrentWeight = this.getTotalWeight(this.selectedCourse.components);
      const editingWeight = this.editingComponent ? this.editingComponent.weight : 0;
      const availableWeight = 70 - (totalCurrentWeight - editingWeight);

      if (this.componentForm.weight > availableWeight) {
        this.weightError = `Weight cannot exceed ${availableWeight}%. Total continuous assessment must not exceed 70%.`;
        return;
      }

      if (this.editingComponent) {
        // Update existing component
        const index = this.selectedCourse.components.findIndex(c => c.id === this.editingComponent.id);
        this.selectedCourse.components[index] = { ...this.componentForm };
      } else {
        // Add new component
        const newComponent = {
          ...this.componentForm
        };

        console.log('Adding new component:', newComponent);
        // this.selectedCourse.components.push(newComponent);
      }

      this.closeModal();
    },
    deleteComponent(componentId) {
      if (confirm('Are you sure you want to delete this component?')) {
        this.selectedCourse.components = this.selectedCourse.components.filter(c => c.id !== componentId);
      }
    },
    getAvailableWeight() {
      const totalCurrentWeight = this.getTotalWeight(this.selectedCourse.components);
      const editingWeight = this.editingComponent ? this.editingComponent.weight : 0;
      return 70 - (totalCurrentWeight - editingWeight);
    },
  }
}
</script>