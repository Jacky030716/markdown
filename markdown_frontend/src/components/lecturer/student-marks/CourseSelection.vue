<template>
  <div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Course Selection</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Select Course
          </label>

          <select :value="selectedCourse?.id || ''" @change="onCourseChange"
            class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
            <option value="">Select a course</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.course_code }} - {{ course.course_name }}
            </option>
          </select>
        </div>

        <div v-if="selectedCourse" class="flex items-end">
          <div class="flex items-center bg-blue-50 p-3 rounded-md">
            <div class="text-sky-600 font-medium">
              Selected: {{ selectedCourse.course_code }} - {{ selectedCourse.course_name }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CourseSelection',
  props: {
    courses: {
      type: Array,
      default: () => []
    },
    selectedCourse: {
      type: Object,
      default: null
    }
  },
  emits: ['course-selected'],
  methods: {
    onCourseChange(event) {
      const courseId = event.target.value;
      if (courseId) {
        const course = this.courses.find(c => c.id === parseInt(courseId));
        this.$emit('course-selected', course);
      } else {
        this.$emit('course-selected', null);
      }
    }
  }
};
</script>