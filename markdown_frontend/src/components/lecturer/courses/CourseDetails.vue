<template>
  <div class="space-y-6">
    <div class="flex items-center space-x-4 mb-6">
      <button @click="backToCourses" class="text-sky-600 hover:text-sky-800 cursor-pointer flex items-center space-x-2">
        <ChevronLeft class="size-5" />
        <span>Back to Courses</span>
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-2">
          <p class="text-sky-600 font-semibold text-3xl">{{ selectedCourse.course_code }}</p>
          <h2 class="text-3xl font-bold text-gray-800">- {{ selectedCourse.course_name }}</h2>
        </div>
        <button @click="openAddModal(false)"
          class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 cursor-pointer">
          <PlusIcon class="w-5 h-5 text-white" />
          <span>Add Component</span>
        </button>
      </div>

      <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Assessment Weight Summary</h3>
          <div class="text-right">
            <div class="text-3xl font-bold"
              :class="getContinuousAssessmentWeight() > getContinuousAssessmentLimit() ? 'text-red-600' : 'text-sky-600'">
              {{ getContinuousAssessmentWeight() }}%
            </div>
            <div class="text-sm text-gray-600" v-if="getRemainingWeight > 0">
              <span class="text-amber-500">
                {{ getRemainingWeight.toFixed(1) }}%
              </span>
              remaining for continuous assessment
            </div>
          </div>
        </div>
        <div v-if="getContinuousAssessmentWeight() > getContinuousAssessmentLimit()" class="mt-2 text-red-600 text-sm">
          ⚠️ Warning: Continuous assessment weight exceeds {{ getContinuousAssessmentLimit() }}% limit
        </div>
      </div>

      <MarkComponentTable :selectedCourse="selectedCourse" :totalWeight="getTotalWeight()"
        :continuousAssessmentWeight="getContinuousAssessmentWeight()"
        :continuousAssessmentLimit="getContinuousAssessmentLimit()" @edit-component="openEditModal"
        @delete-component="deleteMarkComponent" />
    </div>

    <MarkComponentFormModal v-if="showModal" :component="editingComponent" :availableWeight="getAvailableWeight()"
      :componentTypeToForce="componentTypeToForce" @close="closeModal" @save="handleSaveComponent" />
  </div>
</template>

<script>
import { ChevronLeft, PlusIcon } from 'lucide-vue-next';
import MarkComponentTable from './MarkComponentTable.vue';
import MarkComponentFormModal from './MarkComponentFormModal.vue';
import coursesApi from "../../../api/courses"
import { toast } from 'vue-sonner';

export default {
  name: 'CourseDetails',
  components: {
    PlusIcon,
    ChevronLeft,
    MarkComponentTable,
    MarkComponentFormModal
  },
  data() {
    return {
      showModal: false,
      editingComponent: null,
      defaultFinalExamWeight: 30,
      componentTypeToForce: null, // New data property to force component type in modal
    }
  },
  props: {
    selectedCourse: {
      type: Object,
      required: true
    }
  },
  watch: {
    'selectedCourse.id': {
      immediate: true,
      async handler(newCourseId) {
        if (newCourseId) {
          // Check if final exam component exists for the current course
          const finalExamExists = this.selectedCourse.components.some(c => c.type === 'final'); // Check only for type 'final'
          if (!finalExamExists) {
            console.log("No Final Exam component found, creating default...");
            // Automatically create a default Final Exam component
            await this.addDefaultFinalExamComponent();
          }
        }
      }
    }
  },
  computed: {
    getRemainingWeight() {
      return 100 - this.getTotalWeight();
    },
  },
  methods: {
    backToCourses() {
      this.$router.push({ name: 'Courses' });
    },
    // Modified openAddModal to accept a parameter for forced type
    openAddModal(isFinalExam = false) {
      this.showModal = true;
      this.editingComponent = null;
      this.componentTypeToForce = isFinalExam ? 'final' : null; // Set to 'final' if it's the final exam
    },
    // Recalculate total weight based on current components
    getTotalWeight() {
      if (!this.selectedCourse.components || !Array.isArray(this.selectedCourse.components)) {
        return 0;
      }

      return this.selectedCourse.components
        .filter(c => c && c.weight != null) // Filter out null components and components without weight
        .reduce((total, component) => {
          const weight = parseFloat(component.weight);
          return total + (isNaN(weight) ? 0 : weight);
        }, 0);
    },
    getFinalExamWeight() {
      if (!this.selectedCourse.components || !Array.isArray(this.selectedCourse.components)) {
        return 0;
      }

      const finalExam = this.selectedCourse.components.find(c =>
        c && c.type === 'final' // Only check for type 'final'
      );

      if (!finalExam || finalExam.weight == null) {
        return 0;
      }

      const weight = parseFloat(finalExam.weight);
      return isNaN(weight) ? 0 : weight;
    },
    getContinuousAssessmentWeight() {
      if (!this.selectedCourse.components || !Array.isArray(this.selectedCourse.components)) {
        return 0;
      }

      return this.selectedCourse.components
        .filter(c => {
          // Filter out null/undefined components and non-final exam components
          return c && c.type !== 'final';
        })
        .reduce((total, component) => {
          // Ensure weight exists and is a valid number
          const weight = component.weight != null ? parseFloat(component.weight) : 0;
          return total + (isNaN(weight) ? 0 : weight);
        }, 0);
    },
    getContinuousAssessmentLimit() {
      return 100 - this.getFinalExamWeight();
    },
    getAvailableWeight() {
      if (this.editingComponent && this.editingComponent.type === 'final') {
        // If editing the final exam, available weight is 100% minus the sum of ALL continuous assessment components.
        // It's the maximum allowed for the final exam without exceeding 100% total.
        const continuousAssessmentTotal = this.getContinuousAssessmentWeight();
        return 100 - continuousAssessmentTotal;
      } else {
        // If adding a new continuous assessment component OR editing an existing continuous assessment component.
        // It's the limit for continuous assessment minus current continuous assessment weight (excluding the one being edited).
        const currentContinuousAssessmentTotal = this.getContinuousAssessmentWeight();
        const editingComponentWeight = this.editingComponent ? parseFloat(this.editingComponent.weight) : 0;
        const limitForContinuousAssessment = this.getContinuousAssessmentLimit();

        // If editing, subtract its current weight from total before calculating remaining
        const weightWithoutEditingComponent = currentContinuousAssessmentTotal - editingComponentWeight;

        // The available weight for a continuous assessment component cannot exceed the continuous assessment limit.
        // It also cannot exceed the remaining portion within that limit.
        return Math.max(0, limitForContinuousAssessment - weightWithoutEditingComponent);
      }
    },
    closeModal() {
      this.showModal = false;
      this.editingComponent = null;
      this.componentTypeToForce = null; // Clear the forced type on close
    },
    openEditModal(component) {
      this.showModal = true;
      this.editingComponent = { ...component }; // Create a copy to prevent direct mutation
      this.componentTypeToForce = component.type; // Set forced type based on edited component's type
    },
    async deleteMarkComponent(componentId) {
      const component = this.selectedCourse.components.find(c => c.id === componentId);

      // Prevent deletion of final exam
      if (component && component.type === 'final') {
        alert("Final exam cannot be deleted. You can only edit its weight.");
        return;
      }

      // Using toast for confirmation as requested in previous instructions
      if (!confirm("Are you sure you want to delete this component? This action cannot be undone.")) {
        return;
      }

      try {
        const response = await coursesApi.deleteMarkComponent({
          component_id: componentId,
          course_id: this.selectedCourse.id
        });
        if (response.status === "success") {
          // Immediately update the local data
          this.selectedCourse.components = this.selectedCourse.components.filter(c => c.id !== componentId);
          toast.success("Component deleted successfully.");
        } else {
          throw new Error(response.message || "Failed to delete component");
        }
      } catch (error) {
        console.error("Error deleting component:", error);
        toast.error("Failed to delete component. Please try again.");
      }
    },
    async handleSaveComponent(componentData) {
      const isFinalExam = componentData.type === 'final';
      let proposedWeight = parseFloat(componentData.weight);

      // Calculate available weight considering the component being edited
      let currentAvailableWeight;
      if (this.editingComponent && this.editingComponent.id === componentData.id) {
        // If editing the current component, temporarily remove its weight from the total
        const componentsExcludingCurrent = this.selectedCourse.components.filter(c => c.id !== componentData.id);

        if (isFinalExam) {
          const continuousAssessmentTotal = componentsExcludingCurrent
            .filter(c => c.type !== 'final')
            .reduce((sum, comp) => sum + parseFloat(comp.weight), 0);
          currentAvailableWeight = 100 - continuousAssessmentTotal;
        } else {
          const finalExamWeight = componentsExcludingCurrent.find(c => c.type === 'final')?.weight || 0;
          const continuousAssessmentLimit = 100 - finalExamWeight;
          const otherContinuousAssessmentTotal = componentsExcludingCurrent
            .filter(c => c.type !== 'final')
            .reduce((sum, comp) => sum + parseFloat(comp.weight), 0);
          currentAvailableWeight = continuousAssessmentLimit - otherContinuousAssessmentTotal;
        }
      } else {
        // If adding a new component
        currentAvailableWeight = this.getAvailableWeight(); // Uses existing logic which correctly calculates for new component
      }

      const effectiveMaxWeight = this.editingComponent && this.editingComponent.id === componentData.id
        ? currentAvailableWeight + (this.editingComponent.weight || 0) // When editing, allow up to current weight + available
        : currentAvailableWeight; // When adding, allow up to available

      if (proposedWeight > effectiveMaxWeight + 0.01) { // Add a small epsilon for floating point comparison
        const assessmentType = isFinalExam ? 'final exam' : 'continuous assessment';
        toast.error(`Weight cannot exceed ${effectiveMaxWeight.toFixed(1)}% for this ${assessmentType}.`);
        return;
      }

      if (this.editingComponent) {
        // Update existing component
        const index = this.selectedCourse.components.findIndex(c => c.id === this.editingComponent.id);
        if (index !== -1) {
          try {
            const response = await coursesApi.updateMarkComponent({
              ...componentData,
              component_id: this.editingComponent.id,
              course_id: this.selectedCourse.id
            });
            if (response.status === "success") {
              // Update the local data with the server response (assuming server returns the updated data)
              // Ensure componentData includes all necessary fields, especially the 'type' for filtering later
              this.selectedCourse.components.splice(index, 1, { ...componentData, id: this.editingComponent.id });
              toast.success("Component updated successfully.");
            } else {
              throw new Error(response.message || "Failed to update component");
            }
          } catch (error) {
            console.error("Error updating component:", error);
            toast.error("Failed to update component. Please try again.");
            return; // Stop further execution if update fails
          }
        }
      } else {
        // Add new component
        const newComponent = {
          course_id: this.selectedCourse.id,
          weight: parseFloat(componentData.weight).toFixed(2),
          ...componentData
        };

        try {
          const response = await coursesApi.addNewMarkComponent(newComponent);
          if (response.status === "success") {
            toast.success("Component added successfully.");
            this.selectedCourse.components.push({
              ...newComponent,
              id: response.id
            });
          } else {
            throw new Error(response.message || "Failed to add new component");
          }
        } catch (error) {
          console.error("Error adding new component:", error);
          toast.error("Failed to add new component. Please try again.");
        }
      }

      this.closeModal();
    },
    async addDefaultFinalExamComponent() {
      const defaultFinalExamData = {
        name: 'Final Exam',
        type: 'final',
        max_mark: 100,
        weight: this.defaultFinalExamWeight,
      };

      try {
        const response = await coursesApi.addNewMarkComponent(defaultFinalExamData);
        if (response.status === "success") {
          toast.info("Default Final Exam component added.");
          // Add the newly created component directly to the list
          this.selectedCourse.components.push(response.data);
        } else {
          throw new Error(response.message || "Failed to add default Final Exam component.");
        }
      } catch (error) {
        console.error("Error adding default final exam:", error);
        toast.error("Failed to automatically add Final Exam component. Please add it manually.");
      }
    }
  }
}
</script>