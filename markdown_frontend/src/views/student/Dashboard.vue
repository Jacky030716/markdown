<template>
  <div class="space-y-6 p-6 bg-gray-100 min-h-screen font-sans">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Student Dashboard</h1>
    <!-- Marks Summary Section -->
    <MarksSummary />

    <!-- Progress Bar Section -->
    <ProgressBar />

    <!-- Performance Analytics Section -->
    <PerformanceAnalytics />

    <!-- Rank & Percentile Section -->
    <RankInfo />

    <!-- Comparison with Coursemates Section -->
    <ComparisonWidget />
  </div>
</template>

<script setup>
// Importing all the components for the dashboard
import MarksSummary from "../../components/student/dashboard/MarksSummary.vue";
import ProgressBar from "../../components/student/dashboard/ProgressBar.vue";
import PerformanceAnalytics from "../../components/student/dashboard/PerformanceAnalytics.vue";
import RankInfo from "../../components/student/dashboard/RankInfo.vue";
import ComparisonWidget from "../../components/student/dashboard/ComparisonWidget.vue";
</script>

<style scoped>
/* Add your dashboard styles here if needed */
</style>
