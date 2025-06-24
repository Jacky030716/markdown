import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080/api/v1",
});

export default {
  async getAllAdvisees(advisorId) {
    try {
      const response = await apiClient.get(`/advisors/${advisorId}/advisees`);
      return response.data;
    } catch (error) {
      console.error("Error fetching advisees:", error);
      throw error;
    }
  },

  /* getAllAdvisors() {
    return apiClient.get("/advisors");
  },
  getAdvisorById(id) {
    return apiClient.get(`/advisors/${id}`);
  },
  createAdvisor(advisorData) {
    return apiClient.post("/advisors", advisorData);
  },
  updateAdvisor(id, advisorData) {
    return apiClient.put(`/advisors/${id}`, advisorData);
  },
  deleteAdvisor(id) {
    return apiClient.delete(`/advisors/${id}`);
  }, */
};