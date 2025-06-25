<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">🏆</div>
      <h2 class="card-title">Class Ranking</h2>
    </div>

    <div v-if="loading" class="loading-container">
      <p>Loading ranking data...</p>
    </div>

    <div v-else-if="error" class="error-container">
      <p class="error-message">{{ error }}</p>
    </div>

    <div v-else-if="rankingData && rankingData.length > 0" class="chart-container">
      <canvas ref="rankingChart"></canvas>
    </div>

    <div v-else class="no-data-container">
      <p>No ranking data available for this course.</p>
    </div>
  </div>
</template>

<script>
import { onMounted, onUnmounted, ref, watch, nextTick } from "vue";
import { Chart, registerables } from "chart.js";

// Register all Chart.js components
Chart.register(...registerables);

export default {
  name: "ClassRanking",
  props: {
    studentId: {
      type: Number,
      required: true,
    },
    courseId: {
      type: Number,
      required: true,
    },
  },
  setup(props) {
    const rankingChart = ref(null);
    const chartInstance = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const rankingData = ref([]);

    // Function to render the chart
    const renderChart = () => {
      console.log('Attempting to render ranking chart...');
      
      if (!rankingChart.value) {
        console.error("Canvas ref is not available for ranking chart");
        return;
      }

      const ctx = rankingChart.value.getContext("2d");
      if (!ctx || !rankingData.value || rankingData.value.length === 0) {
        console.error("Canvas context is not available or no ranking data.", {
          ctx: !!ctx,
          rankingData: rankingData.value
        });
        return;
      }

      // Destroy the existing chart instance (if any) before creating a new one
      if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
      }

      try {
        // Create sample data for now (you can replace this with actual API call)
        const sampleData = [
          { student: "You", score: 94.8, isCurrentStudent: true },
          { student: "Student A", score: 94.2, isCurrentStudent: false },
          { student: "Student B", score: 92.5, isCurrentStudent: false },
          { student: "Student C", score: 89.3, isCurrentStudent: false },
          { student: "Student D", score: 85.1, isCurrentStudent: false },
        ];

        const labels = sampleData.map(item => item.student);
        const scores = sampleData.map(item => item.score);
        const backgroundColors = sampleData.map(item => 
          item.isCurrentStudent ? "rgba(34, 197, 94, 0.8)" : "rgba(156, 163, 175, 0.6)"
        );
        const borderColors = sampleData.map(item => 
          item.isCurrentStudent ? "rgba(34, 197, 94, 1)" : "rgba(156, 163, 175, 1)"
        );

        console.log('Creating ranking chart with data:', { labels, scores });

        // Create a new chart instance
        chartInstance.value = new Chart(ctx, {
          type: "bar",
          data: {
            labels: labels,
            datasets: [
              {
                label: "Total Score",
                data: scores,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 2,
                borderRadius: 8,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Horizontal bar chart
            plugins: {
              legend: {
                display: false,
              },
              tooltip: {
                callbacks: {
                  label: (context) => {
                    return `Score: ${context.raw.toFixed(1)}%`;
                  },
                },
              },
            },
            scales: {
              x: {
                beginAtZero: true,
                max: 100,
                ticks: {
                  callback: function (value) {
                    return value + "%";
                  },
                },
              },
              y: {
                ticks: {
                  color: function(context) {
                    const item = sampleData[context.index];
                    return item && item.isCurrentStudent ? "#22c55e" : "#6b7280";
                  },
                  font: function(context) {
                    const item = sampleData[context.index];
                    return {
                      weight: item && item.isCurrentStudent ? "bold" : "normal"
                    };
                  }
                },
              },
            },
          },
        });

        console.log('Ranking chart created successfully');
      } catch (error) {
        console.error('Error creating ranking chart:', error);
      }
    };

    // Load ranking data (placeholder for now)
    const loadRankingData = () => {
      loading.value = true;
      error.value = null;

      // Simulate API call
      setTimeout(() => {
        try {
          // This is sample data - replace with actual API call
          rankingData.value = [
            { student: "You", score: 94.8, isCurrentStudent: true },
            { student: "Student A", score: 94.2, isCurrentStudent: false },
            { student: "Student B", score: 92.5, isCurrentStudent: false },
            { student: "Student C", score: 89.3, isCurrentStudent: false },
            { student: "Student D", score: 85.1, isCurrentStudent: false },
          ];
          
          nextTick(() => {
            renderChart();
          });
        } catch (err) {
          error.value = "Failed to load ranking data";
          console.error("Error loading ranking data:", err);
        } finally {
          loading.value = false;
        }
      }, 1000);
    };

    // Watch for prop changes
    watch(
      [() => props.studentId, () => props.courseId],
      ([newStudentId, newCourseId], [oldStudentId, oldCourseId]) => {
        if (newStudentId && newCourseId && 
            (newStudentId !== oldStudentId || newCourseId !== oldCourseId)) {
          loadRankingData();
        }
      },
      { immediate: true }
    );

    // Initial load when the component is mounted
    onMounted(() => {
      console.log('ClassRanking mounted with props:', {
        studentId: props.studentId,
        courseId: props.courseId
      });

      // Wait for next tick to ensure DOM is ready
      nextTick(() => {
        if (props.studentId && props.courseId) {
          loadRankingData();
        }
      });
    });

    // Cleanup on unmount
    onUnmounted(() => {
      if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
      }
    });

    return {
      rankingChart,
      loading,
      error,
      rankingData,
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

.chart-container {
  height: 300px;
  margin-top: 20px;
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
</style>