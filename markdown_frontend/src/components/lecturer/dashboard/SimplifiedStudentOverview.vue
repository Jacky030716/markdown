<template>
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-xs text-gray-700 uppercase bg-gray-100">
        <tr>
          <th scope="col" class="px-6 py-3">Student Name</th>
          <th scope="col" class="px-6 py-3 text-center">Matric ID</th>
          <th scope="col" class="px-6 py-3 text-center">Total Mark (%)</th>
          <th scope="col" class="px-6 py-3 text-center">Grade</th>
          <th scope="col" class="px-6 py-3 text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="studentsData.length === 0">
          <td colspan="5" class="px-6 py-4 text-center text-gray-500">No students to display for this course.</td>
        </tr>
        <tr v-else v-for="student in studentsData.slice(0, Math.min(studentsData.length, 5))" :key="student.id"
          class="bg-white border-b border-b-gray-100 hover:bg-gray-50">
          <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
            <div class="flex items-center">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                <span class="text-blue-600 font-medium text-xs">
                  {{ getInitials(student.name) }}
                </span>
              </div>
              <div>{{ student.name }}</div>
            </div>
          </td>
          <td class="px-6 py-4 text-center">{{ student.matricId }}</td>
          <td class="px-6 py-4 text-center">
            <span :class="getTotalColorClass(student.totalMark)">
              {{ (student.totalMark || 0).toFixed(1) }}%
            </span>
          </td>
          <td class="px-6 py-4 text-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              :class="getGradeColorClass(student.grade)">
              {{ student.grade }}
            </span>
          </td>
          <td class="px-6 py-4 text-center">
            <span v-if="student.allMarksGiven && (student.totalMark || 0) < 40"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
              At Risk
            </span>
            <span v-else-if="!student.allMarksGiven"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
              Incomplete
            </span>
            <span v-else
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
              On Track
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { getTotalColorClass, getGradeColorClass } from "../../../utils/student-management/utils"

export default {
  name: 'SimplifiedStudentOverview',
  props: {
    studentsData: {
      type: Array,
      default: () => []
    }
  },
  setup() {
    const getInitials = (name) => {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    };

    return {
      getInitials,
      getTotalColorClass,
      getGradeColorClass
    };
  }
};
</script>