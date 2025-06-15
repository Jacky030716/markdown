<template>
  <div class="bg-white shadow-md rounded-lg p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-4">Students Currently At Risk</h3>
    <div v-if="studentsData.length > 0" class="space-y-3">
      <div v-for="student in studentsData" :key="student.id"
        class="flex items-center justify-between p-3 bg-red-50 rounded-md border border-red-200">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-red-200 rounded-full flex items-center justify-center mr-3">
            <span class="text-red-800 font-medium text-xs">
              {{ getInitials(student.name) }}
            </span>
          </div>
          <div>
            <div class="font-medium text-red-800">{{ student.name }} ({{ student.matricId }})</div>
            <div class="text-sm text-red-600">Total Mark: {{ (student.totalMark || 0).toFixed(1) }}%</div>
          </div>
        </div>
        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-600 text-white">
          AT RISK
        </span>
      </div>
    </div>
    <div v-else class="text-center py-4 text-gray-500">
      No students currently at risk for this course (or all marks not yet submitted).
    </div>
  </div>
</template>

<script>
export default {
  name: 'AtRiskStudentsList',
  props: {
    studentsData: {
      type: Array,
      default: () => [] // This array should already be filtered for at-risk students by the parent
    }
  },
  setup() {
    const getInitials = (name) => {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    };

    return {
      getInitials
    };
  }
};
</script>