<template>
    <div class="quick-stats">
      <div class="stat-card" v-for="(stat, index) in stats" :key="index">
        <div class="stat-icon">{{ stat.icon }}</div>
        <div class="stat-value">{{ stat.value }}</div>
        <div class="stat-label">{{ stat.label }}</div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "QuickStats",
    props: {
      stats: {
        type: Array,
        required: true
      }
    }
  };
  </script>
  
  <style scoped>
  .quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
  }
  
  .stat-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  
  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #4f46e5, #7c3aed);
  }
  
  .stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  }
  
  .stat-icon {
    font-size: 3rem;
    margin-bottom: 15px;
  }
  
  .stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 10px;
  }
  
  .stat-label {
    font-size: 1.1rem;
    color: #6b7280;
    font-weight: 500;
  }
  </style>
  