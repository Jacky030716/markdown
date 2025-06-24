<template>
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-xs text-gray-700 uppercase bg-gray-100">
        <tr>
          <th scope="col" class="px-6 py-3">Student</th>
          <th scope="col" class="px-6 py-3">Course</th>
          <th scope="col" class="px-6 py-3">Component</th>
          <th scope="col" class="px-6 py-3 text-center">Current Mark</th>
          <th scope="col" class="px-6 py-3 text-center">Status</th>
          <th scope="col" class="px-6 py-3 text-center">Requested At</th>
          <th scope="col" class="px-6 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="requests.length === 0">
          <td colspan="7" class="px-6 py-4 text-center text-gray-500">No remark requests to display.</td>
        </tr>
        <tr v-else v-for="request in requests" :key="request.request_id"
          class="bg-white border-b border-b-gray-100 hover:bg-gray-50">
          <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
            <div class="flex items-center">
              <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center mr-3">
                <span class="text-sky-600 font-medium text-xs">
                  {{ getInitials(request.student_name) }}
                </span>
              </div>
              <div>
                <div class="text-base font-semibold">{{ request.student_name }}</div>
                <div class="font-normal text-gray-500">{{ request.matric_no }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm font-medium text-gray-900">{{ request.course_code }}</div>
            <div class="text-xs text-gray-500">{{ request.course_name }}</div>
          </td>
          <td class="px-6 py-4">{{ request.component }}</td>
          <td class="px-6 py-4 text-center">
            <span class="font-semibold" :class="getMarkColorClass(request.current_mark)">
              {{ request.current_mark !== null ? parseFloat(request.current_mark).toFixed(2) : 'N/A' }}
            </span>
          </td>
          <td class="px-6 py-4 text-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              :class="getStatusClass(request.status)">
              {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
            </span>
          </td>
          <td class="px-6 py-4 text-center">{{ formatDate(request.requested_at) }}</td>
          <td class="px-6 py-4 text-center">
            <button @click="$emit('view-details', request)"
              class="font-medium text-sky-600 hover:text-sky-700 transition-colors cursor-pointer">
              View Details
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'RemarkRequestsTable',
  props: {
    requests: {
      type: Array,
      default: () => []
    }
  },
  emits: ['view-details'],
  methods: {
    getInitials(name) {
      if (!name) return '';
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },
    getStatusClass(status) {
      switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
      }
    },
    getMarkColorClass(mark) {
      if (mark === null) return 'text-gray-500';
      if (mark >= 70) return 'text-green-600';
      if (mark >= 40) return 'text-orange-600';
      return 'text-red-600';
    },
    formatDate(timestamp) {
      if (!timestamp) return 'N/A';
      const date = new Date(timestamp);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
};
</script>