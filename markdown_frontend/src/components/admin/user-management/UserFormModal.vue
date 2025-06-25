<template>
  <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full mx-4 transform transition-all duration-300 ease-out">
      <!-- Header -->
      <div class="relative px-6 pt-6 pb-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-gray-900">
              {{ isEditMode ? 'Edit User' : 'Add New User' }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">
              {{ isEditMode
                ? 'Update user details and role information'
                : "Create a new user account with a specific role" }}
            </p>
          </div>
          <button @click="$emit('close')"
            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Form Content -->
      <form @submit.prevent="handleSubmit" class="px-6 py-6 space-y-6">
        <!-- Common User Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-2">
            <label for="name" class="block text-sm font-semibold text-gray-700">Name <span
                class="text-red-500">*</span></label>
            <input type="text" id="name" v-model="form.name" required
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              placeholder="Full Name">
          </div>
          <div class="space-y-2">
            <label for="email" class="block text-sm font-semibold text-gray-700">Email <span
                class="text-red-500">*</span></label>
            <input type="email" id="email" v-model="form.email" required :readonly="isEditMode"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              :class="{ 'bg-gray-50 cursor-not-allowed': isEditMode }" placeholder="user@university.edu">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-2">
            <label for="password" class="block text-sm font-semibold text-gray-700">Password <span v-if="!isEditMode"
                class="text-red-500">*</span></label>
            <input type="password" id="password" v-model="form.password" :required="!isEditMode"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              placeholder="Leave blank to keep current password if editing">
          </div>
          <div class="space-y-2">
            <label for="role" class="block text-sm font-semibold text-gray-700">Role <span
                class="text-red-500">*</span></label>
            <div class="relative">
              <select id="role" v-model="form.role" @change="handleRoleChange" required :disabled="isEditMode"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 text-gray-900 bg-white appearance-none cursor-pointer"
                :class="{ 'bg-gray-50 cursor-not-allowed': isEditMode }">
                <option value="" disabled>Select a role</option>
                <option value="student">Student</option>
                <option value="lecturer">Lecturer</option>
                <option value="advisor">Advisor</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Role-Specific Fields -->
        <div v-if="form.role === 'student'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t pt-6 border-gray-100">
          <div class="space-y-2">
            <label for="matric_no" class="block text-sm font-semibold text-gray-700">Matric No. <span
                class="text-red-500">*</span></label>
            <input type="text" id="matric_no" v-model="form.matric_no" required
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              placeholder="A12345">
          </div>
          <div class="space-y-2">
            <label for="program" class="block text-sm font-semibold text-gray-700">Program</label>
            <input type="text" id="program" v-model="form.program"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              placeholder="e.g., Computer Science">
          </div>
          <div class="space-y-2">
            <label for="year_of_study" class="block text-sm font-semibold text-gray-700">Year of Study</label>
            <input type="number" id="year_of_study" v-model.number="form.year_of_study" min="1" max="5"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              placeholder="1">
          </div>
          <div class="space-y-2">
            <label for="advisor_id" class="block text-sm font-semibold text-gray-700">Advisor</label>
            <div class="relative">
              <select id="advisor_id" v-model="form.advisor_id"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 text-gray-900 bg-white appearance-none cursor-pointer">
                <option :value="null">No Advisor</option>
                <option v-for="adv in advisors" :key="adv.advisor_id" :value="adv.advisor_id">
                  {{ adv.name }} ({{ adv.advisor_matric_no }})
                </option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="form.role === 'lecturer' || form.role === 'advisor'"
          class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t pt-6 border-gray-100">
          <div class="space-y-2">
            <label :for="`${form.role}_id`" class="block text-sm font-semibold text-gray-700">
              {{ form.role === 'lecturer' ? 'Lecturer ID' : 'Advisor ID' }} <span class="text-red-500">*</span>
            </label>
            <input type="text" :id="`${form.role}_id`" v-model="form[form.role + '_id']" required
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              :placeholder="form.role === 'lecturer' ? 'L001' : 'A001'">
          </div>
          <div class="space-y-2">
            <label for="department" class="block text-sm font-semibold text-gray-700">Department</label>
            <input type="text" id="department" v-model="form.department"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-sky-500 focus:ring-4 focus:ring-sky-100 placeholder-gray-400 text-gray-900"
              placeholder="e.g., Computer Science">
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="flex items-center space-x-2 p-4 bg-red-50 border border-red-200 rounded-xl">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-red-700 text-sm font-medium">{{ errorMessage }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
          <button type="button" @click="$emit('close')"
            class="w-full sm:w-auto px-6 py-3 text-gray-700 font-semibold border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:ring-4 focus:ring-gray-100 cursor-pointer">
            Cancel
          </button>
          <button type="submit" :disabled="isSaving"
            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-sky-500 to-sky-600 text-white font-semibold rounded-xl hover:from-sky-600 hover:to-sky-700 focus:ring-4 focus:ring-sky-200 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2 cursor-pointer"
            :class="{ 'opacity-50 cursor-not-allowed': isSaving }">
            <EditIcon v-if="isSaving" class="w-5 h-5 animate-spin text-white" />
            <UserPlus v-else-if="!isEditMode" class="w-5 h-5" />
            <Edit v v-else class="w-5 h-5" />
            <span>{{ isSaving ? 'Saving...' : (isEditMode ? 'Update User' : 'Add User') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { UserPlus, Edit, EditIcon } from 'lucide-vue-next';
import adminApi from '../../../api/admin';

const props = defineProps({
  user: {
    type: Object,
    default: null // Null when adding a new user
  }
});

const emit = defineEmits(['close', 'save']);

const form = ref({
  name: '',
  email: '',
  password: '',
  role: '',
  // Role-specific fields (will be conditionally populated/used)
  matric_no: '',
  program: '',
  year_of_study: null,
  advisor_id: null, // For students
  lecturer_id: '',  // For lecturers
  department: '',   // For lecturers/advisors
  advisor_id_field: '' // For advisors, avoid conflict with student's advisor_id
});

const isEditMode = ref(false);
const errorMessage = ref('');
const isSaving = ref(false);
const advisors = ref([]); // To store list of advisors for student form

const fetchAdvisors = async () => {
  try {
    const usersResponse = await adminApi.getAllUsers();

    if (usersResponse.status !== "success" || !Array.isArray(usersResponse.data)) {
      throw new Error("Failed to fetch users");
    }

    const allUsers = usersResponse.data;
    advisors.value = allUsers.filter(u => u.role === 'advisor');
  } catch (error) {
    console.error("Error fetching advisors:", error);
    // Handle error, e.g., show a toast
  }
};

onMounted(() => {
  fetchAdvisors();
});

watch(() => props.user, (newUser) => {
  if (newUser) {
    console.log("Editing user:", newUser);
    isEditMode.value = true;
    // Populate form with existing user data
    form.value = {
      id: newUser.id,
      name: newUser.name || '',
      email: newUser.email || '',
      password: '', // Password should never be pre-filled for security
      role: newUser.role || '',
      // Populate role-specific fields
      matric_no: newUser.matric_no || '',
      program: newUser.program || '',
      year_of_study: newUser.year_of_study || null,
      advisor_id: newUser.advisor_id || null, // For students
      lecturer_id: newUser.lecturer_id || '', // For lecturers
      department: newUser.department || '', // For lecturers/advisors
      advisor_id_field: newUser.advisor_id || '', // Populate for advisor_id if it's an advisor being edited
    };
  } else {
    isEditMode.value = false;
    // Reset form for new user
    form.value = {
      name: '',
      email: '',
      password: '',
      role: '',
      matric_no: '',
      program: '',
      year_of_study: null,
      advisor_id: null,
      lecturer_id: '',
      department: '',
      advisor_id_field: ''
    };
  }
  errorMessage.value = ''; // Clear errors on modal open/user change
}, { immediate: true });

const handleRoleChange = () => {
  // Clear role-specific fields when role changes for a new user
  if (!isEditMode.value) {
    form.value.matric_no = '';
    form.value.program = '';
    form.value.year_of_study = null;
    form.value.advisor_id = null;
    form.value.lecturer_id = '';
    form.value.department = '';
    form.value.advisor_id_field = '';
  }
};

const validateForm = () => {
  errorMessage.value = '';
  if (!form.value.name || !form.value.email || !form.value.role) {
    errorMessage.value = 'Name, Email, and Role are required fields.';
    return false;
  }
  if (!isEditMode.value && !form.value.password) {
    errorMessage.value = 'Password is required for new users.';
    return false;
  }

  // Basic email format validation
  if (!/\S+@\S+\.\S+/.test(form.value.email)) {
    errorMessage.value = 'Please enter a valid email address.';
    return false;
  }

  // Role-specific validations
  if (form.value.role === 'student') {
    if (!form.value.matric_no) {
      errorMessage.value = 'Matric No. is required for students.';
      return false;
    }
  } else if (form.value.role === 'lecturer') {
    if (!form.value.lecturer_id) {
      errorMessage.value = 'Lecturer ID is required for lecturers.';
      return false;
    }
  } else if (form.value.role === 'advisor') {
    if (!form.value.advisor_id_field) { // Use advisor_id_field for input
      errorMessage.value = 'Advisor ID is required for advisors.';
      return false;
    }
  }
  return true;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  isSaving.value = true;
  try {
    // Construct payload based on role
    const payload = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
      password: form.value.password, // Only send if updating or new user
    };

    if (form.value.role === 'student') {
      payload.matric_no = form.value.matric_no;
      payload.program = form.value.program;
      payload.year_of_study = form.value.year_of_study;
      payload.advisor_id = form.value.advisor_id;
    } else if (form.value.role === 'lecturer') {
      payload.lecturer_id = form.value.lecturer_id;
      payload.department = form.value.department;
    } else if (form.value.role === 'advisor') {
      payload.advisor_id = form.value.advisor_id_field; // Use the value from the form field
      payload.department = form.value.department;
    }

    // Remove password if empty and in edit mode
    if (isEditMode.value && !payload.password) {
      delete payload.password;
    }

    emit('save', payload); // Emit to parent for API call
  } catch (err) {
    // Error handling from `save` event should be in parent
    console.error("Form submission error:", err);
    errorMessage.value = "An unexpected error occurred during form submission.";
  } finally {
    isSaving.value = false;
  }
};
</script>