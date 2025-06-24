import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080/api/v1",
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  }
});

// Add request interceptor for logging
apiClient.interceptors.request.use(
  (config) => {
    console.log('API Request:', config.method?.toUpperCase(), config.url);
    return config;
  },
  (error) => {
    console.error('API Request Error:', error);
    return Promise.reject(error);
  }
);

// Add response interceptor for logging and error handling
apiClient.interceptors.response.use(
  (response) => {
    console.log('API Response:', response.status, response.config.url);
    return response;
  },
  (error) => {
    console.error('API Response Error:', error.response?.status, error.response?.data || error.message);
    return Promise.reject(error);
  }
);

export default {
  async getAllCourses(studentId) {
    try {
      // The API endpoint doesn't use studentId in the URL, it's hardcoded in PHP
      const response = await apiClient.get('/students/courses');
      
      // Return the response data directly - let the component handle the structure
      return response.data;
    } catch (error) {
      console.error("Error fetching courses:", error);
      throw error;
    }
  },

  async getMarks(studentId, courseId) {
    try {
      const response = await apiClient.get(
        `/students/${studentId}/courses/${courseId}/marks`
      );
      
      // Return the response data directly - let the component handle the structure
      return response.data;
    } catch (error) {
      console.error("Error fetching marks:", error);
      throw error;
    }
  },

  async getCourseDetails(studentId, courseId) {
    try {
      const response = await apiClient.get(
        `/students/${studentId}/courses/${courseId}/details`
      );
      
      return response.data;
    } catch (error) {
      console.error("Error fetching course details:", error);
      throw error;
    }
  }
};