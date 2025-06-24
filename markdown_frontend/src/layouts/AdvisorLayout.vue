<script setup>
import { RouterLink, RouterView } from 'vue-router';
import AdvisorSidebar from '../components/advisor/AdvisorSidebar.vue';

</script>

<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <AdvisorSidebar />

    <!-- Main Content -->
    <main class="flex-1 bg-gray-100 p-6 overflow-y-auto">
      <RouterView />
    </main>
  </div>
</template>
