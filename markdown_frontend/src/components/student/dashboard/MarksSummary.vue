<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold text-gray-700">Marks Summary</h2>
      <div class="mt-4">
        <p class="text-sm text-gray-500">Overall Marks: {{ overallMarks }} / {{ totalMarks }}</p>
        <div class="mt-4 space-y-2">
          <div v-for="component in markComponents" :key="component.name" class="flex justify-between">
            <span class="text-sm text-gray-600">{{ component.name }}</span>
            <span class="text-sm text-gray-900">{{ component.mark }} / {{ component.maxMark }}</span>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  // Example marks data (replace with actual API data)
  const markComponents = [
    { name: 'Quiz 1', mark: 18, maxMark: 20 },
    { name: 'Assignment 1', mark: 55, maxMark: 60 },
    { name: 'Test 1', mark: 75, maxMark: 100 },
    { name: 'Final Exam', mark: 90, maxMark: 100 },
  ]
  
  const overallMarks = markComponents.reduce((sum, component) => sum + component.mark, 0)
  const totalMarks = markComponents.reduce((sum, component) => sum + component.maxMark, 0)
  </script>
  
  <style scoped>
  /* Optional custom styles for MarksSummary */
  </style>