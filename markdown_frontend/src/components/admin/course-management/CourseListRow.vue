<template>
  <tr class="bg-white border-b border-b-gray-100 hover:bg-gray-50 transition-colors duration-200">
    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ course.course_code }}</td>
    <td class="px-6 py-4">{{ course.course_name }}</td>
    <td class="px-6 py-4 text-center">{{ course.credit_hours }}</td>
    <td class="px-6 py-4">
      <span v-if="course.lecturer_name" class="font-medium text-gray-900">{{ course.lecturer_name }}</span>
      <span v-else class="text-red-500">Unassigned</span>
    </td>
    <td class="px-6 py-4 text-center">{{ course.student_count || 0 }}</td>
    <td class="px-6 py-4 text-center">
      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
        :class="course.is_active === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
        {{ course.is_active === 1 ? 'Active' : 'Inactive' }}
      </span>
    </td>
    <td class="px-6 py-4 text-right">
      <div class="flex justify-end space-x-2">
        <button @click.stop="$emit('edit-course', course)"
          class="p-2 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-all duration-200"
          title="Edit Course">
          <Edit class="w-5 h-5" />
        </button>
        <button @click.stop="$emit('toggle-active', course)"
          :class="course.is_active === 1 ? 'text-red-600 hover:text-red-900 hover:bg-red-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50'"
          class="p-2 rounded-lg transition-colors"
          :title="course.is_active === 1 ? 'Deactivate Course' : 'Activate Course'">
          <PowerOff v-if="course.is_active === 1" class="w-5 h-5" />
          <CheckCircle v-else class="w-5 h-5" />
        </button>
      </div>
    </td>
  </tr>
</template>

<script setup>
import { Edit, PowerOff, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
  course: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['edit-course', 'toggle-active']);
</script>