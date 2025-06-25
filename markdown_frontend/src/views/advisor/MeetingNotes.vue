<template>
  <div class="meeting-notes-page">
    <h2>Academic Advisor Meeting Notes</h2>
    <p>Here you can manage your meeting records with advisees and generate consultation reports. 📝</p>

    <section class="add-meeting-note-section">
      <h3>Add New Meeting Note</h3>
      <button @click="showAddNoteModal = true" class="add-button">Add Meeting Note</button>
      <MeetingNoteFormComponent v-if="showAddNoteModal" :advisorId="currentAdvisorId" :advisees="advisees" :mode="'add'"
        @close="showAddNoteModal = false" @note-added="handleNoteAdded" />
    </section>

    <hr />

    <section class="existing-notes-section">
      <h3>My Meeting Records</h3>
      <p v-if="loading" class="loading-message">Loading meeting notes... ⏳</p>
      <p v-else-if="error" class="error-message">Error loading meeting notes: {{ error }} ❌</p>
      <div v-else-if="meetingNotes.length > 0" class="meeting-notes-list">
        <MeetingNoteComponent v-for="note in meetingNotes" :key="note.id" :meetingNote="note"
          :advisorId="currentAdvisorId" @note-updated="handleNoteUpdated" @note-deleted="handleNoteDeleted"
          @generate-report="handleGenerateReport" />
      </div>
      <p v-else class="no-notes-message">No meeting notes found for this advisor. Start by adding one! 🚀</p>
    </section>

    <!-- Report Modal -->
    <div v-if="showReportModal" class="modal-overlay" @click="closeReportModal">
      <div class="report-modal" @click.stop>
        <div class="modal-header">
          <h3 class="text-2xl">Student Consultation Report</h3>
          <div class="header-buttons">
            <button v-if="reportData && !reportLoading" @click="downloadPDF" class="download-btn-header">
              📄 Download PDF
            </button>
            <button @click="closeReportModal" class="close-btn ">&times;</button>
          </div>
        </div>

        <div v-if="reportLoading" class="loading-content">
          <p>Generating report... ⏳</p>
        </div>

        <div v-else-if="reportData" class="report-content">
          <!-- UTM Header -->
          <div class="utm-header">
            <div class="utm-logo-section">
              <img src="../../assets/UTM-LOGO-FULL.png" alt="UTM Logo" class="utm-logo" />
            </div>
            <div class="utm-info">
              <h1 class="utm-title">UNIVERSITI TEKNOLOGI MALAYSIA</h1>
              <p class="utm-subtitle">ACADEMIC MANAGEMENT DIVISION</p>
              <p class="utm-address">81310 UTM JOHOR BAHRU,</p>
              <p class="utm-address">JOHOR, MALAYSIA.</p>
              <p class="utm-report-type">(ACADEMIC CONSULTATION REPORT)</p>
            </div>
          </div>

          <!-- Student Info Header -->
          <div class="student-header">
            <div class="student-info-left">
              <p><span class="info-label">FACULTY:</span> FAKULTI KOMPUTERAN</p>
              <p><span class="info-label">NAME:</span> {{ reportData.student_info.name }}</p>
              <p><span class="info-label">MATRIC CARD NO.:</span> {{ reportData.student_info.matric_no }}</p>
              <p><span class="info-label">YEAR/PROGRAMME:</span> {{ reportData.student_info.year_of_study }} / {{
                reportData.student_info.program.toUpperCase() }}</p>
            </div>
            <div class="student-info-right">
              <p><strong>Total Meetings:</strong> {{ reportData.total_meetings }}</p>
              <p><strong>Physical:</strong> {{ reportData.meetings_summary.physical }}</p>
              <p><strong>Video:</strong> {{ reportData.meetings_summary.video_call }}</p>
              <p><span class="info-label">ACADEMIC ADVISOR:</span> Dr. Teong Lee</p>
            </div>
          </div>

          <!-- Meetings Table -->
          <div class="meetings-table-container">
            <table class="meetings-table">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Date & Time</th>
                  <th>Duration (min)</th>
                  <th>Type</th>
                  <th>Location</th>
                  <th>Summary</th>
                  <th>Special Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(meeting, index) in reportData.meetings" :key="meeting.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ meeting.meeting_date }}</td>
                  <td>{{ meeting.meeting_duration }}</td>
                  <td>{{ meeting.meeting_type }}</td>
                  <td>{{ meeting.meeting_location || '-' }}</td>
                  <td>{{ meeting.meeting_summary }}</td>
                  <td>{{ meeting.meeting_special_notes }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Footer -->
          <div class="report-footer">
            <p><em>Report generated on: {{ new Date().toLocaleDateString() }} at {{ new Date().toLocaleTimeString()
            }}</em></p>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import advisorsApi from '../../api/advisors' // Adjust path if needed
import MeetingNoteComponent from '../../components/advisor/meetingNotes/MeetingNoteComponent.vue'
import MeetingNoteFormComponent from '../../components/advisor/meetingNotes/MeetingNoteFormComponent.vue'

import utmLogo from '../../assets/UTM-LOGO-FULL.png'

export default {
  name: 'MeetingNotes',
  components: {
    MeetingNoteComponent,
    MeetingNoteFormComponent
  },
  data() {
    return {
      currentAdvisorId: 1, // This should come from your authentication context
      meetingNotes: [],
      advisees: [],
      loading: true,
      error: null,
      showAddNoteModal: false,
      showReportModal: false,
      reportData: null,
      reportLoading: false,
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
        const response = await advisorsApi.getMeetingNotes(this.currentAdvisorId);
        if (response.status === 'success') {
          this.meetingNotes = response.data;
        } else {
          this.error = response.message || 'Failed to fetch meeting notes.';
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
        const response = await advisorsApi.getAdviseesForDropdown(this.currentAdvisorId);
        if (response.status === 'success') {
          this.advisees = response.data;
        } else {
          console.error('Failed to fetch advisees for dropdown:', response.message);
        }
      } catch (err) {
        console.error('Error fetching advisees dropdown:', err);
      }
    },
    handleNoteAdded() {
      this.showAddNoteModal = false;
      this.fetchMeetingNotes();
      alert('Meeting note added successfully! 🎉');
    },
    handleNoteUpdated() {
      this.fetchMeetingNotes();
      alert('Meeting note updated successfully! ✅');
    },
    handleNoteDeleted() {
      this.fetchMeetingNotes();
      alert('Meeting note deleted successfully! 🗑️');
    },
    async handleGenerateReport(studentId) {
      this.reportLoading = true;
      this.showReportModal = true;
      try {
        const response = await advisorsApi.generateConsultationReport(this.currentAdvisorId, studentId);
        if (response.status === 'success') {
          this.reportData = response.data;
        } else {
          throw new Error(response.message || 'Failed to generate report');
        }
      } catch (error) {
        console.error('Error generating report:', error);
        alert('Failed to generate consultation report. Please try again. 🚨');
        this.showReportModal = false;
      } finally {
        this.reportLoading = false;
      }
    },
    closeReportModal() {
      this.showReportModal = false;
      this.reportData = null;
    },
    downloadPDF() {
      // Create a simplified PDF download (you can enhance this with a proper PDF library)
      const printWindow = window.open('', '_blank');
      const reportHTML = this.generateReportHTML();
      printWindow.document.write(reportHTML);
      printWindow.document.close();
      printWindow.print();
    },
    generateReportHTML() {
      if (!this.reportData) return '';

      const student = this.reportData.student_info;
      const meetings = this.reportData.meetings;


      return `
<html>
  <head>
    <title>Consultation Report - ${student.name}</title>
    <style>
      body { font-family: Arial, sans-serif; margin: 20px; }
      .utm-header { text-align: center; margin-bottom: 30px; padding: 20px 0; border-bottom: 2px solid #2c3e50; }
      .utm-logo-section { display: flex; justify-content: center; margin-bottom: 15px; }
      .utm-logo { width: 200px; height: 100px; object-fit: contain; display: block; }
      .utm-title { font-size: 1.5rem; font-weight: bold; color: #2c3e50; margin: 10px 0 5px 0; }
      .utm-subtitle, .utm-address { color: #666; margin: 2px 0; font-size: 0.9rem; }
      .utm-report-type { font-weight: 600; color: #2c3e50; margin-top: 10px; }
      .student-info { margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; }
      .info-label { font-weight: 600; color: #2c3e50; }
      .student-info-left, .student-info-right { display: flex; flex-direction: column; gap: 8px; }
      table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-bottom: 20px; 
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      }
      th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
      th { background-color: #34495e; color: white; }
      .meeting-summary p { margin: 5px 0; }
    </style>
  </head>
  <body>
    <div class="utm-header">
      <div class="utm-logo-section">
        <img src="${utmLogo}" alt="UTM Logo" class="utm-logo" />
      </div>
      <h1 class="utm-title">UNIVERSITI TEKNOLOGI MALAYSIA</h1>
      <p class="utm-subtitle">ACADEMIC MANAGEMENT DIVISION</p>
      <p class="utm-address">81310 UTM JOHOR BAHRU,</p>
      <p class="utm-address">JOHOR, MALAYSIA.</p>
      <p class="utm-report-type">(ACADEMIC CONSULTATION REPORT)</p>
    </div>
    
    <div class="student-info">
      <div class="student-info-left">
        <p><span class="info-label">FACULTY:</span> FAKULTI KOMPUTERAN</p>
        <p><span class="info-label">NAME:</span> ${student.name}</p>
        <p><span class="info-label">MATRIC CARD NO.:</span> ${student.matric_no}</p>
        <p><span class="info-label">YEAR/PROGRAMME:</span> ${student.year_of_study} / ${student.program.toUpperCase()}</p>
      </div>
      <div class="student-info-right">
        <p><strong>Total Meetings:</strong> ${this.reportData.total_meetings}</p>
        <p><strong>Physical:</strong> ${this.reportData.meetings_summary.physical}</p>
        <p><strong>Video:</strong> ${this.reportData.meetings_summary.video_call}</p>
        <p><span class="info-label">ACADEMIC ADVISOR:</span> Dr. Teong Lee</p>
      </div>
    </div>
    
    <h3>Meeting Records</h3>
    <table>
      <thead>
        <tr>
          <th>No.</th>
          <th>Date & Time</th>
          <th>Duration (min)</th>
          <th>Type</th>
          <th>Location</th>
          <th>Summary</th>
          <th>Special Notes</th>
        </tr>
      </thead>
      <tbody>
        ${meetings.map((meeting, index) => `
          <tr>
            <td>${index + 1}</td>
            <td>${meeting.meeting_date}</td>
            <td>${meeting.meeting_duration}</td>
            <td>${meeting.meeting_type}</td>
            <td>${meeting.meeting_location || '-'}</td>
            <td>${meeting.meeting_summary}</td>
            <td>${meeting.meeting_special_notes}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
    
    <div style="margin-top: 30px; text-align: center;">
      <p><em>Report generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</em></p>
    </div>
  </body>
</html>
`;
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

.loading-message,
.error-message,
.no-notes-message {
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

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  /* 改为更暗的背景 */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.report-modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 1000px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  position: relative;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 30px;
  border-bottom: 1px solid #eee;
  background: #f8f9fa;
  border-radius: 12px 12px 0 0;
}

.modal-header h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.5rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 15%;
  transition: color 0.3s ease;
}

.close-btn:hover {
  color: #000;
  background-color: rgba(0, 0, 0, 0.2);
}

.loading-content {
  padding: 40px;
  text-align: center;
  color: #666;
}

.report-content {
  padding: 30px;
}

.student-header {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.student-info-section h4 {
  margin: 0 0 10px 0;
  color: #2c3e50;
  font-size: 1.3rem;
}

.student-info-section p,
.meeting-summary p {
  margin: 5px 0;
  color: #555;
}

.meetings-table-container {
  overflow-x: auto;
  margin-bottom: 30px;
}

.meetings-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.meetings-table th {
  background: #34495e;
  color: white;
  padding: 12px 8px;
  text-align: left;
  font-weight: 600;
  white-space: nowrap;
}

.meetings-table td {
  padding: 12px 8px;
  border-bottom: 1px solid #eee;
  color: #555;
}

.meetings-table tbody tr:hover {
  background: #f8f9fa;
}

.report-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 0;
  border-top: 1px solid #eee;
}

.download-btn {
  background: #27ae60;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s ease;
}

.download-btn:hover {
  background: #219a52;
}

/* Responsive Design */
@media (max-width: 768px) {
  .report-modal {
    width: 95%;
    max-height: 85vh;
  }

  .modal-header {
    padding: 15px 20px;
  }

  .report-content {
    padding: 20px;
  }

  .student-header {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .meetings-table th,
  .meetings-table td {
    padding: 8px 4px;
    font-size: 14px;
  }

  .report-footer {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
}

/* UTM Header Styles */
.utm-header {
  text-align: center;
  margin-bottom: 30px;
  padding: 20px 0;
  border-bottom: 2px solid #2c3e50;
}

.utm-logo-section {
  margin-bottom: 15px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.utm-logo {
  width: 200px;
  height: 100px;
  object-fit: contain;
  display: block;
  margin: 0 auto;
}

.utm-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #2c3e50;
  margin: 10px 0 5px 0;
}

.utm-subtitle,
.utm-address {
  color: #666;
  margin: 2px 0;
  font-size: 0.9rem;
}

.utm-report-type {
  font-weight: 600;
  color: #2c3e50;
  margin-top: 10px;
  font-size: 0.95rem;
}

.info-label {
  font-weight: 600;
  color: #2c3e50;
}

/* Update existing student-header styles */
.student-header {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.header-buttons {
  display: flex;
  align-items: center;
  gap: 15px;
}

.download-btn-header {
  background: #00a63e;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s ease;
}

.download-btn-header:hover {
  background: #219a52;
}

.student-info-left,
.student-info-right {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
</style>