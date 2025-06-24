<template>
  <div class="p-6 bg-gray-100 min-h-screen font-sans">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Remark Requests</h1>

    <!-- Filters and Status Summary -->
    <div
      class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
      <div class="flex-grow">
        <label for="statusFilter" class="sr-only">Filter by Status</label>
        <select id="statusFilter" v-model="filterStatus"
          class="block w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500">
          <option value="all">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
      <div class="flex-shrink-0 text-gray-700 text-sm">
        <span class="font-semibold">{{ filteredRequests.length }}</span> requests displayed (Total: {{
          allRequests.length }})
      </div>
    </div>

    <!-- Remark Requests Table -->
    <RemarkRequestTable :requests="filteredRequests" @view-details="openRequestDetailModal" />

    <!-- No Requests State -->
    <div v-if="!isLoading && allRequests.length === 0" class="text-center py-20 text-gray-500 text-xl">
      No remark requests found for your courses.
    </div>
    <div v-else-if="isLoading" class="text-center py-20 text-sky-500 text-xl animate-pulse">
      Loading remark requests...
    </div>
    <div v-else-if="!isLoading && filteredRequests.length === 0 && allRequests.length > 0"
      class="text-center py-20 text-gray-500 text-xl">
      No requests matching the selected filter.
    </div>

    <!-- Remark Request Detail Modal -->
    <RemarkRequestDetailModal v-if="showDetailModal" :request="selectedRequest" @close="closeRequestDetailModal"
      @update-request="handleUpdateRequest" />
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import RemarkRequestTable from '../../components/lecturer/remark-request/RemarkRequestTable.vue';
import RemarkRequestDetailModal from '../../components/lecturer/remark-request/RemarkRequestDetailModal.vue';
import { toast } from 'vue-sonner';
import lecturers from '../../api/lecturers';

export default {
  name: 'RemarkRequestsPage',
  components: {
    RemarkRequestTable,
    RemarkRequestDetailModal
  },
  setup() {
    const allRequests = ref([]);
    const filterStatus = ref('all');
    const isLoading = ref(true);
    const showDetailModal = ref(false);
    const selectedRequest = ref(null);

    const lecturerId = localStorage.getItem('id')

    const fetchRemarkRequests = async () => {
      isLoading.value = true;
      try {
        const response = await lecturers.getRemarkRequests(lecturerId);
        if (response && response.status === 'success' && response.data) {
          allRequests.value = response.data;
        } else {
          allRequests.value = [];
          toast.error(response.message || "Failed to fetch remark requests.");
        }
      } catch (error) {
        console.error("Error fetching remark requests:", error);
        allRequests.value = [];
        toast.error("An error occurred while fetching remark requests.");
      } finally {
        isLoading.value = false;
      }
    };

    const filteredRequests = computed(() => {
      if (filterStatus.value === 'all') {
        return allRequests.value;
      }
      return allRequests.value.filter(request => request.status === filterStatus.value);
    });

    const openRequestDetailModal = (request) => {
      selectedRequest.value = request;
      showDetailModal.value = true;
    };

    const closeRequestDetailModal = () => {
      showDetailModal.value = false;
      selectedRequest.value = null;
    };

    const handleUpdateRequest = async (updatedData) => {
      try {
        const response = await lecturers.updateRemarkRequest(
          updatedData.requestId,
          updatedData.status,
          updatedData.lecturerResponse
        );

        if (response.status === 'success') {
          toast.success("Remark request updated successfully!");
          // Re-fetch all requests to update the list, or manually update the specific item
          await fetchRemarkRequests();
          closeRequestDetailModal(); // Close modal after successful update
        } else {
          toast.error(response.message || "Failed to update remark request.");
        }
      } catch (error) {
        console.error("Error updating remark request:", error);
        toast.error(error.message || "An error occurred while updating the remark request.");
      }
    };

    onMounted(() => {
      fetchRemarkRequests();
    });

    return {
      allRequests,
      filterStatus,
      isLoading,
      showDetailModal,
      selectedRequest,
      filteredRequests,
      openRequestDetailModal,
      closeRequestDetailModal,
      handleUpdateRequest
    };
  }
};
</script>