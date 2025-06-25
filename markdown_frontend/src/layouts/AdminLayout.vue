<script setup>
import { RouterView } from 'vue-router';
import AdminSidebar from '../components/admin/AdminSidebar.vue';

</script>

<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <AdminSidebar />

    <!-- Main Content -->
    <main class="flex-1 bg-gray-100 p-6 overflow-y-auto">
      <RouterView />
    </main>
  </div>
</template>
