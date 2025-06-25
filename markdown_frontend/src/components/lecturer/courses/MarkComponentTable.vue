<template>
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <!-- Table Header -->
    <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
      <h3 class="text-lg font-bold text-gray-900">Grade Components</h3>
      <p class="text-sm text-gray-600 mt-1">Manage your course grading structure</p>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="bg-gray-50/50 border-b border-gray-200">
            <th
              class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider cursor-pointer select-none"
              @click="sort('name')">
              <div class="flex items-center space-x-1">
                <span>
                  Component
                </span>
                <ArrowUpDown class="size-4" />
              </div>
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider cursor-pointer select-none"
              @click="sort('type')">
              <div class="flex items-center space-x-1">
                <span>
                  Type
                </span>
                <ArrowUpDown class="size-4" />
              </div>
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider cursor-pointer select-none"
              @click="sort('max_mark')">
              <div class="flex items-center space-x-1">
                <span>
                  Max Marks
                </span>
                <ArrowUpDown class="size-4" />
              </div>
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider cursor-pointer select-none"
              @click="sort('weight')">
              <div class="flex items-center space-x-1">
                <span>
                  Weight
                </span>
                <ArrowUpDown class="size-4" />
              </div>
            </th>
            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="component in sortedComponents" :key="component.id"
            class="hover:bg-gray-50/50 transition-colors duration-200 group"
            :class="{ 'bg-yellow-50': component.type === 'final' }">
            <td class="px-6 py-5">
              <div class="flex items-center">
                <div
                  class="w-10 h-10 rounded-lg border-[0.5px] flex items-center justify-center text-white font-bold text-sm mr-4">
                  {{ getComponentIcon(component.type) }}
                </div>
                <div>
                  <div class="text-sm font-bold text-gray-900">
                    {{ component.name }}
                  </div>
                  <div class="text-xs text-gray-500">{{ getComponentDescription(component.type) }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-5">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                :class="getTypeClass(component.type)">
                {{ component.type === 'final' ? 'Final Exam' : component.type.charAt(0).toUpperCase() +
                  component.type.slice(1) }}
              </span>
            </td>
            <td class="px-6 py-5">
              <div class="flex items-center">
                <span class="text-sm font-bold text-gray-900">{{ component.max_mark }}</span>
                <span class="text-xs text-gray-500 ml-1">%</span>
              </div>
            </td>
            <td class="px-6 py-5">
              <div class="flex items-center">
                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3 max-w-[60px]">
                  <div class="h-2 rounded-full transition-all duration-300"
                    :class="component.type === 'final' ? 'bg-gradient-to-r from-red-400 to-red-600' : 'bg-gradient-to-r from-sky-400 to-sky-600'"
                    :style="{ width: `${Math.min(component.weight, 100)}%` }"></div>
                </div>
                <span class="text-sm font-bold text-gray-900">{{ component.weight }}%</span>
              </div>
            </td>
            <td class="px-6 py-5">
              <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <button @click="editComponent(component)"
                  class="p-2 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-all duration-200"
                  title="Edit component">
                  <Edit2Icon class="w-4 h-4" />
                </button>
                <button v-if="component.type !== 'final'" @click="deleteComponent(component.id)"
                  class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200"
                  title="Delete component">
                  <TrashIcon class="w-4 h-4" />
                </button>
                <div v-else class="p-2 text-gray-300" title="Final exam cannot be deleted">
                  <TrashIcon class="w-4 h-4" />
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="sortedComponents.length === 0" class="flex flex-col items-center justify-center py-16 px-6">
      <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
            d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
          </path>
        </svg>
      </div>
      <div class="text-center">
        <h3 class="text-lg font-bold text-gray-900 mb-2">No Components Yet</h3>
        <p class="text-gray-500 text-sm max-w-sm">
          Get started by adding your first grading component to build your course structure.
        </p>
      </div>
    </div>

    <!-- Summary Footer -->
    <div v-if="sortedComponents.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-sm text-gray-600">
          <span class="font-semibold">{{ sortedComponents.length }}</span>
          component{{ sortedComponents.length !== 1 ? 's' : '' }} total
        </div>
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <span class="text-sm text-gray-600 mr-3">Continuous Assessment:</span>
            <div class="flex items-center">
              <div class="w-20 bg-gray-200 rounded-full h-3 mr-2">
                <div class="h-3 rounded-full transition-all duration-300"
                  :class="continuousAssessmentWeight > continuousAssessmentLimit ? 'bg-gradient-to-r from-red-400 to-red-600' : 'bg-gradient-to-r from-sky-400 to-sky-600'"
                  :style="{ width: `${Math.min((continuousAssessmentWeight / continuousAssessmentLimit) * 100, 100)}%` }">
                </div>
              </div>
              <span class="text-sm font-bold"
                :class="continuousAssessmentWeight > continuousAssessmentLimit ? 'text-red-600' : 'text-gray-900'">
                {{ continuousAssessmentWeight }}%
              </span>
            </div>
          </div>
          <div class="flex items-center">
            <span class="text-sm text-gray-600 mr-3">Total Weight:</span>
            <div class="flex items-center">
              <div class="w-20 bg-gray-200 rounded-full h-3 mr-2">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-3 rounded-full transition-all duration-300"
                  :style="{ width: `${Math.min(totalWeight, 100)}%` }"></div>
              </div>
              <span class="text-sm font-bold" :class="totalWeight !== 100 ? 'text-orange-600' : 'text-gray-900'">
                {{ totalWeight }}%
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ArrowUpDown, Edit2Icon, TrashIcon } from 'lucide-vue-next';

export default {
  name: 'MarkComponentTable',
  components: {
    Edit2Icon,
    TrashIcon,
    ArrowUpDown
  },
  data() {
    return {
      sortBy: 'name', // default sorting field
      sortDirection: 'asc' // or 'desc'
    };
  },
  props: {
    selectedCourse: {
      type: Object,
      required: true
    },
    totalWeight: { // Prop from parent
      type: Number,
      required: true
    },
    continuousAssessmentWeight: { // Prop from parent
      type: Number,
      required: true
    },
    continuousAssessmentLimit: { // Prop from parent
      type: Number,
      required: true
    }
  },
  computed: {
    sortedComponents() {
      const components = this.selectedCourse?.components || [];
      const { sortBy, sortDirection } = this;

      return [...components].sort((a, b) => {
        // Always put final exam first for visual consistency
        // Use 'final' type for consistency with other components
        const isAFinal = a.type === 'final';
        const isBFinal = b.type === 'final';

        if (isAFinal && !isBFinal) return -1;
        if (!isAFinal && isBFinal) return 1;

        const valueA = a[sortBy];
        const valueB = b[sortBy];

        // For 'max_mark' and 'weight', parse to float
        if (sortBy === 'max_mark' || sortBy === 'weight') {
          const parsedValueA = parseFloat(valueA);
          const parsedValueB = parseFloat(valueB);

          // Handle NaN values by pushing them to the end (or beginning, based on sortDirection)
          if (isNaN(parsedValueA) && !isNaN(parsedValueB)) return sortDirection === 'asc' ? 1 : -1;
          if (!isNaN(parsedValueA) && isNaN(parsedValueB)) return sortDirection === 'asc' ? -1 : 1;
          if (isNaN(parsedValueA) && isNaN(parsedValueB)) return 0; // Both are NaN, treat as equal

          return sortDirection === 'asc' ? parsedValueA - parsedValueB : parsedValueB - parsedValueA;

        } else {
          // For string fields ('name', 'type'), use localeCompare
          return sortDirection === 'asc'
            ? String(valueA).localeCompare(String(valueB))
            : String(valueB).localeCompare(String(valueA));
        }
      });
    }
  },
  methods: {
    getTypeClass(type) {
      const classes = {
        'quiz': 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        'assignment': 'bg-blue-100 text-blue-700 border border-blue-200',
        'test': 'bg-amber-100 text-amber-700 border border-amber-200',
        'lab': 'bg-purple-100 text-purple-700 border border-purple-200',
        'project': 'bg-rose-100 text-rose-700 border border-rose-200',
        'final': 'bg-red-100 text-red-700 border border-red-200'
      };
      return classes[type] || 'bg-gray-100 text-gray-700 border border-gray-200';
    },
    getComponentIcon(type) {
      const icons = {
        'quiz': '📝',
        'assignment': '📄',
        'test': '📊',
        'lab': '🔬',
        'project': '🚀',
        'final': '🎓'
      };
      return icons[type] || '📋';
    },
    getComponentDescription(type) {
      const descriptions = {
        'quiz': 'Short assessment',
        'assignment': 'Take-home work',
        'test': 'Major examination',
        'lab': 'Practical work',
        'project': 'Long-term assignment',
        'final': 'Final examination'
      };
      return descriptions[type] || 'Course component';
    },
    editComponent(component) {
      this.$emit('edit-component', component);
    },
    deleteComponent(id) {
      this.$emit('delete-component', id);
    },
    sort(field) {
      if (this.sortBy === field) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortBy = field;
        this.sortDirection = 'asc';
      }
    },
  }
}
</script>