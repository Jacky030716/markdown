<template>
  <div class="card form-card">
    <div class="card-header">
      <i class="fas fa-plus-circle"></i>
      <h2>Submit New Request</h2>
    </div>

    <form @submit.prevent="submitForm">
      <div class="form-group">
        <label for="courseSelect">Course *</label>
        <select v-model="selectedCourse" id="courseSelect" class="form-control" required>
          <option value="">-- Select Course --</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.name }}</option>
        </select>
        <div v-if="formErrors.course" class="form-validation">{{ formErrors.course }}</div>
      </div>

      <div class="form-group">
        <label for="componentSelect">Assessment Component *</label>
        <select v-model="selectedComponent" id="componentSelect" class="form-control" :disabled="!selectedCourse" required>
          <option value="">-- Select Component --</option>
          <option v-for="component in selectedCourseComponents" :key="component.id" :value="component.id">
            {{ component.name }}
          </option>
        </select>
        <div v-if="formErrors.component" class="form-validation">{{ formErrors.component }}</div>
      </div>

      <div class="form-group">
        <label for="justification">Justification *</label>
        <textarea v-model="justification" id="justification" class="form-control" required minlength="50" placeholder="Please provide clear reasons for requesting a remark."></textarea>
        <div v-if="formErrors.justification" class="form-validation">{{ formErrors.justification }}</div>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-paper-plane"></i> Submit Request
      </button>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    courses: Array,
  },
  data() {
    return {
      selectedCourse: null,
      selectedComponent: null,
      justification: "",
      formErrors: {
        course: "",
        component: "",
        justification: "",
      },
    };
  },
  computed: {
    selectedCourseComponents() {
      return this.selectedCourse
        ? this.courses.find((course) => course.id === this.selectedCourse)
            .components
        : [];
    },
  },
  methods: {
    submitForm() {
      this.formErrors = { course: "", component: "", justification: "" };

      if (!this.selectedCourse)
        this.formErrors.course = "Please select a course";
      if (!this.selectedComponent)
        this.formErrors.component = "Please select an assessment component";
      if (this.justification.length < 30)
        this.formErrors.justification =
          "Justification must be at least 30 characters long";

      if (!Object.values(this.formErrors).some((error) => error)) {
        const remarkData = {
          courseId: this.selectedCourse,
          componentId: this.selectedComponent,
          justification: this.justification,
        };
        this.$emit("submitRequest", remarkData); // Emit the remark data to parent

        // Clear the form after submission
        this.selectedCourse = null;
        this.selectedComponent = null;
        this.justification = "";
      }
    },
  },
};
</script>

<style scoped>
.card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 15px;
  padding: 30px;
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.card-header i {
  font-size: 1.5rem;
  margin-right: 12px;
  color: #4f46e5;
}

.card-header h2 {
  font-size: 1.5rem;
  color: #2d3748;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  color: #2d3748;
  font-size: 1rem;
  margin-bottom: 8px;
}

.form-control {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 2px solid #e2e8f0;
  font-size: 1rem;
}

.form-validation {
  color: red;
  font-size: 0.85rem;
}

.btn-primary {
  background: linear-gradient(90deg, #4f46e5, #7c3aed);
  color: white;
  padding: 12px;
  font-size: 1rem;
  text-align: center;
  width: 100%;
  border-radius: 10px;
  cursor: pointer;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #7c3aed, #4f46e5 50%);
}
</style>
