<template>
  <div class="mt-8">
    <div v-if="loading" class="text-center py-20 text-sky-500 text-xl animate-pulse">
      Loading courses...
    </div>
    <div v-else-if="courses.length === 0" class="text-center py-20 text-gray-500 text-xl">
      No courses found.
    </div>
    <div v-else>
      <CourseGrid v-if="viewMode === 'grid'" :courses="courses" @edit-course="$emit('edit-course', $event)"
        @toggle-active="$emit('toggle-active', $event)" />
      <CourseTable v-else-if="viewMode === 'list'" :courses="courses" :sort-by="sortBy" :sort-direction="sortDirection"
        @sort="$emit('sort', $event)" @edit-course="$emit('edit-course', $event)"
        @toggle-active="$emit('toggle-active', $event)" />
    </div>
  </div>
</template>

<script setup>
import CourseGrid from './CourseGrid.vue';
import CourseTable from './CourseTable.vue';

const props = defineProps({
  courses: {
    type: Array,
    required: true
  },
  viewMode: {
    type: String,
    required: true,
    validator: (value) => ['grid', 'list'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  },
  sortBy: {
    type: String,
    default: ''
  },
  sortDirection: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['edit-course', 'toggle-active', 'sort']);
</script>