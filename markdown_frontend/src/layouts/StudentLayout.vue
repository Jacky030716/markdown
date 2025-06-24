<script setup>
import { RouterLink, RouterView } from 'vue-router';
import StudentSidebar from '../components/student/StudentSidebar.vue';

</script>

<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <StudentSidebar />

    <!-- Main Content -->
    <main class="flex-1 bg-gray-100 p-6 overflow-y-auto">
      <RouterView />
    </main>
  </div>
</template>
