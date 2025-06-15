<template>
  <div class="h-full w-full">
    <canvas ref="componentAveragesChartCanvas"></canvas>
  </div>
</template>

<script>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import Chart from 'chart.js/auto';

export default {
  name: 'ComponentAveragesChart',
  props: {
    componentsData: {
      type: Array, // Expects an array of objects like { component_name: 'Quiz 1', average_mark: 15.5, max_mark: 20 }
      default: () => []
    }
  },
  setup(props) {
    const componentAveragesChartCanvas = ref(null);
    let chartInstance = null;

    const createChart = () => {
      if (chartInstance) {
        chartInstance.destroy();
      }

      if (!props.componentsData.length) {
        return; // Don't create chart if no data
      }

      const ctx = componentAveragesChartCanvas.value.getContext('2d');
      const labels = props.componentsData.map(c => c.component_name);
      const data = props.componentsData.map(c => c.average_mark);
      const maxMarks = props.componentsData.map(c => c.max_mark); // For max line or scaling

      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Average Mark',
              data: data,
              backgroundColor: 'rgba(153, 102, 255, 0.7)', // Purple
              borderColor: 'rgba(153, 102, 255, 1)',
              borderWidth: 1,
            },
            {
              type: 'line', // Overlay a line for max mark if desired
              label: 'Max Mark',
              data: maxMarks,
              borderColor: 'rgba(255, 99, 132, 1)', // Red
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              borderWidth: 2,
              fill: false,
              tension: 0.1,
              pointRadius: 0 // Hide points on the line
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: Math.max(...maxMarks) + 10, // Adjust max for better visual
              title: {
                display: true,
                text: 'Marks'
              },
              ticks: {
                precision: 0 // No decimals for max mark axis
              }
            },
            x: {
              title: {
                display: true,
                text: 'Assessment Component'
              }
            }
          },
          plugins: {
            legend: {
              display: true,
              position: 'top'
            },
            title: {
              display: true,
              text: 'Average Performance Per Component'
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  let label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed.y !== null) {
                    label += context.parsed.y.toFixed(1);
                    if (context.dataset.label === 'Average Mark') {
                      label += ` / ${maxMarks[context.dataIndex].toFixed(0)}`;
                    }
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

    watch(() => props.componentsData, () => {
      createChart(); // Recreate chart when data changes
    }, { deep: true });

    onUnmounted(() => {
      if (chartInstance) {
        chartInstance.destroy();
      }
    });

    return {
      componentAveragesChartCanvas
    };
  }
};
</script>