<script setup>
import {
  ChevronLeft,
  ChevronRight,
  User,
  LogOutIcon
} from "lucide-vue-next"
import SkeletonBox from "../common/SkeletonBox.vue"


const menuItems = [
  { to: '#', icon: 'Home', label: 'Dashboard' },
  { to: '#', icon: 'Users', label: 'Students Mark' },
  { to: '#', icon: 'BookOpen', label: 'Courses' },
  { to: '#', icon: 'BarChart3', label: 'Analytics' }
]
</script>

<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div :class="[
      'bg-white shadow-lg transition-all duration-300 ease-in-out flex flex-col w-64',
    ]">
      <!-- Header -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3 transition-opacity duration-300">
            <div class="w-10 h-10 bg-orange-400 rounded-full flex items-center justify-center">
              <User class="w-6 h-6 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <SkeletonBox customClass="w-2/3 h-4" />
              <SkeletonBox customClass="w-3/4 h-4" />
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-2">
        <div v-for="item in menuItems" :key="item.label" class="relative group">
          <RouterLink :to="item.to" :class="[
            'flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200',
          ]">
            <!-- Icon -->
            <SkeletonBox customClass="w-5 h-5 rounded-full mr-3" />
            <!-- Label -->
            <SkeletonBox customClass="flex-1 h-8 rounded" />
          </RouterLink>

          <div
            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 transform -translate-y-1/2">
            {{ item.label }}
            <div
              class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-800" />
          </div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="p-4 border-t border-gray-200">
        <RouterLink to="#" :class="[
          'flex gap-2 items-center px-3 py-2 rounded-lg text-rose-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200',

        ]">
          <LogOutIcon class="size-4" />
          <span class="transition-opacity duration-300">Logout</span>
        </RouterLink>
      </div>
    </div>

    <!-- Optional toggle button -->
    <!-- <button @click="toggleSidebar" class="absolute top-4 left-full ml-2 p-2 bg-white shadow rounded-full border">
      <component :is="isExpanded ? ChevronLeft : ChevronRight" class="w-4 h-4" />
    </button> -->
  </div>
</template>
