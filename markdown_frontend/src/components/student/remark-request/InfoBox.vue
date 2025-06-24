<template>
    <div class="info-box">
      <i class="fas fa-info-circle"></i>
      <div class="info-box-content">
        <h3>How to Submit a Remark Request</h3>
        <p>Select your course and component, provide clear justification, and submit your request. You'll receive updates on the status via email.</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'InfoBox'
  };
  </script>
  
  <style scoped>
  .info-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    margin: 20px 0;
    display: flex;
    align-items: center;
  }
  
  .info-box i {
    font-size: 1.5rem;
    margin-right: 15px;
  }
  
  .info-box-content h3 {
    font-size: 1.1rem;
    margin-bottom: 5px;
  }
  
  .info-box-content p {
    font-size: 0.9rem;
    opacity: 0.9;
  }
  </style>
  