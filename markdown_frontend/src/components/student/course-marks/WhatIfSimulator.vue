<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">🎯</div>
      <h2 class="card-title">What-If Simulator</h2>
    </div>

    <div class="simulator-controls">
      <div class="simulator-input">
        <label for="quiz1">Quiz 1 (Remaining - 30%)</label>
        <input
          type="number"
          id="quiz1"
          min="0"
          max="100"
          :value="quiz1Score"  
          @input="updateQuiz1Score($event)" 
        />
      </div>

      <div class="simulator-input">
        <label for="assign2">Assignment 2 (Remaining - 20%)</label>
        <input
          type="number"
          id="assign2"
          min="0"
          max="100"
          :value="assignment2Score"  
          @input="updateAssignment2Score($event)"
        />
      </div>
    </div>

    <div class="simulator-result">
      <h3>Projected Final Grade</h3>
      <div class="projected-grade">{{ projectedGrade }}%</div>
    </div>
  </div>
</template>

<script>
export default {
  name: "WhatIfSimulator",
  props: {
    currentMarks: {
      type: Object,
      required: true,
    },
    remainingWeight: {
      type: Object,
      required: true,
    },
    quiz1Score: {
      type: Number,
      required: true,
    },
    assignment2Score: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      projectedGrade: this.currentMarks.total, // Initialize projected grade with current total
    };
  },
  methods: {
    // Method to update quiz1 score and emit it to the parent
    updateQuiz1Score(event) {
      const newScore = parseFloat(event.target.value);
      this.$emit("update:quiz1Score", newScore);
      this.calculateProjectedGrade();
    },

    // Method to update assignment2 score and emit it to the parent
    updateAssignment2Score(event) {
      const newScore = parseFloat(event.target.value);
      this.$emit("update:assignment2Score", newScore);
      this.calculateProjectedGrade();
    },

    calculateProjectedGrade() {
      const currentWeightedScore = (this.currentMarks.total * this.currentMarks.weight) / 100;
      const quiz1WeightedScore = (this.quiz1Score * this.remainingWeight.quiz1) / 100;
      const assignment2WeightedScore = (this.assignment2Score * this.remainingWeight.assignment2) / 100;

      // Calculate the total projected grade
      const totalWeightedScore = currentWeightedScore + quiz1WeightedScore + assignment2WeightedScore;
      const totalWeight = this.currentMarks.weight + this.remainingWeight.quiz1 + this.remainingWeight.assignment2;

      // Set the projected grade
      this.projectedGrade = ((totalWeightedScore / totalWeight) * 100).toFixed(2);
    },
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

.simulator-controls {
  display: grid;
  gap: 20px;
}

.simulator-input label {
  font-weight: bold;
  margin-bottom: 5px;
}

.simulator-input input {
  padding: 10px;
  border: 2px solid #e0e7ff;
  border-radius: 10px;
  font-size: 1rem;
  width: 100%;
}

.simulator-result {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px;
  border-radius: 15px;
  text-align: center;
  margin-top: 20px;
}

.projected-grade {
  font-size: 2rem;
  font-weight: 700;
}
</style>
