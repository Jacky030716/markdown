<template>
  <div>
    <!-- Course Selection -->
    <CourseSelection :courses="courses" :selected-course="selectedCourse" @course-selected="onCourseSelected" />

    <!-- Student Marks Table -->
    <StudentMarkTable v-if="selectedCourse" :students="processedStudents" :assessment-components="assessmentComponents"
      :selected-course="selectedCourse" :class-average="parseFloat(classAverage)" :pass-count="passCount"
      :fail-count="failCount" :search-query="searchQuery" @update-search="searchQuery = $event"
      @update-student-mark="updateStudentMark" @add-student="openAddStudentModal" @edit-student="openEditStudentModal"
      @delete-student="deleteStudent" @save-all-marks="saveAllMarks" @export-csv="exportToCSV" />

    <!-- Add/Edit Student Modal -->
    <StudentModal v-if="showStudentModal" :show="showStudentModal" :student="editingStudent"
      :is-editing="!!editingStudent" :assessment-components="assessmentComponents" @close="closeStudentModal"
      @save="saveStudent" />
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import CourseSelection from './CourseSelection.vue';
import StudentMarkTable from './StudentMarkTable.vue';
import StudentModal from './StudentModal.vue';

// Assuming this path is correct for your API service
import lecturersApi from '../../../api/lecturers';
import { toast } from 'vue-sonner';

export default {
  name: 'CourseMarkManagement',
  components: {
    CourseSelection,
    StudentMarkTable,
    StudentModal
  },
  props: {
    courses: {
      type: Array,
      default: () => []
    }
  },
  emits: ['refresh-data'], // Emit event to refresh data in parent component

  setup(props, { emit }) {
    // Reactive data properties
    const selectedCourse = ref(null); // The currently selected course object
    const searchQuery = ref('');      // Search query for filtering students
    const showStudentModal = ref(false); // Controls visibility of the add/edit student modal
    const editingStudent = ref(null);    // Holds student data when editing
    const assessmentComponents = ref([]); // Holds the dynamic list of assessment components for the selected course
    const localStudents = ref([]);      // Holds the list of students with their marks for the selected course

    /**
     * Computed property to filter students based on the search query.
     * This will reactively update the table display as the user types.
     */
    const processedStudents = computed(() => {
      if (!searchQuery.value) {
        return localStudents.value; // Return all students if no search query
      }
      const lowerCaseQuery = searchQuery.value.toLowerCase();
      return localStudents.value.filter(student =>
        student.name?.toLowerCase().includes(lowerCaseQuery) ||
        student.matricId?.toLowerCase().includes(lowerCaseQuery)
      );
    });

    /**
     * Computed property to calculate the class average mark.
     */
    const classAverage = computed(() => {
      if (localStudents.value.length === 0) return 0;
      const total = localStudents.value.reduce((sum, student) => sum + (student.totalMark || 0), 0);
      return (total / localStudents.value.length).toFixed(1); // Round to 1 decimal place
    });

    /**
     * Computed property to count students who passed (total mark >= 40).
     */
    const passCount = computed(() => {
      return localStudents.value.filter(student => (student.totalMark || 0) >= 40).length;
    });

    /**
     * Computed property to count students who failed (total mark < 40).
     */
    const failCount = computed(() => {
      return localStudents.value.filter(student => (student.totalMark || 0) < 40).length;
    });

    /**
     * Method called when a course is selected from the CourseSelection component.
     * Triggers loading of student data for the selected course.
     * @param {Object} course The selected course object.
     */
    const onCourseSelected = (course) => {
      selectedCourse.value = course;
      loadCourseData(course); // Load data specific to this course
    };

    /**
     * Asynchronously loads student data and assessment components for a given course
     * from the backend API.
     * @param {Object} course The course object for which to load data.
     */
    const loadCourseData = async (course) => {
      if (course) {
        try {

          const lecturerId = 1; // Get from local storage or context later

          // Fetch the students marks for the selected course using the new API endpoint
          const studentMarksResponse = await lecturersApi.getStudentMarks(lecturerId, course.id);
          localStudents.value = studentMarksResponse.data;

          // Dynamically extract assessment components from the first student's marks object
          // This ensures the component structure matches what the backend provides.
          if (localStudents.value.length > 0) {
            const firstStudentMarks = localStudents.value[0].marks;
            const extractedComponents = Object.values(firstStudentMarks).map(markEntry => ({
              component_id: markEntry.component_id,
              component_name: markEntry.component_name,
              component_type: markEntry.component_type,
              max_mark: markEntry.max_mark, // Use max_mark as it comes directly from API
              weight: markEntry.weight      // Use weight as it comes directly from API
            }));
            // Sort components by their ID for consistent display order
            assessmentComponents.value = extractedComponents.sort((a, b) => a.component_id - b.component_id);
          } else {
            assessmentComponents.value = []; // No students, so no components to display
          }

          // After loading data, ensure total marks and grades are calculated for all students.
          // This is crucial if the backend doesn't provide `totalMark` and `grade` directly,
          // or if these need to be re-calculated based on frontend logic changes.
          localStudents.value.forEach(student => {
            updateStudentTotal(student);
          });

        } catch (error) {
          console.error("Error loading course data:", error);
          // You might want to display a user-friendly error message here
          alert("Failed to load course data. Please try again.");
          localStudents.value = [];
          assessmentComponents.value = [];
        }
      }
    };

    /**
     * Recalculates the total mark and grade for a single student.
     * This method iterates through the dynamically loaded assessment components.
     * @param {Object} student The student object to update.
     */
    const updateStudentTotal = (student) => {
      let overallWeightedTotal = 0;

      // Iterate through the dynamically set assessmentComponents
      assessmentComponents.value.forEach(component => {
        // Generate the key that matches the structure from the backend API response (e.g., "quiz1")
        const componentKey = component.component_name.toLowerCase().replace(/[\s-]/g, '');

        // Access the mark entry for this specific component from the student's 'marks' object
        const markEntry = student.marks[componentKey];

        // Ensure the mark entry exists, has a non-null student_mark, and a valid max_mark to prevent division by zero
        if (markEntry && markEntry.student_mark !== null && markEntry.max_mark > 0) {
          const mark = parseFloat(markEntry.student_mark); // Ensure mark is a number
          const max = parseFloat(markEntry.max_mark);     // Ensure max_mark is a number
          const weight = parseFloat(markEntry.weight);     // Ensure weight is a number

          // Calculate the weighted contribution of this component to the total mark
          overallWeightedTotal += (mark / max) * weight;
        }
      });

      // Assign the calculated total mark (rounded to 1 decimal place)
      student.totalMark = parseFloat(overallWeightedTotal.toFixed(1));
      // Calculate and assign the grade
      student.grade = calculateGrade(student.totalMark);
    };

    /**
     * Updates a student's mark for a specific component and then recalculates their total.
     * @param {number} studentId The ID of the student to update.
     * @param {string} componentKey The lower-cased, hyphen-removed name of the component (e.g., 'quiz1').
     * @param {number} newValue The new mark value for the component.
     */
    const updateStudentMark = async (studentId, componentKey, newValue) => {
      const student = localStudents.value.find(s => s.id === studentId);
      if (student && student.marks[componentKey]) {
        try {
          // Update the student_mark property within the specific component's object
          console.log(`Updating mark for student ${studentId}, component ${componentKey} to ${newValue}`);
          student.marks[componentKey].student_mark = newValue !== null && newValue !== '' ? parseFloat(newValue) : null;
          updateStudentTotal(student); // Recalculate total and grade after mark update

        } catch (error) {
          console.error("Error updating student mark:", error);
          toast.error("Failed to update student mark. Please try again.");
        }
      }
    };

    /**
     * Calculates the grade based on the total mark.
     * @param {number} totalMark The student's total calculated mark.
     * @returns {string} The grade (e.g., 'A', 'B', 'F').
     */
    const calculateGrade = (totalMark) => {
      // From A+ to E
      if (totalMark >= 90) return 'A+';
      if (totalMark >= 80) return 'A';
      if (totalMark >= 75) return 'A-';
      if (totalMark >= 70) return 'B+';
      if (totalMark >= 65) return 'B';
      if (totalMark >= 60) return 'B-';
      if (totalMark >= 55) return 'C+';
      if (totalMark >= 50) return 'C';
      if (totalMark >= 45) return 'C-';
      if (totalMark >= 40) return 'D+';
      if (totalMark >= 35) return 'D';
      if (totalMark >= 30) return 'D-';
      return 'E';
    };

    /**
     * Opens the modal for adding a new student.
     */
    const openAddStudentModal = () => {
      editingStudent.value = null; // Clear any existing student data
      showStudentModal.value = true;
    };

    /**
     * Opens the modal for editing an existing student.
     * @param {Object} student The student object to be edited.
     */
    const openEditStudentModal = (student) => {
      editingStudent.value = { ...student };
      showStudentModal.value = true;
    };

    /**
     * Saves student data (add new or update existing).
     * @param {Object} studentData The data of the student to save.
     */
    const saveStudent = async (payload) => {
      if (payload.studentId && payload.updatedMarks) {
        const studentToUpdate = localStudents.value.find(s => s.id === payload.studentId);

        if (studentToUpdate) {
          const lecturerId = 1; // **IMPORTANT: Get actual lecturer ID from your auth system**
          const courseId = selectedCourse.value.id;

          try {
            for (const componentKey in payload.updatedMarks) {
              const markEntry = payload.updatedMarks[componentKey];
              console.log(`Saving mark for student ${payload.studentId}, component ${componentKey}:`, markEntry);

              // Check if the mark entry has a component_id and student_mark defined
              if (markEntry.component_id && markEntry.student_mark !== undefined) {
                // Call the individual mark update API for each component
                await lecturersApi.updateStudentMarks(
                  {
                    lecturerId,
                    courseId,
                    studentId: payload.studentId,
                    componentId: markEntry.component_id,
                    mark: markEntry.student_mark
                  }
                );
                // Update the local student's marks object after successful API call
                studentToUpdate.marks[componentKey].student_mark = markEntry.student_mark;
              }
            }
            // Recalculate total and grade after all marks are updated and saved
            updateStudentTotal(studentToUpdate);
            console.log('All marks from modal saved and updated for student:', payload.studentId);
            toast.success('Marks saved successfully!');

          } catch (error) {
            console.error('Error saving marks from modal:', error);
            toast.error('Failed to save marks. Please try again.');
          }
        }
      }
      // This else block contained the logic for adding/editing student personal info.
      // Since StudentModal is now only for marks, this block is removed or handled elsewhere.
      /* else {
        // Old logic for adding/editing student personal info.
        // This part should be moved to a separate "Add/Edit Student Info Modal" if still needed.
      } */

      closeStudentModal(); // Close the modal regardless of success/failure
    };

    /**
     * Deletes a student.
     * @param {number} studentId The ID of the student to delete.
     */
    const deleteStudent = (studentId) => {
      if (confirm('Are you sure you want to delete this student?')) {
        // Logic to delete student (you'd typically call an API here)
        localStudents.value = localStudents.value.filter(s => s.id !== studentId);
      }
    };

    /**
     * Closes the add/edit student modal.
     */
    const closeStudentModal = () => {
      showStudentModal.value = false;
      editingStudent.value = null; // Clear editing student data
    };

    /**
     * Saves all modified marks (placeholder for API call).
     */
    const saveAllMarks = () => {
      // Here you would typically make an API call to save all marks
      // You'd iterate through localStudents.value and send updated marks to your backend.
      // Example: lecturersApi.saveStudentMarks(selectedCourse.value.id, localStudents.value);
      alert('All marks saved successfully!');
      emit('refresh-data'); // Notify parent component if data needs refreshing
    };

    /**
     * Exports the student marks data to a CSV file.
     */
    const exportToCSV = () => {
      if (!selectedCourse.value) {
        alert('Please select a course first.');
        return;
      }

      let csv = 'Name,Matric ID,';

      // Add component headers dynamically, including max marks for clarity
      assessmentComponents.value.forEach(component => {
        csv += `${component.component_name} (${component.max_mark} marks),`;
      });
      csv += 'Total Mark,Grade\n'; // Final columns

      // Add student data rows
      localStudents.value.forEach(student => {
        csv += `${student.name},${student.matricId},`;
        assessmentComponents.value.forEach(component => {
          const componentKey = component.component_name.toLowerCase().replace(/[\s-]/g, '');
          const markEntry = student.marks[componentKey];
          // Use student_mark if available, otherwise 0 or an empty string
          csv += `${markEntry ? (markEntry.student_mark !== null ? markEntry.student_mark : '') : ''},`;
        });
        csv += `${(student.totalMark || 0).toFixed(1)},${student.grade}\n`;
      });

      // Create and trigger download of the CSV file
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none';
      a.href = url;
      a.download = `${selectedCourse.value.course_code}_marks.csv`; // Use course_code for filename
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    };

    // Return reactive properties and methods for use in the template
    return {
      selectedCourse,
      searchQuery,
      showStudentModal,
      editingStudent,
      assessmentComponents,
      processedStudents,
      classAverage,
      passCount,
      failCount,
      onCourseSelected,
      updateStudentMark,
      openAddStudentModal,
      openEditStudentModal,
      saveStudent,
      deleteStudent,
      closeStudentModal,
      saveAllMarks,
      exportToCSV
    };
  }
};
</script>