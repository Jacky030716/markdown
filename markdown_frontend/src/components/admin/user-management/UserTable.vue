<template>
  <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="relative overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gradient-to-r from-sky-50 to-sky-100 border-b border-sky-200">
          <tr>
            <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-200/50 transition-colors duration-200"
              @click="$emit('sort', 'name')">
              <div class="flex items-center space-x-2">
                <span class="font-semibold text-sky-800">Name</span>
                <ArrowUpDown class="size-4 text-sky-600" />
              </div>
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-200/50 transition-colors duration-200"
              @click="$emit('sort', 'email')">
              <div class="flex items-center space-x-2">
                <span class="font-semibold text-sky-800">Email</span>
                <ArrowUpDown class="size-4 text-sky-600" />
              </div>
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer hover:bg-sky-200/50 transition-colors duration-200"
              @click="$emit('sort', 'role')">
              <div class="flex items-center space-x-2">
                <span class="font-semibold text-sky-800">Role</span>
                <ArrowUpDown class="size-4 text-sky-600" />
              </div>
            </th>
            <th scope="col" class="px-6 py-4 text-center">
              <span class="font-semibold text-sky-800">ID / Matric No.</span>
            </th>
            <th scope="col" class="px-6 py-4 text-center">
              <span class="font-semibold text-sky-800">Status</span>
            </th>
            <th scope="col" class="px-6 py-4 text-right">
              <span class="font-semibold text-sky-800">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading">
            <td colspan="6" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center space-y-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-600"></div>
                <span class="text-gray-500 font-medium">Loading users...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="users.length === 0">
            <td colspan="6" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center space-y-2">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                  <span class="text-gray-400 text-xl">👤</span>
                </div>
                <span class="text-gray-500 font-medium">No users found</span>
              </div>
            </td>
          </tr>
          <tr v-else v-for="user in users" :key="user.id"
            class="hover:bg-gradient-to-r hover:from-sky-50/30 hover:to-transparent transition-all duration-200 group">
            <td class="px-6 py-4">
              <div class="flex items-center">
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold mr-4 shadow-md ring-2 ring-white"
                  :class="getAvatarColor(user.role)">
                  {{ getInitials(user.name || user.email) }}
                </div>
                <span class="font-semibold text-gray-900">{{ user.name || 'N/A' }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="text-gray-700">{{ user.email }}</span>
            </td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm"
                :class="getRoleBadgeClass(user.role)">
                {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <span class="font-mono text-sm text-gray-700 bg-gray-50 px-2 py-1 rounded">
                {{ getRoleSpecificId(user) }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold"
                :class="user.is_active ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' : 'bg-red-100 text-red-700 ring-1 ring-red-200'">
                <div class="w-2 h-2 rounded-full mr-2" :class="user.is_active ? 'bg-emerald-400' : 'bg-red-400'"></div>
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div
                class="flex justify-end space-x-1 opacity-70 group-hover:opacity-100 transition-opacity duration-200">
                <button @click="$emit('edit-user', user)"
                  class="text-sky-600 hover:text-sky-700 hover:bg-sky-50 p-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md border border-transparent hover:border-sky-200 cursor-pointer"
                  title="Edit User">
                  <Edit class="w-4 h-4" />
                </button>
                <button @click="$emit('toggle-active', user)"
                  :class="user.is_active ? 'text-red-600 hover:text-red-700 hover:bg-red-50 hover:border-red-200' : 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 hover:border-emerald-200'"
                  class="p-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md border border-transparent cursor-pointer"
                  :title="user.is_active ? 'Deactivate User' : 'Activate User'">
                  <PowerOff v-if="user.is_active === 1" class="w-4 h-4" />
                  <CheckCircle v-else class="w-4 h-4" />
                </button>

              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Enhanced Pagination -->
    <div v-if="totalPages > 1"
      class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
      <button @click="$emit('change-page', currentPage - 1)" :disabled="currentPage === 1"
        class="flex items-center space-x-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-gray-300 transition-all duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        <span>Previous</span>
      </button>

      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-600">Page</span>
        <div class="flex items-center space-x-1">
          <span v-for="page in getPageNumbers()" :key="page"
            :class="page === currentPage ? 'bg-sky-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50'"
            class="w-8 h-8 flex items-center justify-center text-sm font-medium border border-gray-300 rounded-md cursor-pointer transition-all duration-200"
            @click="page !== '...' && $emit('change-page', page)">
            {{ page }}
          </span>
        </div>
        <span class="text-sm text-gray-600">of {{ totalPages }}</span>
      </div>

      <button @click="$emit('change-page', currentPage + 1)" :disabled="currentPage === totalPages"
        class="flex items-center space-x-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-gray-300 transition-all duration-200">
        <span>Next</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ArrowUpDown, Edit, PowerOff, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
  users: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  },
  sortBy: {
    type: String,
    required: true
  },
  sortDirection: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['sort', 'change-page', 'edit-user', 'toggle-active']);

const getInitials = (name) => {
  if (!name) return '';
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'student': return 'bg-sky-100 text-sky-800 ring-1 ring-sky-200';
    case 'lecturer': return 'bg-purple-100 text-purple-800 ring-1 ring-purple-200';
    case 'advisor': return 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200';
    case 'admin': return 'bg-rose-100 text-rose-800 ring-1 ring-rose-200';
    default: return 'bg-gray-100 text-gray-800 ring-1 ring-gray-200';
  }
};

const getAvatarColor = (role) => {
  switch (role) {
    case 'student': return 'bg-gradient-to-br from-sky-500 to-sky-600';
    case 'lecturer': return 'bg-gradient-to-br from-purple-500 to-purple-600';
    case 'advisor': return 'bg-gradient-to-br from-emerald-500 to-emerald-600';
    case 'admin': return 'bg-gradient-to-br from-rose-500 to-rose-600';
    default: return 'bg-gradient-to-br from-gray-500 to-gray-600';
  }
};

const getRoleSpecificId = (user) => {
  switch (user.role) {
    case 'student': return user.matric_no || 'N/A';
    case 'lecturer': return user.lecturer_id || 'N/A';
    case 'advisor': return user.advisor_id || 'N/A';
    default: return user.id; // For admin or other roles, just use user.id
  }
};

const getPageNumbers = () => {
  const pages = [];
  const maxVisible = 5;

  if (props.totalPages <= maxVisible) {
    for (let i = 1; i <= props.totalPages; i++) {
      pages.push(i);
    }
  } else {
    if (props.currentPage <= 3) {
      for (let i = 1; i <= 4; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(props.totalPages);
    } else if (props.currentPage >= props.totalPages - 2) {
      pages.push(1);
      pages.push('...');
      for (let i = props.totalPages - 3; i <= props.totalPages; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = props.currentPage - 1; i <= props.currentPage + 1; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(props.totalPages);
    }
  }

  return pages;
};
</script>