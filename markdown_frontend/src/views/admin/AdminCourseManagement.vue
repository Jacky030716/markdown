<template>
  <div class="p-6 bg-gray-100 min-h-screen font-sans">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Course Management</h1>

    <!-- Action Bar: Add Course Button & Filters/Search/View Toggle -->
    <div
      class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
      <div class="w-full sm:w-auto">
        <button @click="openAddCourseOverlay"
          class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 w-full sm:w-auto transition-colors duration-200 shadow-md cursor-pointer">
          <BookPlus class="w-5 h-5 text-white" />
          <span>Create New Course</span>
        </button>
      </div>

      <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
        <!-- Search Bar -->
        <div class="w-full sm:w-auto flex-grow relative">
          <input type="text" v-model="searchQuery" placeholder="Search by course code or name..."
            class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search class="w-5 h-5 text-gray-400" />
          </div>
        </div>

        <!-- View Toggle -->
        <div class="flex bg-gray-200 rounded-lg p-1 space-x-1">
          <button @click="viewMode = 'grid'"
            :class="[viewMode === 'grid' ? 'bg-sky-600 text-white shadow' : 'text-gray-700 hover:bg-gray-300', 'px-3 py-1.5 rounded-md transition-colors duration-200 flex items-center space-x-1 text-sm font-medium']">
            <LayoutGrid class="w-4 h-4" />
            <span>Grid</span>
          </button>
          <button @click="viewMode = 'list'"
            :class="[viewMode === 'list' ? 'bg-sky-600 text-white shadow' : 'text-gray-700 hover:bg-gray-300', 'px-3 py-1.5 rounded-md transition-colors duration-200 flex items-center space-x-1 text-sm font-medium']">
            <List class="w-4 h-4" />
            <span>List</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Course Display Area -->
    <CourseDisplayWrapper :courses="paginatedCourses" :view-mode="viewMode" :loading="isLoading" :sort-by="sortBy"
      :sort-direction="sortDirection" @sort="handleSort" @edit-course="openEditCourseOverlay"
      @toggle-active="toggleCourseActiveStatus" />

    <!-- Pagination -->
    <div v-if="totalPages > 1 && !isLoading"
      class="flex justify-between items-center mt-6 px-6 py-3 bg-white rounded-lg shadow-md">
      <button @click="handleChangePage(currentPage - 1)" :disabled="currentPage === 1"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
        Previous
      </button>
      <span class="text-sm text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="handleChangePage(currentPage + 1)" :disabled="currentPage === totalPages"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
        Next
      </button>
    </div>

    <!-- Add/Edit Course Overlay -->
    <AddEditCourseOverlay v-if="showOverlay" :course="editingCourse" :all-lecturers="allLecturers" @close="closeOverlay"
      @save="handleSaveCourse" />

    <!-- Empty State / Loading State for overall courses -->
    <div v-if="!isLoading && filteredSortedCourses.length === 0" class="text-center py-20 text-gray-500 text-xl">
      No courses found matching your criteria.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { BookPlus, Search, LayoutGrid, List } from 'lucide-vue-next';
import adminApi from '../../api/admin';
import CourseDisplayWrapper from '../../components/admin/course-management/CourseDisplayWrapper.vue';
import AddEditCourseOverlay from '../../components/admin/course-management/AddEditCourseOverlay.vue';
import { toast } from 'vue-sonner';

// --- Reactive State ---
const allCourses = ref([]);
const allLecturers = ref([]);
const isLoading = ref(true);

// View and Modals
const viewMode = ref('grid'); // 'grid' or 'list'
const showOverlay = ref(false);
const editingCourse = ref(null); // Null for add, object for edit

// Filters & Search
const searchQuery = ref('');
const filterLecturerId = ref(null); // Filter by lecturer_id, null for all, 'unassigned' for unassigned

// Pagination & Sorting
const sortBy = ref('course_code'); // Default sort by course code
const sortDirection = ref('asc'); // Default sort direction
const currentPage = ref(1);
const itemsPerPage = 8; // Number of items per page for grid/list

// --- Data Fetching ---
const fetchAllData = async () => {
  isLoading.value = true;
  try {
    const coursesResponse = await adminApi.getAllCoursesWithLecturer();
    allCourses.value = Array.isArray(coursesResponse) ? coursesResponse : [];

    // Also fetch all lecturers for the dropdowns
    const usersResponse = await adminApi.getAllUsers();
    allLecturers.value = (Array.isArray(usersResponse) ? usersResponse : [])
      .filter(user => user.role === 'lecturer');

  } catch (error) {
    console.error("Error fetching data for course management:", error);
    toast.error("Failed to load courses data. Please try again.");
    allCourses.value = [];
    allLecturers.value = [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchAllData();
});

// --- Computed Properties ---

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

const filteredSortedCourses = computed(() => {
  const courses = [...filteredCourses.value];

  if (!sortBy.value) return courses;

  return courses.sort((a, b) => {
    let valA = a[sortBy.value];
    let valB = b[sortBy.value];

    // Special handling for lecturer_name which can be null
    if (sortBy.value === 'lecturer_name') {
      valA = valA || '';
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

// --- Handlers for Modals/Overlays ---
const openAddCourseOverlay = () => {
  editingCourse.value = null; // Indicate add mode
  showOverlay.value = true;
};

const openEditCourseOverlay = (course) => {
  editingCourse.value = { ...course }; // Create a shallow copy for editing
  showOverlay.value = true;
};

const closeOverlay = () => {
  showOverlay.value = false;
  editingCourse.value = null; // Clear editing course on close
};

const handleSaveCourse = async (courseData) => {
  try {
    let response;
    if (editingCourse.value) {
      response = await adminApi.updateCourse(editingCourse.value.id, courseData);
      toast.success("Course updated successfully!");
    } else {
      response = await adminApi.addNewCourse(courseData);
      toast.success("Course created successfully!");
    }
    await fetchAllData(); // Re-fetch all data to update the course list
    closeOverlay();
  } catch (error) {
    console.error("Error saving course:", error);
    toast.error(error.message || "Failed to save course. Please check your input and try again.");
  }
};

const toggleCourseActiveStatus = async (course) => {
  const newStatus = course.is_active === 1 ? 0 : 1; // Toggle status
  if (!confirm(`Are you sure you want to ${newStatus === 0 ? 'deactivate' : 'activate'} "${course.course_code} - ${course.course_name}"?`)) {
    return;
  }
  try {
    const response = await adminApi.toggleCourseActiveStatus(course.id, newStatus);
    if (response.status === 'success') {
      toast.success(`Course "${course.course_name}" has been ${newStatus === 0 ? 'deactivated' : 'activated'}.`);
      await fetchAllData(); // Re-fetch to reflect changes
    } else {
      toast.error(response.message || "Failed to update course status.");
    }
  } catch (error) {
    console.error("Error toggling course status:", error);
    toast.error("Failed to update course status. Please try again.");
  }
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