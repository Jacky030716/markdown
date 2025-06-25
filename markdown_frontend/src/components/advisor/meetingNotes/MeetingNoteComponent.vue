<template>
  <div class="meeting-note-card">
    <div class="card-header">
      <h4>Meeting with {{ meetingNote.student_info.name }} ({{ meetingNote.student_info.matric_no }})</h4>
      <div class="actions">
        <button @click="toggleDetails" class="detail-button">
          {{ showDetails ? 'Hide Details' : 'View Details' }}
        </button>
        <button @click="showEditModal = true" class="edit-button">Edit</button>
        <button @click="confirmDelete" class="delete-button">Delete</button>
      </div>
    </div>

    <div class="card-body">
      <p><strong>Date:</strong> {{ formatDate(meetingNote.last_meeting_date) }}</p>
      <p><strong>Type:</strong> {{ meetingNote.last_meeting_type }}</p>
      <p v-if="!showDetails"><strong>Summary:</strong> {{ meetingNote.last_meeting_notes_truncated }}</p>
      
      <div v-if="showDetails" class="full-details">
        <p><strong>Duration:</strong> {{ meetingNote.meeting_duration }} minutes</p>
        <p v-if="meetingNote.meeting_location"><strong>Location:</strong> {{ meetingNote.meeting_location }}</p>
        <p><strong>Full Summary:</strong> {{ meetingNote.meeting_summary }}</p>
        <p v-if="meetingNote.meeting_special_notes"><strong>Special Notes:</strong> {{ meetingNote.meeting_special_notes }}</p>
        <p><strong>Student Program:</strong> {{ meetingNote.student_program }}</p>
        <p><strong>Student Year:</strong> {{ meetingNote.student_year }}</p>
        <p class="timestamps">
          <small>Created: {{ formatDateTime(meetingNote.created_at) }} | Last Updated: {{ formatDateTime(meetingNote.updated_at) }}</small>
        </p>
      </div>
    </div>

    <div class="card-footer">
      <button @click="emitGenerateReport" class="report-button">Generate Student Report</button>
    </div>

    <MeetingNoteFormComponent
      v-if="showEditModal"
      :advisorId="advisorId"
      :advisees="[]" :mode="'edit'"
      :initialData="meetingNote"
      @close="showEditModal = false"
      @note-updated="handleNoteUpdated"
    />
  </div>
</template>

<script>
import axios from 'axios';
import MeetingNoteFormComponent from './MeetingNoteFormComponent.vue'; // Import the form component

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
    formatDate(datetime) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(datetime).toLocaleDateString(undefined, options);
    },
    formatDateTime(datetime) {
      const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
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
          this.$emit('note-deleted'); // Emit event to parent to refresh the list
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
      this.$emit('note-updated'); // Emit event to parent to refresh the list
    },
    emitGenerateReport() {
      this.$emit('generate-report', this.meetingNote.student_info.id);
    }
  }
};
</script>

<style scoped>
.meeting-note-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-bottom: 15px;
  background-color: #fff;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card-header {
  background-color: #eef;
  padding: 15px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h4 {
  margin: 0;
  color: #333;
}

.card-body {
  padding: 15px;
}

.card-body p {
  margin: 5px 0;
  color: #555;
}

.full-details {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px dashed #eee;
}

.timestamps {
  font-style: italic;
  color: #888;
  margin-top: 10px;
}

.card-footer {
  padding: 15px;
  border-top: 1px solid #e0e0e0;
  text-align: right;
}

.actions button, .report-button {
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  margin-left: 10px;
  transition: background-color 0.3s ease;
}

.detail-button {
  background-color: #007bff;
  color: white;
}

.detail-button:hover {
  background-color: #0056b3;
}

.edit-button {
  background-color: #ffc107;
  color: #333;
}

.edit-button:hover {
  background-color: #e0a800;
}

.delete-button {
  background-color: #dc3545;
  color: white;
}

.delete-button:hover {
  background-color: #c82333;
}

.report-button {
  background-color: #17a2b8;
  color: white;
}

.report-button:hover {
  background-color: #138496;
}
</style>