<script>
import { onMounted, ref } from 'vue'
import {
  Home,
  Users,
  BookOpen,
  BarChart3,
  ChevronLeft,
  ChevronRight,
  User,
  LogOutIcon,
  School,
  BookOpenText
} from "lucide-vue-next"
import { RouterLink } from 'vue-router'

export default {
  name: 'ExpandableSidebar',
  components: {
    LogOutIcon,
    Home,
    Users,
    School,
    BookOpen,
    BarChart3,
    ChevronLeft,
    ChevronRight,
    User,
    BookOpenText
  },
  setup() {
    const isExpanded = ref(true)
    const lecturer = ref(null)

    const toggleSidebar = () => {
      isExpanded.value = !isExpanded.value
    }

    const handleLogout = () => {
      localStorage.clear()
      window.location.href = '/'
    }

    const menuItems = [
      {
        to: '/admin/user-management',
        icon: 'Users',
        label: 'Manage Users',
      },
      {
        to: '/admin/course-registration',
        icon: 'School',
        label: 'Course Registration',
      },
      {
        to: '/admin/course-management',
        icon: 'BookOpenText',
        label: 'Manage Courses',
      }
    ]

    // onMounted(async () => {
    //   const lecturerId = localStorage.getItem('id')

    //   try {
    //     const lecturerProfile = await lecturers.getLecturerProfile(lecturerId)
    //     if (lecturerProfile.status === "success") {
    //       lecturer.value = lecturerProfile.data
    //     }
    //   } catch (error) {
    //     console.error("Error fetching lecturer profile:", error)
    //     lecturer.value = null
    //   }
    // })

    return {
      isExpanded,
      toggleSidebar,
      menuItems,
      lecturer,
      handleLogout,
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
            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
              <User class="w-6 h-6 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                Admin
              </p>
              <p class="text-xs text-gray-500 truncate">
                admin email
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
        <button @click="handleLogout" :class="[
          'flex gap-2 items-center px-3 py-2 rounded-lg text-rose-700 hover:text-rose-800 hover:bg-rose-100 transition-colors duration-200',
        ]">
          <LogOutIcon class="size-4" />
          <span class="transition-opacity duration-300">
            Logout
          </span>
        </button>
      </div>
    </div>
  </div>
</template>