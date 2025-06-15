<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Total Students</div>
      <div class="text-4xl font-bold text-blue-600 mt-2">{{ summaryData.totalStudents }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Class Average</div>
      <div class="text-4xl font-bold text-green-600 mt-2">{{ summaryData.classAverage }}%</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Passed Students</div>
      <div class="text-4xl font-bold text-purple-600 mt-2">{{ summaryData.passCount }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Failed Students</div>
      <div class="text-4xl font-bold text-orange-600 mt-2">{{ summaryData.failCount }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Highest Mark</div>
      <div class="text-4xl font-bold text-teal-600 mt-2">{{ summaryData.highestMark }}%</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="text-sm font-medium text-gray-500">Lowest Mark</div>
      <div class="text-4xl font-bold text-red-600 mt-2">{{ summaryData.lowestMark }}%</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClassPerformanceSummary',
  props: {
    summaryData: {
      type: Object,
      default: () => ({
        totalStudents: 0,
        classAverage: 0,
        passCount: 0,
        failCount: 0,
        highestMark: 0,
        lowestMark: 0
      })
    }
  }
};
</script>
