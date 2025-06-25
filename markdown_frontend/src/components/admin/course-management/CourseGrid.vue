<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <CourseCard v-for="course in courses" :key="course.id" :course="course" @edit-course="$emit('edit-course', $event)"
      @toggle-active="$emit('toggle-active', $event)" />
  </div>
</template>

<script setup>
import CourseCard from './CourseCard.vue';

const props = defineProps({
  courses: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['edit-course', 'toggle-active']);
</script>