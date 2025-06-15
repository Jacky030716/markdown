<template>
  <div v-if="course">
    <CourseDetails :selectedCourse="course" />
  </div>
  <div v-else class="text-center text-gray-500 mt-10">
    <p>Loading course details...</p>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import lecturersApi from "../../api/lecturers";
import CourseDetails from '../../components/lecturer/courses/CourseDetails.vue';
import { useRoute } from 'vue-router';

const course = ref(null);

const route = useRoute();
const courseId = route.params.courseId;

onMounted(async () => {
  try {
    course.value = await lecturersApi.getCourseDetail(courseId);
    return course.value;
  } catch (error) {
    console.error("Error fetching course details:", error);
    return null;
  }
})
</script>