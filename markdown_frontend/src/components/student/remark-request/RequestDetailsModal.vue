<template>
    <div v-if="visible" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Request Details</h3>
          <button @click="closeModal" class="close-btn">X</button>
        </div>
        <div class="modal-body">
          <div class="detail-item">
            <span class="detail-label">Date Submitted:</span>
            <span class="detail-value">{{ request.dateSubmitted }}</span>
          </div>
  
          <div class="detail-item">
            <span class="detail-label">Course:</span>
            <span class="detail-value">{{ request.course }}</span>
          </div>
  
          <div class="detail-item">
            <span class="detail-label">Component:</span>
            <span class="detail-value">{{ request.component }}</span>
          </div>
  
          <div class="detail-item">
            <span class="detail-label">Current Mark:</span>
            <span class="detail-value">{{ request.currentMark }}</span>
          </div>
  
          <div class="detail-item">
            <span class="detail-label">Status:</span>
            <span :class="`status-badge status-${request.status}`">{{ request.status }}</span>
          </div>
  
          <div v-if="request.status !== 'pending'" class="detail-item">
            <span class="detail-label">Response Date:</span>
            <span class="detail-value">{{ request.responseDate || '-' }}</span>
          </div>
  
          <!-- Lecturer's Comments -->
          <div v-if="request.status !== 'pending'" class="detail-item">
            <span class="detail-label">Lecturer's Comments:</span>
            <div class="comment-box">
              <p>{{ request.lecturerResponse }}</p>
            </div>
          </div>
        </div>
  
        <!-- Close Button -->
        <div class="modal-footer">
          <button class="btn close-btn" @click="closeModal">Close</button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      request: Object,
      visible: Boolean
    },
    methods: {
      closeModal() {
        this.$emit('close');
      }
    }
  };
  </script>
  
  <style scoped>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease;
  }
  
  .modal-content {
    background: white;
    padding: 25px;
    border-radius: 12px;
    width: 500px;
    max-width: 95%;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease;
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 15px;
  }
  
  .modal-header h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3748;
  }
  
  .close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #4facfe;
    cursor: pointer;
  }
  
  .modal-body {
    margin-top: 20px;
    font-size: 1rem;
  }
  
  .detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
  }
  
  .detail-label {
    font-weight: 600;
    color: #4a5568;
  }
  
  .detail-value {
    color: #2d3748;
  }
  
  .status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
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
  
  .comment-box {
    background: #e8ebec;
    margin-left: 10px;
    padding: 10px;
    border-radius: 8px;
    font-style: italic;
    font-size: 0.95rem;
    color: #4a5568;
    margin-top: 8px;
  }
  
  .modal-footer {
    display: flex;
    justify-content: center;
    margin-top: 20px;
  }
  
  .btn {
    padding: 8px 20px;
    font-size: 1rem;
    font-weight: 600;
    background: #4facfe;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
  }
  
  .btn:hover {
    background: #4f46e5;
  }
  
  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }
  
  @keyframes slideIn {
    from {
      transform: translateY(-10%);
    }
    to {
      transform: translateY(0);
    }
  }
  </style>
  