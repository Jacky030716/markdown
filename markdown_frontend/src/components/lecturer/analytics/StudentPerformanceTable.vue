<template>
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-gray-700 uppercase bg-gray-100">
        <tr>
          <th scope="col" class="px-6 py-4 cursor-pointer" @click="sortStudents('name')">
            <div class="flex items-center">
              <span class="font-medium">Student Name</span>
              <ArrowUpDown class="inline-block w-4 h-4 ml-1 text-gray-400" />
            </div>
          </th>
          <th scope="col" class="px-6 py-4 cursor-pointer" @click="sortStudents('totalMark')">
            <div class="flex items-center justify-center">
              <span class="font-medium">Total Mark (%)</span>
              <ArrowUpDown class="inline-block w-4 h-4 ml-1 text-gray-400" />
            </div>
          </th>
          <th scope="col" class="px-6 py-4 cursor-pointer" @click="sortStudents('grade')">
            <div class="flex items-center justify-center">
              <span class="font-medium">Grade</span>
              <ArrowUpDown class="inline-block w-4 h-4 ml-1 text-gray-400" />
            </div>
          </th>
          <th scope="col" class="px-6 py-4 text-center">Status</th>
          <th scope="col" class="px-6 py-4 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="sortedStudents.length === 0">
          <td colspan="6" class="px-6 py-4 text-center text-gray-500">No students to display for this course.</td>
        </tr>
        <tr v-else v-for="student in sortedStudents" :key="student.id"
          class="bg-white border-b border-b-gray-100 hover:bg-gray-50">
          <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center mr-3">
                <span class="text-sky-600 font-medium text-sm">
                  {{ getInitials(student.name) }}
                </span>
              </div>
              <div>
                <div class="text-base font-semibold">{{ student.name }}</div>
                <div class="font-normal text-gray-500">{{ student.matricId }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 font-semibold text-center">
            <span :class="getTotalColorClass(student.totalMark)">
              {{ (student.totalMark || 0).toFixed(1) }}%
            </span>
          </td>
          <td class="px-6 py-4 text-center">
            <span class="size-8 inline-flex items-center justify-center px-2 py-1 text-xs font-semibold rounded-full"
              :class="getGradeColorClass(student.grade)">
              {{ student.grade }}
            </span>
          </td>
          <td class="px-6 py-4 text-center">
            <span v-if="student.isAtRisk"
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-12a1 1 0 10-2 0v4a1 1 0 102 0V6zm3 4a1 1 0 10-2 0v4a1 1 0 102 0v-4z"
                  clip-rule="evenodd"></path>
              </svg>
              At Risk
            </span>
            <span v-else
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
              On Track
            </span>
          </td>
          <td class="px-6 py-4 text-center">
            <button @click="$emit('view-student-details', student)"
              class="font-medium text-sky-600 hover:text-sky-700 transition-colors cursor-pointer">View Details</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { getGradeColorClass, getTotalColorClass } from '../../../utils/student-management/utils';
import { ArrowUpDown } from 'lucide-vue-next';

export default {
  name: 'StudentPerformanceTable',
  components: {
    ArrowUpDown
  },
  props: {
    studentsData: {
      type: Array,
      default: () => []
    },
    atRiskThreshold: {
      type: Number,
      default: 2.0
    }
  },
  emits: ['view-student-details'],
  setup(props) {
    const sortKey = ref('');
    const sortOrder = ref('asc'); // 'asc' or 'desc'

    const sortedStudents = computed(() => {
      if (!sortKey.value) {
        return props.studentsData;
      }

      return [...props.studentsData].sort((a, b) => {
        let valA = a[sortKey.value];
        let valB = b[sortKey.value];

        // Handle numeric sorting for marks and CGPA
        if (sortKey.value === 'totalMark' || sortKey.value === 'cgpa') {
          valA = parseFloat(valA || 0);
          valB = parseFloat(valB || 0);
        } else if (sortKey.value === 'grade') {
          // Custom sort for grades (A+ > A > B...)
          const gradeOrder = { 'A+': 6, 'A': 5, 'B': 4, 'C': 3, 'D': 2, 'E': 1, 'F': 0 };
          valA = gradeOrder[valA] || 0;
          valB = gradeOrder[valB] || 0;
        } else {
          valA = valA ? valA.toString().toLowerCase() : '';
          valB = valB ? valB.toString().toLowerCase() : '';
        }

        if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1;
        if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1;
        return 0;
      });
    });

    const sortStudents = (key) => {
      if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
      } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
      }
    };

    const getInitials = (name) => {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    };

    const getCgpaColorClass = (cgpa) => {
      if (!cgpa) return 'text-gray-400';
      if (cgpa >= 3.5) return 'text-green-600';
      if (cgpa >= 3.0) return 'text-sky-600';
      if (cgpa >= 2.5) return 'text-yellow-600';
      return 'text-red-600'; // Below 2.5 often indicates caution
    };

    return {
      sortKey,
      sortOrder,
      getCgpaColorClass,
      getGradeColorClass,
      sortedStudents,
      sortStudents,
      getInitials,
      getTotalColorClass,
      getCgpaColorClass
    };
  }
};
</script>