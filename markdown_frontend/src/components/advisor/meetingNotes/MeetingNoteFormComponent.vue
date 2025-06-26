<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <!-- Header with close button -->
      <div class="modal-header">
        <h2 class="modal-title">
          {{ mode === 'add' ? 'Add New Meeting Note' : 'Edit Meeting Note' }}
        </h2>
        <button type="button" class="close-btn" @click="handleClose" aria-label="Close">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </button>
      </div>

      <!-- Form Content -->
      <div class="modal-body">
        <form @submit.prevent="handleSubmit" class="meeting-form">
          <!-- Advisee Selection (Add mode only) -->
          <div class="form-group" v-if="mode === 'add'">
            <label for="student" class="form-label">
              <span class="label-text">Advisee</span>
              <span class="required-indicator">*</span>
            </label>
            <div class="input-wrapper">
              <select id="student" v-model="form.student_id" required class="form-select">
                <option value="" disabled>Select an Advisee</option>
                <option v-for="advisee in advisees" :key="advisee.id" :value="advisee.id">
                  {{ advisee.display_name }}
                </option>
              </select>
              <div class="select-arrow">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Meeting Date & Time -->
          <div class="form-group">
            <label for="meetingDate" class="form-label">
              <span class="label-text">Meeting Date & Time</span>
              <span class="required-indicator">*</span>
            </label>
            <div class="input-wrapper">
              <input type="datetime-local" id="meetingDate" v-model="form.meeting_date" required class="form-input" />
              <div class="input-icon">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12.6667 2.66669H3.33333C2.59695 2.66669 2 3.26364 2 4.00002V13.3334C2 14.0697 2.59695 14.6667 3.33333 14.6667H12.6667C13.403 14.6667 14 14.0697 14 13.3334V4.00002C14 3.26364 13.403 2.66669 12.6667 2.66669Z"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M10.6667 1.33331V3.99998" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
                  <path d="M5.33333 1.33331V3.99998" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
                  <path d="M2 7.33331H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Duration and Meeting Type Row -->
          <div class="form-row">
            <div class="form-group">
              <label for="meetingDuration" class="form-label">
                <span class="label-text">Duration (minutes)</span>
              </label>
              <div class="input-wrapper">
                <input type="number" id="meetingDuration" v-model.number="form.meeting_duration" min="0"
                  placeholder="60" class="form-input" />
                <div class="input-icon">
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5" />
                    <path d="M8 4V8L10.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="meetingType" class="form-label">
                <span class="label-text">Meeting Type</span>
                <span class="required-indicator">*</span>
              </label>
              <div class="input-wrapper">
                <select id="meetingType" v-model="form.meeting_type" @change="handleTypeChange" required
                  class="form-select">
                  <option value="Physical">Physical</option>
                  <option value="Video Call">Video Call</option>
                  <option value="Phone Call">Phone Call</option>
                </select>
                <div class="select-arrow">
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Meeting Location (Physical only) -->
          <div class="form-group" v-if="form.meeting_type === 'Physical'">
            <label for="meetingLocation" class="form-label">
              <span class="label-text">Meeting Location</span>
            </label>
            <div class="input-wrapper">
              <input type="text" id="meetingLocation" v-model="form.meeting_location"
                placeholder="e.g., Room 101, Faculty Office" class="form-input" />
              <div class="input-icon">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M8 8.66669C9.10457 8.66669 10 7.77126 10 6.66669C10 5.56212 9.10457 4.66669 8 4.66669C6.89543 4.66669 6 5.56212 6 6.66669C6 7.77126 6.89543 8.66669 8 8.66669Z"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path
                    d="M8 1.33331C10.2091 1.33331 12 3.12417 12 5.33331C12 8.66665 8 14.6666 8 14.6666S4 8.66665 4 5.33331C4 3.12417 5.79086 1.33331 8 1.33331Z"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Meeting Summary -->
          <div class="form-group">
            <label for="meetingSummary" class="form-label">
              <span class="label-text">Meeting Summary</span>
              <span class="required-indicator">*</span>
            </label>
            <div class="textarea-wrapper">
              <textarea id="meetingSummary" v-model="form.meeting_summary" rows="4" required
                placeholder="Describe what was discussed during the meeting..." class="form-textarea"></textarea>
            </div>
          </div>

          <!-- Special Notes -->
          <div class="form-group">
            <label for="specialNotes" class="form-label">
              <span class="label-text">Special Notes</span>
              <span class="optional-indicator">(Optional)</span>
            </label>
            <div class="textarea-wrapper">
              <textarea id="specialNotes" v-model="form.meeting_special_notes" rows="3"
                placeholder="Any additional notes or follow-up actions..." class="form-textarea"></textarea>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="formError" class="error-message">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5" />
              <path d="M8 4V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                stroke-linejoin="round" />
              <path d="M8 12H8.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
            {{ formError }}
          </div>

          <!-- Form Actions -->
          <div class="form-actions">
            <button type="button" @click="handleClose" class="btn btn-secondary">
              Cancel
            </button>
            <button type="submit" :disabled="loading" class="btn btn-primary">
              <span v-if="loading" class="loading-spinner"></span>
              {{ loading ? 'Saving...' : (mode === 'add' ? 'Add Meeting Note' : 'Update Meeting Note') }}
            </button>
          </div>
        </form>
      </div>
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
      default: () => []
    },
    mode: {
      type: String,
      default: 'add',
      validator: (value) => ['add', 'edit'].includes(value)
    },
    initialData: {
      type: Object,
      default: null
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
  mounted() {
    this.$nextTick(() => {
      // Add class to body to prevent scrolling
      document.body.classList.add('modal-open');
      document.body.style.overflow = 'hidden';
      document.body.style.position = 'fixed';
      document.body.style.width = '100%';
      document.body.style.height = '100%';

      // Force reflow to prevent flickering
      this.$el.offsetHeight;

      // Optional: Move modal to document body to escape parent positioning
      if (this.$el.parentNode !== document.body) {
        document.body.appendChild(this.$el);
      }
    });
  },

  beforeUnmount() {
    // Clean up body styles
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
    document.body.style.height = '';

    // Remove from body if we moved it there
    if (this.$el && this.$el.parentNode === document.body) {
      this.$el.remove();
    }
  },
  methods: {
    handleClose() {
      // Clean up before closing
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
      document.body.style.position = '';
      document.body.style.width = '';
      document.body.style.height = '';

      this.$emit('close');
    },

    populateFormForEdit() {
      const date = new Date(this.initialData.last_meeting_date);
      this.form = {
        student_id: this.initialData.student_info.id,
        meeting_date: date.toISOString().slice(0, 16),
        meeting_duration: this.initialData.meeting_duration,
        meeting_type: this.initialData.last_meeting_type,
        meeting_location: this.initialData.meeting_location,
        meeting_summary: this.initialData.meeting_summary,
        meeting_special_notes: this.initialData.meeting_special_notes
      };
    },
    handleClose() {
      document.body.style.overflow = '';
      this.$emit('close');
    },

    handleTypeChange() {
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
            this.handleClose();
          } else {
            this.formError = response.data.message || 'Failed to add meeting note.';
          }
        } else {
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
/* Modal Overlay */
.modal-overlay {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  /* Use viewport units instead of % */
  height: 100vh !important;
  /* Use viewport units instead of % */
  background-color: rgba(0, 0, 0, 1) !important;
  /* Fixed the transparency */

  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999 !important;
  /* Very high z-index */
  padding: 20px;
  box-sizing: border-box;

  /* Prevent any parent transform from affecting this */
  transform: none !important;

  /* Ensure it's always on top */
  isolation: isolate;
}

/* Modal Content */
.modal-content {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 600px;
  min-width: 500px;
  /* ADD */
  max-height: 90vh;
  min-height: 400px;
  /* ADD */
  overflow: hidden;
  display: flex;
  flex-direction: column;
  pointer-events: auto;
  transform: translateZ(0);
  /* ADD */
  backface-visibility: hidden;
  /* ADD */
}

/* Modal Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 32px;
  border-bottom: 1px solid #f1f5f9;
  background: #fafafa;
  pointer-events: auto;
  /* Added pointer event */
}

.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  user-select: none;
  /* Added to prevent text selection */
}

.close-btn {
  background: none;
  border: none;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  /* Enhanced pointer event */
  color: #64748b;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: auto;
  /* Added pointer event */
}

.close-btn:hover {
  background-color: #cdcdcd;
  color: #334155;
  cursor: pointer;
  /* Enhanced pointer event */
}

.close-btn:active {
  transform: scale(0.95);
  cursor: pointer;
  /* Enhanced pointer event */
}

/* Modal Body */
.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 32px;
  pointer-events: auto;
  /* Added pointer event */
}

/* Form Styling */
.meeting-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
  pointer-events: auto;
  /* Added pointer event */
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  /* Added pointer event for labels */
  user-select: none;
  /* Added to prevent text selection */
}

.label-text {
  font-weight: 500;
  cursor: pointer;
  /* Added pointer event */
}

.required-indicator {
  color: #ef4444;
  font-weight: 600;
  cursor: default;
  /* Added pointer event */
}

.optional-indicator {
  color: #6b7280;
  font-weight: 400;
  font-size: 13px;
  cursor: default;
  /* Added pointer event */
}

/* Input Styling */
.input-wrapper,
.textarea-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  pointer-events: auto;
  /* Added pointer event */
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 12px 16px;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #334155;
  background-color: #ffffff;
  transition: all 0.2s ease;
  box-sizing: border-box;
  cursor: text;
  /* Added pointer event for text inputs */
  pointer-events: auto;
  /* Added pointer event */
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  cursor: text;
  /* Enhanced pointer event */
}

.form-input:hover,
.form-select:hover,
.form-textarea:hover {
  border-color: #cbd5e1;
  /* Added hover effect */
  cursor: text;
  /* Enhanced pointer event */
}

.form-input::placeholder,
.form-textarea::placeholder {
  color: #9ca3af;
  cursor: text;
  /* Added pointer event */
}

.form-select {
  appearance: none;
  cursor: pointer;
  /* Enhanced pointer event for selects */
  padding-right: 40px;
  pointer-events: auto;
  /* Added pointer event */
}

.form-select:hover {
  cursor: pointer;
  /* Enhanced pointer event */
}

.form-select option {
  cursor: pointer;
  /* Added pointer event for options */
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
  line-height: 1.5;
  cursor: text;
  /* Enhanced pointer event */
}

/* Input Icons */
.input-icon {
  position: absolute;
  right: 12px;
  color: #9ca3af;
  pointer-events: none;
  /* Important: icons should not block clicks */
  cursor: default;
  /* Added pointer event */
}

.select-arrow {
  position: absolute;
  right: 12px;
  color: #6b7280;
  pointer-events: none;
  /* Important: arrow should not block clicks */
  cursor: default;
  /* Added pointer event */
}

/* Error Message */
.error-message {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  color: #dc2626;
  font-size: 14px;
  cursor: default;
  /* Added pointer event */
  user-select: none;
  /* Added to prevent text selection */
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 8px;
  pointer-events: auto;
  /* Added pointer event */
}

/* Buttons */
.btn {
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  /* Enhanced pointer event */
  transition: all 0.2s ease;
  border: none;
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 44px;
  box-sizing: border-box;
  pointer-events: auto;
  /* Added pointer event */
  user-select: none;
  /* Added to prevent text selection */
}

.btn:hover {
  cursor: pointer;
  /* Enhanced pointer event */
}

.btn:active {
  cursor: pointer;
  /* Enhanced pointer event */
}

.btn-secondary {
  background-color: #f8fafc;
  color: #475569;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  /* Enhanced pointer event */
}

.btn-secondary:hover {
  background-color: #f1f5f9;
  border-color: #cbd5e1;
  cursor: pointer;
  /* Enhanced pointer event */
}

.btn-secondary:active {
  transform: translateY(1px);
  /* Added active state */
  cursor: pointer;
  /* Enhanced pointer event */
}

.btn-primary {
  background-color: #4aab4e;
  color: white;
  cursor: pointer;
  /* Enhanced pointer event */
}

.btn-primary:hover:not(:disabled) {
  background-color: #3d8b41;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(74, 171, 78, 0.3);
  cursor: pointer;
  /* Enhanced pointer event */
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
  /* Added active state */
  cursor: pointer;
  /* Enhanced pointer event */
}

.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
  /* Enhanced pointer event for disabled state */
  transform: none;
  box-shadow: none;
  pointer-events: auto;
  /* Allow pointer events to show cursor */
}

/* Loading Spinner */
.loading-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  pointer-events: none;
  /* Added pointer event */
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 16px;
  }

  .modal-header {
    padding: 20px 24px;
  }

  .modal-title {
    font-size: 18px;
  }

  .modal-body {
    padding: 24px;
  }

  .form-row {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .form-actions {
    flex-direction: column-reverse;
    gap: 12px;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .modal-header {
    padding: 16px 20px;
  }

  .modal-body {
    padding: 20px;
  }

  .meeting-form {
    gap: 20px;
  }
}

/* Additional pointer event enhancements */
.form-input:disabled,
.form-select:disabled,
.form-textarea:disabled {
  cursor: not-allowed;
  /* Added pointer event for disabled inputs */
  opacity: 0.6;
}

.form-input[readonly],
.form-textarea[readonly] {
  cursor: default;
  /* Added pointer event for readonly inputs */
}

/* Improve visual feedback for interactive elements */
.form-input:focus-visible,
.form-select:focus-visible,
.form-textarea:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.btn:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.close-btn:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.modal-overlay.v-enter-active,
.modal-overlay.v-leave-active {
  transition: none !important;
}

.modal-overlay.v-enter-from,
.modal-overlay.v-leave-to {
  opacity: 1;
}

@media (max-width: 768px) {
  .modal-content {
    min-width: auto;
    margin: 0 10px;
    max-height: 95vh;
  }
}
</style>