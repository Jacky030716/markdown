<template>
  <div class="meeting-notes-page">
    <div class="meeting-notes-header">
      <div class="header-content">
        <div class="header-text">
          <h2 class="page-title">Meeting Notes</h2>
          <p class="page-subtitle">Manage your meeting records and generate consultation reports</p>
        </div>
        <div class="header-actions">
          <button @click="showAddNoteModal = true" class="add-note-btn btn-primary">
            <span class="btn-icon">+</span>
            <span class="btn-text">Add Meeting Note</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Keep the modal component -->
    <MeetingNoteFormComponent v-if="showAddNoteModal" :advisorId="currentAdvisorId" :advisees="advisees" :mode="'add'"
      @close="showAddNoteModal = false" @note-added="handleNoteAdded" />

    <div class="existing-notes-section">
      <h3 class="section-title">My Meeting Records</h3>
      <p v-if="loading" class="loading-message">
        <span class="loading-icon">⏳</span>
        Loading meeting notes...
      </p>
      <p v-else-if="error" class="error-message">
        <span class="error-icon">❌</span>
        Error loading meeting notes: {{ error }}
      </p>
      <div v-else-if="meetingNotes.length > 0" class="meeting-notes-grid">
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
      <div v-else class="no-notes-container">
        <div class="no-notes-icon">📝</div>
        <h4 class="no-notes-title">No Meeting Notes Yet</h4>
        <p class="no-notes-message">Start by adding your first meeting note to keep track of student consultations.</p>
        <button @click="showAddNoteModal = true" class="add-first-note-btn">
          <span class="btn-icon">+</span>
          Add Your First Note
        </button>
      </div>
    </div>

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
/* Main page styles */
.meeting-notes-page {
  min-height: 100vh;
  background: #f8fafc;
}

.meeting-notes-header {
  background: white;
  border-bottom: 1px solid #e2e8f0;
  padding: 24px 0;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-text {
  flex: 1;
}

.page-title {
  margin: 0 0 8px 0;
  font-size: 32px;
  font-weight: 700;
  color: #1a202c;
}

.page-subtitle {
  margin: 0;
  color: #64748b;
  font-size: 16px;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.add-note-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-note-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}

.btn-icon {
  font-size: 18px;
  font-weight: bold;
}

/* Existing notes section */
.existing-notes-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px;
}

.section-title {
  margin: 0 0 24px 0;
  font-size: 24px;
  font-weight: 600;
  color: #1e293b;
}

/* Grid layout for meeting notes */
.meeting-notes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
  gap: 24px;
  margin-top: 24px;
}

/* Loading and error states */
.loading-message, .error-message {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 24px;
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  font-size: 16px;
  color: #64748b;
  margin: 24px 0;
}

.loading-icon, .error-icon {
  font-size: 20px;
}

.error-message {
  color: #dc2626;
  border-color: #fecaca;
  background: #fef2f2;
}

/* No notes state */
.no-notes-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 16px;
  padding: 48px 32px;
  text-align: center;
  border: 1px solid #e2e8f0;
  margin: 24px 0;
}

.no-notes-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.no-notes-title {
  margin: 0 0 8px 0;
  font-size: 20px;
  font-weight: 600;
  color: #1e293b;
}

.no-notes-message {
  margin: 0 0 24px 0;
  color: #64748b;
  font-size: 16px;
  max-width: 400px;
  line-height: 1.5;
}

.add-first-note-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-first-note-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}

/* Report Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 24px;
}

.report-modal {
  background: white;
  border-radius: 16px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 64px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}

.modal-header h3 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  color: #1e293b;
}

.header-buttons {
  display: flex;
  gap: 12px;
}

.download-btn-header {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.download-btn-header:hover {
  background: #2563eb;
}

.close-btn {
  padding: 8px 12px;
  background: #f1f5f9;
  color: #64748b;
  border: none;
  border-radius: 8px;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #e2e8f0;
  color: #374151;
}

.loading-content {
  padding: 48px;
  text-align: center;
  color: #64748b;
  font-size: 16px;
}

.report-content {
  padding: 24px;
}

/* UTM Report Styles */
.utm-header {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 2px solid #e2e8f0;
}

.utm-logo {
  height: 80px;
  width: auto;
}

.utm-info {
  flex: 1;
}

.utm-title {
  margin: 0 0 8px 0;
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
}

.utm-subtitle {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  color: #64748b;
}

.utm-address {
  margin: 0 0 2px 0;
  font-size: 12px;
  color: #64748b;
}

.utm-report-type {
  margin: 8px 0 0 0;
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}

.student-header {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  margin-bottom: 32px;
  padding: 20px;
  background: #f8fafc;
  border-radius: 12px;
}

.student-info-left p, .student-info-right p {
  margin: 0 0 8px 0;
  font-size: 14px;
  color: #374151;
}

.info-label {
  font-weight: 600;
  color: #1e293b;
}

.meetings-table-container {
  overflow-x: auto;
  margin-bottom: 24px;
}

.meetings-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.meetings-table th {
  background: #f1f5f9;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.meetings-table td {
  padding: 12px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 14px;
  color: #374151;
}

.meetings-table tr:last-child td {
  border-bottom: none;
}

.report-footer {
  text-align: center;
  padding-top: 24px;
  border-top: 1px solid #e2e8f0;
  color: #64748b;
  font-size: 12px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 20px;
    align-items: flex-start;
    padding: 0 16px;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-end;
  }

  .existing-notes-section {
    padding: 24px 16px;
  }

  .meeting-notes-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .page-title {
    font-size: 24px;
  }

  .section-title {
    font-size: 20px;
  }

  .utm-header {
    flex-direction: column;
    text-align: center;
  }

  .student-header {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .modal-overlay {
    padding: 16px;
  }

  .modal-header {
    padding: 16px;
  }

  .modal-header h3 {
    font-size: 20px;
  }

  .report-content {
    padding: 16px;
  }
}

@media (max-width: 480px) {
  .existing-notes-section {
    padding: 16px 12px;
  }

  .meeting-notes-grid {
    grid-template-columns: 1fr;
  }

  .add-note-btn, .add-first-note-btn {
    padding: 10px 20px;
    font-size: 14px;
  }

  .no-notes-container {
    padding: 32px 20px;
  }

  .meetings-table {
    font-size: 12px;
  }

  .meetings-table th, .meetings-table td {
    padding: 8px;
  }
}
</style>