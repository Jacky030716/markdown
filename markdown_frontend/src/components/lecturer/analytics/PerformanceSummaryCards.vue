<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="flex justify-center items-center bg-blue-100 rounded-full w-12 h-12 mx-auto mb-2">
        <Users class="w-6 h-6 text-blue-600" />
      </div>
      <div class="font-medium text-gray-500">Total Students</div>
      <div class="text-4xl font-bold text-blue-600 mt-2">{{ summaryData.totalStudents }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="flex justify-center items-center bg-green-100 rounded-full w-12 h-12 mx-auto mb-2">
        <BarChart class="w-6 h-6 text-green-600" />
      </div>
      <div class="font-medium text-gray-500">Class Average</div>
      <div class="text-4xl font-bold text-green-600 mt-2">{{ summaryData.classAverage }}%</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="flex justify-center items-center bg-purple-100 rounded-full w-12 h-12 mx-auto mb-2">
        <CheckCircle class="w-6 h-6 text-purple-600" />
      </div>
      <div class="font-medium text-gray-500">Passed Students</div>
      <div class="text-4xl font-bold text-purple-600 mt-2">{{ summaryData.passCount }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="flex justify-center items-center bg-orange-100 rounded-full w-12 h-12 mx-auto mb-2">
        <AlertCircle class="w-6 h-6 text-orange-600" />
      </div>
      <div class="font-medium text-gray-500">Failed Students</div>
      <div class="text-4xl font-bold text-orange-600 mt-2">{{ summaryData.failCount }}</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="flex justify-center items-center bg-teal-100 rounded-full w-12 h-12 mx-auto mb-2">
        <ArrowUpCircle class="w-6 h-6 text-teal-600" />
      </div>
      <div class="font-medium text-gray-500">Highest Mark</div>
      <div class="text-4xl font-bold text-teal-600 mt-2">{{ summaryData.highestMark }}%</div>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
      <div class="flex justify-center items-center bg-red-100 rounded-full w-12 h-12 mx-auto mb-2">
        <ArrowDownCircle class="w-6 h-6 text-red-600" />
      </div>
      <div class="font-medium text-gray-500">Lowest Mark</div>
      <div class="text-4xl font-bold text-red-600 mt-2">{{ summaryData.lowestMark }}%</div>
    </div>
  </div>
</template>

<script>
import { Users, BarChart, CheckCircle, AlertCircle, ArrowUpCircle, ArrowDownCircle } from 'lucide-vue-next';

export default {
  name: 'PerformanceSummaryCards',
  components: {
    Users,
    BarChart,
    CheckCircle,
    AlertCircle,
    ArrowUpCircle,
    ArrowDownCircle
  },
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