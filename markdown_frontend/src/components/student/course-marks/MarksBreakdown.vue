<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">📝</div>
      <h2 class="card-title">Marks Breakdown</h2>
    </div>
    <div v-if="marksData && marksData.length > 0" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Assessment</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Your Mark</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (%)</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Weighted Score</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="mark in marksData" :key="`${mark.component_id}-${mark.name}`">
            <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium text-gray-900">
              {{ mark.name }}
            </td>
            <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium text-gray-900 capitalize">
              {{ mark.type }}
            </td>
            <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-900">
              <template v-if="mark.mark !== null && mark.mark !== undefined">
                {{ mark.mark }} / {{ mark.max_mark }}
              </template>
              <template v-else>
                <span class="text-gray-400">Not graded</span>
              </template>
            </td>
            <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-500">
              {{ mark.weight }}%
            </td>
            <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-900">
              <template v-if="mark.mark !== null && mark.mark !== undefined">
                {{ getWeightedScore(mark).toFixed(2) }}
              </template>
              <template v-else>
                <span class="text-gray-400">-</span>
              </template>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Total Weight Score Row -->
      <div class="bg-gray-50 border-t border-gray-200">
        <table class="min-w-full">
          <tbody>
            <tr>
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-semibold text-gray-800" colspan="4">
                TOTAL WEIGHTED SCORE
              </td>
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-semibold text-gray-800">
                {{ totalWeightScore.toFixed(2) }} / {{ totalMaxWeightedScore.toFixed(2) }}
              </td>
            </tr>
            <!-- Grade Row -->
            <tr class="border-t border-gray-200">
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-semibold text-gray-800" colspan="4">
                GRADE
              </td>
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-semibold text-gray-800">
                {{ calculateGrade(finalPercentage) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="text-center p-6 text-gray-600">
      <p>No marks available for this course.</p>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'MarksBreakdown',
  props: {
    marksData: {
      type: Array,
      default: () => []
    },
    studentId: {
      type: Number,
      required: false
    },
    courseId: {
      type: Number,
      required: false
    }
  },
  setup(props) {
    // Calculate weighted score for a single mark
    const getWeightedScore = (mark) => {
      if (mark.mark === null || mark.mark === undefined || !mark.max_mark || !mark.weight) {
        return 0;
      }
      
      const percentage = (parseFloat(mark.mark) / parseFloat(mark.max_mark)) * 100;
      const weightedScore = (percentage * parseFloat(mark.weight)) / 100;
      return weightedScore;
    };

    // Calculate total weighted score (sum of all weighted scores)
    const totalWeightScore = computed(() => {
      if (!props.marksData || props.marksData.length === 0) {
        return 0;
      }
      
      return props.marksData.reduce((sum, mark) => {
        return sum + getWeightedScore(mark);
      }, 0);
    });

    // Calculate total maximum weighted score (sum of all weights)
    const totalMaxWeightedScore = computed(() => {
      if (!props.marksData || props.marksData.length === 0) {
        return 0;
      }
      
      return props.marksData.reduce((sum, mark) => {
        return sum + (parseFloat(mark.weight) || 0);
      }, 0);
    });

    // Calculate final percentage
    const finalPercentage = computed(() => {
      if (totalMaxWeightedScore.value === 0) {
        return 0;
      }
      
      return totalWeightScore.value;
    });

    // Grade calculation function
    const calculateGrade = (mark) => {
      if (mark >= 90) return 'A+';
      if (mark >= 80) return 'A';
      if (mark >= 75) return 'A-';
      if (mark >= 70) return 'B+';
      if (mark >= 65) return 'B';
      if (mark >= 60) return 'B-';
      if (mark >= 55) return 'C+';
      if (mark >= 50) return 'C';
      if (mark >= 45) return 'C-';
      if (mark >= 40) return 'D+';
      if (mark >= 35) return 'D';
      if (mark >= 30) return 'D-';
      return 'E';
    };

    return {
      getWeightedScore,
      totalWeightScore,
      totalMaxWeightedScore,
      finalPercentage,
      calculateGrade
    };
  }
};
</script>

<style scoped>
.card {
  background: white;
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border: 1px solid #e0e7ff;
}

.card-header {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e0e7ff;
}

.card-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  margin-right: 15px;
}

.card-title {
  font-size: 1.4rem;
  font-weight: 600;
  color: #1e293b;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e0e7ff;
}

th {
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
}

tbody tr:hover {
  background: #f8fafc;
}

.text-center {
  text-align: center;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.font-semibold {
  font-weight: 600;
}
</style>