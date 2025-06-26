<template>
  <div class="meeting-note-card">
    <!-- Card Header with Student Info -->
    <div class="card-header">
      <div class="student-avatar">
        <div class="avatar-circle">
          {{ getInitials(meetingNote.student_info.name) }}
        </div>
      </div>
      <div class="student-details">
        <h3 class="student-name">{{ meetingNote.student_info.name }}</h3>
        <p class="student-matric">{{ meetingNote.student_info.matric_no }}</p>
        <div class="student-program">
          <span class="program-badge">{{ meetingNote.student_program }}</span>
          <span class="year-badge">Year {{ meetingNote.student_year }}</span>
        </div>
      </div>
    </div>

    <!-- Meeting Info Section -->
    <div class="meeting-info">
      <div class="info-row">
        <div class="info-item">
          <div class="info-icon date-icon">📅</div>
          <div class="info-content">
            <span class="info-label">Date</span>
            <span class="info-value">{{ formatDate(meetingNote.last_meeting_date) }}</span>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon type-icon">{{ getTypeIcon(meetingNote.last_meeting_type) }}</div>
          <div class="info-content">
            <span class="info-label">Type</span>
            <span class="info-value">{{ meetingNote.last_meeting_type }}</span>
          </div>
        </div>
      </div>
      
      <div class="info-row" v-if="showDetails && meetingNote.meeting_duration">
        <div class="info-item">
          <div class="info-icon duration-icon">⏱️</div>
          <div class="info-content">
            <span class="info-label">Duration</span>
            <span class="info-value">{{ meetingNote.meeting_duration }} min</span>
          </div>
        </div>
        <div class="info-item" v-if="meetingNote.meeting_location">
          <div class="info-icon location-icon">📍</div>
          <div class="info-content">
            <span class="info-label">Location</span>
            <span class="info-value">{{ meetingNote.meeting_location }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
      <div class="summary-header">
        <h4>Meeting Summary</h4>
        <button @click="toggleDetails" class="toggle-btn">
          <span class="toggle-icon">{{ showDetails ? '▼' : '▶' }}</span>
          {{ showDetails ? 'Less' : 'More' }}
        </button>
      </div>
      
      <div class="summary-content">
        <p v-if="!showDetails" class="summary-text">
          {{ meetingNote.last_meeting_notes_truncated }}
        </p>
        
        <div v-if="showDetails" class="detailed-summary">
          <p class="summary-text">
            {{ meetingNote.meeting_summary }}
          </p>
          
          <div v-if="meetingNote.meeting_special_notes" class="special-notes">
            <h5>Special Notes</h5>
            <p>{{ meetingNote.meeting_special_notes }}</p>
          </div>
          
          <div class="timestamps">
            <div class="timestamp-item">
              <span class="timestamp-label">Created:</span>
              <span class="timestamp-value">{{ formatDateTime(meetingNote.created_at) }}</span>
            </div>
            <div class="timestamp-item">
              <span class="timestamp-label">Updated:</span>
              <span class="timestamp-value">{{ formatDateTime(meetingNote.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="card-actions">
      <div class="action-buttons">
        <button @click="showEditModal = true" class="action-btn edit-btn">
          <span class="btn-icon">✏️</span>
          <span class="btn-text">Edit</span>
        </button>
        <button @click="confirmDelete" class="action-btn delete-btn">
          <span class="btn-icon">🗑️</span>
          <span class="btn-text">Delete</span>
        </button>
      </div>
      <button @click="emitGenerateReport" class="report-btn">
        <span class="btn-icon">📊</span>
        <span class="btn-text">Generate Report</span>
      </button>
    </div>

    <!-- Edit Modal (unchanged) -->
    <MeetingNoteFormComponent
      v-if="showEditModal"
      :advisorId="advisorId"
      :advisees="[]" 
      :mode="'edit'"
      :initialData="meetingNote"
      @close="showEditModal = false"
      @note-updated="handleNoteUpdated"
    />
  </div>
</template>

<script>
import axios from 'axios';
import MeetingNoteFormComponent from './MeetingNoteFormComponent.vue';

export default {
  name: 'MeetingNoteComponent',
  components: {
    MeetingNoteFormComponent
  },
  props: {
    meetingNote: {
      type: Object,
      required: true
    },
    advisorId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      showDetails: false,
      showEditModal: false,
    };
  },
  methods: {
    toggleDetails() {
      this.showDetails = !this.showDetails;
    },
    getInitials(name) {
      return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    getTypeIcon(type) {
      const icons = {
        'Physical': '👥',
        'Video Call': '💻',
        'Phone Call': '📞',
        'Online': '🌐'
      };
      return icons[type] || '💬';
    },
    formatDate(datetime) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(datetime).toLocaleDateString(undefined, options);
    },
    formatDateTime(datetime) {
      const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
      };
      return new Date(datetime).toLocaleDateString(undefined, options);
    },
    confirmDelete() {
      if (confirm(`Are you sure you want to delete this meeting note with ${this.meetingNote.student_info.name}?`)) {
        this.deleteMeetingNote();
      }
    },
    async deleteMeetingNote() {
      try {
        const response = await axios.delete(`http://localhost:8080/api/v1/advisors/${this.advisorId}/meeting-notes/${this.meetingNote.id}`);
        if (response.data.status === 'success') {
          this.$emit('note-deleted');
        } else {
          alert('Failed to delete meeting note: ' + (response.data.message || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error deleting meeting note:', error);
        alert('Network error or server issue during deletion. Please try again.');
      }
    },
    handleNoteUpdated() {
      this.showEditModal = false;
      this.$emit('note-updated');
    },
    emitGenerateReport() {
      this.$emit('generate-report', this.meetingNote.student_info.id);
    }
  }
};
</script>

<style scoped>
.meeting-note-card {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
  border: 1px solid #f0f0f0;
  overflow: hidden;
  transition: all 0.3s ease;
  height: fit-content;
}

.meeting-note-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

/* Card Header */
.card-header {
  padding: 24px;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  align-items: center;
  gap: 16px;
}

.student-avatar {
  flex-shrink: 0;
}

.avatar-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 18px;
  letter-spacing: 1px;
}

.student-details {
  flex: 1;
  min-width: 0;
}

.student-name {
  margin: 0 0 4px 0;
  font-size: 18px;
  font-weight: 600;
  color: #2d3748;
  line-height: 1.3;
}

.student-matric {
  margin: 0 0 8px 0;
  color: #718096;
  font-size: 14px;
  font-weight: 500;
}

.student-program {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.program-badge, .year-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.program-badge {
  background: #e6f3ff;
  color: #0066cc;
}

.year-badge {
  background: #f0f4f8;
  color: #4a5568;
}

/* Meeting Info */
.meeting-info {
  padding: 20px 24px;
}

.info-row {
  display: flex;
  gap: 24px;
  margin-bottom: 16px;
}

.info-row:last-child {
  margin-bottom: 0;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 0;
}

.info-icon {
  font-size: 20px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: #f7fafc;
  flex-shrink: 0;
}

.info-content {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.info-label {
  font-size: 12px;
  color: #a0aec0;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 14px;
  color: #2d3748;
  font-weight: 500;
  margin-top: 2px;
}

/* Summary Section */
.summary-section {
  padding: 0 24px 20px;
}

.summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.summary-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #2d3748;
}

.toggle-btn {
  background: none;
  border: none;
  color: #4299e1;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.toggle-btn:hover {
  background: #ebf8ff;
}

.toggle-icon {
  font-size: 12px;
  transition: transform 0.2s ease;
}

.summary-text {
  margin: 0;
  color: #4a5568;
  line-height: 1.6;
  font-size: 14px;
}

.detailed-summary {
  animation: fadeIn 0.3s ease;
}

.special-notes {
  margin-top: 16px;
  padding: 16px;
  background: #fffbf0;
  border-radius: 8px;
  border-left: 4px solid #f6ad55;
}

.special-notes h5 {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
  color: #c05621;
}

.special-notes p {
  margin: 0;
  color: #744210;
  font-size: 14px;
  line-height: 1.5;
}

.timestamps {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px dashed #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.timestamp-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.timestamp-label {
  font-size: 12px;
  color: #a0aec0;
  font-weight: 500;
  min-width: 60px;
}

.timestamp-value {
  font-size: 12px;
  color: #718096;
}

/* Action Buttons */
.card-actions {
  padding: 20px 24px;
  background: #fafafa;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #4a5568;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: #f7fafc;
  border-color: #cbd5e0;
}

.edit-btn:hover {
  background: #fffbf0;
  border-color: #f6ad55;
  color: #c05621;
}

.delete-btn:hover {
  background: #fff5f5;
  border-color: #feb2b2;
  color: #c53030;
}

.report-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(66, 153, 225, 0.2);
}

.report-btn:hover {
  background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
  box-shadow: 0 4px 8px rgba(66, 153, 225, 0.3);
  transform: translateY(-1px);
}

.btn-icon {
  font-size: 16px;
}

.btn-text {
  font-size: 14px;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .meeting-note-card {
    border-radius: 12px;
  }
  
  .card-header {
    padding: 20px;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .student-details {
    width: 100%;
  }
  
  .meeting-info {
    padding: 16px 20px;
  }
  
  .info-row {
    flex-direction: column;
    gap: 16px;
  }
  
  .summary-section {
    padding: 0 20px 16px;
  }
  
  .summary-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .card-actions {
    padding: 16px 20px;
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  
  .action-buttons {
    justify-content: center;
  }
  
  .report-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .card-header {
    padding: 16px;
  }
  
  .meeting-info {
    padding: 12px 16px;
  }
  
  .summary-section {
    padding: 0 16px 12px;
  }
  
  .card-actions {
    padding: 12px 16px;
  }
  
  .action-buttons {
    flex-direction: column;
    width: 100%;
  }
  
  .action-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>