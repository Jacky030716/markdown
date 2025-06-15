<script setup lang="ts">
import { Toaster } from 'vue-sonner'
import 'vue-sonner/style.css'
import { RouterView } from 'vue-router'
</script>

<template>
  <Toaster richColors />
  <div class="min-h-screen bg-gray-100 text-gray-900">
    <RouterView />
  </div>
</template>