<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">📈</div>
      <h2 class="card-title">Anonymous Comparison</h2>
    </div>

    <!-- Performance Comparison Table -->
    <table class="comparison-table">
      <thead>
        <tr>
          <th>Student</th>
          <th>Quiz 1</th>
          <th>Assignment 1</th>
          <th>Lab 1</th>
          <th>Quiz 2</th>
          <th>Final</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <!-- Loop through comparison data and render each row -->
        <tr v-for="(row, index) in comparisonData" :key="index" :class="{ 'student-row': row.student === 'You' }">
          <td>{{ row.student }}</td>
          <td>{{ row.quiz1 }}</td>
          <td>{{ row.assignment1 }}</td>
          <td>{{ row.lab1 }}</td>
          <td>{{ row.quiz2 }}</td>
          <td>{{ row.final }}</td>
          <td>{{ row.total }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "PerformanceComparison",
  props: {
    comparisonData: {
      type: Array,
      required: true,
    }
  }
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
  font-size: 1.4rem;
  font-weight: 600;
  color: #1e293b;
}

.comparison-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.comparison-table th, .comparison-table td {
  padding: 10px;
  text-align: center;
  border-bottom: 1px solid #e0e7ff;
}

.comparison-table th {
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
}

.comparison-table .student-row {
  background: #eff6ff;
  font-weight: 600;
}
</style>
