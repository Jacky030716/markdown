<template>
    <div class="recent-updates">
      <h2 class="section-title">🔔 Recent Updates</h2>
      <ul class="updates-list">
        <li class="update-item" v-for="(update, index) in updates" :key="index">
          <div class="update-icon">{{ update.icon }}</div>
          <div class="update-content">
            <div class="update-title">{{ update.title }}</div>
            <div class="update-desc">{{ update.desc }}</div>
          </div>
          <div class="update-time">{{ update.time }}</div>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  export default {
    name: "RecentUpdates",
    props: {
      updates: {
        type: Array,
        required: true
      }
    }
  };
  </script>
  
  <style scoped>
  .recent-updates {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  }
  
  .section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .updates-list {
    list-style: none;
  }
  
  .update-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 15px;
    background: #f8fafc;
    transition: all 0.3s ease;
  }
  
  .update-item:hover {
    background: #e2e8f0;
    transform: translateX(5px);
  }
  
  .update-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
  }
  
  .update-content {
    flex: 1;
  }
  
  .update-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 5px;
  }
  
  .update-desc {
    color: #6b7280;
    font-size: 0.9rem;
  }
  
  .update-time {
    color: #9ca3af;
    font-size: 0.8rem;
    font-weight: 500;
  }
  </style>
  