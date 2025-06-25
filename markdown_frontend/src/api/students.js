import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080/api/v1",
  timeout: 10000,
  headers: {
    "Content-Type": "application/json",
  },
});

// Add request interceptor for logging
apiClient.interceptors.request.use(
  (config) => {
    console.log("API Request:", config.method?.toUpperCase(), config.url);
    return config;
  },
  (error) => {
    console.error("API Request Error:", error);
    return Promise.reject(error);
  }
);

// Add response interceptor for logging and error handling
apiClient.interceptors.response.use(
  (response) => {
    console.log("API Response:", response.status, response.config.url);
    return response;
  },
  (error) => {
    console.error(
      "API Response Error:",
      error.response?.status,
      error.response?.data || error.message
    );
    return Promise.reject(error);
  }
);

export default {
  // General Use
  async getStudentProfile(studentId) {
    try {
      const response = await apiClient.get(`/students/${studentId}/profile`);
      return response.data;
    } catch (error) {
      console.error("Error fetching student profile:", error);
      throw error;
    }
  },


  /**
   * Fetch all courses for a student (currently hardcoded to student ID 4)
   * @param {number} studentId - The student ID (not used in current implementation)
   * @returns {Promise<Object>} Response object with courses data
   */
  async getAllCourses(studentId) {
    try {
      // The API endpoint doesn't use studentId in the URL, it's hardcoded in PHP
      const response = await apiClient.get("/students/courses");

      // Return the response data directly - let the component handle the structure
      return response.data;
    } catch (error) {
      console.error("Error fetching courses:", error);

      // Provide more detailed error information
      if (error.response) {
        // Server responded with error status
        throw {
          message: error.response.data?.message || "Failed to fetch courses",
          status: error.response.status,
          data: error.response.data,
        };
      } else if (error.request) {
        // Request was made but no response received
        throw {
          message: "No response from server. Please check your connection.",
          status: "network_error",
        };
      } else {
        // Something else happened
        throw {
          message: error.message || "An unexpected error occurred",
          status: "unknown_error",
        };
      }
    }
  },

  // 1. For Dashboard Page //
  /**
   * Fetch detailed information for a student
   * @param {number} studentId - The student ID
   * @returns {Promise<Object>} Response object with student details
   */

  async getStudentDetails(studentId) {
    try {
      if (!studentId) {
        throw {
          message: "Student ID is required",
          status: "validation_error",
        };
      }

      // Perform the GET request to the backend API
      const response = await apiClient.get(`/students/${studentId}/info`);

      return response.data;
    } catch (error) {
      console.error("Error fetching student details:", error);

      // Handle the different types of errors based on the response
      if (error.response) {
        throw {
          message:
            error.response.data?.message || "Failed to fetch student details",
          status: error.response.status,
          data: error.response.data,
        };
      } else if (error.request) {
        throw {
          message: "No response from server. Please check your connection.",
          status: "network_error",
        };
      } else {
        throw {
          message: error.message || "An unexpected error occurred",
          status: "unknown_error",
        };
      }
    }
  },

  /**
   * Fetch overall progress summary for a student
   * @param {number} studentId - The student ID
   * @returns {Promise<Object>} Response object with progress summary
   */
  async getProgressSummary(studentId) {
    try {
      if (!studentId) {
        throw {
          message: "Student ID is required",
          status: "validation_error",
        };
      }

      const response = await apiClient.get(
        `/students/${studentId}/progress-summary`
      );

      return response.data;
    } catch (error) {
      console.error("Error fetching progress summary:", error);

      if (error.response) {
        throw {
          message:
            error.response.data?.message || "Failed to fetch progress summary",
          status: error.response.status,
          data: error.response.data,
        };
      } else if (error.request) {
        throw {
          message: "No response from server. Please check your connection.",
          status: "network_error",
        };
      } else {
        throw {
          message: error.message || "An unexpected error occurred",
          status: "unknown_error",
        };
      }
    }
  },

  // 2. For Course Mark Page //
  /** a)
   * Fetch marks for a specific student and course
   * @param {number} studentId - The student ID
   * @param {number} courseId - The course ID
   * @returns {Promise<Object>} Response object with marks data
   */
  async getMarks(studentId, courseId) {
    try {
      if (!studentId || !courseId) {
        throw {
          message: "Student ID and Course ID are required",
          status: "validation_error",
        };
      }

      const response = await apiClient.get(
        `/students/${studentId}/courses/${courseId}/marks`
      );

      // Return the response data directly - let the component handle the structure
      return response.data;
    } catch (error) {
      console.error("Error fetching marks:", error);

      if (error.response) {
        throw {
          message: error.response.data?.message || "Failed to fetch marks",
          status: error.response.status,
          data: error.response.data,
        };
      } else if (error.request) {
        throw {
          message: "No response from server. Please check your connection.",
          status: "network_error",
        };
      } else {
        throw {
          message: error.message || "An unexpected error occurred",
          status: "unknown_error",
        };
      }
    }
  },

  /** b)
   * Fetch detailed information about a course for a specific student
   * @param {number} studentId - The student ID
   * @param {number} courseId - The course ID
   * @returns {Promise<Object>} Response object with course details
   */
  async getCourseDetails(studentId, courseId) {
    try {
      if (!studentId || !courseId) {
        throw {
          message: "Student ID and Course ID are required",
          status: "validation_error",
        };
      }

      const response = await apiClient.get(
        `/students/${studentId}/courses/${courseId}/details`
      );

      return response.data;
    } catch (error) {
      console.error("Error fetching course details:", error);

      if (error.response) {
        throw {
          message:
            error.response.data?.message || "Failed to fetch course details",
          status: error.response.status,
          data: error.response.data,
        };
      } else if (error.request) {
        throw {
          message: "No response from server. Please check your connection.",
          status: "network_error",
        };
      } else {
        throw {
          message: error.message || "An unexpected error occurred",
          status: "unknown_error",
        };
      }
    }
  },

  /** c)
   * Fetch performance analytics for a student in a specific course
   * @param {number} studentId - The student ID
   * @param {number} courseId - The course ID
   * @returns {Promise<Object>} Response object with analytics data
   */
  async getAnalytics(studentId, courseId) {
    try {
      if (!studentId || !courseId) {
        throw {
          message: "Student ID and Course ID are required",
          status: "validation_error",
        };
      }

      const response = await apiClient.get(
        `/students/${studentId}/courses/${courseId}/analytics`
      );

      return response.data;
    } catch (error) {
      console.error("Error fetching analytics:", error);

      if (error.response) {
        throw {
          message: error.response.data?.message || "Failed to fetch analytics",
          status: error.response.status,
          data: error.response.data,
        };
      } else if (error.request) {
        throw {
          message: "No response from server. Please check your connection.",
          status: "network_error",
        };
      } else {
        throw {
          message: error.message || "An unexpected error occurred",
          status: "unknown_error",
        };
      }
    }
  },

  /** d)
 * Fetch class ranking data for a student in a specific course
 * @param {number} studentId - The student ID
 * @param {number} courseId - The course ID
 * @returns {Promise<Object>} Response object with ranking data
 */
async getRanking(studentId, courseId) {
  try {
    if (!studentId || !courseId) {
      throw {
        message: "Student ID and Course ID are required",
        status: "validation_error",
      };
    }

    const response = await apiClient.get(
      `/students/${studentId}/courses/${courseId}/ranking`
    );

    return response.data;
  } catch (error) {
    console.error("Error fetching ranking:", error);

    if (error.response) {
      throw {
        message: error.response.data?.message || "Failed to fetch ranking",
        status: error.response.status,
        data: error.response.data,
      };
    } else if (error.request) {
      throw {
        message: "No response from server. Please check your connection.",
        status: "network_error",
      };
    } else {
      throw {
        message: error.message || "An unexpected error occurred",
        status: "unknown_error",
      };
    }
  }
},




  // 3. For Remark Request Page //
  //Fetch all the remark request of a student
  async getRemarkRequests(studentId) {
    try {
      const response = await apiClient.get(
        `/students/${studentId}/remark-requests`
      );
      return response.data;
    } catch (error) {
      console.error("Error fetching remark requests:", error);
      throw error;
    }
  },

  //Submit remark request by a student
  async submitRemarkRequest(studentId, courseId, remarkData) {
    try {
      const response = await apiClient.post(
        `/students/${studentId}/courses/${courseId}/remark-requests`,
        remarkData
      );
      return response.data;
    } catch (error) {
      console.error("Error submitting remark request:", error);
      throw error;
    }
  },

  //Get all course of the students and respective components
  async getCoursesWithComponents(studentId) {
    try {
      // Call the new endpoint that includes components
      const response = await apiClient.get(
        `/students/${studentId}/courses-with-components`
      );

      // Handle the API response structure
      if (response.data && response.data.status === "success") {
        return {
          data: response.data.data, // Extract the actual courses array with components
        };
      } else {
        throw new Error(
          response.data?.message || "Failed to fetch courses with components"
        );
      }
    } catch (error) {
      console.error("Error fetching courses with components:", error);

      // Provide more detailed error information
      if (error.response) {
        // Server responded with error status
        throw {
          message:
            error.response.data?.message ||
            "Failed to fetch courses with components",
          status: error.response.status,
          data: error.response.data,
        };
      } else if (error.request) {
        // Request was made but no response received
        throw {
          message: "No response from server. Please check your connection.",
          status: "network_error",
        };
      } else {
        // Something else happened
        throw {
          message: error.message || "An unexpected error occurred",
          status: "unknown_error",
        };
      }
    }
  },
};
