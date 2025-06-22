<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold text-gray-700">Progress</h2>
      <div class="mt-4">
        <div class="relative pt-1">
          <div class="flex mb-2 items-center justify-between">
            <span class="text-sm font-medium text-gray-500">Your Progress</span>
            <span class="text-sm font-medium text-gray-600">{{ progressPercentage }}%</span>
          </div>
          <div class="flex mb-2">
            <div class="flex-1 bg-gray-200 rounded-full h-2">
              <div
                class="bg-blue-600 h-2 rounded-full"
                :style="{ width: progressPercentage + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  // Example data (replace with actual calculations)
  const overallMarks = 238  // Sum of all marks (e.g., Quiz 1 + Assignment 1 + etc.)
  const totalMarks = 280    // Total possible marks (e.g., Quiz 1 max + Assignment 1 max + etc.)
  
  // Calculate progress percentage
  const progressPercentage = Math.round((overallMarks / totalMarks) * 100)
  </script>
  
  <style scoped>
  /* Optional custom styles for ProgressBar */
  </style>
  