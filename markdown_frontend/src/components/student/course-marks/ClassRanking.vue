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

    <div v-else-if="rankingData" class="ranking-content">
      <!-- Student Position Card -->
      <div class="ranking-card">
        <h3>Your Rank</h3>
        <div class="rank">{{ rankingData.current_student.position_text }}</div>
        <div class="percentile">{{ rankingData.current_student.percentile_text }}</div>
        <div class="score">Total Score: {{ rankingData.current_student.total_score }}%</div>
      </div>

      <!-- Chart Container -->
      <div class="chart-container">
        <canvas ref="rankingChart"></canvas>
      </div>
    </div>

    <div v-else class="no-data-container">
      <p>No ranking data available for this course.</p>
    </div>
  </div>
</template>

<script>
import { onMounted, onUnmounted, ref, watch, nextTick } from "vue";
import { Chart, registerables } from "chart.js";
import studentsApi from "../../../api/students"; // Adjust path as needed

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
    const rankingData = ref(null);

    // Function to render the doughnut chart
    const renderChart = () => {
      console.log('Attempting to render ranking chart...');
      
      if (!rankingChart.value || !rankingData.value) {
        console.error("Canvas ref or ranking data not available");
        return;
      }

      const ctx = rankingChart.value.getContext("2d");
      if (!ctx) {
        console.error("Canvas context is not available");
        return;
      }

      // Destroy the existing chart instance (if any) before creating a new one
      if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
      }

      try {
        const distribution = rankingData.value.class_distribution;
        
        // Prepare chart data
        const chartLabels = ['Above You', 'Your Position', 'Below You'];
        const chartData = [
          distribution.above_percentage,
          distribution.current_percentage,
          distribution.below_percentage
        ];

        console.log('Creating ranking chart with data:', { chartLabels, chartData });

        // Create a new doughnut chart instance
        chartInstance.value = new Chart(ctx, {
          type: "doughnut",
          data: {
            labels: chartLabels,
            datasets: [{
              data: chartData,
              backgroundColor: [
                'rgba(239, 68, 68, 0.8)',   // Red for above
                'rgba(34, 197, 94, 0.8)',   // Green for current student
                'rgba(156, 163, 175, 0.8)'  // Gray for below
              ],
              borderColor: [
                'rgba(239, 68, 68, 1)',
                'rgba(34, 197, 94, 1)',
                'rgba(156, 163, 175, 1)'
              ],
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  padding: 20,
                  usePointStyle: true,
                  font: {
                    size: 12
                  }
                }
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed;
                    const count = label === 'Above You' ? distribution.students_above :
                                 label === 'Your Position' ? 1 :
                                 distribution.students_below;
                    return `${label}: ${value}% (${count} student${count !== 1 ? 's' : ''})`;
                  }
                }
              }
            },
            cutout: '60%', // Creates the doughnut hole
          }
        });

        console.log('Ranking chart created successfully');
      } catch (error) {
        console.error('Error creating ranking chart:', error);
      }
    };

    // Load ranking data from API
    const loadRankingData = async () => {
      if (!props.studentId || !props.courseId) {
        return;
      }

      loading.value = true;
      error.value = null;
      rankingData.value = null;

      try {
        console.log(`Fetching ranking for student ${props.studentId}, course ${props.courseId}`);
        
        const response = await studentsApi.getRanking(props.studentId, props.courseId);
        
        if (response.status === "success" && response.data) {
          rankingData.value = response.data;
          console.log("Ranking data loaded:", response.data);
          
          // Wait for next tick to ensure DOM is updated, then render chart
          nextTick(() => {
            renderChart();
          });
        } else {
          console.warn("No ranking data found or unexpected response structure:", response);
          error.value = response.message || "No ranking data available";
        }
      } catch (err) {
        console.error("Error loading ranking data:", err);
        error.value = err.message || "Failed to load ranking data";
        rankingData.value = null;
      } finally {
        loading.value = false;
      }
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

      // Load data if props are available
      if (props.studentId && props.courseId) {
        loadRankingData();
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
  font-size: 1.25rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
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
  padding: 2rem;
  text-align: center;
  color: #6b7280;
}

.error-message {
  color: #dc2626;
  margin: 0;
}

.ranking-content {
  padding: 1.5rem;
}

.ranking-card {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border-radius: 1rem;
  padding: 1rem;
  text-align: center;
  margin-bottom: 1.5rem;
  transition: transform 0.25s ease;
}

.ranking-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.ranking-card h3 {
  margin-bottom: 0.75rem;
  color: #374151;
  font-size: 1.125rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.rank {
  font-size: 2.25rem;
  font-weight: 700;
  color: #4f46e5;
  margin-bottom: 0.5rem;
}

.percentile {
  font-size: 1rem;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.score {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.chart-container {
  height: 300px;
  position: relative;
}
</style>