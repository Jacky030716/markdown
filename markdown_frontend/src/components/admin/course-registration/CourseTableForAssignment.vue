<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="relative overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
          <tr>
            <th scope="col" class="px-6 py-3 cursor-pointer" @click="$emit('sort', 'course_code')">
              <div class="flex items-center space-x-1">
                <span>Course Code</span>
                <ArrowUpDown class="size-4" />
              </div>
            </th>
            <th scope="col" class="px-6 py-3 cursor-pointer" @click="$emit('sort', 'course_name')">
              <div class="flex items-center space-x-1">
                <span>Course Name</span>
                <ArrowUpDown class="size-4" />
              </div>
            </th>
            <th scope="col" class="px-6 py-3 text-center cursor-pointer" @click="$emit('sort', 'credit_hours')">
              <div class="flex items-center space-x-1 justify-center">
                <span>Credits</span>
                <ArrowUpDown class="size-4" />
              </div>
            </th>
            <th scope="col" class="px-6 py-3 cursor-pointer" @click="$emit('sort', 'lecturer_name')">
              <div class="flex items-center space-x-1">
                <span>Assigned Lecturer</span>
                <ArrowUpDown class="size-4" />
              </div>
            </th>
            <th scope="col" class="px-6 py-3 text-center">Student Count</th>
            <th scope="col" class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading courses...</td>
          </tr>
          <tr v-else-if="courses.length === 0">
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No courses found.</td>
          </tr>
          <tr v-else v-for="course in courses" :key="course.id" class="bg-white border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
              {{ course.course_code }}
            </td>
            <td class="px-6 py-4">
              {{ course.course_name }}
            </td>
            <td class="px-6 py-4 text-center">
              {{ course.credit_hours }}
            </td>
            <td class="px-6 py-4">
              <span v-if="course.lecturer_name" class="font-medium text-gray-900">{{ course.lecturer_name }}</span>
              <span v-else class="text-red-500">Unassigned</span>
            </td>
            <td class="px-6 py-4 text-center">
              {{ course.student_count || 0 }}
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end space-x-2">
                <button @click="$emit('assign-lecturer', course)"
                  class="text-sky-600 hover:text-sky-900 p-2 rounded-md hover:bg-sky-50 transition-colors"
                  title="Assign Lecturer">
                  <UserCog class="w-5 h-5" />
                </button>
                <button @click="$emit('manage-students', course)"
                  class="text-purple-600 hover:text-purple-900 p-2 rounded-md hover:bg-purple-50 transition-colors"
                  title="Manage Students">
                  <Users class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-between items-center mt-6 px-6 py-3 bg-gray-50 rounded-b-lg">
      <button @click="$emit('change-page', currentPage - 1)" :disabled="currentPage === 1"
        class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
        Previous
      </button>
      <span class="text-sm text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="$emit('change-page', currentPage + 1)" :disabled="currentPage === totalPages"
        class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ArrowUpDown, UserCog, Users } from 'lucide-vue-next';

const props = defineProps({
  courses: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
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

const emit = defineEmits(['sort', 'change-page', 'assign-lecturer', 'manage-students']);
</script>