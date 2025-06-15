<script setup>
import { RouterLink, RouterView } from 'vue-router';
import LecturerSidebar from '../components/lecturer/LecturerSidebar.vue';

</script>

<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <LecturerSidebar />

    <!-- Main Content -->
    <main class="flex-1 bg-gray-100 p-6 overflow-y-auto">
      <RouterView />
    </main>
  </div>
</template>
