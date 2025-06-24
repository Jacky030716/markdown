import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080/api/v1",
});

export default {
  async login(email, password) {
    try {
      const response = await apiClient.post("/users/login", {
        email,
        password,
      });

      if (response.data.status === "success") {
        return response.data;
      }
    } catch (error) {
      console.error("Error during login:", error);
      if (error.response && error.response.status === 401) {
        throw new Error("Invalid email or password");
      } else {
        throw new Error("An unexpected error occurred during login");
      }
    }
  },
};
