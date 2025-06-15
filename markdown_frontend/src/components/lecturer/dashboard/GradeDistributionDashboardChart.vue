<template>
  <div class="h-full w-full">
    <canvas ref="gradeChartCanvas"></canvas>
  </div>
</template>

<script>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import Chart from 'chart.js/auto'; // Import Chart.js

export default {
  name: 'GradeDistributionDashboardChart',
  props: {
    gradesData: {
      type: Object, // Expects an object like { 'A+': 5, 'A': 10, 'B': 15, ... }
      default: () => ({})
    }
  },
  setup(props) {
    const gradeChartCanvas = ref(null);
    let chartInstance = null;

    const createChart = () => {
      if (chartInstance) {
        chartInstance.destroy(); // Destroy existing chart before creating a new one
      }

      const ctx = gradeChartCanvas.value.getContext('2d');
      // Define a comprehensive list of grades for consistent axis, even if count is 0
      const labels = ['A+', 'A', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'E', 'F'];
      const data = labels.map(label => props.gradesData[label] || 0);

      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Number of Students',
            data: data,
            backgroundColor: [
              'rgba(75, 192, 192, 0.7)', // A+
              'rgba(54, 162, 235, 0.7)', // A
              'rgba(75, 192, 192, 0.5)', // B+
              'rgba(54, 162, 235, 0.5)', // B
              'rgba(75, 192, 192, 0.3)', // B-
              'rgba(255, 206, 86, 0.7)', // C+
              'rgba(255, 206, 86, 0.5)', // C
              'rgba(255, 206, 86, 0.3)', // C-
              'rgba(255, 159, 64, 0.7)', // D+
              'rgba(255, 159, 64, 0.5)', // D
              'rgba(255, 159, 64, 0.3)', // D-
              'rgba(255, 99, 132, 0.7)', // E
            ],
            borderColor: [
              'rgba(75, 192, 192, 1)', // A+
              'rgba(54, 162, 235, 1)', // A
              'rgba(75, 192, 192, 0.8)', // B+
              'rgba(54, 162, 235, 0.8)', // B
              'rgba(75, 192, 192, 0.6)', // B-
              'rgba(255, 206, 86, 1)', // C+
              'rgba(255, 206, 86, 0.8)', // C
              'rgba(255, 206, 86, 0.6)', // C-
              'rgba(255, 159, 64, 1)', // D+
              'rgba(255, 159, 64, 0.8)', // D
              'rgba(255, 159, 64, 0.6)', // D-
              'rgba(255, 99, 132, 1)', // E
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false, // Allows chart to fill parent container
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Number of Students'
              },
              ticks: {
                stepSize: 1, // Ensure whole numbers for student count
                precision: 0 // No decimals
              }
            },
            x: {
              title: {
                display: true,
                text: 'Grade'
              }
            }
          },
          plugins: {
            legend: {
              display: false // No need for legend in single dataset bar chart
            },
            title: {
              display: true,
              text: 'Grade Distribution Across Class'
            }
          }
        }
      });
    };

    onMounted(() => {
      createChart();
    });

    watch(() => props.gradesData, () => {
      createChart(); // Recreate chart when data changes
    }, { deep: true }); // Watch deeply for changes within the object

    onUnmounted(() => {
      if (chartInstance) {
        chartInstance.destroy(); // Clean up chart instance when component is unmounted
      }
    });

    return {
      gradeChartCanvas
    };
  }
};
</script>