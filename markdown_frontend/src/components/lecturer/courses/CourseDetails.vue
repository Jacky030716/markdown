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
              :class="getTotalWeight(selectedCourse.components) > 70 ? 'text-red-600' : 'text-sky-600'">
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
      <MarkComponentTable :selectedCourse="selectedCourse" :totalWeight="getTotalWeight(selectedCourse.components)"
        @edit-component="openEditModal" @delete-component="deleteMarkComponent" />
    </div>

    <!-- Modal for Adding/Editing Components -->
    <MarkComponentFormModal v-if="showModal" :component="editingComponent" :availableWeight="getAvailableWeight()"
      @close="closeModal" @save="handleSaveComponent" />
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
      editingComponent: null
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
    getTotalWeight(components) {
      return components.reduce((total, component) => total + parseFloat(component.weight), 0);
    },
    getAvailableWeight() {
      const totalCurrentWeight = this.getTotalWeight(this.selectedCourse.components);
      const editingWeight = this.editingComponent ? this.editingComponent.weight : 0;
      return 70 - (totalCurrentWeight - editingWeight);
    },
    closeModal() {
      this.showModal = false;
      this.editingComponent = null;
    },
    openEditModal(component) {
      this.showModal = true;
      this.editingComponent = { ...component };
    },
    async deleteMarkComponent(componentId) {
      if (!confirm("Are you sure you want to delete this component? This action cannot be undone.")) {
        return;
      }

      try {
        await coursesApi.deleteMarkComponent({
          component_id: componentId,
          course_id: this.selectedCourse.id
        });
        this.selectedCourse.components = this.selectedCourse.components.filter(c => c.id !== componentId);
        toast.success("Component deleted successfully.");
      } catch (error) {
        console.error("Error deleting component:", error);
        alert("Failed to delete component. Please try again.");
      }
    },
    async handleSaveComponent(componentData) {
      const totalCurrentWeight = this.getTotalWeight(this.selectedCourse.components);
      const editingWeight = this.editingComponent ? this.editingComponent.weight : 0;
      const availableWeight = 70 - (totalCurrentWeight - editingWeight);

      if (componentData.weight > availableWeight) {
        alert(`Weight cannot exceed ${availableWeight}%. Total continuous assessment must not exceed 70%.`);
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
              this.selectedCourse.components.splice(index, 1, { ...componentData, id: this.editingComponent.id });
              toast.success("Component updated successfully.");
            } else {
              throw new Error("Failed to update component");
            }
          } catch (error) {
            console.error("Error updating component:", error);
            alert("Failed to update component. Please try again.");
            return;
          }
        }
      } else {
        const newComponent = {
          course_id: this.selectedCourse.id,
          ...componentData
        };

        try {
          const response = await coursesApi.addNewMarkComponent(newComponent);
          if (response.status === "success") {
            toast.success("Component added successfully.");
            this.selectedCourse.components.push(newComponent);
          } else {
            throw new Error("Failed to add new component");
          }
        } catch (error) {
          console.error("Error adding new component:", error);
          alert("Failed to add new component. Please try again.");
        }
      }

      this.closeModal();
    }
  },
  props: {
    selectedCourse: {
      type: Object,
      required: true
    }
  }
}

</script>