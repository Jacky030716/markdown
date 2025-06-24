import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080/api/v1",
});

export default {
  async getLecturerProfile(lecturerId) {
    try {
      const response = await apiClient.get(`/lecturers/${lecturerId}/profile`);
      return response.data;
    } catch (error) {
      console.error("Error fetching lecturer profile:", error);
      throw error;
    }
  },
  async getTaughtCourses(lecturerId) {
    try {
      const response = await apiClient.get(`/lecturers/${lecturerId}/courses`);
      return response.data;
    } catch (error) {
      console.error("Error fetching courses:", error);
      throw error;
    }
  },
  // Get Course Detail
  async getCourseDetail(courseId) {
    try {
      const response = await apiClient.get(`/lecturers/course/${courseId}`);
      if (!response.data) {
        throw new Error("Course not found");
      }
      return response.data;
    } catch (error) {
      console.error("Error fetching course detail:", error);
      throw error;
    }
  },
  async getStudentMarks(lecturerId, courseId) {
    try {
      const response = await apiClient.get(
        `/lecturers/${lecturerId}/courses/${courseId}/students/marks`
      );
      return response.data;
    } catch (error) {
      console.error("Error fetching student marks:", error);
      throw error;
    }
  },
  async getStudentsAnalysis(lecturerId, courseId) {
    try {
      const response = await apiClient.get(
        `/lecturers/${lecturerId}/courses/${courseId}/students/analysis`
      );

      return response.data;
    } catch (error) {
      console.error("Error fetching students analysis:", error);
      throw error;
    }
  },
  async updateStudentMarks({
    lecturerId,
    courseId,
    studentId,
    mark,
    componentId,
  }) {
    try {
      const response = await apiClient.patch(
        `/lecturers/${lecturerId}/courses/${courseId}/students/${studentId}/marks/${componentId}`,
        { mark }
      );

      return response.data;
    } catch (error) {
      console.error("Error updating student marks:", error);
      throw error;
    }
  },
  /**
   * Fetches all remark requests relevant to a specific lecturer's courses.
   * @param {number} lecturerId The ID of the lecturer.
   * @returns {Promise<Array>} A promise that resolves with an array of remark request objects.
   */
  async getRemarkRequests(lecturerId) {
    try {
      const response = await apiClient.get(
        `/lecturers/${lecturerId}/remark-requests`
      );
      return response.data;
    } catch (error) {
      if (error.response && error.response.status === 404) {
        console.warn("No remark requests found for this lecturer.");
        return [];
      }
    }
  },
  /**
   * Updates a specific remark request's status and lecturer response.
   * @param {number} requestId The ID of the remark request.
   * @param {string} status The new status ('approved' or 'rejected').
   * @param {string} lecturerResponse The lecturer's response text.
   * @returns {Promise<Object>} A promise that resolves with a success message.
   */
  async updateRemarkRequest(requestId, status, lecturerResponse) {
    try {
      const response = await apiClient.patch(
        `/lecturers/remark-requests/${requestId}`,
        {
          status: status,
          lecturer_response: lecturerResponse,
        }
      );
      return response.data; // Assuming response.data is { message: ..., status: 'success' }
    } catch (error) {
      console.error(`Error updating remark request ${requestId}:`, error);
      throw error;
    }
  },
};
