<template>
  <div class="p-6 bg-gray-100 min-h-screen font-sans">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Course Assignment</h1>

    <!-- Search and Filter for Courses -->
    <div
      class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
      <div class="w-full sm:w-auto flex-grow relative">
        <input type="text" v-model="searchQuery" placeholder="Search courses by code or name..."
          class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <Search class="w-5 h-5 text-gray-400" />
        </div>
      </div>
      <div class="flex gap-2 w-full sm:w-auto flex-grow sm:flex-grow-0">
        <EnhancedSelect :options="allLecturers.map(lecturer => ({ value: lecturer.id, label: lecturer.name }))"
          v-model="filterLecturerId" placeholder="Filter by Lecturer" />

        <!-- Clear filters -->
        <button @click="clearFilters" v-if="filterLecturerId || searchQuery"
          class="inline-flex items-center gap-1 border border-rose-100 px-4 py-1.5 rounded-lg text-sm font-medium text-rose-700 hover:text-rose-600 hover:bg-rose-50 transition-all duration-200 cursor-pointer hover:scale-105">
          <XIcon />
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Course Table for Assignment -->
    <CourseTableForAssignment :courses="paginatedCourses" :loading="isLoading" :current-page="currentPage"
      :total-pages="totalPages" :sort-by="sortBy" :sort-direction="sortDirection" @sort="handleSort"
      @change-page="handleChangePage" @assign-lecturer="openAssignLecturerModal"
      @manage-students="openManageStudentsModal" />

    <!-- Assign Lecturer Modal -->
    <AssignLecturerModal v-if="showAssignLecturerModal" :course="selectedCourse" :all-lecturers="allLecturers"
      @close="closeAssignLecturerModal" @lecturer-assigned="handleLecturerAssigned" />

    <!-- Manage Students Modal -->
    <ManageStudentsModal v-if="showManageStudentsModal" :course="selectedCourse" :all-students="allStudents"
      @close="closeManageStudentsModal" @enrollments-updated="handleEnrollmentsUpdated" />

    <!-- Empty State / Loading State -->
    <div v-if="isLoading" class="text-center py-20 text-sky-500 text-xl animate-pulse">
      Loading courses...
    </div>
    <div v-else-if="!isLoading && filteredSortedCourses.length === 0" class="text-center py-20 text-gray-500 text-xl">
      No courses found matching your criteria.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Search, XIcon } from 'lucide-vue-next';
import adminApi from '../../api/admin'; // Your admin API service
import { toast } from 'vue-sonner';
import EnhancedSelect from '../../components/common/EnhancedSelect.vue'
import ManageStudentsModal from '../../components/admin/course-registration/ManageStudentsModal.vue';
import AssignLecturerModal from '../../components/admin/course-registration/AssignLecturerModal.vue'
import CourseTableForAssignment from '../../components/admin/course-registration/CourseTableForAssignment.vue'

// --- Reactive State ---
const allCourses = ref([]);
const allUsers = ref([]); // Store all users to filter lecturers and students
const isLoading = ref(true);

// Modals
const showAssignLecturerModal = ref(false);
const showManageStudentsModal = ref(false);
const selectedCourse = ref(null); // The course object selected for action

// Filters & Search
const searchQuery = ref('');
const filterLecturerId = ref(null); // Filter by lecturer_id, null for all, 'unassigned' for unassigned

// Pagination & Sorting
const sortBy = ref('course_code'); // Default sort
const sortDirection = ref('asc');
const currentPage = ref(1);
const itemsPerPage = 10;

// --- Data Fetching ---
const fetchAllData = async () => {
  isLoading.value = true;
  try {
    // Fetch all courses with their lecturer info
    const coursesResponse = await adminApi.getAllCoursesWithLecturer();
    allCourses.value = Array.isArray(coursesResponse) ? coursesResponse : [];

    // Fetch all users (to get lecturers and students)
    const usersResponse = await adminApi.getAllUsers();

    if (usersResponse.status === "success") {
      allUsers.value = Array.isArray(usersResponse.data) ? usersResponse.data : [];
    }

  } catch (error) {
    console.error("Error fetching data for course assignment:", error);
    toast.error("Failed to load data. Please try again.");
    allCourses.value = [];
    allUsers.value = [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchAllData();
});

// --- Computed Properties ---

const allLecturers = computed(() => {
  // Filter users to get only lecturers
  return allUsers.value.filter(user => user.role === 'lecturer');
});

const allStudents = computed(() => {
  // Filter users to get only students
  return allUsers.value.filter(user => user.role === 'student');
});

const filteredCourses = computed(() => {
  let courses = Array.isArray(allCourses.value) ? [...allCourses.value] : [];

  // Search by course code or name
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    courses = courses.filter(course =>
      (course.course_code && course.course_code.toLowerCase().includes(query)) ||
      (course.course_name && course.course_name.toLowerCase().includes(query))
    );
  }

  // Filter by lecturer
  if (filterLecturerId.value !== null) {
    if (filterLecturerId.value === 'unassigned') {
      courses = courses.filter(course => course.lecturer_id === null || course.lecturer_id === undefined);
    } else {
      courses = courses.filter(course => course.lecturer_id === filterLecturerId.value);
    }
  }

  return courses;
});

const clearFilters = () => {
  searchQuery.value = '';
  filterLecturerId.value = null; // Reset to show all lecturers
  currentPage.value = 1; // Reset to first page
};

const filteredSortedCourses = computed(() => {
  const courses = [...filteredCourses.value];

  if (!sortBy.value) return courses;

  return courses.sort((a, b) => {
    let valA = a[sortBy.value];
    let valB = b[sortBy.value];

    // Handle null/undefined lecturer_name when sorting by it
    if (sortBy.value === 'lecturer_name') {
      valA = valA || ''; // Treat null/undefined lecturer name as empty string for sorting
      valB = valB || '';
    }

    if (valA === null || valA === undefined) return sortDirection.value === 'asc' ? 1 : -1;
    if (valB === null || valB === undefined) return sortDirection.value === 'asc' ? -1 : 1;

    if (typeof valA === 'string' && typeof valB === 'string') {
      return sortDirection.value === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
    } else {
      return sortDirection.value === 'asc' ? valA - valB : valB - valA;
    }
  });
});

const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredSortedCourses.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredSortedCourses.value.length / itemsPerPage);
});

// --- Watchers ---
watch([searchQuery, filterLecturerId], () => {
  currentPage.value = 1; // Reset to first page on filter/search change
});

// --- Handlers for Modals ---

const openAssignLecturerModal = (course) => {
  selectedCourse.value = course;
  showAssignLecturerModal.value = true;
};

const closeAssignLecturerModal = () => {
  showAssignLecturerModal.value = false;
  selectedCourse.value = null;
};

const handleLecturerAssigned = async () => {
  toast.success("Lecturer assigned successfully!");
  await fetchAllData(); // Re-fetch all data to update the course list
  closeAssignLecturerModal();
};

const openManageStudentsModal = (course) => {
  selectedCourse.value = course;
  showManageStudentsModal.value = true;
};

const closeManageStudentsModal = () => {
  showManageStudentsModal.value = false;
  selectedCourse.value = null;
};

const handleEnrollmentsUpdated = async () => {
  toast.success("Student enrollments updated successfully!");
  await fetchAllData(); // Re-fetch all data to update the course list (e.g., student counts)
  closeManageStudentsModal();
};

// --- Handlers for Table Sorting/Pagination ---

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