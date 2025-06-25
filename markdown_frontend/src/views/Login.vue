<template>
  <div
    class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-xl">
      <!-- Logo/Header Section -->
      <div class="text-center mb-8">
        <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-2">MarkDown Management System</h1>
        <p class="text-gray-600 text-sm sm:text-base">Sign In to access your course marks easily</p>
      </div>

      <!-- Login Form Card -->
      <div
        class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8 transform transition-all duration-300"
        :class="{ 'scale-105': isFormFocused }">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email Field -->
          <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email Address
            </label>
            <div class="relative">
              <input id="email" v-model="email" type="email" required autocomplete="email" @focus="handleInputFocus"
                @blur="handleInputBlur" @input="clearError"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500': emailError,
                  'border-emerald-300 focus:ring-emerald-500 focus:border-emerald-500': email && !emailError && isEmailValid
                }" placeholder="Enter your email address" />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <CheckIcon v-if="email && !emailError && isEmailValid"
                  class="h-5 w-5 text-emerald-500 animate-fade-in" />
                <XIcon v-else-if="emailError" class="h-5 w-5 text-rose-500 animate-shake" />
              </div>
            </div>
            <transition name="slide-down">
              <p v-if="emailError" class="text-red-600 text-sm animate-shake">
                {{ emailError }}
              </p>
            </transition>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-gray-700">
              Password
            </label>
            <div class="relative">
              <input id="password" v-model="password" :type="showPassword ? 'text' : 'password'" required
                autocomplete="current-password" @focus="handleInputFocus" @blur="handleInputBlur" @input="clearError"
                class="w-full px-4 py-3 border outline-none border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white pr-10"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500': passwordError,
                  'border-emerald-300 focus:ring-emerald-500 focus:border-emerald-500': password && !passwordError && password.length >= 6
                }" placeholder="Enter your password" />
              <button type="button" @click="togglePasswordVisibility"
                class="absolute inset-y-0 right-0 pr-3 flex items-center hover:text-blue-600 transition-colors duration-200">
                <EyeIcon v-if="showPassword" class="h-5 w-5 text-gray-400" />
                <EyeClosedIcon v-else class="h-5 w-5 text-gray-400" />
              </button>
            </div>
            <transition name="slide-down">
              <p v-if="passwordError" class="text-red-600 text-sm animate-shake">
                {{ passwordError }}
              </p>
            </transition>
          </div>

          <!-- Error Message -->
          <transition name="slide-down">
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-3 animate-shake">
              <div class="flex items-center">
                <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-700 text-sm">{{ errorMessage }}</p>
              </div>
            </div>
          </transition>

          <!-- Submit Button -->
          <button type="submit" :disabled="isLoading || !isFormValid"
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform"
            :class="{
              'opacity-50 cursor-not-allowed': isLoading || !isFormValid,
              'hover:scale-105 active:scale-95': !isLoading && isFormValid
            }">
            <transition name="fade" mode="out-in">
              <div v-if="isLoading" class="flex items-center" key="loading">
                <Loader2Icon class="animate-spin h-5 w-5 text-white mr-2" />
                Signing In...
              </div>
              <span v-else key="signin">Sign In</span>
            </transition>
          </button>
        </form>

        <!-- Demo Credentials -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <h3 class="text-sm font-medium text-gray-700 mb-3">Demo Credentials:</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button v-for="credential in demoCredentials" :key="credential.role" @click="fillCredentials(credential)"
              class="p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-all duration-200 hover:shadow-md">
              <div class="text-xs font-medium text-gray-600 uppercase tracking-wide">{{ credential.role }}</div>
              <div class="text-sm text-gray-900 mt-1 font-mono">{{ credential.email }}</div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import users from '../api/users';
import { toast } from 'vue-sonner';
import { useRouter } from 'vue-router';
import { ref, computed, watch } from 'vue';
import { CheckIcon, EyeClosedIcon, EyeIcon, Loader2Icon, XIcon } from 'lucide-vue-next';

const email = ref('');
const password = ref('');
const errorMessage = ref('');
const emailError = ref('');
const passwordError = ref('');
const isLoading = ref(false);
const showPassword = ref(false);
const isFormFocused = ref(false);
const router = useRouter();

const demoCredentials = [
  { role: 'Lecturer', email: 'lecturer1@utm.my', password: 'lecturer1' },
  { role: 'Student', email: 'student1@utm.my', password: 'student1' },
  { role: 'Admin', email: 'admin@utm.my', password: 'admin1234' },
  { role: 'Advisor', email: 'advisor1@utm.my', password: 'advisor1' }
];

// Computed properties
const isEmailValid = computed(() => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value);
});

const isFormValid = computed(() => {
  return email.value && password.value && isEmailValid.value && !emailError.value && !passwordError.value;
});

// Watchers for real-time validation
watch(email, (newEmail) => {
  if (newEmail && !isEmailValid.value) {
    emailError.value = 'Please enter a valid email address';
  } else {
    emailError.value = '';
  }
});

watch(password, (newPassword) => {
  if (newPassword && newPassword.length < 6) {
    passwordError.value = 'Password must be at least 6 characters long';
  } else {
    passwordError.value = '';
  }
});

// Methods
const handleInputFocus = () => {
  isFormFocused.value = true;
  clearError();
};

const handleInputBlur = () => {
  isFormFocused.value = false;
};

const clearError = () => {
  errorMessage.value = '';
};

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const fillCredentials = (credential) => {
  email.value = credential.email;
  password.value = credential.password;
  clearError();
};

const handleLogin = async () => {
  if (!isFormValid.value) return;

  errorMessage.value = '';
  isLoading.value = true;

  try {
    const user = await users.login(email.value, password.value);

    if (!user) {
      toast.error('Invalid email or password. Please try again.');
    }

    if (user.status === 'success') {
      const { role, email, id } = user.data;

      localStorage.setItem('id', id);
      localStorage.setItem('email', email);
      localStorage.setItem('role', role);

      // Redirect based on role
      switch (role) {
        case 'lecturer':
          router.push('/lecturer/dashboard');
          break;
        case 'student':
          router.push('/student/dashboard');
          break;
        case 'admin':
          router.push('/admin/user-management');
          break;
        case 'advisor':
          router.push('/advisor/dashboard');
          break;
        default:
          router.push('/dashboard');
          break;
      }
    } else {
      toast.error(user.message || 'Login failed. Please try again.');
    }
  } catch (error) {
    console.error('Login failed:', error);
    errorMessage.value = error.message || 'Invalid email or password. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

</script>

<style scoped>
/* Animation keyframes */
@keyframes shake {

  0%,
  100% {
    transform: translateX(0);
  }

  25% {
    transform: translateX(-5px);
  }

  75% {
    transform: translateX(5px);
  }
}

@keyframes fade-in {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

.animate-shake {
  animation: shake 0.5s ease-in-out;
}

.animate-fade-in {
  animation: fade-in 0.3s ease-in-out;
}

/* Vue transitions */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>