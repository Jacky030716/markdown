<template>
  <div class="relative overflow-x-auto shadow-sm rounded-lg border border-gray-200">
    <table class="w-full text-sm text-left text-gray-600">
      <thead
        class="text-xs text-gray-700 uppercase bg-gradient-to-r from-sky-50 to-emerald-50 border-b border-gray-200">
        <tr>
          <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-100 transition-colors duration-200"
            @click="$emit('sort', 'course_code')">
            <div class="flex items-center space-x-2">
              <span class="font-medium">Course Code</span>
              <ArrowUpDown class="w-4 h-4 text-gray-400" />
            </div>
          </th>
          <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-100 transition-colors duration-200"
            @click="$emit('sort', 'course_name')">
            <div class="flex items-center space-x-2">
              <span class="font-medium">Course Name</span>
              <ArrowUpDown class="w-4 h-4 text-gray-400" />
            </div>
          </th>
          <th scope="col" class="px-6 py-4 text-center cursor-pointer hover:bg-sky-100 transition-colors duration-200"
            @click="$emit('sort', 'credit_hours')">
            <div class="flex items-center space-x-2 justify-center">
              <span class="font-medium">Credits</span>
              <ArrowUpDown class="w-4 h-4 text-gray-400" />
            </div>
          </th>
          <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-100 transition-colors duration-200"
            @click="$emit('sort', 'lecturer_name')">
            <div class="flex items-center space-x-2">
              <span class="font-medium">Assigned Lecturer</span>
              <ArrowUpDown class="w-4 h-4 text-gray-400" />
            </div>
          </th>
          <th scope="col" class="px-6 py-4 text-center">
            <span class="font-medium">Student Count</span>
          </th>
          <th scope="col" class="px-6 py-4 text-center">
            <span class="font-medium">Status</span>
          </th>
          <th scope="col" class="px-6 py-4 text-right">
            <span class="font-medium">Actions</span>
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <CourseListRow v-for="course in courses" :key="course.id" :course="course"
          @edit-course="$emit('edit-course', $event)" @toggle-active="$emit('toggle-active', $event)" />
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ArrowUpDown } from 'lucide-vue-next';
import CourseListRow from './CourseListRow.vue';

const props = defineProps({
  courses: {
    type: Array,
    required: true
  },
  sortBy: {
    type: String,
    required: true
  },
  sortDirection: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['sort', 'edit-course', 'toggle-active']);
</script>