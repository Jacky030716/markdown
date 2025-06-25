<template>
  <div class="welcome-section">
    <h1 class="welcome-title">Welcome Back, {{ student.name }}! 👋</h1>
    <p class="welcome-subtitle">Ready to track your academic progress today?</p>

    <div class="student-info">
      <div class="info-item">
        <span class="info-label">Student ID</span>
        <span class="info-value">{{ student.matric_no }}</span>
      </div>
      <div class="info-item">
        <span class="info-label">Program</span>
        <span class="info-value">{{ student.program }}</span>
      </div>
      <div class="info-item">
        <span class="info-label">Year</span>
        <span class="info-value">{{ student.year_of_study }}</span>
      </div>
      <div class="info-item">
        <span class="info-label">Semester</span>
        <span class="info-value"> 2 </span>
      </div>
      <div class="info-item">
        <span class="info-label">Advisor Name</span>
        <span class="info-value">{{ student.advisor_name || 'N/A' }}</span> <!-- Fallback if advisor_name is not available -->
      </div>
    </div>

    <div class="quick-actions">
      <a href="/student/marks" class="action-btn">
        📊 View Course Marks
      </a>
      <a href="/student/remark" class="action-btn">
        📝 Request Remark
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: "WelcomeCard",
  props: {
    student: {
      type: Object,
      required: true
    }
  }
};
</script>

<style scoped>
.welcome-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 40px;
  margin-bottom: 50px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.welcome-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 10px;
}

.welcome-subtitle {
  font-size: 1.2rem;
  color: #6b7280;
  margin-bottom: 20px;
}

.student-info {
  display: flex;
  justify-content: center;
  gap: 40px;
  flex-wrap: wrap;
  margin-top: 25px;
}

.info-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
}

.info-label {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
}

.info-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.quick-actions {
  display: flex;
  gap: 20px;
  margin-top: 30px;
  flex-wrap: wrap;
  justify-content: center;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 15px 25px;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white;
  text-decoration: none;
  border-radius: 25px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
}

.action-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
}
</style>
