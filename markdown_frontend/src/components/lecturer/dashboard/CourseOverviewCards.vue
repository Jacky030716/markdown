<template>
  <div v-if="course" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Course Code</div>
      <div class="text-2xl font-bold text-sky-600 mt-2">{{ course.course_code }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Course Name</div>
      <div class="text-2xl font-bold text-sky-600 mt-2">{{ course.course_name }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Semester & Year</div>
      <div class="text-2xl font-bold text-sky-600 mt-2">{{ course.semester }} {{ course.academic_year }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center col-span-full sm:col-span-1 lg:col-span-1">
      <div class="text-sm font-medium text-gray-500">Total Students</div>
      <div class="text-2xl font-bold text-sky-600 mt-2">{{ course.student_count }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center col-span-full sm:col-span-1 lg:col-span-1">
      <div class="text-sm font-medium text-gray-500">Credit Hours</div>
      <div class="text-2xl font-bold text-sky-600 mt-2">{{ course.credit_hours }}</div>
    </div>
  </div>
  <div v-else class="text-center py-4 text-gray-500">
    No course selected.
  </div>
</template>

<script>
export default {
  name: 'CourseOverviewCards',
  props: {
    course: {
      type: Object,
      default: null
    }
  }
};
</script>