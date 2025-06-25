<!-- EnhancedSelect.vue -->
<template>
  <div class="relative">
    <!-- Label -->
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Headless UI Listbox -->
    <Listbox v-model="selectedValue" :disabled="disabled">
      <div class="relative">
        <!-- Select Button -->
        <ListboxButton :id="id" :class="[
          'relative min-w-[200px] w-full cursor-pointer rounded-lg border py-3 pl-4 pr-10 text-left shadow-sm transition-all duration-200 ease-in-out',
          disabled
            ? 'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed'
            : 'bg-white border-gray-300 text-gray-900 hover:border-gray-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500 focus:ring-opacity-20',
          error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : '',
          size === 'sm' ? 'py-2 pl-3 pr-8 text-sm' : size === 'lg' ? 'py-4 pl-5 pr-12 text-lg' : ''
        ]">
          <span class="flex items-center">
            <!-- Icon (if provided) -->
            <component v-if="selectedOption?.icon" :is="selectedOption.icon" :class="[
              'mr-3 flex-shrink-0',
              size === 'sm' ? 'h-4 w-4' : size === 'lg' ? 'h-6 w-6' : 'h-5 w-5'
            ]" />

            <!-- Selected text -->
            <span :class="[
              'block truncate',
              !selectedValue && placeholder ? 'text-gray-400' : 'text-gray-900'
            ]">
              {{ selectedOption?.label || placeholder || 'Select an option' }}
            </span>
          </span>

          <!-- Chevron Icon -->
          <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <ChevronDownIcon :class="[
              'text-gray-400 transition-transform duration-200',
              size === 'sm' ? 'h-4 w-4' : size === 'lg' ? 'h-6 w-6' : 'h-5 w-5'
            ]" aria-hidden="true" />
          </span>
        </ListboxButton>

        <!-- Error Message -->
        <p v-if="error && errorMessage" class="mt-1 text-sm text-red-600">
          {{ errorMessage }}
        </p>

        <!-- Options Dropdown -->
        <transition enter-active-class="transition duration-200 ease-out"
          enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-150 ease-in" leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0">
          <ListboxOptions :class="[
            'absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white py-1 shadow-lg ring-gray-500 ring-opacity-5 focus:outline-none',
            size === 'sm' ? 'text-sm' : size === 'lg' ? 'text-lg' : 'text-base'
          ]">
            <!-- Loading State -->
            <div v-if="loading" class="relative cursor-default select-none py-3 px-4 text-gray-400 text-center">
              <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-sky-600 mr-2"></div>
                Loading...
              </div>
            </div>

            <!-- No Options State -->
            <div v-else-if="!loading && options.length === 0"
              class="relative cursor-default select-none py-3 px-4 text-gray-400 text-center">
              No options available
            </div>

            <!-- Search Input (if searchable) -->
            <div v-if="searchable && !loading && options.length > 0"
              class="sticky top-0 bg-white border-b border-gray-100 p-2">
              <input v-model="searchQuery" type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                :placeholder="searchPlaceholder" />
            </div>

            <!-- Options List -->
            <ListboxOption v-for="option in filteredOptions" :key="option.value" :value="option.value"
              :disabled="option.disabled" v-slot="{ active, selected, disabled: optionDisabled }">
              <li :class="[
                'relative cursor-pointer select-none py-2.5 px-4 transition-colors duration-150',
                active && !optionDisabled ? 'bg-sky-50 text-sky-900' : 'text-gray-900',
                optionDisabled ? 'opacity-50 cursor-not-allowed' : '',
                size === 'sm' ? 'py-2 px-3' : size === 'lg' ? 'py-3 px-5' : ''
              ]">
                <div class="flex items-center">
                  <!-- Option Icon -->
                  <component v-if="option.icon" :is="option.icon" :class="[
                    'mr-3 flex-shrink-0',
                    active && !optionDisabled ? 'text-sky-600' : 'text-gray-400',
                    size === 'sm' ? 'h-4 w-4' : size === 'lg' ? 'h-6 w-6' : 'h-5 w-5'
                  ]" />

                  <!-- Option Text -->
                  <div class="flex-1 min-w-0">
                    <span :class="['block truncate', selected ? 'font-semibold' : 'font-normal']">
                      {{ option.label }}
                    </span>
                    <span v-if="option.description" class="block text-xs text-gray-500 mt-0.5 truncate">
                      {{ option.description }}
                    </span>
                  </div>

                  <!-- Check Icon for Selected -->
                  <span v-if="selected" class="flex items-center pl-3 text-sky-600">
                    <CheckIcon :class="[
                      'flex-shrink-0',
                      size === 'sm' ? 'h-4 w-4' : size === 'lg' ? 'h-6 w-6' : 'h-5 w-5'
                    ]" aria-hidden="true" />
                  </span>
                </div>
              </li>
            </ListboxOption>
          </ListboxOptions>
        </transition>
      </div>
    </Listbox>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
import {
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue'
import {
  CheckIcon,
  ChevronDownIcon,
} from 'lucide-vue-next'

export default {
  name: 'EnhancedSelect',
  components: {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
    CheckIcon,
    ChevronDownIcon
  },
  props: {
    // Core functionality
    modelValue: {
      type: [String, Number, Object, null],
      default: null
    },
    options: {
      type: Array,
      required: true,
      default: () => []
    },

    // Appearance
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Select an option'
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },

    // States
    disabled: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    required: {
      type: Boolean,
      default: false
    },
    error: {
      type: Boolean,
      default: false
    },
    errorMessage: {
      type: String,
      default: ''
    },

    // Features
    searchable: {
      type: Boolean,
      default: false
    },
    searchPlaceholder: {
      type: String,
      default: 'Search options...'
    },

    // Accessibility
    id: {
      type: String,
      default: () => `select-${Math.random().toString(36).substr(2, 9)}`
    }
  },
  emits: ['update:modelValue', 'change'],
  setup(props, { emit }) {
    const searchQuery = ref('')

    const selectedValue = computed({
      get: () => props.modelValue,
      set: (value) => {
        emit('update:modelValue', value)
        emit('change', value)
      }
    })

    const selectedOption = computed(() => {
      return props.options.find(option => option.value === selectedValue.value)
    })

    const filteredOptions = computed(() => {
      if (!props.searchable || !searchQuery.value) {
        return props.options
      }

      const query = searchQuery.value.toLowerCase()
      return props.options.filter(option =>
        option.label.toLowerCase().includes(query) ||
        (option.description && option.description.toLowerCase().includes(query))
      )
    })

    // Clear search when options change
    watch(() => props.options, () => {
      searchQuery.value = ''
    })

    return {
      selectedValue,
      selectedOption,
      searchQuery,
      filteredOptions
    }
  }
}
</script>