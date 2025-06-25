<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Academic Advisor Dashboard</h1>
            <p class="mt-2 text-gray-600">Monitor and support your advisees' academic journey</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="refreshData"
              :disabled="isLoading"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 disabled:opacity-50"
            >
              <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Refresh Data
            </button>
            <RouterLink
              to="/advisor/analytics"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              View Analytics
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error loading advisees data</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>{{ error }}</p>
            </div>
            <div class="mt-4">
              <button
                @click="retryFetch"
                class="bg-red-100 px-3 py-2 rounded-md text-sm font-medium text-red-800 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                Try Again
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Stats Banner -->
      <div v-if="!isLoading && allAdvisees.length > 0" class="mb-6 bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-8">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-800">{{ allAdvisees.length }}</div>
              <div class="text-sm text-gray-600">Total Advisees</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-red-600">{{ atRiskCount }}</div>
              <div class="text-sm text-gray-600">Need Attention</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ excellentCount }}</div>
              <div class="text-sm text-gray-600">Excellent Performance</div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-sm text-gray-600">Last Updated</div>
            <div class="text-sm font-medium text-gray-900">{{ lastUpdated }}</div>
          </div>
        </div>
      </div>

      <!-- Main Content - Advisees List Component -->
      <div class="mb-8">
        <Transition name="fade" mode="out-in">
          <AdviseesListComponent 
            :advisees="allAdvisees"
            :loading="isLoading"
            :items-per-page="10"
            @advisee-selected="handleAdviseeSelection"
          />
        </Transition>
      </div>

      <!-- Selected Advisee Details (Optional additional section) -->
      <div v-if="selectedAdvisee && !isLoading" class="mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium text-gray-900">Recently Selected Advisee</h2>
            <button
              @click="clearSelection"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Student Name</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ selectedAdvisee.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Matric Number</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ selectedAdvisee.matric_no }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Current GPA</dt>
              <dd class="mt-1">
                <span :class="getGPAStatusClass(selectedAdvisee.gpa)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ selectedAdvisee.gpa?.toFixed(2) || '0.00' }}
                </span>
              </dd>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!isLoading && allAdvisees.length === 0 && !error" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No advisees found</h3>
        <p class="mt-1 text-sm text-gray-500">You don't have any advisees assigned to you yet.</p>
        <div class="mt-6">
          <button
            @click="refreshData"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black-600 hover:bg-black-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black hover:cursor-pointer"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh Data
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import advisorsApi from '../../api/advisors' // Adjust path if needed
import AdviseesListComponent from '../../components/advisor/dashboard/AdviseesListComponent.vue' // Adjust path to your component
import { RouterLink } from 'vue-router'

export default {
  name: 'Dashboard',
  
  components: {
    AdviseesListComponent,
    RouterLink
  },
  
  setup() {
    // --- Reactive State ---
    const allAdvisees = ref([])
    const selectedAdvisee = ref(null)
    const isLoading = ref(false)
    const error = ref(null)
    const lastUpdated = ref('')

    // --- Computed Properties ---
    const atRiskCount = computed(() => 
      allAdvisees.value.filter(advisee => advisee.gpa < 2.0).length
    )

    const excellentCount = computed(() => 
      allAdvisees.value.filter(advisee => advisee.gpa >= 3.5).length
    )

    // --- Utility Functions ---
    const getGPAStatusClass = (gpa) => {
      if (gpa >= 3.5) return 'bg-green-100 text-green-800'
      if (gpa >= 3.0) return 'bg-blue-100 text-blue-800'
      if (gpa >= 2.0) return 'bg-yellow-100 text-yellow-800'
      return 'bg-red-100 text-red-800'
    }

    const updateLastUpdated = () => {
      const now = new Date()
      lastUpdated.value = now.toLocaleString()
    }

    // --- Data Fetching ---
    const fetchAllAdvisees = async () => {
      isLoading.value = true
      error.value = null
      
      try {
        console.log('Fetching advisees data...')
        const response = await advisorsApi.getAllAdvisees(1) // We will change 1 to actual advisor ID later after implementing login
        
        // Handle different response structures
        if (response && response.data) {
          allAdvisees.value = Array.isArray(response.data) ? response.data : []
        } else if (Array.isArray(response)) {
          allAdvisees.value = response
        } else {
          allAdvisees.value = []
        }
        
        updateLastUpdated()
        console.log('Advisees fetched successfully:', allAdvisees.value)
        
      } catch (err) {
        console.error('Error fetching advisees:', err)
        error.value = err.message || 'An error occurred while fetching advisees data'
        allAdvisees.value = []
      } finally {
        isLoading.value = false
      }
    }

    // --- Event Handlers ---
    const handleAdviseeSelection = (advisee) => {
      selectedAdvisee.value = advisee
      console.log('Selected advisee:', advisee)
      
      // You can add additional logic here:
      // - Fetch detailed information about the selected advisee
      // - Update navigation state
      // - Send analytics events
      // - etc.
    }

    const clearSelection = () => {
      selectedAdvisee.value = null
    }

    const refreshData = async () => {
      await fetchAllAdvisees()
    }

    const retryFetch = async () => {
      await fetchAllAdvisees()
    }

    // --- Lifecycle Hook ---
    onMounted(() => {
      fetchAllAdvisees()
    })

    return {
      // State
      allAdvisees,
      selectedAdvisee,
      isLoading,
      error,
      lastUpdated,
      
      // Computed
      atRiskCount,
      excellentCount,
      
      // Methods
      handleAdviseeSelection,
      clearSelection,
      refreshData,
      retryFetch,
      getGPAStatusClass,
      fetchAllAdvisees
    }
  }
}
</script>

<style scoped>
/* Custom animations and transitions */
.fade-enter-active, 
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, 
.fade-leave-to {
  opacity: 0;
}

/* Print styles for reports */
@media print {
  .print\\:hidden {
    display: none !important;
  }
  
  .print\\:p-6 {
    padding: 1.5rem !important;
  }
}

/* Loading animation */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}
</style>