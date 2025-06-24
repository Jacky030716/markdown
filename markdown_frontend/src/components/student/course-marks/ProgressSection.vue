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
        <div class="progress-bar" :style="{ width: progressData['Current Total Mark'].value + '%' }" :data-percentage="progressData['Current Total Mark'].value + '%'"></div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "ProgressSection",
    data() {
      return {
        progressData: {
          "Components Completed": { value: "4/5", label: "Assessments" },
          "Total Weight Completed": { value: "54/100", label: "Percentage" },
          "Current Total Mark": { value: "75.8%", label: "Average Score" }
        }
      };
    }
  };
  </script>
  
  <style scoped>
  .progress-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    color: white;
  }
  
  .progress-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 20px;
    text-align: center;
  }
  
  .progress-container {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
  }
  
  .progress-card {
    background: rgba(255,255,255,0.1);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
  }
  
  .progress-card h3 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    opacity: 0.9;
  }
  
  .progress-card .value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
  }
  
  .progress-card .label {
    font-size: 0.9rem;
    opacity: 0.8;
  }
  
  .progress-bar-container {
    background: rgba(255,255,255,0.1);
    border-radius: 25px;
    height: 20px;
    overflow: hidden;
    margin-top: 10px;
  }
  
  .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #00ff88, #00cc6a);
    border-radius: 25px;
    transition: width 0.8s ease;
    position: relative;
  }
  
  .progress-bar::after {
    content: attr(data-percentage);
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
  }
  </style>
  