// api/advisors.js
// Example API file structure for advisors endpoints

import axios from 'axios'

// Create an Axios instance for API requests
// This instance will handle base URL and headers for all requests
const api = axios.create({
  baseURL: 'http://localhost:8080/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
})

// Add request interceptor for authentication
api.interceptors.request.use(
  (config) => {
    // Add auth token if available
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Add response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle common errors
    if (error.response?.status === 401) {
      // Handle unauthorized - maybe redirect to login
      console.error('Unauthorized access')
    }
    return Promise.reject(error)
  }
)

const advisorsApi = {
  /**
   * Get all advisees for a specific advisor
   * @param {number} advisorId - The ID of the advisor
   * @returns {Promise} - Promise containing advisees data
   */
  async getAllAdvisees(advisorId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/advisees`)
      return response.data
    } catch (error) {
      console.error('Error fetching advisees:', error)
      throw new Error(error.response?.data?.message || 'Failed to fetch advisees')
    }
  },

  /**
   * Get specific advisee details
   * @param {number} advisorId - The ID of the advisor
   * @param {number} adviseeId - The ID of the advisee
   * @returns {Promise} - Promise containing advisee details
   */
  async getAdviseeDetails(advisorId, adviseeId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/advisees/${adviseeId}`)
      return response.data
    } catch (error) {
      console.error('Error fetching advisee details:', error)
      throw new Error(error.response?.data?.message || 'Failed to fetch advisee details')
    }
  },

  /**
   * Update advisee information
   * @param {number} advisorId - The ID of the advisor
   * @param {number} adviseeId - The ID of the advisee
   * @param {Object} data - Updated advisee data
   * @returns {Promise} - Promise containing updated advisee data
   */
  async updateAdvisee(advisorId, adviseeId, data) {
    try {
      const response = await api.put(`/advisors/${advisorId}/advisees/${adviseeId}`, data)
      return response.data
    } catch (error) {
      console.error('Error updating advisee:', error)
      throw new Error(error.response?.data?.message || 'Failed to update advisee')
    }
  },

  /**
   * Generate academic report for advisee
   * @param {number} advisorId - The ID of the advisor
   * @param {number} adviseeId - The ID of the advisee
   * @returns {Promise} - Promise containing report data
   */
  async generateAdviseeReport(advisorId, adviseeId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/advisees/${adviseeId}/report`)
      return response.data
    } catch (error) {
      console.error('Error generating report:', error)
      throw new Error(error.response?.data?.message || 'Failed to generate report')
    }
  },

  /**
   * Get advisor dashboard statistics
   * @param {number} advisorId - The ID of the advisor
   * @returns {Promise} - Promise containing dashboard stats
   */
  async getDashboardStats(advisorId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/dashboard-stats`)
      return response.data
    } catch (error) {
      console.error('Error fetching dashboard stats:', error)
      throw new Error(error.response?.data?.message || 'Failed to fetch dashboard statistics')
    }
  },

  /**
   * Search advisees by criteria
   * @param {number} advisorId - The ID of the advisor
   * @param {Object} criteria - Search criteria (name, matric_no, gpa_range, etc.)
   * @returns {Promise} - Promise containing filtered advisees
   */
  async searchAdvisees(advisorId, criteria) {
    try {
      const response = await api.get(`/advisors/${advisorId}/advisees/search`, {
        params: criteria
      })
      return response.data
    } catch (error) {
      console.error('Error searching advisees:', error)
      throw new Error(error.response?.data?.message || 'Failed to search advisees')
    }
  },

  
  /**
   * Get all meeting notes for a specific advisor
   * @param {number} advisorId - The ID of the advisor
   * @returns {Promise} - Promise containing meeting notes data with student info
   */
  async getMeetingNotes(advisorId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/meeting-notes`)
      return response.data
    } catch (error) {
      console.error('Error fetching meeting notes:', error)
      throw new Error(error.response?.data?.message || 'Failed to fetch meeting notes')
    }
  },

  /**
   * Get advisees list for dropdown selection
   * @param {number} advisorId - The ID of the advisor
   * @returns {Promise} - Promise containing simplified advisees data for dropdown
   */
  async getAdviseesForDropdown(advisorId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/advisees-dropdown`)
      return response.data
    } catch (error) {
      console.error('Error fetching advisees for dropdown:', error)
      throw new Error(error.response?.data?.message || 'Failed to fetch advisees list')
    }
  },

  /**
   * Create a new meeting note
   * @param {number} advisorId - The ID of the advisor
   * @param {Object} meetingData - Meeting note data object
   * @returns {Promise} - Promise containing created meeting note
   */
  async createMeetingNote(advisorId, meetingData) {
    try {
      const response = await api.post(`/advisors/${advisorId}/meeting-notes`, meetingData)
      return response.data
    } catch (error) {
      console.error('Error creating meeting note:', error)
      throw new Error(error.response?.data?.message || 'Failed to create meeting note')
    }
  },

  /**
   * Update an existing meeting note
   * @param {number} advisorId - The ID of the advisor
   * @param {number} meetingNoteId - The ID of the meeting note
   * @param {Object} meetingData - Updated meeting note data
   * @returns {Promise} - Promise containing updated meeting note
   */
  async updateMeetingNote(advisorId, meetingNoteId, meetingData) {
    try {
      const response = await api.put(`/advisors/${advisorId}/meeting-notes/${meetingNoteId}`, meetingData)
      return response.data
    } catch (error) {
      console.error('Error updating meeting note:', error)
      throw new Error(error.response?.data?.message || 'Failed to update meeting note')
    }
  },

  /**
   * Delete a meeting note
   * @param {number} advisorId - The ID of the advisor
   * @param {number} meetingNoteId - The ID of the meeting note
   * @returns {Promise} - Promise containing deletion confirmation
   */
  async deleteMeetingNote(advisorId, meetingNoteId) {
    try {
      const response = await api.delete(`/advisors/${advisorId}/meeting-notes/${meetingNoteId}`)
      return response.data
    } catch (error) {
      console.error('Error deleting meeting note:', error)
      throw new Error(error.response?.data?.message || 'Failed to delete meeting note')
    }
  },

  /**
   * Generate consultation report for a specific student
   * @param {number} advisorId - The ID of the advisor
   * @param {number} studentId - The ID of the student
   * @returns {Promise} - Promise containing report data
   */
  async generateConsultationReport(advisorId, studentId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/students/${studentId}/consultation-report`)
      return response.data
    } catch (error) {
      console.error('Error generating consultation report:', error)
      throw new Error(error.response?.data?.message || 'Failed to generate consultation report')
    }
  }
}

export default advisorsApi