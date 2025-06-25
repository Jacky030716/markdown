<template>
  <div class="bg-white p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-2">My Advisees</h1>
      <p class="text-gray-600">Manage and monitor your advisees' academic progress</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <span class="ml-2 text-gray-600">Loading advisees...</span>
    </div>

    <!-- Content when not loading -->
    <div v-else>
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-600 text-sm font-medium">Number of Matched Advisees</p>
              <p class="text-2xl font-bold text-blue-800">{{ totalAdvisees }}</p>
            </div>
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
          </div>
        </div>

        <div class="bg-red-50 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-red-600 text-sm font-medium">At-Risk Students</p>
              <p class="text-2xl font-bold text-red-800">{{ atRiskStudents }}</p>
            </div>
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
        </div>

        <div class="bg-green-50 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-600 text-sm font-medium">High Performers</p>
              <p class="text-2xl font-bold text-green-800">{{ highPerformers }}</p>
            </div>
            <svg class="w-8 h-8 text-green-600 transform rotate-180" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
        </div>

        <div class="bg-yellow-50 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-yellow-600 text-sm font-medium">Average GPA</p>
              <p class="text-2xl font-bold text-yellow-800">{{ averageGPA }}</p>
            </div>
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
      </div>

      <div class="mb-6">

        <!-- Search and Filter Bar -->
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
          <!-- Search Bar -->
          <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input v-model="searchQuery" type="text" placeholder="Search by Student Name, Matric No"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 " />
          </div>

          <!-- Status Filter -->
          <div class="relative">
            <select v-model="selectedStatus"
              class="appearance-none bg-white border border-gray-300 rounded-md px-4 py-2 pr-8 text-sm focus:outline-none ">
              <option value="All Statuses">All Statuses</option>
              <option value="Good">Good</option>
              <option value="At Risk">At Risk</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto min-w-full">
        <div class="inline-block min-w-full align-middle">
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-600">Student Info</th>
                    <th class="px-6 py-4 text-center text-sm font-medium text-gray-600 cursor-pointer hover:bg-gray-100"
                      @click="handleSort('program')">
                      <div class="flex items-center justify-center space-x-1">
                        <span>Program</span>
                        <svg v-if="sortField === 'program'" class="w-4 h-4"
                          :class="sortDirection === 'asc' ? 'transform rotate-180' : ''" fill="none"
                          stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </th>
                    <th class="px-6 py-4 text-center text-sm font-medium text-gray-600 cursor-pointer hover:bg-gray-100"
                      @click="handleSort('year_of_study')">
                      <div class="flex items-center justify-center space-x-1">
                        <span>Year</span>
                        <svg v-if="sortField === 'year_of_study'" class="w-4 h-4"
                          :class="sortDirection === 'asc' ? 'transform rotate-180' : ''" fill="none"
                          stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </th>
                    <th class="px-6 py-4 text-center text-sm font-medium text-gray-600 cursor-pointer hover:bg-gray-100"
                      @click="handleSort('courses')">
                      <div class="flex items-center justify-center space-x-1">
                        <span>Courses</span>
                        <svg v-if="sortField === 'courses'" class="w-4 h-4"
                          :class="sortDirection === 'asc' ? 'transform rotate-180' : ''" fill="none"
                          stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </th>
                    <th class="px-6 py-4 text-center text-sm font-medium text-gray-600 cursor-pointer hover:bg-gray-100"
                      @click="handleSort('gpa')">
                      <div class="flex items-center justify-center space-x-1">
                        <span>GPA</span>
                        <svg v-if="sortField === 'gpa'" class="w-4 h-4"
                          :class="sortDirection === 'asc' ? 'transform rotate-180' : ''" fill="none"
                          stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </div>
                    </th>
                    <th class="px-6 py-4 text-center text-sm font-medium text-gray-600">Status</th>
                    <th class="px-6 py-4 text-center text-sm font-medium text-gray-600">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="student in currentAdvisees" :key="student.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                      <div>
                        <div class="font-medium text-gray-800">{{ student.name }}</div>
                        <div class="text-sm text-gray-600">{{ student.matric_no }}</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">{{ student.program }}</td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">Year {{ student.year_of_study }}</td>
                    <td class="px-6 py-4 text-center">
                      <div class="text-sm text-gray-800">{{ student.courses_enrolled?.length || 0 }} courses</div>
                      <div class="text-xs text-gray-500">
                        {{ getTotalCredits(student) }} credits
                      </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                      <span :class="getGPAColor(student.gpa)" class="px-3 py-1 rounded-full text-sm font-medium">
                        {{ student.gpa?.toFixed(2) || '0.00' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                      <span v-if="isAtRisk(student.gpa)" class="flex items-center justify-center text-red-600 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        At Risk
                      </span>
                      <span v-else class="text-green-600 text-sm">Good</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                      <div class="flex justify-center space-x-2">
                        <button @click="handleViewDetails(student)"
                          class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Details">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        </button>
                        <button @click="handleGenerateReport(student)"
                          class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                          title="Generate Report">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
              <div class="text-sm text-gray-600">
                Showing {{ startIndex + 1 }} to {{ Math.min(endIndex, totalAdvisees) }} of {{ totalAdvisees }} advisees
              </div>
              <div class="flex space-x-2">
                <button @click="previousPage" :disabled="currentPage === 1"
                  class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <span class="px-4 py-2 text-sm text-gray-600">
                  Page {{ currentPage }} of {{ totalPages }}
                </span>
                <button @click="nextPage" :disabled="currentPage === totalPages"
                  class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Details Modal -->
    <div v-if="selectedStudent" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-auto">
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800">Student Details</h2>
            <button @click="closeDetailsModal" class="text-gray-500 hover:text-gray-700 text-2xl">
              ×
            </button>
          </div>
        </div>

        <div class="p-6">
          <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div class="space-y-3">
              <h3 class="font-semibold text-lg text-gray-800">Personal Information</h3>
              <div class="space-y-2">
                <p><span class="font-medium">Name:</span> {{ selectedStudent.name }}</p>
                <p><span class="font-medium">Matric No:</span> {{ selectedStudent.matric_no }}</p>
                <p><span class="font-medium">Program:</span> {{ selectedStudent.program }}</p>
                <p><span class="font-medium">Year of Study:</span> Year {{ selectedStudent.year_of_study }}</p>
              </div>
            </div>

            <div class="space-y-3">
              <h3 class="font-semibold text-lg text-gray-800">Academic Performance</h3>
              <div class="space-y-2">
                <p><span class="font-medium">Current GPA:</span>
                  <span :class="getGPAColor(selectedStudent.gpa)"
                    class="ml-2 px-3 py-1 rounded-full text-sm font-medium">
                    {{ selectedStudent.gpa?.toFixed(2) || '0.00' }}
                  </span>
                </p>
                <p><span class="font-medium">Courses Enrolled:</span> {{ selectedStudent.courses_enrolled?.length || 0
                }}</p>
                <p><span class="font-medium">Total Credits:</span> {{ getTotalCredits(selectedStudent) }}</p>
                <div v-if="isAtRisk(selectedStudent.gpa)" class="flex items-center text-red-600 bg-red-50 p-2 rounded">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                  <span class="text-sm font-medium">At-Risk Student</span>
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <h3 class="font-semibold text-lg text-gray-800">Course Performance</h3>
            <div class="overflow-x-auto">
              <table class="w-full table-auto">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Course Name</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Credits</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Total Mark</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Grade</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(course, index) in selectedStudent.courses_enrolled" :key="index"
                    class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-800">{{ course.course_name }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ course.credits }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ course.totalMark }}</td>
                    <td class="px-4 py-3 text-center">
                      <span :class="getGradeColor(course.grade)" class="px-2 py-1 rounded text-xs font-medium">
                        {{ course.grade }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button @click="handleGenerateReport(selectedStudent)"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span>Generate Report</span>
            </button>
            <button @click="closeDetailsModal"
              class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Report Modal -->
    <div v-if="showReport && reportStudent"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-6xl"
        :class="showReport ? 'report-modal-container' : 'max-h-[90vh] overflow-auto'">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center print:hidden">
          <h2 class="text-2xl font-semibold text-gray-800">Student Academic Report</h2>
          <div class="flex space-x-2">
            <button @click="generatePDF"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span>Download PDF</span>
            </button>
            <button @click="closeReportModal" class="text-gray-500 hover:text-gray-700 text-2xl">
              ×
            </button>
          </div>
        </div>

        <div class="p-8 print:p-6">
          <div class="report-content">
            <!-- Header -->
            <div class="text-center mb-8">
              <div class="flex items-center justify-center">
                <img src="../../../assets/UTM-LOGO-FULL.png" alt="UTM Logo" class="w-80 h-40 object-contain" />
              </div>
              <h1 class="text-2xl font-bold text-gray-800">UNIVERSITI TEKNOLOGI MALAYSIA</h1>
              <p class="text-gray-600">ACADEMIC MANAGEMENT DIVISION</p>
              <p class="text-gray-600">81310 UTM JOHOR BAHRU,</p>
              <p class="text-gray-600">JOHOR, MALAYSIA.</p>
              <p class="text-gray-600 font-medium mt-2">(ACADEMIC REPORT)</p>
            </div>

            <!-- Student Information -->
            <div class="grid md:grid-cols-2 gap-8 mb-8">
              <div class="space-y-2">
                <p><span class="font-medium">FACULTY:</span> FAKULTI KOMPUTERAN</p>
                <p><span class="font-medium">NAME:</span> {{ reportStudent.name.toUpperCase() }}</p>
                <p><span class="font-medium">MATRIC CARD NO.:</span> {{ reportStudent.matric_no }}</p>
                <p><span class="font-medium">ACADEMIC ADVISOR:</span> DR. TEONG LEE</p>
              </div>
              <div class="space-y-2">
                <p><span class="font-medium">SESSION/SEMESTER:</span> 2024/2025 / 1</p>
                <p><span class="font-medium">YEAR/PROGRAMME:</span> {{ reportStudent.year_of_study }} / {{
                  reportStudent.program.toUpperCase() }}</p>
                <p><span class="font-medium">CURRENT GPA:</span> {{ reportStudent.gpa?.toFixed(2) || '0.00' }}</p>
              </div>
            </div>

            <!-- Course Table -->
            <div class="mb-8">
              <table class="w-full border-collapse border border-gray-400">
                <thead>
                  <tr class="bg-gray-100">
                    <th class="border border-gray-400 px-4 py-2 text-center font-medium">COURSE CODE</th>
                    <th class="border border-gray-400 px-4 py-2 text-center font-medium">COURSE NAME</th>
                    <th class="border border-gray-400 px-4 py-2 text-center font-medium">CREDITS</th>
                    <th class="border border-gray-400 px-4 py-2 text-center font-medium">TOTAL MARK</th>
                    <th class="border border-gray-400 px-4 py-2 text-center font-medium">GRADE</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(course, index) in reportStudent.courses_enrolled" :key="index">
                    <td class="border border-gray-400 px-4 py-2 text-center">{{ course.course_code || 'CS' +
                      String(index + 1).padStart(3, '0') }}
                    </td>
                    <td class="border border-gray-400 px-4 py-2">{{ course.course_name.toUpperCase() }}</td>
                    <td class="border border-gray-400 px-4 py-2 text-center">{{ course.credits }}</td>
                    <td class="border border-gray-400 px-4 py-2 text-center">{{ course.totalMark }}</td>
                    <td class="border border-gray-400 px-4 py-2 text-center font-medium">{{ course.grade }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Footer Notes -->
            <div class="text-sm text-gray-600 space-y-2">
              <p class="font-medium text-red-600">Note:</p>
              <p>1. The result is not final. It still depends on the outcome of the meeting at the University level.</p>
              <p>2. This report is for academic advisory purposes and can be used for consultation records.</p>
              <p v-if="isAtRisk(reportStudent.gpa)" class="text-red-600 font-medium">3. This student is identified as
                at-risk and requires immediate academic intervention.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
  name: 'AdviseesListComponent',
  props: {
    advisees: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    itemsPerPage: {
      type: Number,
      default: 10
    }
  },
  emits: ['advisee-selected'],
  setup(props, { emit }) {
    // --- Reactive State ---
    const currentPage = ref(1)
    const selectedStudent = ref(null)
    const showReport = ref(false)
    const reportStudent = ref(null)
    const searchQuery = ref('')
    const selectedStatus = ref('All Statuses')
    const sortField = ref(null)
    const sortDirection = ref('asc')

    // --- Computed Properties ---
    const totalAdvisees = computed(() => filteredAndSortedAdvisees.value.length)

    const totalPages = computed(() => Math.ceil(totalAdvisees.value / props.itemsPerPage))

    const startIndex = computed(() => (currentPage.value - 1) * props.itemsPerPage)

    const endIndex = computed(() => startIndex.value + props.itemsPerPage)

    const currentAdvisees = computed(() =>
      filteredAndSortedAdvisees.value.slice(startIndex.value, endIndex.value)
    )

    const atRiskStudents = computed(() =>
      filteredAndSortedAdvisees.value.filter(student => isAtRisk(student.gpa)).length
    )

    const highPerformers = computed(() =>
      filteredAndSortedAdvisees.value.filter(student => student.gpa >= 3.5).length
    )

    const averageGPA = computed(() => {
      if (filteredAndSortedAdvisees.value.length === 0) return '0.00'
      const sum = filteredAndSortedAdvisees.value.reduce((acc, student) => acc + (student.gpa || 0), 0)
      return (sum / filteredAndSortedAdvisees.value.length).toFixed(2)
    })

    // --- Utility Functions ---
    const isAtRisk = (gpa) => gpa < 2.0

    const getGPAColor = (gpa) => {
      if (gpa >= 3.5) return 'text-green-600 bg-green-50'
      if (gpa >= 3.0) return 'text-blue-600 bg-blue-50'
      if (gpa >= 2.0) return 'text-yellow-600 bg-yellow-50'
      return 'text-red-600 bg-red-50'
    }

    const getGradeColor = (grade) => {
      if (['A', 'A+', 'A-'].includes(grade)) return 'bg-green-100 text-green-800'
      if (['B', 'B+', 'B-'].includes(grade)) return 'bg-blue-100 text-blue-800'
      if (['C', 'C+', 'C-'].includes(grade)) return 'bg-yellow-100 text-yellow-800'
      return 'bg-red-100 text-red-800'
    }

    const getTotalCredits = (student) => {
      if (!student.courses_enrolled) return 0
      return student.courses_enrolled.reduce((sum, course) => sum + course.credits, 0)
    }

    // --- Event Handlers ---
    // ADD COMPUTED PROPERTIES FOR FILTERING AND SORTING
    const filteredAndSortedAdvisees = computed(() => {
      let filtered = props.advisees

      // Apply search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(student =>
          student.name.toLowerCase().includes(query) ||
          student.matric_no.toLowerCase().includes(query)
        )
      }

      // Apply status filter
      if (selectedStatus.value !== 'All Statuses') {
        filtered = filtered.filter(student => {
          const studentStatus = isAtRisk(student.gpa) ? 'At Risk' : 'Good'
          return studentStatus === selectedStatus.value
        })
      }

      // Apply sorting
      if (sortField.value) {
        filtered = [...filtered].sort((a, b) => {
          let aValue, bValue

          switch (sortField.value) {
            case 'program':
              aValue = a.program
              bValue = b.program
              break
            case 'year_of_study':
              aValue = a.year_of_study
              bValue = b.year_of_study
              break
            case 'courses':
              aValue = a.courses_enrolled?.length || 0
              bValue = b.courses_enrolled?.length || 0
              break
            case 'gpa':
              aValue = a.gpa || 0
              bValue = b.gpa || 0
              break
            default:
              return 0
          }

          if (typeof aValue === 'string') {
            aValue = aValue.toLowerCase()
            bValue = bValue.toLowerCase()
          }

          if (sortDirection.value === 'asc') {
            return aValue > bValue ? 1 : aValue < bValue ? -1 : 0
          } else {
            return aValue < bValue ? 1 : aValue > bValue ? -1 : 0
          }
        })
      }

      return filtered
    })

    // ADD SORT HANDLER METHOD
    const handleSort = (field) => {
      if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortField.value = field
        sortDirection.value = 'asc'
      }
    }


    const handleViewDetails = (student) => {
      selectedStudent.value = student
      emit('advisee-selected', student)
    }

    const handleGenerateReport = (student) => {
      reportStudent.value = student
      showReport.value = true
    }

    const closeDetailsModal = () => {
      selectedStudent.value = null
    }

    const closeReportModal = () => {
      showReport.value = false
      reportStudent.value = null
    }

    const generatePDF = () => {
      // Ensure modal is properly sized before printing
      const reportContent = document.querySelector('.report-content');
      if (reportContent) {
        reportContent.style.transform = 'none';
      }

      setTimeout(() => {
        window.print();

        // Restore scaling after print dialog
        setTimeout(() => {
          if (reportContent) {
            reportContent.style.transform = '';
          }
        }, 1000);
      }, 100);
    }

    // --- Pagination ---
    const previousPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--
      }
    }

    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++
      }
    }

    // --- Watchers ---
    // Reset to first page when advisees data changes
    watch([searchQuery, selectedStatus, sortField, sortDirection], () => {
      currentPage.value = 1
    })

    return {
      // State
      currentPage,
      selectedStudent,
      showReport,
      reportStudent,

      // Computed
      totalAdvisees,
      totalPages,
      startIndex,
      endIndex,
      currentAdvisees,
      atRiskStudents,
      highPerformers,
      averageGPA,

      // Methods
      isAtRisk,
      getGPAColor,
      getGradeColor,
      getTotalCredits,
      handleViewDetails,
      handleGenerateReport,
      closeDetailsModal,
      closeReportModal,
      generatePDF,
      previousPage,
      nextPage,

      searchQuery,
      selectedStatus,
      sortField,
      sortDirection,
      filteredAndSortedAdvisees,
      handleSort
    }
  }
}
</script>

<style>
.report-modal-container {
  max-height: 100vh;
  overflow-y: auto;
  transform-origin: top left;
}

.report-content {
  transform-origin: top left;
  transition: transform 0.3s ease;
}

@media (max-width: 1400px) {
  .report-content {
    transform: scale(0.85);
  }
}

@media (max-width: 1200px) {
  .report-content {
    transform: scale(0.75);
  }
}

@media (max-width: 900px) {
  .report-content {
    transform: scale(0.65);
  }
}

@media print {
  * {
    background-color: white !important;
    background-image: none !important;
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
  }

  .report-content {
    transform: none !important;
    page-break-inside: avoid;
  }

  .print\\:hidden {
    display: none !important;
  }

  .report-modal-container {
    max-height: none !important;
    overflow: visible !important;
  }
}
</style>