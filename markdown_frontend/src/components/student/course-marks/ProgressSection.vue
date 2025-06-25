<template>
  <div class="progress-section">
    <h2 class="progress-title">📈 Your Current Progress</h2>
    <div class="progress-container">
      <div class="progress-card" v-for="(value, key) in progressData" :key="key">
        <h3>{{ key }}</h3>
        <div class="value">{{ value.value }}</div>
        <div class="label">{{ value.label }}</div>
      </div>
    </div>
    <div class="progress-bar-container">
      <div 
        class="progress-bar" 
        :style="{ width: progressPercentage + '%' }" 
        :data-percentage="progressPercentage + '%'"
      ></div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: "ProgressSection",
  props: {
    progressData: {
      type: Object,
      default: () => ({
        "Components Completed": { value: "0/0", label: "Assessments" },
        "Total Weight Completed": { value: "0/100", label: "Percentage" },
        "Current Total Mark": { value: "0%", label: "Average Score" }
      })
    },
    course: {
      type:Object,
    }
  },
  setup(props) {
    const progressPercentage = computed(() => {
      const currentMarkString = props.progressData["Current Total Mark"]?.value || "0%";
      const percentage = parseFloat(currentMarkString.replace('%', ''));
      return isNaN(percentage) ? 0 : Math.min(percentage, 100); // Cap at 100%
    });

    return {
      progressPercentage
    };
  }
};
</script>

<style scoped>
.progress-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 30px;
  border-radius: 20px;
  margin-bottom: 30px;
  position: relative;
  overflow: hidden;
}

.progress-section::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

.progress-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 25px;
  text-align: center;
  position: relative;
  z-index: 1;
}

.progress-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
  position: relative;
  z-index: 1;
}

.progress-card {
  background: rgba(255, 255, 255, 0.15);
  padding: 20px;
  border-radius: 15px;
  text-align: center;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.progress-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.progress-card h3 {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 10px;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.progress-card .value {
  font-size: 28px;
  font-weight: 800;
  margin-bottom: 5px;
  color: #fff;
}

.progress-card .label {
  font-size: 12px;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.progress-bar-container {
  position: relative;
  height: 12px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  overflow: hidden;
  z-index: 1;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #4ade80 0%, #22c55e 50%, #16a34a 100%);
  border-radius: 10px;
  position: relative;
  transition: width 0.8s ease;
  box-shadow: 0 2px 10px rgba(34, 197, 94, 0.4);
}

.progress-bar::after {
  content: attr(data-percentage);
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 10px;
  font-weight: 600;
  color: white;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

/* Responsive design */
@media (max-width: 768px) {
  .progress-container {
    grid-template-columns: 1fr;
  }
  
  .progress-card .value {
    font-size: 24px;
  }
  
  .progress-title {
    font-size: 20px;
  }
}
</style>