<template>
  <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="relative overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gradient-to-r from-sky-50 to-sky-100 border-b border-sky-200">
          <tr>
            <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-200/50 transition-colors duration-200"
              @click="$emit('sort', 'course_code')">
              <div class="flex items-center space-x-2">
                <span class="font-semibold text-sky-800">Course Code</span>
                <ArrowUpDown class="size-4 text-sky-600" />
              </div>
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-200/50 transition-colors duration-200"
              @click="$emit('sort', 'course_name')">
              <div class="flex items-center space-x-2">
                <span class="font-semibold text-sky-800">Course Name</span>
                <ArrowUpDown class="size-4 text-sky-600" />
              </div>
            </th>
            <th scope="col"
              class="px-6 py-4 text-center cursor-pointer hover:bg-sky-200/50 transition-colors duration-200"
              @click="$emit('sort', 'credit_hours')">
              <div class="flex items-center space-x-2 justify-center">
                <span class="font-semibold text-sky-800">Credits</span>
                <ArrowUpDown class="size-4 text-sky-600" />
              </div>
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-200/50 transition-colors duration-200"
              @click="$emit('sort', 'lecturer_name')">
              <div class="flex items-center space-x-2">
                <span class="font-semibold text-sky-800">Assigned Lecturer</span>
                <ArrowUpDown class="size-4 text-sky-600" />
              </div>
            </th>
            <th scope="col" class="px-6 py-4 text-center">
              <span class="font-semibold text-sky-800">Student Count</span>
            </th>
            <th scope="col" class="px-6 py-4 text-right">
              <span class="font-semibold text-sky-800">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading">
            <td colspan="6" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center space-y-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-600"></div>
                <span class="text-gray-500 font-medium">Loading courses...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="courses.length === 0">
            <td colspan="6" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center space-y-2">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                  <span class="text-gray-400 text-xl">📚</span>
                </div>
                <span class="text-gray-500 font-medium">No courses found</span>
              </div>
            </td>
          </tr>
          <tr v-else v-for="course in courses" :key="course.id"
            class="hover:bg-gradient-to-r hover:from-sky-50/30 hover:to-transparent transition-all duration-200 group">
            <td class="px-6 py-4">
              <div class="flex items-center">
                <div
                  class="w-10 h-10 bg-gradient-to-br from-sky-500 to-sky-600 rounded-lg flex items-center justify-center text-white text-xs font-bold mr-3 shadow-md">
                  {{ course.course_code.substring(0, 2).toUpperCase() }}
                </div>
                <span class="font-bold text-gray-900 font-mono">{{ course.course_code }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="font-semibold text-gray-900">{{ course.course_name }}</span>
            </td>
            <td class="px-6 py-4 text-center">
              <span
                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-amber-100 text-amber-800 ring-1 ring-amber-200">
                {{ course.credit_hours }} {{ course.credit_hours === 1 ? 'Credit' : 'Credits' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div v-if="course.lecturer_name" class="flex items-center">
                <div
                  class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 shadow-md">
                  {{ course.lecturer_name.split(' ')[1].substring(0, 1).toUpperCase() }}
                </div>
                <span class="font-semibold text-gray-900">{{ course.lecturer_name }}</span>
              </div>
              <div v-else class="flex items-center">
                <div
                  class="w-8 h-8 bg-gradient-to-br from-red-400 to-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 shadow-md">
                  !
                </div>
                <span class="text-red-600 font-semibold">Unassigned</span>
              </div>
            </td>
            <td class="px-6 py-4 text-center">
              <div class="flex items-center justify-center">
                <div
                  class="w-8 h-8 rounded-full text-emerald-700 bg-gradient-to-br from-emerald-100 to-emerald-200 p-1.5 flex items-center justify-center mr-2">
                  <UserIcon />
                </div>
                <span class="font-bold text-gray-900">{{ course.student_count || 0 }}</span>
              </div>
            </td>
            <td class="px-6 py-4 text-right">
              <div
                class="flex justify-end space-x-1 opacity-70 group-hover:opacity-100 transition-opacity duration-200">
                <button @click="$emit('assign-lecturer', course)"
                  class="cursor-pointer text-sky-600 hover:text-sky-700 hover:bg-sky-50 p-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md border border-transparent hover:border-sky-200"
                  title="Assign Lecturer">
                  <UserCog class="w-4 h-4" />
                </button>
                <button @click="$emit('manage-students', course)"
                  class="cursor-pointer text-purple-600 hover:text-purple-700 hover:bg-purple-50 p-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md border border-transparent hover:border-purple-200"
                  title="Manage Students">
                  <Users class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Enhanced Pagination -->
    <div v-if="totalPages > 1"
      class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
      <button @click="$emit('change-page', currentPage - 1)" :disabled="currentPage === 1"
        class="flex items-center space-x-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-gray-300 transition-all duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        <span>Previous</span>
      </button>

      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-600">Page</span>
        <div class="flex items-center space-x-1">
          <span v-for="page in getPageNumbers()" :key="page"
            :class="page === currentPage ? 'bg-sky-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50'"
            class="w-8 h-8 flex items-center justify-center text-sm font-medium border border-gray-300 rounded-md cursor-pointer transition-all duration-200"
            @click="page !== '...' && $emit('change-page', page)">
            {{ page }}
          </span>
        </div>
        <span class="text-sm text-gray-600">of {{ totalPages }}</span>
      </div>

      <button @click="$emit('change-page', currentPage + 1)" :disabled="currentPage === totalPages"
        class="flex items-center space-x-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-gray-300 transition-all duration-200">
        <span>Next</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ArrowUpDown, UserCog, UserIcon, Users } from 'lucide-vue-next';

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

const getPageNumbers = () => {
  const pages = [];
  const maxVisible = 5;

  if (props.totalPages <= maxVisible) {
    for (let i = 1; i <= props.totalPages; i++) {
      pages.push(i);
    }
  } else {
    if (props.currentPage <= 3) {
      for (let i = 1; i <= 4; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(props.totalPages);
    } else if (props.currentPage >= props.totalPages - 2) {
      pages.push(1);
      pages.push('...');
      for (let i = props.totalPages - 3; i <= props.totalPages; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = props.currentPage - 1; i <= props.currentPage + 1; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(props.totalPages);
    }
  }

  return pages;
};
</script>