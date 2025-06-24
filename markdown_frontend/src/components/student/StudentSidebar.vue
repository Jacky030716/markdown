<script>
import { ref } from 'vue'
import {
  Home,
  Clipboard,
  ChevronLeft,
  ChevronRight,
  User,
  ChartColumn,
  ClipboardPenLine,
  ChartPie
} from "lucide-vue-next"
import { RouterLink } from 'vue-router'

export default {
  name: 'StudentSidebar',
  components: {
    Home,
    ChevronLeft,
    ChevronRight,
    User,
    ClipboardPenLine,
    ChartPie
  },
  setup() {
    const isExpanded = ref(true)

    const toggleSidebar = () => {
      isExpanded.value = !isExpanded.value
    }

    const menuItems = [
      {
        to: '/student/dashboard',
        icon: 'Home',
        label: 'Dashboard',
      },
      {
        to: '/student/marks',
        icon: 'ChartPie',
        label: 'Course Mark',
      },
      {
        to: '/student/remark',
        icon: 'ClipboardPenLine',
        label: 'Remark Request',
      }
    ]

    return {
      isExpanded,
      toggleSidebar,
      menuItems
    }
  },
}
</script>

<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div :class="[ 
      'bg-white shadow-lg transition-all duration-300 ease-in-out flex flex-col', 
      isExpanded ? 'w-64' : 'w-16' 
    ]">
      <!-- Header -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <!-- User Profile -->
          <div class="flex items-center space-x-3 transition-opacity duration-300">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
              <User class="w-6 h-6 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                John Doe
              </p>
              <p class="text-xs text-gray-500 truncate">
                student@graduate.utm.my
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 p-4 space-y-2">
        <div v-for="item in menuItems" :key="item.to" class="relative">
          <RouterLink :to="item.to" :class="[ 
            'flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200 group', 
            !isExpanded && 'justify-center' 
          ]" active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-700">
            <!-- Icon -->
            <component :is="item.icon" :class="[ 
              'flex-shrink-0 w-5 h-5', 
              isExpanded ? 'mr-3' : '' 
            ]" />
            <!-- Label -->
            <span class="flex-1 transition-opacity duration-300">
              {{ item.label }}
            </span>
          </RouterLink>

          <!-- Tooltip for collapsed state -->
          <div v-if="!isExpanded"
            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 transform -translate-y-1/2">
            {{ item.label }}
            <div
              class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-800">
            </div>
          </div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="p-4 border-t border-gray-200">
        <RouterLink to="/student/settings" :class="[ 
          'flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200', 
          !isExpanded && 'justify-center' 
        ]">
          <svg :class="[ 
            'flex-shrink-0 w-5 h-5', 
          ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span class="transition-opacity duration-300">
            Settings
          </span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>
