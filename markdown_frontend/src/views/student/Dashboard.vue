<template>
  <div class="container">
    <!-- Welcome Section -->
    <WelcomeCard :student="student" />

    <!-- Quick Stats -->
    <div class="quick-stats">
      <QuickStats :stats="stats" />
    </div>

    <!-- Courses Section -->
    <CourseOverview :courses="courses" />

    <!-- Recent Updates -->
    <RecentUpdates :updates="updates" />
  </div>
</template>

<script>
import WelcomeCard from '../../components/student/dashboard/WelcomeCard.vue';
import QuickStats from '../../components/student/dashboard/QuickStats.vue';
import CourseOverview from '../../components/student/dashboard/CourseOverview.vue';
import RecentUpdates from '../../components/student/dashboard/RecentUpdates.vue';

export default {
  name: 'Dashboard',
  components: {
    WelcomeCard,
    QuickStats,
    CourseOverview,
    RecentUpdates
  },
  data() {
    return {
      student: {
        name: 'John Smith',
        id: 'A20EC0234',
        program: 'Computer Science',
        year: 'Year 3',
        semester: 'Semester 2'
      },
      stats: [
        { icon: '🎯', value: '3.45', label: 'Current GPA' },
        { icon: '📚', value: '6', label: 'Enrolled Courses' },
        { icon: '📈', value: '78.5%', label: 'Average Score' },
      ],
      courses: [
        {
          code: 'SECJ 3483', title: 'Web Technology', grade: 78.5, rank: '12th', progress: 85
        },
        {
          code: 'SECJ 3453', title: 'Database Systems', grade: 82.3, rank: '8th', progress: 75
        },
        {
          code: 'SECJ 3543', title: 'Software Engineering', grade: 76.8, rank: '15th', progress: 70
        },
        {
          code: 'SECJ 3443', title: 'Data Structures', grade: 81.2, rank: '9th', progress: 80
        },
        {
          code: 'SECJ 3463', title: 'Computer Networks', grade: 79.1, rank: '11th', progress: 65
        },
        {
          code: 'SECJ 3473', title: 'Operating Systems', grade: 77.6, rank: '13th', progress: 60
        }
      ],
      updates: [
        {
          icon: '📊', title: 'New marks uploaded for Web Technology', desc: 'Assignment 2 marks have been updated', time: '2 hours ago'
        },
        {
          icon: '📝', title: 'Remark request approved', desc: 'Your Database Systems Quiz remark has been processed', time: '1 day ago'
        },
        {
          icon: '🎯', title: 'Rank improvement', desc: "You've moved up 2 positions in Software Engineering", time: '3 days ago'
        },
        {
          icon: '📈', title: 'Performance milestone reached', desc: 'Your GPA has improved to 3.45', time: '1 week ago'
        }
      ]
    };
  }
};
</script>

<style scoped>
.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 30px;
}
.quick-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}
</style>
