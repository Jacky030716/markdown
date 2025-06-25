<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">📊</div>
      <h2 class="card-title">Performance Analytics & Class Average per Component</h2>
    </div>

    <div v-if="loading" class="loading-container">
      <p>Loading analytics...</p>
    </div>

    <div v-else-if="error" class="error-container">
      <p class="error-message">{{ error }}</p>
    </div>

    <div v-else-if="chartData && chartData.length > 0" class="chart-container">
      <canvas ref="performanceChart"></canvas>
    </div>

    <div v-else class="no-data-container">
      <p>No performance data available for this course.</p>
    </div>
  </div>
</template>

<script>
import { onMounted, onUnmounted, watch, nextTick, ref, computed } from "vue";
import { Chart, registerables } from "chart.js";

// Register all Chart.js components
Chart.register(...registerables);

export default {
  name: "PerformanceAnalytics",
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
    const performanceChart = ref(null);
    const chartInstance = ref(null);

    // Computed property to process the analytics data for the chart
    const chartData = computed(() => {
      console.log('Computing chartData with:', {
        hasAnalyticsData: !!props.analyticsData,
        loading: props.loading,
        error: props.error,
        analyticsData: props.analyticsData
      });
      
      if (props.loading || props.error) {
        return null;
      }
      
      if (!props.analyticsData || !props.analyticsData.student_marks || !props.analyticsData.class_averages) {
        console.log('Missing required data in analyticsData', {
          hasAnalyticsData: !!props.analyticsData,
          hasStudentMarks: !!(props.analyticsData?.student_marks),
          hasClassAverages: !!(props.analyticsData?.class_averages)
        });
        return null;
      }

      const studentMarks = props.analyticsData.student_marks;
      const classAverages = props.analyticsData.class_averages;

      // Create a map of class averages by component ID for easy lookup
      const averagesMap = {};
      classAverages.forEach(avg => {
        averagesMap[avg.component_id] = avg.average_percentage || 0;
      });

      // Process student marks and match with class averages
      const processedData = studentMarks.map(mark => ({
        name: mark.name,
        studentScore: mark.percentage || 0,
        classAverage: averagesMap[mark.component_id] || 0,
        type: mark.type,
        weight: mark.weight
      }));

      console.log('Processed chart data:', processedData);
      return processedData;
    });

    // Function to render the chart
    const renderChart = () => {
      console.log('Attempting to render chart...');
      
      if (!performanceChart.value) {
        console.error("Canvas ref is not available");
        return;
      }

      const ctx = performanceChart.value.getContext("2d");
      if (!ctx || !chartData.value) {
        console.error("Canvas context is not available or no chart data.", {
          ctx: !!ctx,
          chartData: chartData.value
        });
        return;
      }

      // Destroy the existing chart instance (if any) before creating a new one
      if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
      }

      const labels = chartData.value.map(item => item.name);
      const studentScores = chartData.value.map(item => item.studentScore);
      const classAverages = chartData.value.map(item => item.classAverage);

      console.log('Creating chart with data:', { labels, studentScores, classAverages });

      try {
        // Create a new chart instance
        chartInstance.value = new Chart(ctx, {
          type: "bar",
          data: {
            labels: labels,
            datasets: [
              {
                label: "Your Score",
                data: studentScores,
                backgroundColor: "rgba(102, 126, 234, 0.8)",
                borderColor: "rgba(102, 126, 234, 1)",
                borderWidth: 2,
                borderRadius: 10,
              },
              {
                label: "Class Average",
                data: classAverages,
                backgroundColor: "rgba(118, 75, 162, 0.6)",
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
                    return `${context.dataset.label}: ${context.raw.toFixed(1)}%`;
                  },
                  afterLabel: (context) => {
                    const dataIndex = context.dataIndex;
                    const item = chartData.value[dataIndex];
                    return `Weight: ${item.weight}% | Type: ${item.type}`;
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
                    return value + "%";
                  },
                },
              },
              x: {
                ticks: {
                  maxRotation: 45,
                  minRotation: 0,
                },
              },
            },
          },
        });

        console.log('Chart created successfully');
      } catch (error) {
        console.error('Error creating chart:', error);
      }
    };

    // Watch for data changes and render chart
    watch(
      [() => props.analyticsData, () => props.loading, () => props.error],
      ([newAnalyticsData, newLoading, newError], [oldAnalyticsData, oldLoading, oldError]) => {
        console.log('Props changed:', { 
          hasAnalyticsData: !!newAnalyticsData, 
          loading: newLoading, 
          error: newError,
          chartDataLength: chartData.value?.length,
          propsChanged: {
            analyticsData: newAnalyticsData !== oldAnalyticsData,
            loading: newLoading !== oldLoading,
            error: newError !== oldError
          }
        });
        
        // Only render if we have data, not loading, and no error
        if (newAnalyticsData && !newLoading && !newError && chartData.value && chartData.value.length > 0) {
          console.log('Conditions met for rendering chart...');
          nextTick(() => {
            renderChart();
          });
        } else if (newLoading || newError || !newAnalyticsData) {
          // Clear chart if loading, error, or no data
          if (chartInstance.value) {
            chartInstance.value.destroy();
            chartInstance.value = null;
          }
        }
      },
      { immediate: true }
    );

    // Initial render when the component is mounted
    onMounted(() => {
      console.log('PerformanceAnalytics mounted with props:', {
        hasAnalyticsData: !!props.analyticsData,
        loading: props.loading,
        error: props.error
      });

      // Only render if we already have data
      if (props.analyticsData && !props.loading && !props.error) {
        nextTick(() => {
          if (chartData.value && chartData.value.length > 0) {
            renderChart();
          }
        });
      }
    });

    // Cleanup on unmount
    onUnmounted(() => {
      if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
      }
    });

    return { 
      performanceChart,
      chartData
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