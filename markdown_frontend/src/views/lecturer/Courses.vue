<script setup>
import { onMounted, ref } from 'vue';
import lecturersApi from "../../api/lecturers";
import CourseList from '../../components/lecturer/courses/CourseList.vue';

const taughtCourses = ref([]);

onMounted(async () => {
  try {
    const coursesResponse = await lecturersApi.getTaughtCourses(1);
    if (coursesResponse.status === "success") {
      taughtCourses.value = coursesResponse.data;

      return taughtCourses.value;
    }
  } catch (error) {
    console.error("Error fetching taught courses:", error);
    return [];
  }
});
</script>

<template>
  <CourseList :courses="taughtCourses" />
</template>