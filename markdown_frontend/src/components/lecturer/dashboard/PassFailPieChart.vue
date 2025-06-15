<template>
  <div class="h-full w-full flex items-center justify-center">
    <canvas ref="passFailChartCanvas"></canvas>
  </div>
</template>

<script>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import Chart from 'chart.js/auto';

export default {
  name: 'PassFailPieChart',
  props: {
    passCount: {
      type: Number,
      default: 0
    },
    failCount: {
      type: Number,
      default: 0
    }
  },
  setup(props) {
    const passFailChartCanvas = ref(null);
    let chartInstance = null;

    const createChart = () => {
      if (chartInstance) {
        chartInstance.destroy();
      }

      const total = props.passCount + props.failCount;
      if (total === 0) {
        // No data to display, return early
        if (passFailChartCanvas.value) {
          const ctx = passFailChartCanvas.value.getContext('2d');
          ctx.clearRect(0, 0, passFailChartCanvas.value.width, passFailChartCanvas.value.height);
          // Optionally, draw a "No data" message
          ctx.fillStyle = '#9CA3AF'; // gray-400
          ctx.font = '16px Arial';
          ctx.textAlign = 'center';
          ctx.fillText('No Data Available', passFailChartCanvas.value.width / 2, passFailChartCanvas.value.height / 2);
        }
        return;
      }

      const ctx = passFailChartCanvas.value.getContext('2d');
      chartInstance = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['Passed', 'Failed'],
          datasets: [{
            data: [props.passCount, props.failCount],
            backgroundColor: [
              'rgba(75, 192, 192, 0.7)', // Green for Passed
              'rgba(255, 99, 132, 0.7)'  // Red for Failed
            ],
            borderColor: [
              'rgba(75, 192, 192, 1)',
              'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
              labels: {
                font: {
                  size: 14
                }
              }
            },
            title: {
              display: true,
              text: 'Pass/Fail Distribution',
              font: {
                size: 16
              }
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  let label = context.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed !== null) {
                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                    label += `${context.parsed} students (${percentage}%)`;
                  }
                  return label;
                }
              }
            }
          }
        }
      });
    };

    onMounted(() => {
      createChart();
    });

    watch(() => [props.passCount, props.failCount], () => {
      createChart(); // Recreate chart when data changes
    });

    onUnmounted(() => {
      if (chartInstance) {
        chartInstance.destroy();
      }
    });

    return {
      passFailChartCanvas
    };
  }
};
</script>