import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080/api/v1",
});

export default {
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
      console.log("Fetched students analysis:", response.data);

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
};
