<script>
import { ref } from 'vue'
import {
  Home,
  Users,
  User,
  FileText,
  AlertTriangle,
  BarChart3,
  Settings,
  ChevronLeft,
  ChevronRight,
  MessageSquare
} from "lucide-vue-next"
import { RouterLink } from 'vue-router'

export default {
  name: 'AdvisorSidebar',
  components: {
    Home,
    Users,
    User,
    FileText,
    AlertTriangle,
    BarChart3,
    Settings,
    ChevronLeft,
    ChevronRight,
    MessageSquare
  },
  setup() {
    const isExpanded = ref(true)

    const toggleSidebar = () => {
      isExpanded.value = !isExpanded.value
    }

    const menuItems = [
      {
        to: '/advisor/dashboard',
        icon: 'Home',
        label: 'Dashboard',
        description: 'Overview of all advisees and at-risk students'
      },
      {
        to: '/advisor/advisees',
        icon: 'Users',
        label: 'My Advisees',
        description: 'Complete list of students under supervision'
      },
      {
        to: '/advisor/at-risk',
        icon: 'AlertTriangle',
        label: 'At-Risk Students',
        description: 'Students with GPA < 2.0 or performance issues'
      },
      {
        to: '/advisor/meetings',
        icon: 'MessageSquare',
        label: 'Meeting Notes',
        description: 'Consultation records and follow-up actions'
      },
      {
        to: '/advisor/analytics',
        icon: 'BarChart3',
        label: 'Analytics',
        description: 'Performance trends and advisee statistics'
      },
      {
        to: '/advisor/reports',
        icon: 'FileText',
        label: 'Reports',
        description: 'Generate consultation and progress reports'
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
        <div class="flex items-center gap-6">
          <!-- Toggle Button -->
          <button 
            @click="toggleSidebar"
            class="p-2 rounded-md hover:bg-gray-100 transition-colors duration-200 border border-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <ChevronLeft v-if="isExpanded" class="w-5 h-5 text-gray-600" />
            <ChevronRight v-else class="w-5 h-5 text-gray-600" />
          </button>
          
          <!-- User Profile -->
          <div v-if="isExpanded" class="flex items-center space-x-3 transition-opacity duration-300">
            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
              <User class="w-6 h-6 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                Dr. Teong Lee
              </p>
              <p class="text-xs text-gray-500 truncate">
                Academic Advisor
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 p-4 space-y-2">
        <div v-for="item in menuItems" :key="item.to" class="relative group">
          <RouterLink :to="item.to" :class="[
            'flex items-center px-3 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200',
            !isExpanded && 'justify-center'
          ]" active-class="bg-blue-100 text-blue-800 border-r-4 border-blue-600 shadow-sm">
            
            <!-- Icon -->
            <component :is="item.icon" :class="[
              'flex-shrink-0 w-5 h-5',
              isExpanded ? 'mr-3' : ''
            ]" />

            <!-- Label -->
            <div v-if="isExpanded" class="flex-1 transition-opacity duration-300">
              <span class="font-medium">{{ item.label }}</span>
              <!-- <p class="text-xs text-gray-500 mt-0.5">{{ item.description }}</p> -->
            </div>
          </RouterLink>

          <!-- Tooltip for collapsed state -->
          <div v-if="!isExpanded"
            class="absolute left-full ml-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 transform -translate-y-1/2 shadow-lg">
            <div class="font-medium">{{ item.label }}</div>
            <div class="text-xs text-gray-300 mt-0.5">{{ item.description }}</div>
            <div class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-800"></div>
          </div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="p-4 border-t border-gray-200">
        <RouterLink to="/advisor/settings" :class="[
          'flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200',
          !isExpanded && 'justify-center'
        ]">
          <Settings :class="[
            'flex-shrink-0 w-5 h-5',
            isExpanded ? 'mr-3' : ''
          ]" />
          <span v-if="isExpanded" class="transition-opacity duration-300">
            Settings
          </span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for navigation */
nav::-webkit-scrollbar {
  width: 4px;
}

nav::-webkit-scrollbar-track {
  background: #f1f5f9;
}

nav::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Active link styling */
.router-link-active {
  position: relative;
}

.router-link-active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #2563eb;
  border-radius: 0 2px 2px 0;
}
</style>