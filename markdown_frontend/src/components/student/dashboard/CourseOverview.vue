<template>
  <div class="courses-section">
    <h2 class="section-title">📚 Your Courses</h2>
    <div class="courses-grid">
      <div class="course-card" v-for="(course, index) in courses" :key="index">
        <div class="course-code">{{ course.course_code }}</div>
        <div class="course-title">{{ course.course_name }}</div>
        <div class="course-details">
          <div class="course-detail">
            <div class="course-detail-value">{{ course.semester }}</div>
            <div class="course-detail-label">Semester</div>
          </div>
          <div class="course-detail">
            <div class="course-detail-value">{{ course.academic_year }}</div>
            <div class="course-detail-label">Academic Year</div>
          </div>
          <div class="course-detail">
            <div class="course-detail-value">{{ course.credit_hours }}</div>
            <div class="course-detail-label">Credit Hours</div>
          </div>
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
  margin-bottom: 50px;
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

.course-details {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}

.course-detail {
  text-align: center;
}

.course-detail-value {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1f2937;
}

.course-detail-label {
  font-size: 0.8rem;
  color: #6b7280;
}
</style>
