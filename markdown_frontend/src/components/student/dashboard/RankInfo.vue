<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold text-gray-700">Rank & Percentile</h2>
      <div class="mt-4">
        <p class="text-sm text-gray-500">Your Rank: {{ rank }}</p>
        <p class="text-sm text-gray-500">Your Percentile: {{ percentile }}%</p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  // Example rank and percentile (replace with actual API data)
  const rank = 5
  const percentile = 95
  </script>
  
  <style scoped>
  /* Optional custom styles for RankInfo */
  </style>
  