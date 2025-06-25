import axios from "axios";

const api = axios.create({
  baseURL: "http://localhost:8080/api/v1",
  headers: {
    "Content-Type": "application/json",
  },
});

export default {
  /**
   * Fetches all users from the system with their associated role-specific data.
   * @returns {Promise<Array>} An array of user objects. Each user object should combine
   * data from `users` table and their respective role table (students, lecturers, advisors).
   */
  async getAllUsers() {
    try {
      const response = await api.get("/admin/users");
      return response.data;
    } catch (error) {
      console.error("Error fetching users:", error);
      throw new Error(error.response?.data?.message || "Failed to fetch users");
    }
  },
  /**
   * Adds a new user to the system.
   * @param {Object} userData - User data including email, password, role, and role-specific fields.
   * Example userData:
   * {
   * email: 'new.student@utm.my',
   * password: 'password123',
   * role: 'student',
   * name: 'New Student Name',
   * matric_no: 'A20015',
   * program: 'Software Engineering',
   * year_of_study: 1,
   * advisor_id: 1 // optional, if linking to an advisor
   * }
   * @returns {Promise<Object>} The newly created user object, including its generated IDs.
   */
  async addNewUser(userData) {
    try {
      const response = await api.post(`/admin/users`, userData);
      return response.data.data; // Assuming your API returns the created user data in 'data'
    } catch (error) {
      console.error(
        "Error adding new user:",
        error.response?.data || error.message
      );
      throw error;
    }
  },

  /**
   * Updates an existing user's details.
   * @param {number} userId - The ID of the user in the `users` table.
   * @param {Object} userData - User data to update. This should contain only the fields that can be updated.
   * @returns {Promise<Object>} The updated user object.
   */
  async updateUser(userId, userData) {
    try {
      const response = await api.patch(`/admin/users/${userId}`, userData);
      return response.data.data;
    } catch (error) {
      console.error(
        `Error updating user ${userId}:`,
        error.response?.data || error.message
      );
      throw error;
    }
  },

  /**
   * Deactivates a user account.
   * @param {number} userId - The ID of the user in the `users` table.
   * @returns {Promise<Object>} Confirmation of deletion.
   */
  async deleteUser(userId) {
    try {
      const response = await api.delete(`/admin/users/${userId}`);
      return response.data;
    } catch (error) {
      console.error(
        `Error deleting user ${userId}:`,
        error.response?.data || error.message
      );
      throw error;
    }
  },

  /**
   * Fetches all courses with their assigned lecturer information.
   * @returns {Promise<Array>} Array of course objects. Each course should include lecturer name/ID.
   * Expected data structure for each course:
   * { id: 1, course_code: 'CS101', course_name: 'Software Quality Assurance', lecturer_id: 1, lecturer_name: 'Dr. Norizam', ... }
   */
  async getAllCoursesWithLecturer() {
    try {
      const response = await api.get(`/admin/courses`);
      return response.data.data; // Assuming API returns { data: [...], status: 'success' }
    } catch (error) {
      console.error("Error fetching all courses with lecturer info:", error);
      throw error;
    }
  },

  /**
   * Updates the lecturer for a specific course.
   * @param {number} courseId - The ID of the course.
   * @param {number|null} lecturerId - The ID of the new lecturer, or null to unassign.
   * @returns {Promise<Object>} Updated course information.
   */
  async updateCourseLecturer(courseId, lecturerId) {
    try {
      const response = await api.patch(
        `/admin/courses/${courseId}/assign-lecturer`,
        {
          lecturer_id: lecturerId,
        }
      );
      return response.data; // Assuming API returns success message/updated course data
    } catch (error) {
      console.error(
        `Error updating lecturer for course ${courseId}:`,
        error.response?.data || error.message
      );
      throw error;
    }
  },

  /**
   * Fetches all students currently enrolled in a specific course.
   * @param {number} courseId - The ID of the course.
   * @returns {Promise<Array>} Array of enrolled student objects (id, name, matric_no).
   */
  async getCourseEnrollments(courseId) {
    try {
      const response = await api.get(`/admin/courses/${courseId}/enrollments`);
      return response.data.data; // Assuming API returns { data: [...], status: 'success' }
    } catch (error) {
      console.error(
        `Error fetching enrollments for course ${courseId}:`,
        error
      );
      throw error;
    }
  },

  /**
   * Enrolls multiple students into a course.
   * @param {number} courseId - The ID of the course.
   * @param {Array<number>} studentIds - An array of student IDs to enroll.
   * @returns {Promise<Object>} Confirmation message.
   */
  async enrollStudents(courseId, studentIds) {
    try {
      const response = await api.post(
        `/admin/courses/${courseId}/enroll-students`,
        {
          student_ids: studentIds,
        }
      );
      return response.data;
    } catch (error) {
      console.error(
        `Error enrolling students in course ${courseId}:`,
        error.response?.data || error.message
      );
      throw error;
    }
  },

  /**
   * Unenrolls multiple students from a course.
   * @param {number} courseId - The ID of the course.
   * @param {Array<number>} studentIds - An array of student IDs to unenroll.
   * @returns {Promise<Object>} Confirmation message.
   */
  async unenrollStudents(courseId, studentIds) {
    try {
      // Using a PATCH request to update enrollment status to 'dropped'
      const response = await api.patch(
        `/admin/courses/${courseId}/unenroll-students`,
        {
          student_ids: studentIds,
        }
      );
      return response.data;
    } catch (error) {
      console.error(
        `Error unenroll students from course ${courseId}:`,
        error.response?.data || error.message
      );
      throw error;
    }
  },

  /**
   * Adds a new course to the system.
   * @param {Object} courseData - Course data for creation.
   * @returns {Promise<Object>} The newly created course object.
   */
  async addNewCourse(courseData) {
    try {
      const response = await api.post(`/admin/courses/create`, courseData);
      return response.data.data; // Assuming your API returns the created course data in 'data'
    } catch (error) {
      console.error(
        "Error adding new course:",
        error.response?.data || error.message
      );
      throw error;
    }
  },

  /**
   * Updates an existing course's details.
   * @param {number} courseId - The ID of the course.
   * @param {Object} courseData - Course data to update.
   * @returns {Promise<Object>} The updated course object.
   */
  async updateCourse(courseId, courseData) {
    try {
      const response = await api.patch(
        `/admin/courses/${courseId}`,
        courseData
      );
      return response.data.data;
    } catch (error) {
      console.error(
        `Error updating course ${courseId}:`,
        error.response?.data || error.message
      );
      throw error;
    }
  },

  /**
   * Toggles the active status of a course.
   * @param {number} courseId - The ID of the course.
   * @param {boolean} isActive - The new active status.
   * @returns {Promise<Object>} Confirmation message.
   */
  async toggleCourseActiveStatus(courseId, isActive) {
    try {
      const response = await api.patch(
        `/admin/courses/${courseId}/toggle-active`,
        {
          is_active: isActive ? 1 : 0,
        }
      );
      return response.data;
    } catch (error) {
      console.error(
        `Error toggling course status ${courseId}:`,
        error.response?.data || error.message
      );
      throw error;
    }
  },
};
