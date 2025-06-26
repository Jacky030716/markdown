<template>
  <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all duration-300 ease-out overflow-y-auto max-h-[90vh]">
      <!-- Header -->
      <div class="relative px-6 pt-6 pb-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-gray-900">Remark Request Details</h3>
            <p class="text-sm text-gray-500 mt-1">Review and respond to the student's request.</p>
          </div>
          <button @click="$emit('close')"
            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Request Details -->
      <div class="px-6 py-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-bold text-gray-700">Student Name:</p>
            <p class="text-base text-gray-900">{{ request.student_name }} ({{ request.matric_no }})</p>
          </div>
          <div>
            <p class="text-sm font-bold text-gray-700">Course:</p>
            <p class="text-base text-gray-900">{{ request.course_code }} - {{ request.course_name }}</p>
          </div>
          <div>
            <p class="text-sm font-bold text-gray-700">Component:</p>
            <p class="text-base text-teal-700">{{ request.component_name }}</p>
          </div>
          <div>
            <p class="text-sm font-bold text-gray-700">Current Mark:</p>
            <p class="text-base text-amber-700">{{ request.current_mark !== null ?
              parseFloat(request.current_mark).toFixed(2) :
              'N/A' }}</p>
          </div>
        </div>

        <div class="space-y-2 mt-12">
          <p class="text-sm font-semibold text-gray-700">Student's Justification:</p>
          <p
            class="text-base text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-200 break-words whitespace-pre-wrap">
            {{ request.justification }}</p>
        </div>

        <div class="space-y-2">
          <p class="text-sm font-semibold text-gray-700">Requested At:</p>
          <p class="text-base text-gray-900">{{ formatDate(request.requested_at) }}</p>
        </div>

        <div class="space-y-2">
          <p class="text-sm font-semibold text-gray-700">Status:</p>
          <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
            :class="getStatusClass(request.status)">
            {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
          </span>
        </div>

        <!-- Lecturer Response Section (only for pending requests) -->
        <div v-if="request.status === 'pending'" class="space-y-4 mt-12 pt-4 border-t border-gray-100">
          <div class="space-y-2">
            <label for="lecturerResponse" class="block text-sm font-semibold text-gray-700">Your Response:</label>
            <textarea id="lecturerResponse" v-model="lecturerResponse" rows="4"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              placeholder="Enter your response here..."></textarea>
          </div>
          <div v-if="errorMessage" class="text-red-600 text-sm text-center">{{ errorMessage }}</div>
          <div class="flex justify-end gap-3">
            <button @click="respondToRequest('rejected')" :disabled="isResponding"
              class="w-full sm:w-auto px-6 py-3 text-red-700 font-semibold border-2 border-red-200 rounded-xl hover:bg-red-50 hover:border-red-300 transition-all duration-200 focus:ring-4 focus:ring-red-100 flex items-center justify-center space-x-2">
              <XCircle class="w-5 h-5" />
              <span>{{ isResponding ? 'Rejecting...' : 'Reject' }}</span>
            </button>
            <button @click="respondToRequest('approved')" :disabled="isResponding"
              class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-sky-500 to-sky-600 text-white font-semibold rounded-xl hover:from-sky-600 hover:to-sky-700 focus:ring-4 focus:ring-sky-200 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
              <CheckCircle class="w-5 h-5" />
              <span>{{ isResponding ? 'Approving...' : 'Approve' }}</span>
            </button>
          </div>
        </div>
        <!-- Display lecturer's response if not pending -->
        <div v-else-if="request.lecturer_response" class="space-y-2 mt-6 pt-4 border-t border-gray-100">
          <p class="text-sm font-semibold text-gray-700">Your Previous Response:</p>
          <p
            class="text-base text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-200 break-words whitespace-pre-wrap">
            {{ request.lecturer_response }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import { CheckCircle, XCircle } from 'lucide-vue-next';

export default {
  name: 'RemarkRequestDetailModal',
  components: {
    CheckCircle,
    XCircle
  },
  props: {
    request: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'update-request'],
  setup(props, { emit }) {
    const lecturerResponse = ref('');
    const isResponding = ref(false);
    const errorMessage = ref('');

    // Watch for changes in the request prop to reset response field
    watch(() => props.request, (newRequest) => {
      if (newRequest) {
        lecturerResponse.value = newRequest.lecturer_response || '';
        errorMessage.value = ''; // Clear error when new request is selected
      }
    }, { immediate: true });

    const respondToRequest = async (status) => {
      errorMessage.value = '';
      if (!lecturerResponse.value.trim()) {
        errorMessage.value = 'Please provide a response before updating the request.';
        return;
      }

      isResponding.value = true;
      try {
        // Emit an event to the parent component (RemarkRequestsPage) to handle the API call
        emit('update-request', {
          requestId: props.request.request_id,
          status: status,
          lecturerResponse: lecturerResponse.value.trim()
        });
      } catch (error) {
        // Error handling is mostly in the parent component that listens to 'update-request'
        // But local error state can be set here if needed for immediate feedback.
        errorMessage.value = error.message || 'Failed to send response.';
      } finally {
        isResponding.value = false;
      }
    };

    const getStatusClass = (status) => {
      switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
      }
    };

    const formatDate = (timestamp) => {
      if (!timestamp) return 'N/A';
      const date = new Date(timestamp);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    return {
      lecturerResponse,
      isResponding,
      errorMessage,
      respondToRequest,
      getStatusClass,
      formatDate
    };
  }
};
</script>