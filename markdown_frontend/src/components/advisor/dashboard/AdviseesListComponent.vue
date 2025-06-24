<template>
  <div class="bg-white shadow-md rounded-lg p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-2 sm:mb-0">My Advisees</h2>
      <div class="text-sm text-gray-500">
        Showing {{ startIndex + 1 }}-{{ Math.min(endIndex, totalAdvisees) }} of {{ totalAdvisees }} advisees
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!advisees || advisees.length === 0" class="text-center py-8">
      <div class="text-gray-400 text-lg mb-2">📚</div>
      <p class="text-gray-500">No advisees found</p>
    </div>

    <!-- Advisees Grid -->
    <div v-else class="space-y-4">
      <!-- Desktop View -->
      <div class="hidden md:block">
        <div class="grid grid-cols-1 gap-4">
          <div v-for="advisee in paginatedAdvisees" :key="advisee.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 cursor-pointer hover:border-blue-300"
            @click="$emit('advisee-selected', advisee)">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <!-- Avatar -->
                <div
                  class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                  {{ getInitials(advisee.name) }}
                </div>

                <!-- Student Info -->
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-800 text-lg">{{ advisee.name }}</h3>
                  <div class="flex flex-wrap gap-4 text-sm text-gray-600 mt-1">
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd"
                          d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                          clip-rule="evenodd" />
                      </svg>
                      {{ advisee.matric_no }}
                    </span>
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h2zm4-3a1 1 0 00-1 1v1h2V4a1 1 0 00-1-1z"
                          clip-rule="evenodd" />
                      </svg>
                      {{ advisee.program }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex flex-col items-end space-y-2">
                <div class="text-xs text-gray-400">
                  Year {{ advisee.year_of_study || 'N/A' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile View -->
      <div class="md:hidden">
        <div class="space-y-3">
          <div v-for="advisee in paginatedAdvisees" :key="advisee.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 cursor-pointer hover:border-blue-300"
            @click="$emit('advisee-selected', advisee)">
            <div class="flex items-start space-x-3">
              <!-- Avatar -->
              <div
                class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                {{ getInitials(advisee.name) }}
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-800 truncate">{{ advisee.name }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ advisee.matric_no }}</p>
                <p class="text-sm text-gray-500 truncate">{{ advisee.program }}</p>

                <!-- Status Row -->
                <div class="flex items-center justify-between mt-2">
                  <span :class="getStatusBadgeClass(advisee.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ advisee.status || 'Active' }}
                  </span>
                  <span class="text-xs text-gray-400">Year {{ advisee.year || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div v-if="totalPages > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
      <!-- Previous Button -->
      <button @click="previousPage" :disabled="currentPage === 1" :class="[
        'flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
        currentPage === 1
          ? 'text-gray-400 cursor-not-allowed'
          : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600'
      ]">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Previous
      </button>

      <!-- Page Indicators -->
      <div class="flex space-x-1">
        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="[
          'px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
          page === currentPage
            ? 'bg-blue-600 text-white'
            : 'text-gray-700 hover:bg-gray-100'
        ]">
          {{ page }}
        </button>
      </div>

      <!-- Next Button -->
      <button @click="nextPage" :disabled="currentPage === totalPages" :class="[
        'flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
        currentPage === totalPages
          ? 'text-gray-400 cursor-not-allowed'
          : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600'
      ]">
        Next
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';

export default {
  name: 'AdviseesListComponent',
  props: {
    advisees: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    itemsPerPage: {
      type: Number,
      default: 10
    }
  },
  emits: ['advisee-selected'],
  setup(props) {
    const currentPage = ref(1);

    // Computed properties
    const totalAdvisees = computed(() => props.advisees?.length || 0);
    const totalPages = computed(() => Math.ceil(totalAdvisees.value / props.itemsPerPage));

    const startIndex = computed(() => (currentPage.value - 1) * props.itemsPerPage);
    const endIndex = computed(() => currentPage.value * props.itemsPerPage);

    const paginatedAdvisees = computed(() => {
      if (!props.advisees) return [];
      return props.advisees.slice(startIndex.value, endIndex.value);
    });

    // Visible page numbers for pagination
    const visiblePages = computed(() => {
      const pages = [];
      const total = totalPages.value;
      const current = currentPage.value;

      if (total <= 5) {
        for (let i = 1; i <= total; i++) {
          pages.push(i);
        }
      } else {
        if (current <= 3) {
          pages.push(1, 2, 3, 4, 5);
        } else if (current >= total - 2) {
          pages.push(total - 4, total - 3, total - 2, total - 1, total);
        } else {
          pages.push(current - 2, current - 1, current, current + 1, current + 2);
        }
      }

      return pages;
    });

    // Helper functions
    const getInitials = (name) => {
      if (!name) return 'N/A';
      return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2);
    };

    const getStatusBadgeClass = (status) => {
      const statusLower = (status || 'active').toLowerCase();
      switch (statusLower) {
        case 'active':
          return 'bg-green-100 text-green-800';
        case 'inactive':
          return 'bg-red-100 text-red-800';
        case 'warning':
          return 'bg-yellow-100 text-yellow-800';
        default:
          return 'bg-gray-100 text-gray-800';
      }
    };

    // Pagination methods
    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++;
      }
    };

    const previousPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--;
      }
    };

    const goToPage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
      }
    };

    // Reset to first page when advisees change
    watch(() => props.advisees, () => {
      currentPage.value = 1;
    });

    return {
      currentPage,
      totalAdvisees,
      totalPages,
      startIndex,
      endIndex,
      paginatedAdvisees,
      visiblePages,
      getInitials,
      getStatusBadgeClass,
      nextPage,
      previousPage,
      goToPage
    };
  }
};
</script>