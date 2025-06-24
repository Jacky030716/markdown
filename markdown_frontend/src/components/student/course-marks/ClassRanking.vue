<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">🏆</div>
      <h2 class="card-title">Class Ranking</h2>
    </div>
    <div class="ranking-card">
      <h3>Your Position</h3>
      <div class="rank">2nd</div>
      <div class="percentile">Top 15% (85th Percentile)</div>
    </div>
    <div class="chart-container">
      <canvas id="rankingChart"></canvas>
    </div>
  </div>
</template>

<script>
import { Chart } from "chart.js";

export default {
  name: "ClassRanking",
  mounted() {
    const ctx = this.$refs.rankingChart.getContext("2d");
    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Above You", "Your Position", "Below You"],
        datasets: [
          {
            data: [15, 5, 80],
            backgroundColor: [
              "rgba(239, 68, 68, 0.8)",
              "rgba(34, 197, 94, 0.8)",
              "rgba(156, 163, 175, 0.8)",
            ],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      },
    });
  },
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
.ranking-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
  padding: 10px;
  border-radius: 15px;
  margin-bottom: 15px;
  max-height: fit-content;
}

.ranking-card h3 {
  font-size: 1.2rem;
  margin-bottom: 5px;
}

.ranking-card .rank {
  font-size: 3rem;
  font-weight: 500;
  margin-bottom: 5px;
}

.ranking-card .percentile {
  font-size: 1.1rem;
  opacity: 0.9;
}

.chart-container {
  height: 300px;
  margin-top: 20px;
}
</style>
