<template>
  <div class="p-6 bg-gray-100 min-h-screen font-sans">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Academic Advisor Dashboard</h1>

    <!-- Advisees List Component -->
    <div class="mb-8">
      <AdviseesListComponent 
        :advisees="allAdvisees"
        :loading="isLoading"
        :items-per-page="10"
        @advisee-selected="handleAdviseeSelection"
      />
    </div>

    <!-- Selected Advisee Details (Optional - you can expand this later) -->
    <div v-if="selectedAdvisee" class="bg-white shadow-md rounded-lg p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">
        Selected Advisee: {{ selectedAdvisee.name }}
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p><strong>Matric No:</strong> {{ selectedAdvisee.matric_no }}</p>
          <p><strong>Program:</strong> {{ selectedAdvisee.program }}</p>
        </div>
        <div>
          <p><strong>Year:</strong> {{ selectedAdvisee.year || 'N/A' }}</p>
          <p><strong>Status:</strong> {{ selectedAdvisee.status || 'Active' }}</p>
        </div>
      </div>
    </div>

    <!-- Placeholder for future dashboard components -->
    <div v-else class="text-center py-20 text-gray-500 text-xl">
      Select an advisee to view detailed information.
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import advisorsApi from '../../api/advisors'; // Adjust path if needed
import AdviseesListComponent from '../../components/advisor/dashboard/AdviseesListComponent.vue'; // Adjust path to your component
import { RouterLink } from 'vue-router';

export default {
  name: 'AcademicAdvisorDashboard', // Renamed from LecturerDashboard
  components: {
    AdviseesListComponent,
    RouterLink
  },
  setup() {
    // --- Reactive State ---
    const allAdvisees = ref([]);
    const selectedAdvisee = ref(null);
    const isLoading = ref(false);

    // --- Data Fetching ---
    const fetchAllAdvisees = async () => {
      isLoading.value = true;
      try {
        const advisees = await advisorsApi.getAllAdvisees(1);
        // we will change 1 above with the actual advisor ID later



        allAdvisees.value = advisees.data || []; // Access the 'data' key
        console.log("Advisees fetched successfully:", allAdvisees.value);
      } catch (error) {
        console.error("Error fetching all advisees for advisee list:", error);
        // You could add a toast notification or error state here
        allAdvisees.value = []; // Reset on error
      } finally {
        isLoading.value = false;
      }
    };

    // --- Event Handlers ---
    const handleAdviseeSelection = (advisee) => {
      selectedAdvisee.value = advisee;
      console.log("Selected advisee:", advisee);
      // Here you can fetch additional data for the selected advisee
      // await fetchAdviseeDetails(advisee.id);
    };

    // --- Lifecycle Hook ---
    onMounted(() => {
      fetchAllAdvisees();
    });

    return {
      allAdvisees,
      selectedAdvisee,
      isLoading,
      fetchAllAdvisees,
      handleAdviseeSelection,
    };
  }
};
</script>

<style scoped>
/* Add any custom styles here if needed */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>