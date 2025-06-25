<template>
  <div class="p-6 bg-gray-100 min-h-screen font-sans">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">User Management</h1>

    <!-- Action Bar: Add User Button & Filters/Search -->
    <div
      class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
      <div class="w-full sm:w-auto">
        <button @click="openAddModal"
          class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 w-full sm:w-auto transition-colors duration-200 shadow-md cursor-pointer">
          <UserPlus class="w-5 h-5 text-white" />
          <span>Add New User</span>
        </button>
      </div>

      <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
        <!-- Role Filter -->
        <EnhancedSelect :options="[
          { value: 'all', label: 'All Roles' },
          { value: 'student', label: 'Student' },
          { value: 'lecturer', label: 'Lecturer' },
          { value: 'advisor', label: 'Advisor' }
        ]" v-model="filterRole" placeholder="Filter by Role" />

        <!-- Search Bar -->
        <div class="w-full sm:w-auto flex-grow sm:flex-grow-0 relative">
          <input type="text" v-model="searchQuery" placeholder="Search by name or email..."
            class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search class="w-5 h-5 text-gray-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- User Table -->
    <UserTable :users="paginatedUsers" :loading="isLoading" :current-page="currentPage" :total-pages="totalPages"
      :sort-by="sortBy" :sort-direction="sortDirection" @sort="handleSort" @change-page="handleChangePage"
      @edit-user="openEditModal" @toggle-active="toggleUserActiveStatus" />

    <!-- User Form Modal for Add/Edit -->
    <UserFormModal v-if="showModal" :user="editingUser" @close="closeModal" @save="handleSaveUser" />

    <!-- Empty State / Loading State -->
    <div v-if="isLoading" class="text-center py-20 text-sky-500 text-xl animate-pulse">
      Loading users...
    </div>
    <div v-else-if="!isLoading && filteredSortedUsers.length === 0" class="text-center py-20 text-gray-500 text-xl">
      No users found matching your criteria.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import EnhancedSelect from "../../components/common/EnhancedSelect.vue"
import { UserPlus, Search } from 'lucide-vue-next';

import adminApi from '../../api/admin';
import { toast } from 'vue-sonner';
import UserTable from '../../components/admin/user-management/UserTable.vue';
import UserFormModal from '../../components/admin/user-management/UserFormModal.vue';

// --- Reactive State ---
const allUsers = ref([]);
const isLoading = ref(true);
const showModal = ref(false);
const editingUser = ref(null); // Null for add, object for edit

// --- Filtering, Sorting, Pagination State ---
const filterRole = ref('all');
const searchQuery = ref('');
const sortBy = ref('name'); // Default sort by name
const sortDirection = ref('asc'); // Default sort direction
const currentPage = ref(1);
const itemsPerPage = 10; // Number of items per page

// --- Data Fetching ---
const fetchAllUsers = async () => {
  isLoading.value = true;
  try {
    const usersResponse = await adminApi.getAllUsers();

    if (usersResponse.status !== "success" || !Array.isArray(usersResponse.data)) {
      throw new Error("Invalid user data format received from server.");
    }
    allUsers.value = usersResponse.data;
  } catch (error) {
    console.error("Error fetching all users:", error);
    toast.error("Failed to fetch user data. Please try again.");
    allUsers.value = [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchAllUsers();
});

// --- Computed Properties for Table Data ---

// 1. Filtered Users
const filteredUsers = computed(() => {
  let users = allUsers.value;

  // Filter by role
  if (filterRole.value !== 'all') {
    users = users.filter(user => user.role === filterRole.value);
  }

  // Filter by search query (name or email)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    users = users.filter(user =>
      (user.name && user.name.toLowerCase().includes(query)) ||
      (user.email && user.email.toLowerCase().includes(query))
    );
  }
  return users;
});

// 2. Sorted Users
const filteredSortedUsers = computed(() => {
  const users = [...filteredUsers.value];

  if (!sortBy.value) return users; // No sorting if sortBy is not set

  return users.sort((a, b) => {
    let valA = a[sortBy.value];
    let valB = b[sortBy.value];

    // Handle nested properties for specific roles if sorting by e.g. matric_no
    if (sortBy.value === 'matric_no' && a.role === 'student' && b.role === 'student') {
      valA = a.matric_no;
      valB = b.matric_no;
    } else if (sortBy.value === 'lecturer_id' && a.role === 'lecturer' && b.role === 'lecturer') {
      valA = a.lecturer_id;
      valB = b.lecturer_id;
    } else if (sortBy.value === 'advisor_id' && a.role === 'advisor' && b.role === 'advisor') {
      valA = a.advisor_id;
      valB = b.advisor_id;
    }


    // Handle null/undefined values by placing them at the end
    if (valA === null || valA === undefined) return sortDirection.value === 'asc' ? 1 : -1;
    if (valB === null || valB === undefined) return sortDirection.value === 'asc' ? -1 : 1;

    // Type-agnostic comparison
    if (typeof valA === 'string' && typeof valB === 'string') {
      return sortDirection.value === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
    } else {
      return sortDirection.value === 'asc' ? valA - valB : valB - valA;
    }
  });
});

// 3. Paginated Users
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredSortedUsers.value.slice(start, end);
});

// 4. Total Pages
const totalPages = computed(() => {
  return Math.ceil(filteredSortedUsers.value.length / itemsPerPage);
});

// Watchers to reset page when filters/search change
watch([filterRole, searchQuery], () => {
  currentPage.value = 1;
});

// --- Handlers ---
const openAddModal = () => {
  editingUser.value = null; // Set to null to indicate add mode
  showModal.value = true;
};

const openEditModal = (user) => {
  editingUser.value = { ...user }; // Create a shallow copy for editing
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingUser.value = null;
};

const handleSaveUser = async (userData) => {
  try {

    let response;
    if (editingUser.value) {
      // Update existing user
      response = await adminApi.updateUser(editingUser.value.id, userData);
      toast.success("User updated successfully!");
    } else {
      // Add new user
      response = await adminApi.addNewUser(userData);
      toast.success("User added successfully!");
    }
    await fetchAllUsers(); // Re-fetch data to update the table
    closeModal();
  } catch (error) {
    console.error("Error saving user:", error);
    toast.error(error.message || "Failed to save user. Please check your input and try again.");
  }
};

const toggleUserActiveStatus = async (user) => {
  const newStatus = user.is_active ? 0 : 1; // Toggle status
  if (!confirm(`Are you sure you want to ${!newStatus ? 'deactivate' : 'activate'} ${user.name}'s account?`)) {
    return;
  }
  try {
    // Assuming backend endpoint for toggling active status
    const response = await adminApi.updateUser(user.id, { is_active: newStatus });
    if (response) {
      toast.success(`User ${user.name} has been ${newStatus === 0 ? 'deactivated' : 'activated'}.`);
      await fetchAllUsers();
    }
  } catch (error) {
    console.error("Error toggling user status:", error);
    toast.error("Failed to update user status. Please try again.");
  }
};

const handleSort = (column) => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = 'asc';
  }
};

const handleChangePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};
</script>