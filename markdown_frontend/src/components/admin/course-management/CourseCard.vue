<template>
  <div
    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-md hover:border-sky-200 group">

    <!-- Header with subtle gradient background -->
    <div class="bg-gradient-to-r from-sky-50 to-emerald-50 p-6 border-b border-gray-100 h-[120px]">
      <div class="flex items-start justify-between">
        <div class="flex items-center space-x-4">
          <div
            class="w-12 h-12 bg-gradient-to-br from-sky-500 to-sky-600 rounded-lg flex items-center justify-center shadow-sm">
            <BookOpen class="w-6 h-6 text-white" />
          </div>
          <div>
            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ course.course_code }}</h3>
            <p class="text-gray-600 leading-tight">{{ course.course_name }}</p>
          </div>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium"
          :class="course.is_active === 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-50 text-gray-600 border border-gray-200'">
          <div class="w-2 h-2 rounded-full mr-2" :class="course.is_active === 1 ? 'bg-emerald-500' : 'bg-gray-400'">
          </div>
          {{ course.is_active === 1 ? 'Active' : 'Inactive' }}
        </span>
      </div>
    </div>

    <!-- Content section -->
    <div class="p-6">
      <!-- Course details grid -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
          <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center mr-3">
            <Users class="w-4 h-4 text-sky-600" />
          </div>
          <div>
            <div class="text-lg font-semibold text-gray-900">{{ course.student_count || 0 }}</div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Students</div>
          </div>
        </div>
        <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
          <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
            <CreditCard class="w-4 h-4 text-emerald-600" />
          </div>
          <div>
            <div class="text-lg font-semibold text-gray-900">{{ course.credit_hours }}</div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Credits</div>
          </div>
        </div>
      </div>

      <!-- Semester info -->
      <div class="flex items-center p-3 bg-gray-50 rounded-lg mb-6 border border-gray-100">
        <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center mr-3">
          <CalendarDays class="w-4 h-4 text-sky-600" />
        </div>
        <div class="flex-1">
          <div class="font-medium text-gray-900">{{ course.semester }}</div>
          <div class="text-sm text-gray-500">{{ course.academic_year }}</div>
        </div>
      </div>

      <!-- Footer with lecturer and actions -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-100">
        <div class="flex items-center flex-1">
          <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 border-2"
            :class="course.lecturer_name ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 border-emerald-200' : 'bg-gray-100 border-gray-200'">
            <User class="w-5 h-5" :class="course.lecturer_name ? 'text-white' : 'text-gray-400'" />
          </div>
          <div class="flex-1 min-w-0">
            <div v-if="course.lecturer_name" class="font-medium text-gray-900 truncate">
              {{ course.lecturer_name }}
            </div>
            <div v-else class="text-gray-400 italic">
              Unassigned Lecturer
            </div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Lecturer</div>
          </div>
        </div>

        <div class="flex space-x-1 opacity-70 group-hover:opacity-100 transition-opacity duration-200">
          <button @click.stop="$emit('edit-course', course)"
            class="p-2.5 text-gray-500 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-all duration-200 border border-transparent hover:border-sky-200"
            title="Edit Course">
            <Edit class="w-4 h-4" />
          </button>
          <button @click.stop="$emit('toggle-active', course)"
            :class="course.is_active === 1 ? 'text-gray-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200' : 'text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200'"
            class="p-2.5 rounded-lg transition-all duration-200 border border-transparent"
            :title="course.is_active === 1 ? 'Deactivate Course' : 'Activate Course'">
            <PowerOff v-if="course.is_active === 1" class="w-4 h-4" />
            <CheckCircle v-else class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { BookOpen, Users, CreditCard, CalendarDays, User, Edit, PowerOff, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
  course: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['edit-course', 'toggle-active']);
</script>