<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <h3>{{ mode === 'add' ? 'Add New Meeting Note' : 'Edit Meeting Note' }}</h3>
      <form @submit.prevent="handleSubmit">
        <div class="form-group" v-if="mode === 'add'">
          <label for="student">Advisee:</label>
          <select id="student" v-model="form.student_id" required>
            <option value="" disabled>Select an Advisee</option>
            <option v-for="advisee in advisees" :key="advisee.id" :value="advisee.id">
              {{ advisee.display_name }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label for="meetingDate">Meeting Date & Time:</label>
          <input type="datetime-local" id="meetingDate" v-model="form.meeting_date" required />
        </div>

        <div class="form-group">
          <label for="meetingDuration">Duration (minutes):</label>
          <input type="number" id="meetingDuration" v-model.number="form.meeting_duration" min="0" />
        </div>

        <div class="form-group">
          <label for="meetingType">Meeting Type:</label>
          <select id="meetingType" v-model="form.meeting_type" @change="handleTypeChange" required>
            <option value="Physical">Physical</option>
            <option value="Video Call">Video Call</option>
            <option value="Phone Call">Phone Call</option>
          </select>
        </div>

        <div class="form-group" v-if="form.meeting_type === 'Physical'">
          <label for="meetingLocation">Meeting Location:</label>
          <input type="text" id="meetingLocation" v-model="form.meeting_location" />
        </div>

        <div class="form-group">
          <label for="meetingSummary">Meeting Summary:</label>
          <textarea id="meetingSummary" v-model="form.meeting_summary" rows="5" required></textarea>
        </div>

        <div class="form-group">
          <label for="specialNotes">Special Notes (Optional):</label>
          <textarea id="specialNotes" v-model="form.meeting_special_notes" rows="3"></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" :disabled="loading">{{ mode === 'add' ? 'Add Note' : 'Update Note' }}</button>
          <button type="button" @click="$emit('close')" class="cancel-button">Cancel</button>
        </div>
        <p v-if="formError" class="error-message">{{ formError }}</p>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'MeetingNoteFormComponent',
  props: {
    advisorId: {
      type: Number,
      required: true
    },
    advisees: {
      type: Array,
      default: () => [] // Used for 'add' mode dropdown
    },
    mode: {
      type: String,
      default: 'add', // 'add' or 'edit'
      validator: (value) => ['add', 'edit'].includes(value)
    },
    initialData: {
      type: Object,
      default: null // For 'edit' mode, provide the existing note data
    }
  },
  data() {
    return {
      form: {
        student_id: '',
        meeting_date: '',
        meeting_duration: null,
        meeting_type: 'Physical',
        meeting_location: '',
        meeting_summary: '',
        meeting_special_notes: ''
      },
      loading: false,
      formError: null
    };
  },
  created() {
    if (this.mode === 'edit' && this.initialData) {
      this.populateFormForEdit();
    }
  },
  methods: {
    populateFormForEdit() {
      // Ensure meeting_date is formatted correctly for datetime-local input
      const date = new Date(this.initialData.last_meeting_date);
      this.form = {
        student_id: this.initialData.student_info.id,
        meeting_date: date.toISOString().slice(0, 16), // Format to 'YYYY-MM-DDTHH:MM'
        meeting_duration: this.initialData.meeting_duration,
        meeting_type: this.initialData.last_meeting_type,
        meeting_location: this.initialData.meeting_location,
        meeting_summary: this.initialData.meeting_summary,
        meeting_special_notes: this.initialData.meeting_special_notes
      };
    },
    handleTypeChange() {
      // Clear location if not a physical meeting
      if (this.form.meeting_type !== 'Physical') {
        this.form.meeting_location = null;
      }
    },
    async handleSubmit() {
      this.loading = true;
      this.formError = null;

      try {
        let response;
        if (this.mode === 'add') {
          response = await axios.post(`http://localhost:8080/api/v1/advisors/${this.advisorId}/meeting-notes`, this.form);
          if (response.data.status === 'success') {
            this.$emit('note-added');
            this.$emit('close');
          } else {
            this.formError = response.data.message || 'Failed to add meeting note.';
          }
        } else { // mode === 'edit'
          response = await axios.put(`http://localhost:8080/api/v1/advisors/${this.advisorId}/meeting-notes/${this.initialData.id}`, this.form);
          if (response.data.status === 'success') {
            this.$emit('note-updated');
            this.$emit('close');
          } else {
            this.formError = response.data.message || 'Failed to update meeting note.';
          }
        }
      } catch (error) {
        console.error('Error submitting form:', error);
        this.formError = 'Network error or server issue. Please try again.';
      } finally {
        this.loading = false;
      }
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
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 600px;
  position: relative;
}

.modal-content h3 {
  margin-top: 0;
  color: #333;
  text-align: center;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #555;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="datetime-local"],
.form-group select,
.form-group textarea {
  width: calc(100% - 20px);
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 16px;
  box-sizing: border-box; /* Include padding in width */
}

.form-group textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.form-actions button {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s ease;
}

.form-actions button[type="submit"] {
  background-color: #28a745;
  color: white;
}

.form-actions button[type="submit"]:hover:not(:disabled) {
  background-color: #218838;
}

.form-actions button[type="submit"]:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.cancel-button {
  background-color: #6c757d;
  color: white;
}

.cancel-button:hover {
  background-color: #5a6268;
}

.error-message {
  color: #dc3545;
  text-align: center;
  margin-top: 10px;
}
</style>