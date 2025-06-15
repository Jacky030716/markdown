<template>
  <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 cursor-pointer border-l-6
    border-sky-900" @click="onCourseClick(course.id)">
    <div class="relative">
      <img src="../../../assets/course_thumbnail.jpg" alt="Course Image" class="w-full h-40 object-cover rounded-t" />
      <div class="absolute top-4 right-4 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
        {{ course.student_count }} students
      </div>
    </div>

    <div class="p-6">
      <div class="flex flex-col gap-1 justify-between items-start mb-4">
        <div class="flex items-center gap-2">
          <p class="text-lg font-semibold text-sky-600">{{ course.course_code }}</p>
          <p class="text-lg font-semibold text-gray-800">- {{ course.course_name }}</p>
        </div>
      </div>
      <div class="mt-4 pt-4 border-t border-gray-200">
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Click to manage</span>
          <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

defineProps({
  course: {
    type: Object,
    required: true
  },
});

const router = useRouter();

const onCourseClick = (courseId) => {
  router.push({ name: 'CourseManagement', params: { courseId } });
};
</script>