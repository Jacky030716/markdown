<template>
    <div class="courses-section">
      <h2 class="section-title">📚 Your Courses</h2>
      <div class="courses-grid">
        <div class="course-card" v-for="(course, index) in courses" :key="index">
          <div class="course-code">{{ course.code }}</div>
          <div class="course-title">{{ course.title }}</div>
          <div class="course-stats">
            <div class="course-stat">
              <div class="course-stat-value">{{ course.grade }}</div>
              <div class="course-stat-label">Current Grade</div>
            </div>
            <div class="course-stat">
              <div class="course-stat-value">{{ course.rank }}</div>
              <div class="course-stat-label">Rank</div>
            </div>
            <div class="course-stat">
              <div class="course-stat-value">{{ course.progress }}%</div>
              <div class="course-stat-label">Progress</div>
            </div>
          </div>
          <div class="course-progress">
            <div class="course-progress-fill" :style="{ width: course.progress + '%' }"></div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "CourseOverview",
    props: {
      courses: {
        type: Array,
        required: true
      }
    }
  };
  </script>
  
  <style scoped>
  .courses-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 30px;
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
  
  .courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
  }
  
  .course-card {
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border-radius: 15px;
    padding: 25px;
    border-left: 5px solid #4f46e5;
    transition: all 0.3s ease;
    cursor: pointer;
  }
  
  .course-card:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  }
  
  .course-code {
    font-size: 0.9rem;
    color: #4f46e5;
    font-weight: 600;
    margin-bottom: 5px;
  }
  
  .course-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 15px;
  }
  
  .course-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
  }
  
  .course-stat {
    text-align: center;
  }
  
  .course-stat-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1f2937;
  }
  
  .course-stat-label {
    font-size: 0.8rem;
    color: #6b7280;
  }
  
  .course-progress {
    background: #e5e7eb;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 15px;
  }
  
  .course-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4f46e5, #7c3aed);
    transition: width 0.3s ease;
  }
  </style>
  