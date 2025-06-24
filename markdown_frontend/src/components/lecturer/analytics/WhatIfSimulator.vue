<template>
  <div v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div
      class="max-h-[90vh] overflow-y-auto relative p-8 border w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-2xl font-bold text-gray-900">
          "What-If" Simulator for {{ student?.name }} ({{ student?.matricId }})
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl font-semibold">
          &times;
        </button>
      </div>

      <div v-if="student && Object.keys(simulatedMarks).length > 0" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Mark Inputs for Simulation -->
        <div>
          <h4 class="text-lg font-semibold text-gray-800 mb-3">Simulate Marks:</h4>
          <div v-for="(markEntry, componentKey) in simulatedMarks" :key="componentKey" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ markEntry.component_name }} (Max: {{ markEntry.max_mark }}, Weight: {{ markEntry.weight }}%)
            </label>
            <input v-model.number="markEntry.student_mark" type="number" :min="0" :max="markEntry.max_mark"
              :placeholder="`Enter mark (0-${markEntry.max_mark})`"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Simulation Results -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
          <h4 class="text-lg font-semibold text-gray-800 mb-3">Simulation Results:</h4>
          <div class="space-y-3">
            <p class="text-gray-700">
              <span class="font-medium">Original Total Mark:</span>
              <span class="ml-2 font-bold text-blue-600">{{ (originalStudentTotalMark || 0).toFixed(1) }}%</span>
            </p>
            <p class="text-gray-700">
              <span class="font-medium">Original Grade:</span>
              <span class="ml-2 font-bold text-blue-600">{{ originalStudentGrade }}</span>
            </p>
            <hr class="my-4 border-gray-200">
            <p class="text-gray-700">
              <span class="font-medium">Simulated Total Mark:</span>
              <span class="ml-2 text-xl font-bold" :class="getTotalColorClass(simulatedTotalMark)">
                {{ (simulatedTotalMark || 0).toFixed(1) }}%
              </span>
            </p>
            <p class="text-gray-700">
              <span class="font-medium">Simulated Grade:</span>
              <span class="ml-2 text-xl font-bold" :class="getGradeColorClass(simulatedGrade)">
                {{ simulatedGrade }}
              </span>
            </p>
          </div>
          <!-- Optional: Small chart visualizing component contribution under simulation -->
          <div v-if="chartData.datasets[0].data.length > 0" class="mt-6 h-48 w-full">
            <canvas ref="simulatedChartCanvas"></canvas>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-8 text-gray-500">
        No student or assessment components selected for simulation.
      </div>

      <div class="mt-6 flex justify-end">
        <button @click="$emit('close')"
          class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
          Close Simulator
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import Chart from 'chart.js/auto'; // For the optional mini-chart

export default {
  name: 'WhatIfSimulator',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    student: {
      type: Object,
      default: null // The student object whose marks are being simulated
    },
    assessmentComponents: {
      type: Array,
      default: () => [] // All assessment components for the current course
    }
  },
  emits: ['close', 'simulate-change'], // Emit 'simulate-change' to parent if dynamic update needed there

  setup(props, { emit }) {
    const simulatedMarks = ref({});
    const originalStudentTotalMark = ref(0);
    const originalStudentGrade = ref('');

    let chartInstance = null; // For Chart.js instance

    // --- Chart Helpers (re-used from other components) ---
    const getTotalColorClass = (total) => {
      if (!total) return 'text-gray-400';
      if (total >= 80) return 'text-green-600';
      if (total >= 70) return 'text-blue-600';
      if (total >= 60) return 'text-yellow-600';
      if (total >= 50) return 'text-orange-600';
      return 'text-red-600';
    };

    const getGradeColorClass = (grade) => {
      const gradeClasses = {
        'A+': 'bg-green-100 text-green-800',
        'A': 'bg-green-100 text-green-800',
        'B': 'bg-blue-100 text-blue-800',
        'C': 'bg-yellow-100 text-yellow-800',
        'D': 'bg-orange-100 text-orange-800',
        'E': 'bg-red-100 text-red-800',
        'F': 'bg-gray-100 text-gray-800'
      };
      // Use the grade directly for class
      return gradeClasses[grade] ? gradeClasses[grade].replace('bg-green-100', 'bg-blue-100').replace('text-green-800', 'text-blue-800') : ''; // Simpler visual for chart labels
    };

    const calculateGrade = (totalMark) => {
      if (totalMark >= 90) return 'A+';
      if (totalMark >= 80) return 'A';
      if (totalMark >= 70) return 'B';
      if (totalMark >= 60) return 'C';
      if (totalMark >= 50) return 'D';
      if (totalMark >= 40) return 'E';
      return 'F';
    };
    // --- End Chart Helpers ---

    /**
     * Helper to get the lower-cased, hyphen-removed key for a component name.
     */
    const getComponentKey = (componentName) => {
      return componentName.toLowerCase().replace(/[\s-]/g, '');
    };

    // Watch for student prop changes to initialize simulated marks
    watch(() => props.student, (newStudent) => {
      if (newStudent) {
        // Deep copy original marks
        const tempSimulatedMarks = {};
        props.assessmentComponents.forEach(comp => {
          const key = getComponentKey(comp.component_name);
          const originalMarkEntry = newStudent.marks[key];

          tempSimulatedMarks[key] = {
            component_id: comp.component_id,
            component_name: comp.component_name,
            component_type: comp.component_type,
            max_mark: comp.max_mark,
            weight: comp.weight,
            student_mark: originalMarkEntry ? originalMarkEntry.student_mark : null // Use original or null
          };
        });
        simulatedMarks.value = tempSimulatedMarks;

        originalStudentTotalMark.value = newStudent.totalMark || 0;
        originalStudentGrade.value = newStudent.grade || 'F';

        // Recreate chart with new data
        if (props.show) { // Only create if modal is visible
          setTimeout(createChart, 0); // Ensure DOM is updated before chart creation
        }
      } else {
        simulatedMarks.value = {}; // Reset if no student
      }
    }, { immediate: true, deep: true });

    // Recreate chart when modal becomes visible or marks change
    watch([() => props.show, simulatedMarks], ([newShow], [oldSimulatedMarks, newSimulatedMarks]) => {
      if (newShow && props.student) {
        setTimeout(createChart, 0); // Defer chart creation until modal is rendered
      } else if (!newShow && chartInstance) {
        chartInstance.destroy(); // Destroy chart when modal closes
        chartInstance = null;
      }
    }, { deep: true });


    // Computed properties for simulation results
    const simulatedTotalMark = computed(() => {
      let overallWeightedTotal = 0;
      props.assessmentComponents.forEach(component => {
        const componentKey = getComponentKey(component.component_name);
        const markEntry = simulatedMarks.value[componentKey];

        if (markEntry && markEntry.student_mark !== null && !isNaN(markEntry.student_mark) && markEntry.max_mark > 0) {
          const mark = parseFloat(markEntry.student_mark);
          const max = parseFloat(markEntry.max_mark);
          const weight = parseFloat(markEntry.weight);
          overallWeightedTotal += (mark / max) * weight;
        }
      });
      const total = parseFloat(overallWeightedTotal.toFixed(1));
      emit('simulate-change', { studentId: props.student.id, simulatedTotalMark: total, simulatedGrade: calculateGrade(total) });
      return total;
    });

    const simulatedGrade = computed(() => {
      return calculateGrade(simulatedTotalMark.value);
    });

    // Chart.js data for component contribution (optional)
    const chartData = computed(() => {
      const labels = [];
      const data = [];
      const backgroundColors = [];
      const borderColors = [];

      Object.values(simulatedMarks.value).forEach(markEntry => {
        if (markEntry.student_mark !== null && markEntry.max_mark > 0) {
          labels.push(markEntry.component_name);
          // Calculate actual percentage contribution to the 100% total
          const percentageContribution = (markEntry.student_mark / markEntry.max_mark) * markEntry.weight;
          data.push(parseFloat(percentageContribution.toFixed(2)));

          // Assign dynamic colors for chart segments
          const hue = (markEntry.component_id * 137) % 360; // Randomize hue for distinct colors
          backgroundColors.push(`hsla(${hue}, 70%, 60%, 0.7)`);
          borderColors.push(`hsla(${hue}, 70%, 50%, 1)`);
        }
      });

      return {
        labels: labels,
        datasets: [{
          label: 'Weighted Contribution (%)',
          data: data,
          backgroundColor: backgroundColors,
          borderColor: borderColors,
          borderWidth: 1
        }]
      };
    });

    const simulatedChartCanvas = ref(null);

    const createChart = () => {
      if (chartInstance) {
        chartInstance.destroy();
      }
      if (!simulatedChartCanvas.value) return;

      const ctx = simulatedChartCanvas.value.getContext('2d');
      chartInstance = new Chart(ctx, {
        type: 'doughnut', // Or 'bar' for different visualization
        data: chartData.value,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'right',
            },
            title: {
              display: true,
              text: 'Simulated Component Contribution',
              position: 'top',
              padding: {
                bottom: 20
              }
            },
            tooltip: {
              padding: {
                top: 10
              },
              callbacks: {
                label: function (context) {
                  let label = context.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed !== null) {
                    label += context.parsed.toFixed(1) + '%';
                  }
                  return label;
                }
              }
            }
          }
        }
      });
    };

    onUnmounted(() => {
      if (chartInstance) {
        chartInstance.destroy();
      }
    });


    return {
      simulatedMarks,
      originalStudentTotalMark,
      originalStudentGrade,
      simulatedTotalMark,
      simulatedGrade,
      getTotalColorClass,
      getGradeColorClass,
      simulatedChartCanvas,
      chartData // Expose chartData for the template/chart creation
    };
  }
};
</script>