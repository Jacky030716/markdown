<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">📈</div>
      <h2 class="card-title">Anonymous Comparison</h2>
    </div>

    <div v-if="loading" class="loading-container">
      <p>Loading comparison data...</p>
    </div>

    <div v-else-if="error" class="error-container">
      <p class="error-message">{{ error }}</p>
    </div>

    <div v-else-if="tableData && tableData.length > 0" class="table-container">
      <table class="comparison-table">
        <thead>
          <tr>
            <th>Student</th>
            <th v-for="component in componentsList" :key="component.id">
              {{ component.name }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(student, index) in sortedStudents"
            :key="student.student_id"
            :class="{ 'student-row': student.is_current_student, 'class-average-row': student.is_class_average }"
          >
            <td>
              {{ student.is_current_student ? 'You' : 
                 student.is_class_average ? 'Class Average' : 
                 `Student ${getAnonymousLabel(index)}` }}
            </td>
            <td v-for="component in componentsList" :key="component.id">
              {{ formatPercentage(getComponentScore(student, component.id)) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="no-data-container">
      <p>No comparison data available for this course.</p>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'AnonymousComparison',
  props: {
    analyticsData: {
      type: Object,
      default: null,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    error: {
      type: String,
      default: null,
    },
  },
  setup(props) {
    // Extract components list
    const componentsList = computed(() => {
      return props.analyticsData?.components_list || [];
    });

    // Process table data
    const tableData = computed(() => {
      if (!props.analyticsData?.anonymous_comparison) {
        return [];
      }

      const students = props.analyticsData.anonymous_comparison;
      const classAverages = props.analyticsData.class_averages || [];

      // Calculate class average row
      const classAverageRow = {
        student_id: 'class_average',
        is_current_student: false,
        is_class_average: true,
        components: {},
        total_percentage: null
      };

      // Calculate total class average
      let totalWeightedSum = 0;
      let totalWeight = 0;

      classAverages.forEach(avg => {
        classAverageRow.components[avg.component_id] = {
          percentage: avg.average_percentage,
          weight: avg.weight
        };
        
        if (avg.average_percentage !== null) {
          totalWeightedSum += avg.average_percentage * avg.weight;
          totalWeight += avg.weight;
        }
      });

      if (totalWeight > 0) {
        classAverageRow.total_percentage = totalWeightedSum / totalWeight;
      }

      return [...students, classAverageRow];
    });

    // Sort students: current student first, then by total percentage (descending), then class average last
    const sortedStudents = computed(() => {
      if (!tableData.value) return [];

      return [...tableData.value].sort((a, b) => {
        // Current student always first
        if (a.is_current_student) return -1;
        if (b.is_current_student) return 1;
        
        // Class average always last
        if (a.is_class_average) return 1;
        if (b.is_class_average) return -1;
        
        // Sort others by total percentage (descending)
        const aTotal = a.total_percentage || 0;
        const bTotal = b.total_percentage || 0;
        return bTotal - aTotal;
      });
    });

    // Get anonymous label for students (A, B, C, etc.)
    const getAnonymousLabel = (index) => {
      // Skip the current student and class average when generating labels
      let labelIndex = 0;
      const currentStudent = sortedStudents.value.find(s => s.is_current_student);
      const currentStudentIndex = currentStudent ? sortedStudents.value.indexOf(currentStudent) : -1;
      
      if (index <= currentStudentIndex) {
        labelIndex = index - 1; // Adjust for "You" being first
      } else {
        labelIndex = index - 1; // Adjust for "You" being first
      }
      
      // Skip class average
      if (sortedStudents.value[index].is_class_average) {
        return '';
      }
      
      return String.fromCharCode(65 + Math.max(0, labelIndex)); // A, B, C, ...
    };

    // Get component score for a student
    const getComponentScore = (student, componentId) => {
      return student.components[componentId]?.percentage || null;
    };

    // Format percentage display
    const formatPercentage = (value) => {
      if (value === null || value === undefined) return '-';
      return `${value.toFixed(1)}%`;
    };

    return {
      componentsList,
      tableData,
      sortedStudents,
      getAnonymousLabel,
      getComponentScore,
      formatPercentage,
    };
  },
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

.table-container {
  overflow-x: auto;
  margin-top: 20px;
}

.comparison-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.comparison-table th,
.comparison-table td {
  padding: 12px 8px;
  text-align: center;
  border-bottom: 1px solid #e5e7eb;
}

.comparison-table th {
  background-color: #f8fafc;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #d1d5db;
}

.comparison-table tbody tr:hover {
  background-color: #f9fafb;
}

.student-row {
  background-color: #eff6ff !important;
  font-weight: 600;
}

.student-row:hover {
  background-color: #dbeafe !important;
}

.class-average-row {
  background-color: #fef3c7 !important;
  font-weight: 600;
  border-top: 2px solid #f59e0b;
}

.class-average-row:hover {
  background-color: #fde68a !important;
}

.total-cell {
  font-weight: 600;
  color: #1f2937;
}

.loading-container,
.error-container,
.no-data-container {
  padding: 40px 20px;
  text-align: center;
  color: #6b7280;
}

.error-message {
  color: #dc2626;
  font-weight: 500;
}

@media (max-width: 768px) {
  .comparison-table {
    font-size: 0.8rem;
  }
  
  .comparison-table th,
  .comparison-table td {
    padding: 8px 4px;
  }
}
</style>