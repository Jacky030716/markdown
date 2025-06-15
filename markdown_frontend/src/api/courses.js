import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080/api/v1",
});

export default {
  async addNewMarkComponent(courseData) {
    try {
      if (!courseData.course_id) {
        throw new Error("Course ID is required");
      }

      const response = await apiClient.post(
        `/courses/${courseData.course_id}/marks`,
        courseData
      );
      return response.data;
    } catch (error) {
      console.error("Error fetching courses:", error);
      throw error;
    }
  },
  async updateMarkComponent(courseData) {
    try {
      if (!courseData.course_id || !courseData.component_id) {
        throw new Error("Course ID and Component ID are required");
      }
      const response = await apiClient.put(
        `/courses/${courseData.course_id}/marks/${courseData.component_id}`,
        courseData
      );

      return response.data;
    } catch (error) {
      console.error("Error updating mark component:", error);
      throw error;
    }
  },
  async deleteMarkComponent({ course_id, component_id }) {
    try {
      if (!course_id || !component_id) {
        throw new Error("Course ID and Component ID are required");
      }
      const response = await apiClient.delete(
        `/courses/${course_id}/marks/${component_id}`
      );
      return response.data;
    } catch (error) {
      console.error("Error deleting mark component:", error);
      throw error;
    }
  },
};
