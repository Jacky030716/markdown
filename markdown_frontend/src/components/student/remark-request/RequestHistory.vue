<template>
    <div class="card history-section">
      <div class="card-header">
        <i class="fas fa-history"></i>
        <h2>Request History</h2>
      </div>
  
      <table class="requests-table">
        <thead>
          <tr>
            <th>Date Submitted</th>
            <th>Course</th>
            <th>Component</th>
            <th>Current Mark</th>
            <th>Status</th>
            <th>Response Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(request, index) in requests" :key="index">
            <td>{{ request.dateSubmitted }}</td>
            <td>{{ request.course }}</td>
            <td>{{ request.component }}</td>
            <td>{{ request.currentMark }}</td>
            <td>
              <span :class="`status-badge status-${request.status}`">{{ request.status }}</span>
            </td>
            <td class="text-center">{{ request.responseDate || '-' }}</td>
            <td>
              <button class="btn" @click="viewDetails(request)">
                View Details
              </button>
            </td>
          </tr>
        </tbody>
      </table>
  
      <!-- Request Details Modal -->
      <RequestDetailsModal 
        :request="selectedRequest" 
        :visible="isModalVisible" 
        @close="closeModal" 
      />
    </div>
  </template>
  
  <script>
  import RequestDetailsModal from './RequestDetailsModal.vue';
  
  export default {
    components: {
      RequestDetailsModal
    },
    props: {
      requests: Array
    },
    data() {
      return {
        isModalVisible: false,
        selectedRequest: null
      };
    },
    methods: {
      viewDetails(request) {
        this.selectedRequest = request;
        this.isModalVisible = true;
      },
      closeModal() {
        this.isModalVisible = false;
        this.selectedRequest = null;
      }
    }
  };
  </script>
  
  <style scoped>
  .card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 20px;
  }
  
  .card-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }
  
  .card-header i {
    font-size: 1.5rem;
    margin-right: 12px;
    color: #4f46e5;
  }
  
  .card-header h2 {
    font-size: 1.5rem;
    color: #2d3748;
  }
  
  .requests-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }
  
  .requests-table th {
    background:  #4f46e5;
    color: white;
    padding: 15px 12px;
    text-align: center;
    font-weight: 600;
    font-size: 0.9rem;
  }
  
  .requests-table td {
    padding: 15px 12px;
    border-bottom: 1px solid #e2e8f0;
    color: #4a5568;
    text-align: center;
  }
  
  .requests-table tr:hover {
    background-color: #f7fafc;
  }
  
  .status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .status-pending {
    background-color: #fed7d7;
    color: #c53030;
  }
  
  .status-approved {
    background-color: #c6f6d5;
    color: #2f855a;
  }
  
  .status-rejected {
    background-color: #fbb6ce;
    color: #b83280;
  }
  
  .btn {
    padding: 6px 12px;
    font-size: 0.8rem;
    background: #e2e8f0;
    color: #4a5568;
    border: none;
    border-radius: 8px;
    cursor: pointer;
  }
  
  .btn:hover {
    background: #cbd5e0;
  }
  </style>
  