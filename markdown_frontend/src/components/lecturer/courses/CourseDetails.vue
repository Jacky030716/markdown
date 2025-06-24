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
        <button @click="openAddModal"
          class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 cursor-pointer">
          <PlusIcon class="w-5 h-5 text-white" />
          <span>Add Component</span>
        </button>
      </div>

      <!-- Weight Summary -->
      <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Assessment Weight Summary</h3>
          <div class="text-right">
            <div class="text-3xl font-bold"
              :class="getContinuousAssessmentWeight() > getContinuousAssessmentLimit() ? 'text-red-600' : 'text-sky-600'">
              {{ getContinuousAssessmentWeight() }}%
            </div>
            <div class="text-sm text-gray-600">
              <span class="text-amber-500">
                {{ (getContinuousAssessmentLimit() - getContinuousAssessmentWeight()).toFixed(1) }}%
              </span>
              remaining for
              continuous
              assessment
            </div>
          </div>
        </div>
        <div v-if="getContinuousAssessmentWeight() > getContinuousAssessmentLimit()" class="mt-2 text-red-600 text-sm">
          ⚠️ Warning: Continuous assessment weight exceeds {{ getContinuousAssessmentLimit() }}% limit
        </div>
      </div>

      <!-- Components Table -->
      <MarkComponentTable :selectedCourse="selectedCourse" :totalWeight="getTotalWeight()"
        :continuousAssessmentWeight="getContinuousAssessmentWeight()"
        :continuousAssessmentLimit="getContinuousAssessmentLimit()" @edit-component="openEditModal"
        @delete-component="deleteMarkComponent" />
    </div>

    <!-- Modal for Adding/Editing Components -->
    <MarkComponentFormModal v-if="showModal" :component="editingComponent" :availableWeight="getAvailableWeight()"
      :isFinalExam="editingComponent?.type === 'final_exam'" @close="closeModal" @save="handleSaveComponent" />
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
          const finalExamExists = this.selectedCourse.components.some(c => c.name.toLowerCase() === 'final exam' || c.type === 'final');
          if (!finalExamExists) {
            console.log("No Final Exam component found, creating default...");
            // Automatically create a default Final Exam component
            await this.addDefaultFinalExamComponent();
          }
        }
      }
    }
  },
  methods: {
    backToCourses() {
      this.$router.push({ name: 'Courses' });
    },
    openAddModal() {
      this.showModal = true;
      this.editingComponent = null;
    },
    // Recalculate total weight based on current components
    getTotalWeight() {
      return this.selectedCourse.components.reduce((total, component) => total + parseFloat(component.weight), 0);
    },
    getFinalExamWeight() {
      const finalExam = this.selectedCourse.components.find(c => c.type === 'final_exam');
      return finalExam ? parseFloat(finalExam.weight) : 0;
    },
    getContinuousAssessmentWeight() {
      return this.selectedCourse.components
        .filter(c => c.type !== 'final_exam')
        .reduce((total, component) => total + parseFloat(component.weight), 0);
    },
    getContinuousAssessmentLimit() {
      return 100 - this.getFinalExamWeight();
    },
    getAvailableWeight() {
      if (this.editingComponent && this.editingComponent.type === 'final_exam') {
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
    },
    openEditModal(component) {
      this.showModal = true;
      this.editingComponent = { ...component }; // Create a copy to prevent direct mutation
    },
    async deleteMarkComponent(componentId) {
      const component = this.selectedCourse.components.find(c => c.id === componentId);

      // Prevent deletion of final exam
      if (component && component.type === 'final_exam') {
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
      const isFinalExam = componentData.type === 'final_exam';
      let proposedWeight = parseFloat(componentData.weight);

      // Calculate available weight considering the component being edited
      let currentAvailableWeight;
      if (this.editingComponent && this.editingComponent.id === componentData.id) {
        // If editing the current component, temporarily remove its weight from the total
        const componentsExcludingCurrent = this.selectedCourse.components.filter(c => c.id !== componentData.id);
        const totalWeightExcludingCurrent = componentsExcludingCurrent.reduce((sum, comp) => sum + parseFloat(comp.weight), 0);

        if (isFinalExam) {
          const continuousAssessmentTotal = componentsExcludingCurrent
            .filter(c => c.type !== 'final_exam')
            .reduce((sum, comp) => sum + parseFloat(comp.weight), 0);
          currentAvailableWeight = 100 - continuousAssessmentTotal;
        } else {
          const finalExamWeight = componentsExcludingCurrent.find(c => c.type === 'final_exam')?.weight || 0;
          const continuousAssessmentLimit = 100 - finalExamWeight;
          const otherContinuousAssessmentTotal = componentsExcludingCurrent
            .filter(c => c.type !== 'final_exam')
            .reduce((sum, comp) => sum + parseFloat(comp.weight), 0);
          currentAvailableWeight = continuousAssessmentLimit - otherContinuousAssessmentTotal;
        }
      } else {
        // If adding a new component
        currentAvailableWeight = this.getAvailableWeight(); // Uses existing logic which correctly calculates for new component
      }

      if (proposedWeight > currentAvailableWeight) {
        const assessmentType = isFinalExam ? 'final exam' : 'continuous assessment';
        toast.error(`Weight cannot exceed ${currentAvailableWeight.toFixed(1)}% for this ${assessmentType}.`);
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
          ...componentData
        };

        try {
          const response = await coursesApi.addNewMarkComponent(newComponent);
          if (response.status === "success") {
            toast.success("Component added successfully.");
            // Append the new component returned from the API (which should have an ID)
            this.selectedCourse.components.push(response.data); // Assuming response.data contains the new component with ID
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
      const defaultFinalExam = {
        name: 'Final Exam',
        type: 'final',
        max_mark: 100,
        weight: this.defaultFinalExamWeight,
        course_id: this.selectedCourse.id
      };

      try {
        const response = await coursesApi.addNewMarkComponent(defaultFinalExam);
        if (response.status === "success") {
          toast.info("Default Final Exam component added.");
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