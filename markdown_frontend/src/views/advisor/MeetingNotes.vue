<template>
  <div class="meeting-notes-page">
    <h2>Academic Advisor Meeting Notes</h2>
    <p>Here you can manage your meeting records with advisees and generate consultation reports. 📝</p>

    <section class="add-meeting-note-section">
      <h3>Add New Meeting Note</h3>
      <button @click="showAddNoteModal = true" class="add-button">Add Meeting Note</button>
      <MeetingNoteFormComponent
        v-if="showAddNoteModal"
        :advisorId="currentAdvisorId"
        :advisees="advisees"
        :mode="'add'"
        @close="showAddNoteModal = false"
        @note-added="handleNoteAdded"
      />
    </section>

    <hr />

    <section class="existing-notes-section">
      <h3>My Meeting Records</h3>
      <p v-if="loading" class="loading-message">Loading meeting notes... ⏳</p>
      <p v-else-if="error" class="error-message">Error loading meeting notes: {{ error }} ❌</p>
      <div v-else-if="meetingNotes.length > 0" class="meeting-notes-list">
        <MeetingNoteComponent
          v-for="note in meetingNotes"
          :key="note.id"
          :meetingNote="note"
          :advisorId="currentAdvisorId"
          @note-updated="handleNoteUpdated"
          @note-deleted="handleNoteDeleted"
          @generate-report="handleGenerateReport"
        />
      </div>
      <p v-else class="no-notes-message">No meeting notes found for this advisor. Start by adding one! 🚀</p>
    </section>
  </div>
</template>

<script>
import axios from 'axios';
import MeetingNoteComponent from '../../components/advisor/meetingNotes/MeetingNoteComponent.vue';
import MeetingNoteFormComponent from '../../components/advisor/meetingNotes/MeetingNoteFormComponent.vue'; // A new component for the add/edit form

export default {
  name: 'MeetingNotes',
  components: {
    MeetingNoteComponent,
    MeetingNoteFormComponent
  },
  data() {
    return {
      currentAdvisorId: 1, // This should come from your authentication context (e.g., Vuex store, localStorage)
      meetingNotes: [],
      advisees: [], // To populate the dropdown in the add/edit form
      loading: true,
      error: null,
      showAddNoteModal: false,
    };
  },
  created() {
    this.fetchMeetingNotes();
    this.fetchAdviseesForDropdown();
  },
  methods: {
    async fetchMeetingNotes() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`http://localhost:8080/api/v1/advisors/${this.currentAdvisorId}/meeting-notes`);
        if (response.data.status === 'success') {
          this.meetingNotes = response.data.data;
        } else {
          this.error = response.data.message || 'Failed to fetch meeting notes.';
        }
      } catch (err) {
        console.error('Error fetching meeting notes:', err);
        this.error = 'Network error or server issue. Please try again later.';
      } finally {
        this.loading = false;
      }
    },
    async fetchAdviseesForDropdown() {
      try {
        const response = await axios.get(`http://localhost:8080/api/v1/advisors/${this.currentAdvisorId}/advisees-dropdown`);
        if (response.data.status === 'success') {
          this.advisees = response.data.data;
        } else {
          console.error('Failed to fetch advisees for dropdown:', response.data.message);
        }
      } catch (err) {
        console.error('Error fetching advisees dropdown:', err);
      }
    },
    handleNoteAdded() {
      this.showAddNoteModal = false;
      this.fetchMeetingNotes(); // Refresh the list after adding a new note
      alert('Meeting note added successfully! 🎉');
    },
    handleNoteUpdated() {
      this.fetchMeetingNotes(); // Refresh the list after a note is updated
      alert('Meeting note updated successfully! ✅');
    },
    handleNoteDeleted() {
      this.fetchMeetingNotes(); // Refresh the list after a note is deleted
      alert('Meeting note deleted successfully! 🗑️');
    },
    async handleGenerateReport(studentId) {
      try {
        // Here you'd likely trigger a backend endpoint that generates a report (e.g., PDF, CSV)
        // For demonstration, let's assume it returns a URL or directly triggers a download.
        alert(`Generating consultation report for student ID: ${studentId}... This feature would typically download a file. 📄`);
        const response = await axios.get(`http://localhost:8080/api/v1/advisors/${this.currentAdvisorId}/students/${studentId}/consultation-report`, {
          responseType: 'blob' // Important for file downloads
        });

        // Create a blob URL and trigger download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `consultation_report_student_${studentId}.pdf`); // Or .csv, depending on your backend
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } catch (error) {
        console.error('Error generating report:', error);
        alert('Failed to generate consultation report. Please try again. 🚨');
      }
    }
  }
};
</script>

<style scoped>
.meeting-notes-page {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: Arial, sans-serif;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

h2 {
  color: #2c3e50;
  text-align: center;
  margin-bottom: 20px;
}

h3 {
  color: #34495e;
  margin-top: 30px;
  margin-bottom: 15px;
}

.add-meeting-note-section,
.existing-notes-section {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  margin-bottom: 20px;
}

.add-button {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s ease;
}

.add-button:hover {
  background-color: #45a049;
}

.loading-message, .error-message, .no-notes-message {
  text-align: center;
  padding: 20px;
  color: #555;
}

.error-message {
  color: #e74c3c;
}

.meeting-notes-list {
  display: grid;
  gap: 20px;
}

hr {
  border: 0;
  height: 1px;
  background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0));
  margin: 40px 0;
}
</style>