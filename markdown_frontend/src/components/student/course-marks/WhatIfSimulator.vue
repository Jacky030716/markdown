<template>
  <div class="card">
    <div class="card-header">
      <div class="card-icon">🎯</div>
      <h2 class="card-title">What-If Simulator</h2>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="simulator-loading">
      <p>Loading simulation data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="simulator-error">
      <p>{{ error }}</p>
    </div>

    <!-- No Ungraded Components -->
    <div v-else-if="ungradedComponents.length === 0" class="simulator-complete">
      <div class="complete-icon">✅</div>
      <h3>All Components Completed</h3>
      <p>You have received marks for all components in this course. There are no remaining components to simulate.</p>
      <div class="final-grade">
        <span class="label">Your Final Grade:</span>
        <span class="grade">{{ currentTotalGrade.toFixed(2) }}% (Grade {{ calculateGrade(currentTotalGrade) }})</span>
      </div>
    </div>

    <!-- Simulation Interface -->
    <div v-else>
      <div class="current-status">
        <h3>Current Status :</h3>
        <div class="status-grid">
          <div class="status-item">
            <span class="label">Current Grade :</span>
            <span class="value">{{ currentTotalGrade.toFixed(2) }}%</span>
          </div>
          <div class="status-item">
            <span class="label">Completed Weight :</span>
            <span class="value">{{ completedWeight.toFixed(1) }}%</span>
          </div>
          <div class="status-item">
            <span class="label">Remaining Weight :</span>
            <span class="value">{{ remainingWeight.toFixed(1) }}%</span>
          </div>
        </div>
      </div>

      <div class="simulator-controls">
        <h3>Simulate Remaining Components :</h3>
        <div class="simulator-inputs">
          <div 
            v-for="component in ungradedComponents" 
            :key="component.component_id"
            class="simulator-input"
          >
            <label :for="`component-${component.component_id}`">
              {{ component.name }} 
              <span class="component-details">
                ({{ component.type.charAt(0).toUpperCase() + component.type.slice(1) }} - {{ component.weight }}% weight, Max: {{ component.max_mark }})
              </span>
            </label>
            <div class="input-group">
              <input
                :id="`component-${component.component_id}`"
                type="number"
                :min="0"
                :max="component.max_mark"
                :step="0.1"
                :value="simulatedScores[component.component_id] || 0"
                @input="updateScore(component.component_id, $event.target.value)"
                class="score-input"
              />
              <span class="input-suffix">/ {{ component.max_mark }}</span>
            </div>
            <div class="percentage-display">
              {{ getPercentage(component.component_id, component.max_mark) }}%
            </div>
          </div>
        </div>
      </div>

      <div class="simulator-result">
        <h3>Projected Final Grade</h3>
        <div class="projected-grade">{{ projectedGrade.toFixed(2) }}% (Grade {{ calculateGrade(projectedGrade) }})</div>
        <div class="grade-breakdown">
          <div class="breakdown-item">
            <span>Current Weighted Score :</span>
            <span>{{ currentWeightedScore.toFixed(2) }}</span>
          </div>
          <div class="breakdown-item">
            <span>Projected Additional Score :</span>
            <span>{{ projectedAdditionalScore.toFixed(2) }}</span>
          </div>
          <div class="breakdown-item total">
            <span>Total Projected Score :</span>
            <span>{{ projectedGrade.toFixed(2) }}% ({{ calculateGrade(projectedGrade) }})</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';

export default {
  name: "WhatIfSimulator",
  props: {
    marksData: {
      type: Array,
      required: true,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: null
    }
  },
  setup(props) {
    // Reactive data
    const simulatedScores = ref({});

    // Computed properties
    const ungradedComponents = computed(() => {
      return props.marksData.filter(component => 
        component.mark === null || component.mark === undefined
      );
    });

    const gradedComponents = computed(() => {
      return props.marksData.filter(component => 
        component.mark !== null && component.mark !== undefined
      );
    });

    const currentTotalGrade = computed(() => {
      if (gradedComponents.value.length === 0) return 0;
      
      let weightedSum = 0;
      gradedComponents.value.forEach(component => {
        const percentage = (parseFloat(component.mark) / parseFloat(component.max_mark)) * 100;
        weightedSum += (percentage * parseFloat(component.weight)) / 100;
      });
      
      return weightedSum;
    });

    const completedWeight = computed(() => {
      return gradedComponents.value.reduce((sum, component) => 
        sum + parseFloat(component.weight), 0
      );
    });

    const remainingWeight = computed(() => {
      return ungradedComponents.value.reduce((sum, component) => 
        sum + parseFloat(component.weight), 0
      );
    });

    const currentWeightedScore = computed(() => {
      return currentTotalGrade.value;
    });

    const projectedAdditionalScore = computed(() => {
      let additionalScore = 0;
      
      ungradedComponents.value.forEach(component => {
        const simulatedMark = simulatedScores.value[component.component_id] || 0;
        const percentage = (parseFloat(simulatedMark) / parseFloat(component.max_mark)) * 100;
        additionalScore += (percentage * parseFloat(component.weight)) / 100;
      });
      
      return additionalScore;
    });

    const projectedGrade = computed(() => {
      return currentWeightedScore.value + projectedAdditionalScore.value;
    });

    // Methods
    const updateScore = (componentId, value) => {
      const numValue = parseFloat(value) || 0;
      // Use Vue 3 reactive assignment
      simulatedScores.value[componentId] = numValue;
    };

    const getPercentage = (componentId, maxMark) => {
      const score = simulatedScores.value[componentId] || 0;
      const maxMarkNum = parseFloat(maxMark);
      return maxMarkNum > 0 ? ((score / maxMarkNum) * 100).toFixed(1) : '0.0';
    };

    // Grade calculation function
    const calculateGrade = (mark) => {
      if (mark >= 90) return 'A+';
      if (mark >= 80) return 'A';
      if (mark >= 75) return 'A-';
      if (mark >= 70) return 'B+';
      if (mark >= 65) return 'B';
      if (mark >= 60) return 'B-';
      if (mark >= 55) return 'C+';
      if (mark >= 50) return 'C';
      if (mark >= 45) return 'C-';
      if (mark >= 40) return 'D+';
      if (mark >= 35) return 'D';
      if (mark >= 30) return 'D-';
      return 'E';
    };

    const initializeSimulatedScores = () => {
      const newScores = {};
      ungradedComponents.value.forEach(component => {
        // Initialize with 0 or keep existing value
        newScores[component.component_id] = simulatedScores.value[component.component_id] || 0;
      });
      simulatedScores.value = newScores;
    };

    // Watch for changes in marksData and reinitialize
    watch(
      () => props.marksData,
      () => {
        initializeSimulatedScores();
      },
      { immediate: true, deep: true }
    );

    // Return all reactive properties and methods
    return {
      simulatedScores,
      ungradedComponents,
      gradedComponents,
      currentTotalGrade,
      completedWeight,
      remainingWeight,
      currentWeightedScore,
      projectedAdditionalScore,
      projectedGrade,
      updateScore,
      getPercentage,
      calculateGrade,
      initializeSimulatedScores
    };
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

.simulator-loading, .simulator-error {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.simulator-error {
  color: #dc2626;
}

.simulator-complete {
  text-align: center;
  padding: 40px;
}

.complete-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.simulator-complete h3 {
  color: #059669;
  margin-bottom: 12px;
}

.simulator-complete p {
  color: #6b7280;
  margin-bottom: 24px;
  line-height: 1.6;
}

.final-grade {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  background: #f0fdf4;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #bbf7d0;
}

.final-grade .label {
  font-weight: 500;
  color: #065f46;
}

.final-grade .grade {
  font-size: 24px;
  font-weight: 700;
  color: #059669;
}

.current-status {
  margin-bottom: 32px;
}

.current-status h3 {
  color: #1f2937;
  margin-bottom: 16px;
  font-size: 18px;
  font-weight: 600;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.status-item {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-item .label {
  color: #6b7280;
  font-weight: 500;
}

.status-item .value {
  color: #1f2937;
  font-weight: 600;
  font-size: 18px;
}

.simulator-controls h3 {
  color: #1f2937;
  margin-bottom: 20px;
  font-size: 18px;
  font-weight: 600;
}

.simulator-inputs {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.simulator-input {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  background: #fafafa;
}

.simulator-input label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 12px;
}

.component-details {
  font-weight: 400;
  color: #6b7280;
  font-size: 14px;
}

.input-group {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.score-input {
  flex: 1;
  padding: 12px;
  border: 2px solid #d1d5db;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.2s;
}

.score-input:focus {
  outline: none;
  border-color: #3b82f6;
}

.input-suffix {
  color: #6b7280;
  font-weight: 500;
  min-width: 60px;
}

.percentage-display {
  color: #059669;
  font-weight: 600;
  font-size: 14px;
}

.simulator-result {
  margin-top: 32px;
  background: #f0f9ff;
  padding: 24px;
  border-radius: 12px;
  border: 1px solid #bae6fd;
}

.simulator-result h3 {
  color: #0c4a6e;
  margin-bottom: 16px;
  text-align: center;
  font-weight: 600;
}

.projected-grade {
  font-size: 36px;
  font-weight: 700;
  color: #0284c7;
  text-align: center;
  margin-bottom: 24px;
}

.grade-breakdown {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  color: #374151;
}

.breakdown-item.total {
  border-top: 2px solid #bae6fd;
  padding-top: 12px;
  margin-top: 8px;
  font-weight: 600;
  font-size: 18px;
  color: #0c4a6e;
}

@media (max-width: 768px) {
  .status-grid {
    grid-template-columns: 1fr;
  }
  
  .input-group {
    flex-direction: column;
    align-items: stretch;
  }
  
  .input-suffix {
    text-align: center;
    margin-top: 4px;
  }
}
</style>