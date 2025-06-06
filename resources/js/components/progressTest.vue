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
    </header>

    <!-- Closed notice -->
    <div v-if="isClosed" class="alert alert-warning text-center">
      Editing window has closed. You cannot modify scores anymore.
    </div>

    <!-- =========== Students × Skills table =========== -->
    <div v-if="ready" class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:60px">#</th>
            <th style="min-width:220px">Student</th>
            <!-- one column per skill -->
            <th v-for="skill in skills"
                :key="skill.pivot.id"
                class="text-center"
                style="min-width:120px">
              {{ skill.name }}<br />
              <small class="text-muted">( / {{ skill.pivot.progress_test_max }})</small>
            </th>
            <th class="text-center" style="width:100px">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(student, idx) in studentsList" :key="student.id">
            <td>{{ idx + 1 }}</td>
            <td>
              <strong>{{ student.name }}</strong><br />
              <small class="text-muted">
                <i class="fa fa-phone me-1" />{{ student.phone }}
              </small>
            </td>
            <!-- score input for each skill -->
            <td v-for="skill in skills"
                :key="skill.pivot.id">
              <input
                type="number"
                class="form-control form-control-sm text-center"
                v-model.number="student.scores[skill.pivot.id]"
                :max="skill.pivot.progress_test_max"
                min="0"
                :placeholder="`0-${skill.pivot.progress_test_max}`"
                :disabled="isClosed"
              />
            </td>
            <!-- status: has grades? -->
            <td class="text-center">
              <i v-if="student.hasGrades" class="fa fa-check text-success"></i>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Buttons -->
    <footer class="d-flex justify-content-between mt-4">
      <a
        class="btn btn-secondary btn-sm text-light"
        href="/instructor/courses?status=ongoing"
      >
        <i class="fa fa-arrow-left" /> Back
      </a>
      <button
        class="btn btn-primary"
        :disabled="isClosed"
        @click="submitProgressTest"
      >
        <i class="fa fa-save" /> Save Scores
      </button>
    </footer>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import instance from '../instance'
import toastr from 'toastr'

export default {
  name: 'ProgressTest',
  props: {
    progressTestId: { type: Number, required: true }
  },

  setup(props) {
    const globalLoading = ref(false)
    const progressTest = ref({ date: '', close_at: null })
    const skills = ref([])
    const studentsList = ref([])

    const isClosed = computed(() => {
        const raw = progressTest.value.close_at
        if (!raw) return false

        // Turn "2025-05-18 13:04:00" into "2025-05-18T13:04:00"
        const iso = raw.replace(' ', 'T')
        const closeDate = new Date(iso)

        // now() >= close_at?
        return Date.now() >= closeDate.getTime()
      });
    const ready = computed(
      () => skills.value.length > 0 && studentsList.value.length > 0
    )

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

        // load skills
        skills.value = pt.course.course_type.skills

        // build students with scores and hasGrades
        studentsList.value = pt.progress_test_students.map((rec) => {
          const base = rec.student
          const scores = {}
          let hasGrades = false

          skills.value.forEach((sk) => {
            const grade = rec.grades.find(
              (g) => g.course_type_skill_id === sk.pivot.id
            )
            if (grade) hasGrades = true
            scores[sk.pivot.id] = grade
              ? grade.progress_test_grade
              : null
          })

          return {
            id: base.id,
            name: base.name,
            phone: base.phone,
            scores,
            hasGrades
          }
        })
      } catch (e) {
        toastr.error('Failed to load progress test data')
      } finally {
        globalLoading.value = false
      }
    }

    const submitProgressTest = async () => {
      if (!ready.value || isClosed.value) return
      const payload = studentsList.value.map((st) => ({
        student_id: st.id,
        scores: st.scores
      }))

      try {
        await instance.put(
          `/progress-tests/${props.progressTestId}`,
          {
            date: progressTest.value.date,
            students: payload
          }
        )
        toastr.success('Scores saved successfully')
      } catch (e) {
        toastr.error('Error saving scores')
      }
    }

    onMounted(fetchData)

    return {
      globalLoading,
      progressTest,
      skills,
      studentsList,
      isClosed,
      ready,
      submitProgressTest
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
  max-width: 1080px;
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
</style>
