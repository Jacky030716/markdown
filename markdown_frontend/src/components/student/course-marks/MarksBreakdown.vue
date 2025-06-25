<template>
    <div class="card">
      <div class="card-header">
        <div class="card-icon">📝</div>
        <h2 class="card-title">Marks Breakdown</h2>
      </div>
      <div v-if="marksData.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Assesment</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Your Mark</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (%)</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Weighted Score</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="mark in marksData" :key="mark.name">
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium text-gray-900">{{ mark.name }}</td>
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium text-gray-900">{{ mark.type }}</td>
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-900">{{ mark.mark }} / {{ mark.max_mark }}</td>
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-500">{{ mark.weight }}%</td>
              <!-- Calculate weighted score safely -->
              <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-900">
                {{ getWeightedScore(mark).toFixed(2) }}
              </td>
            </tr>
          </tbody>
        </table>
  
        <!-- Total Weight Score Row -->
        <div class="px-6 py-4 flex justify-between bg-gray-50">
          <span class="font-semibold text-gray-800">TOTAL WEIGHT SCORE</span>
          <span class="text-gray-800 mr-15">
            {{ totalWeightScore }} / {{ totalMaxWeightedScore }}
          </span>
        </div>
      </div>
  
      <div v-else class="text-center p-6 text-gray-600">
        <p>No marks available for this course.</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "MarksBreakdown",
    props: {
      marksData: {
        type: Array,
        required: true
      }
    },
    computed: {
      // Calculate total weight score and total max weighted score
      totalWeightScore() {
        return this.marksData.reduce((sum, mark) => sum + this.getWeightedScore(mark), 0).toFixed(2);
      },
      totalMaxWeightedScore() {
        // Final Exam always has 30% weight
        const otherComponentsWeight = 70;
        return (otherComponentsWeight + 30).toFixed(2); // This always sums up to 100%
      }
    },
    methods: {
      // Function to safely calculate weighted score
      getWeightedScore(mark) {
        const validMark = parseFloat(mark.mark) || 0; // If mark is invalid, treat it as 0
        const validMaxMark = parseFloat(mark.max_mark) || 0; // If max_mark is invalid, treat it as 0
        const validWeight = parseFloat(mark.weight) || 0; // If weight is invalid, treat it as 0
  
        // Return 0 if any of the values are invalid, otherwise calculate the weighted score
        if (validMaxMark === 0 || validWeight === 0) {
          return 0;
        }
        return (validMark / validMaxMark) * validWeight;
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
  
  table {
    width: 100%;
    border-collapse: collapse;
  }
  
  th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e7ff;
  }
  
  th {
    background: #f8fafc;
    font-weight: 600;
    color: #475569;
  }
  
  tbody tr:hover {
    background: #f8fafc;
  }
  
  .text-center {
    text-align: center;
  }
  
  .bg-gray-50 {
    background-color: #f9fafb;
  }
  
  .font-semibold {
    font-weight: 600;
  }
  </style>
  