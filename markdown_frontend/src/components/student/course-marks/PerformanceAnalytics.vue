<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">📊</div>
      <h2 class="card-title">Performance Analytics</h2>
    </div>

    <!-- Canvas for Chart.js -->
    <div class="chart-container">
      <canvas ref="performanceChart"></canvas> <!-- Match the ref name -->
    </div>
  </div>
</template>

<script>
import { onMounted, onUnmounted, watch, nextTick, ref } from "vue"; // Import Vue hooks
import { Chart, registerables } from "chart.js"; // Import Chart.js with registerables

// Register all Chart.js components
Chart.register(...registerables);

export default {
  name: "PerformanceAnalytics",
  props: {
    marksData: {
      type: Array,
      required: true,
    },
  },
  setup(props) {
    const performanceChart = ref(null); // Match the template ref name
    const chartInstance = ref(null); // Store the chart instance to destroy it later if necessary

    // Function to render the chart
    const renderChart = () => {
      const ctx = performanceChart.value?.getContext("2d"); // Access the canvas context

      if (!ctx) {
        console.error("Canvas context is not available.");
        return;
      }

      // Example Data: 'Your Score' and 'Class Average'
      const yourScores = props.marksData.map((mark) => parseFloat(mark.mark) || 0);
      const classAverages = props.marksData.map(() => Math.floor(Math.random() * 100)); // Replace with actual data

      // Destroy the existing chart instance (if any) before creating a new one
      if (chartInstance.value) {
        chartInstance.value.destroy();
      }

      // Create a new chart instance
      chartInstance.value = new Chart(ctx, {
        type: "bar", // Bar chart type
        data: {
          labels: props.marksData.map((mark) => mark.name), // Use assessment names as labels
          datasets: [
            {
              label: "Your Score",
              data: yourScores,
              backgroundColor: "rgba(102, 126, 234, 0.8)", // Custom color for your scores
              borderColor: "rgba(102, 126, 234, 1)",
              borderWidth: 2,
              borderRadius: 10,
            },
            {
              label: "Class Average",
              data: classAverages,
              backgroundColor: "rgba(118, 75, 162, 0.6)", // Custom color for class averages
              borderColor: "rgba(118, 75, 162, 1)",
              borderWidth: 2,
              borderRadius: 10,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "top",
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  return `${context.dataset.label}: ${context.raw}%`;
                },
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              ticks: {
                callback: function (value) {
                  return value + "%"; // Show percentage on y-axis
                },
              },
            },
          },
        },
      });
    };

    // Watch for changes in marksData prop to re-render the chart if data changes
    watch(
      () => props.marksData,
      (newMarksData) => {
        if (newMarksData && newMarksData.length > 0) {
          nextTick(() => {
            renderChart(); // Re-render the chart when data changes and DOM updates
          });
        }
      },
      { immediate: false } // Don't trigger immediately, let onMounted handle initial render
    );

    // Initial render when the component is mounted
    onMounted(() => {
      nextTick(() => {
        if (props.marksData && props.marksData.length > 0) {
          renderChart(); // Initial render if data is available
        }
      });
    });

    // Cleanup on unmount
    onUnmounted(() => {
      if (chartInstance.value) {
        chartInstance.value.destroy();
      }
    });

    return { performanceChart }; // Return the correct ref name
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

.chart-container {
  height: 300px;
  margin-top: 20px;
}
</style>