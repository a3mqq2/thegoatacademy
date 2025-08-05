<template>
  <div class="progress-test-component">
    <!-- Global spinner -->
    <div v-if="globalLoading" class="global-spinner-overlay">
      <div class="spinner" />
    </div>

    <!-- Header -->
    <header class="text-center mb-4">
      <h3 class="mb-1">
        <i class="fa fa-pencil-alt me-1" /> Enter Progress-Test Scores
      </h3>
      <p class="text-muted mb-0">
        <i class="fa fa-calendar me-1" /> Date : {{ progressTest.date }}
      </p>
      <!-- Admin indicator -->
      <div v-if="isAdmin" class="mt-2">
        <span class="badge bg-warning text-dark">
          <i class="fa fa-shield-alt me-1" /> Admin Mode - Time Limit Bypassed
        </span>
      </div>
    </header>

    <!-- Closed notice (only show if not admin) -->
    <div v-if="isClosed && !isAdmin" class="alert alert-warning text-center">
      <i class="fa fa-lock me-1" />
      Editing window has closed. You cannot modify scores anymore.
    </div>

    <!-- Admin override notice (show if closed but admin) -->
    <div v-if="isClosed && isAdmin" class="alert alert-info text-center">
      <i class="fa fa-info-circle me-1" />
      Editing window has closed, but you can still modify scores as an administrator.
    </div>

    <!-- Attendance Controls -->
    <div v-if="ready && skills.length > 0" class="attendance-controls mb-3">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="d-flex align-items-center gap-3">
            <button 
              class="btn btn-outline-primary btn-sm"
              @click="toggleShowAbsent"
            >
              <i class="fa" :class="showAbsentStudents ? 'fa-eye-slash' : 'fa-eye'" />
              {{ showAbsentStudents ? 'Hide Absent' : 'Show Absent' }}
              <span class="badge bg-secondary ms-2">{{ absentStudentsCount }}</span>
            </button>
            
            <button 
              class="btn btn-outline-success btn-sm"
              @click="toggleShowPresent"
            >
              <i class="fa" :class="showPresentStudents ? 'fa-eye-slash' : 'fa-eye'" />
              {{ showPresentStudents ? 'Collapse Present' : 'Show Present' }}
              <span class="badge bg-success ms-2">{{ presentStudentsCount }}</span>
            </button>
          </div>
        </div>
        <div class="col-md-6 text-end">
          <small class="text-muted">
            Total: {{ studentsList.length }} students 
            ({{ presentStudentsCount }} present, {{ absentStudentsCount }} absent)
          </small>
        </div>
      </div>
    </div>

    <!-- Skills summary -->
    <div v-if="ready && skills.length > 0" class="alert alert-light mb-3">
      <h6><i class="fa fa-star me-1" />Progress Test Skills Summary:</h6>
      <div class="row">
        <div v-for="skill in skills" :key="skill.pivot.id" class="col-md-3 mb-2">
          <span class="badge bg-info me-1">{{ skill.name }}</span>
          <small class="text-muted">Max: {{ skill.pivot.progress_test_max }}</small>
        </div>
      </div>
      <hr class="my-2">
      <small class="text-muted">
        <strong>Total Max Score:</strong> {{ totalMaxScore }}
      </small>
    </div>

    <!-- No skills warning -->
    <div v-if="ready && skills.length === 0" class="alert alert-warning">
      <i class="fa fa-exclamation-triangle me-1" />
      No progress test skills found for this course type. Please contact administrator.
    </div>

    <!-- =========== Students × Skills table =========== -->
    <div v-if="ready && skills.length > 0" class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:60px">#</th>
            <th style="min-width:220px">Student</th>
            <th style="width:100px" class="text-center">Status</th>
            <!-- one column per progress skill only -->
            <th v-for="skill in skills"
                :key="skill.pivot.id"
                class="text-center"
                style="min-width:120px">
              {{ skill.name }}<br />
              <small class="text-muted">( / {{ skill.pivot.progress_test_max }})</small>
            </th>
            <th class="text-center" style="width:80px">Total</th>
            <th class="text-center" style="width:80px">%</th>
            <th class="text-center" style="width:100px">Grades</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(student, idx) in filteredStudentsList" 
              :key="student.id"
              :class="{ 'absent-row': student.status === 'absent' }">
            <td>{{ getStudentNumber(student, idx) }}</td>
            <td>
              <strong>{{ student.name }}</strong><br />
              <small class="text-muted">
                <i class="fa fa-phone me-1" />{{ student.phone }}
              </small>
            </td>
            <!-- Attendance Status -->
            <td class="text-center">
              <button
                class="btn btn-sm attendance-btn"
                :class="student.status === 'present' ? 'btn-success' : 'btn-danger'"
                @click="toggleStudentStatus(student)"
                :disabled="!canEdit"
                :title="`Click to mark as ${student.status === 'present' ? 'absent' : 'present'}`"
              >
                <i class="fa" :class="student.status === 'present' ? 'fa-check' : 'fa-times'" />
                {{ student.status === 'present' ? 'Present' : 'Absent' }}
              </button>
            </td>
            <!-- score input for each progress skill -->
            <td v-for="skill in skills"
                :key="skill.pivot.id">
              <input
                type="number"
                class="form-control form-control-sm text-center"
                v-model.number="student.scores[skill.pivot.id]"
                :max="skill.pivot.progress_test_max"
                min="0"
                step="0.01"
                :placeholder="`0-${skill.pivot.progress_test_max}`"
                :disabled="!canEdit || student.status === 'absent'"
                @input="updateStudentStats(student)"
                :class="{ 
                  'admin-override': isClosed && isAdmin && student.status === 'present',
                  'disabled-absent': student.status === 'absent'
                }"
              />
            </td>
            <!-- total score -->
            <td class="text-center">
              <strong :class="student.status === 'absent' ? 'text-muted' : getScoreClass(student.totalScore, totalMaxScore)">
                {{ student.status === 'absent' ? 'N/A' : student.totalScore.toFixed(1) }}
              </strong>
            </td>
            <!-- percentage -->
            <td class="text-center">
              <strong :class="student.status === 'absent' ? 'text-muted' : getScoreClass(student.percentage, 100)">
                {{ student.status === 'absent' ? 'N/A' : student.percentage.toFixed(1) + '%' }}
              </strong>
            </td>
            <!-- status: has grades? -->
            <td class="text-center">
              <span v-if="student.status === 'absent'" class="badge bg-secondary">
                <i class="fa fa-user-times" /> Absent
              </span>
              <span v-else-if="student.hasGrades" class="badge bg-success">
                <i class="fa fa-check" /> Saved
              </span>
              <span v-else class="badge bg-warning">
                <i class="fa fa-clock" /> Pending
              </span>
            </td>
          </tr>
        </tbody>
        
        <!-- Summary row -->
        <tfoot v-if="presentStudentsList.length > 0">
          <tr class="table-info">
            <td colspan="3"><strong>Present Students Average</strong></td>
            <td v-for="skill in skills" :key="skill.pivot.id" class="text-center">
              <strong>{{ getSkillAverage(skill.pivot.id).toFixed(1) }}</strong>
            </td>
            <td class="text-center">
              <strong>{{ classAverageTotal.toFixed(1) }}</strong>
            </td>
            <td class="text-center">
              <strong>{{ classAveragePercentage.toFixed(1) }}%</strong>
            </td>
            <td class="text-center">
              <small>{{ passCount }}/{{ presentStudentsList.length }} Pass</small>
            </td>
            <td v-if="isAdmin"></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Buttons -->
    <footer class="d-flex justify-content-between mt-4">
      <a
        v-if="!isAdmin"
        class="btn btn-secondary btn-sm text-light"
        href="/instructor/courses?status=ongoing"
      >
        <i class="fa fa-arrow-left" /> Back
      </a>

      <a
        v-else
        class="btn btn-secondary btn-sm text-light"
        href="/admin/courses?status=ongoing"
      >
        <i class="fa fa-arrow-left" /> Back
      </a>

      <button
        class="btn btn-primary"
        :disabled="!canEdit || skills.length === 0"
        @click="submitProgressTest"
        :class="{ 'btn-warning': isClosed && isAdmin }"
      >
        <i class="fa fa-save" /> 
        {{ isClosed && isAdmin ? 'Save Scores (Admin Override)' : 'Save Scores' }}
      </button>
    </footer>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="modal-overlay"
      @click="cancelDelete"
    >
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa fa-exclamation-triangle text-warning me-2" />
            Confirm Delete Student
          </h5>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to remove <strong>{{ studentToDelete?.name }}</strong> from this progress test?</p>
          <p class="text-muted small">
            <i class="fa fa-info-circle me-1" />
            This action will only remove the student from this progress test. The student record will remain intact.
          </p>
        </div>
        <div class="modal-footer">
          <button
            class="btn btn-secondary"
            @click="cancelDelete"
          >
            <i class="fa fa-times me-1" />
            Cancel
          </button>
          <button
            class="btn btn-danger"
            @click="deleteStudent"
            :disabled="deletingStudent"
          >
            <i class="fa fa-trash me-1" />
            {{ deletingStudent ? 'Removing...' : 'Remove Student' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import instance from '../instance'
import toastr from 'toastr'

export default {
  name: 'ProgressTest',
  props: {
    progressTestId: { type: Number, required: true },
    isAdmin: { type: Boolean, default: false }
  },

  setup(props) {
    const globalLoading = ref(false)
    const progressTest = ref({ date: '', close_at: null })
    const skills = ref([])
    const studentsList = ref([])
    
    // Attendance visibility controls
    const showAbsentStudents = ref(false)
    const showPresentStudents = ref(true)
    
    // Delete modal state
    const showDeleteModal = ref(false)
    const studentToDelete = ref(null)
    const deletingStudent = ref(false)

    const isClosed = computed(() => {
      const raw = progressTest.value.close_at
      if (!raw) return false

      // Turn "2025-05-18 13:04:00" into "2025-05-18T13:04:00"
      const iso = raw.replace(' ', 'T')
      const closeDate = new Date(iso)

      // now() >= close_at?
      return Date.now() >= closeDate.getTime()
    })

    // New computed property to determine if editing is allowed
    const canEdit = computed(() => {
      return !isClosed.value || props.isAdmin
    })

    const ready = computed(
      () => skills.value.length >= 0 && studentsList.value.length > 0
    )

    // Attendance computed properties
    const presentStudentsList = computed(() => {
      return studentsList.value.filter(student => student.status === 'present')
    })

    const absentStudentsList = computed(() => {
      return studentsList.value.filter(student => student.status === 'absent')
    })

    const presentStudentsCount = computed(() => presentStudentsList.value.length)
    const absentStudentsCount = computed(() => absentStudentsList.value.length)

    // Filtered students list based on visibility settings
    const filteredStudentsList = computed(() => {
      let filtered = []
      
      if (showPresentStudents.value) {
        filtered = [...filtered, ...presentStudentsList.value]
      }
      
      if (showAbsentStudents.value) {
        filtered = [...filtered, ...absentStudentsList.value]
      }
      
      // Sort by name for consistency
      return filtered.sort((a, b) => a.name.localeCompare(b.name))
    })

    const totalMaxScore = computed(() => {
      return skills.value.reduce((sum, skill) => {
        return sum + (skill.pivot.progress_test_max || 0)
      }, 0)
    })

    const classAverageTotal = computed(() => {
      if (presentStudentsList.value.length === 0) return 0
      return presentStudentsList.value.reduce((sum, student) => {
        return sum + student.totalScore
      }, 0) / presentStudentsList.value.length
    })

    const classAveragePercentage = computed(() => {
      if (totalMaxScore.value === 0) return 0
      return (classAverageTotal.value / totalMaxScore.value) * 100
    })

    const passCount = computed(() => {
      return presentStudentsList.value.filter(student => student.percentage >= 50).length
    })

    const fetchData = async () => {
      globalLoading.value = true
      try {
        const { data } = await instance.get(
          `/progress-tests/${props.progressTestId}`
        )
        const pt = data.progressTest
        progressTest.value = {
          date: pt.date,
          close_at: pt.close_at
        }

        // load ONLY progress skills
        skills.value = pt.course.course_type.progress_skills || []

        // build students with scores and hasGrades
        studentsList.value = pt.progress_test_students.map((rec) => {
          const base = rec.student
          const scores = {}
          let hasGrades = false

          skills.value.forEach((sk) => {
            const grade = rec.grades.find(
              (g) => g.course_type_skill_id == sk.pivot.id
            )
            if (grade) hasGrades = true
            scores[sk.pivot.id] = grade
              ? grade.progress_test_grade
              : 0
          })

          const student = {
            id: base.id,
            name: base.name,
            phone: base.phone,
            scores,
            hasGrades,
            status: rec.status || 'present', // Default to present if no status
            totalScore: 0,
            percentage: 0
          }

          updateStudentStats(student)
          return student
        })
      } catch (e) {
        console.error('Error loading progress test:', e)
        toastr.error('Failed to load progress test data')
      } finally {
        globalLoading.value = false
      }
    }

    const updateStudentStats = (student) => {
      if (student.status === 'absent') {
        student.totalScore = 0
        student.percentage = 0
        return
      }

      student.totalScore = Object.values(student.scores).reduce((sum, score) => {
        return sum + (parseFloat(score) || 0)
      }, 0)
      
      student.percentage = totalMaxScore.value > 0 
        ? (student.totalScore / totalMaxScore.value) * 100 
        : 0
    }

    const getSkillAverage = (skillPivotId) => {
      if (presentStudentsList.value.length === 0) return 0
      return presentStudentsList.value.reduce((sum, student) => {
        return sum + (parseFloat(student.scores[skillPivotId]) || 0)
      }, 0) / presentStudentsList.value.length
    }

    const getScoreClass = (score, maxScore) => {
      const percentage = (score / maxScore) * 100
      if (percentage >= 80) return 'text-success'
      if (percentage >= 60) return 'text-warning'
      if (percentage >= 50) return 'text-info'
      return 'text-danger'
    }

    const getStudentNumber = (student, filteredIndex) => {
      // Find the original index in the full students list
      const originalIndex = studentsList.value.findIndex(s => s.id === student.id)
      return originalIndex + 1
    }

    // Attendance control functions
    const toggleShowAbsent = () => {
      showAbsentStudents.value = !showAbsentStudents.value
    }

    const toggleShowPresent = () => {
      showPresentStudents.value = !showPresentStudents.value
    }

    const toggleStudentStatus = async (student) => {
      if (!canEdit.value) return

      const newStatus = student.status === 'present' ? 'absent' : 'present'
      const oldStatus = student.status

      try {
        // Optimistically update the UI
        student.status = newStatus
        updateStudentStats(student)

        // Send API request to update status
        await instance.put(
          `/progress-tests/${props.progressTestId}/students/${student.id}/status`,
          { status: newStatus }
        )

        toastr.success(`${student.name} marked as ${newStatus}`)
        
      } catch (e) {
        // Revert on error
        student.status = oldStatus
        updateStudentStats(student)
        console.error('Error updating student status:', e)
        toastr.error('Error updating student attendance status')
      }
    }

    const submitProgressTest = async () => {
      if (!ready.value || !canEdit.value || skills.value.length === 0) return
      
      const payload = studentsList.value.map((st) => ({
        student_id: st.id,
        scores: st.scores,
        status: st.status
      }))

      try {
        await instance.put(
          `/progress-tests/${props.progressTestId}`,
          {
            date: progressTest.value.date,
            students: payload,
            admin_override: props.isAdmin && isClosed.value // Flag for backend
          }
        )

        const message = isClosed.value && props.isAdmin 
          ? 'Progress test scores saved successfully (Admin Override)'
          : 'Progress test scores saved successfully'
        
        toastr.success(message)
        
        // Refresh data to update status
        await fetchData()
      } catch (e) {
        console.error('Error saving scores:', e)
        toastr.error('Error saving progress test scores')
      }
    }

    // Delete student functions
    const confirmDeleteStudent = (student) => {
      studentToDelete.value = student
      showDeleteModal.value = true
    }

    const cancelDelete = () => {
      showDeleteModal.value = false
      studentToDelete.value = null
    }

    const deleteStudent = async () => {
      if (!studentToDelete.value || deletingStudent.value) return
      
      deletingStudent.value = true
      
      try {
        // API call to remove student from progress test
        await instance.delete(
          `/progress-tests/${props.progressTestId}/students/${studentToDelete.value.id}`
        )
        
        // Remove student from local list
        const index = studentsList.value.findIndex(s => s.id === studentToDelete.value.id)
        if (index !== -1) {
          studentsList.value.splice(index, 1)
        }
        
        toastr.success(`${studentToDelete.value.name} has been removed from the progress test`)
        
        // Close modal
        showDeleteModal.value = false
        studentToDelete.value = null
        
      } catch (e) {
        console.error('Error deleting student:', e)
        toastr.error('Error removing student from progress test')
      } finally {
        deletingStudent.value = false
      }
    }

    onMounted(fetchData)

    return {
      globalLoading,
      progressTest,
      skills,
      studentsList,
      isClosed,
      canEdit,
      ready,
      totalMaxScore,
      classAverageTotal,
      classAveragePercentage,
      passCount,
      updateStudentStats,
      getSkillAverage,
      getScoreClass,
      getStudentNumber,
      submitProgressTest,
      isAdmin: props.isAdmin,
      // Attendance functionality
      showAbsentStudents,
      showPresentStudents,
      presentStudentsList,
      absentStudentsList,
      presentStudentsCount,
      absentStudentsCount,
      filteredStudentsList,
      toggleShowAbsent,
      toggleShowPresent,
      toggleStudentStatus,
      // Delete functionality
      showDeleteModal,
      studentToDelete,
      deletingStudent,
      confirmDeleteStudent,
      cancelDelete,
      deleteStudent
    }
  }
}
</script>

<style scoped>
:root { --primary: #6f42c1; --shadow: 0 6px 14px rgba(0, 0, 0, .08) }
.progress-test-component {
  background: #fff;
  border-radius: 12px;
  box-shadow: var(--shadow);
  padding: 22px;
  max-width: 1200px;
  margin: auto;
}
.global-spinner-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, .7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.spinner {
  width: 28px;
  height: 28px;
  border: 4px solid #bbb;
  border-top: 4px solid #333;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg) } }
.table th,
.table td { vertical-align: middle }
.alert {
  margin-bottom: 1rem;
}
.badge {
  font-size: 0.75em;
}

/* Attendance controls styling */
.attendance-controls {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e9ecef;
}

/* Attendance button styling */
.attendance-btn {
  min-width: 80px;
  font-size: 0.8rem;
}

/* Absent row styling */
.absent-row {
  background-color: #fff5f5;
  opacity: 0.7;
}

.absent-row:hover {
  background-color: #fee;
}

/* Disabled input for absent students */
.disabled-absent {
  background-color: #f8f9fa !important;
  opacity: 0.6;
  cursor: not-allowed;
}

/* Admin override styling */
.admin-override {
  border: 2px solid #ffc107 !important;
  background-color: #fff3cd;
}

.admin-override:focus {
  border-color: #ff6b35 !important;
  box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.btn-warning {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background-color: #ffb300;
  border-color: #ffb300;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 20px 24px 0 24px;
  border-bottom: 1px solid #e9ecef;
}

.modal-title {
  margin: 0 0 16px 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-body {
  padding: 20px 24px;
}

.modal-footer {
  padding: 0 24px 20px 24px;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
  border-color: #bd2130;
}

.btn-danger:disabled {
  background-color: #dc3545;
  border-color: #dc3545;
  opacity: 0.65;
}

/* Button group styling */
.btn-group-vertical .btn {
  border-radius: 4px !important;
}
</style>