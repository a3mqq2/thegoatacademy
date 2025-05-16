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

    <!-- =========== Students × Skills table =========== -->
    <div v-if="ready" class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:60px">#</th>
            <th style="min-width:220px">Student</th>

            <!-- one column per skill -->
            <th v-for="skill in skills"
                :key="skill.id"
                class="text-center"
                style="min-width:120px">
              {{ skill.name }}<br />
              <small class="text-muted">( / {{ skill.pivot.progress_test_max }})</small>
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(student, idx) in course.students" :key="student.id">
            <td>{{ idx + 1 }}</td>
            <td>
              <strong>{{ student.name }}</strong><br />
              <small class="text-muted"><i class="fa fa-phone me-1" />{{ student.phone }}</small>
            </td>

            <!-- score input for each skill -->
            <td v-for="skill in skills"
                :key="skill.id">
              <input type="number"
                     class="form-control form-control-sm text-center"
                     v-model.number="student.scores[skill.id]"
                     :max="skill.pivot.progress_test_max"
                     min="0"
                     :placeholder="`0-${skill.pivot.progress_test_max}`" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Buttons -->
    <footer class="d-flex justify-content-between mt-4">
      <a class="btn btn-secondary btn-sm text-light"
         href="/instructor/courses?status=ongoing">
        <i class="fa fa-arrow-left" /> Back
      </a>

      <button class="btn btn-primary"
              :disabled="!ready"
              @click="submitProgressTest">
        <i class="fa fa-save" /> Save Scores
      </button>
    </footer>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import instance                      from '../instance'
import toastr                        from 'toastr'

export default {
  name : 'ProgressTest',
  props: { courseId: { type: Number, required: true } },

  setup (props) {
    const globalLoading = ref(false)
    const course        = ref(null)
    const progressTest  = ref({ date: new Date().toISOString().slice(0, 10) })

    /* ========= derived ========= */
    const skills = computed(() => course.value?.course_type?.skills ?? [])
    const ready  = computed(() => course.value && course.value.students?.length && skills.value.length)

    /* ========= fetch ========= */
    const fetchData = async () => {
      globalLoading.value = true
      try {
        const { data } = await instance.get(`/courses/${props.courseId}?progress_test_id=true`)
        course.value = data.course

        /* initialise scores object for each student */
        const skillIds = skills.value.map(s => s.id)
        course.value.students = course.value.students.map(st => {
          const scores = {}
          skillIds.forEach(id => { scores[id] = null })
          return { ...st, scores }
        })
      } catch (e) {
        toastr.error('Failed to load course data')
      } finally {
        globalLoading.value = false
      }
    }

    /* ========= submit ========= */
    const submitProgressTest = async () => {
      if (!ready.value) return

      const payload = course.value.students.map(st => ({
        student_id: st.id,
        scores    : st.scores   // { skillId : score }
      }))

      try {
        await instance.post(`/courses/${props.courseId}/progress-tests`, {
          date    : progressTest.value.date,
          students: payload
        })
        toastr.success('Scores saved successfully')
        setTimeout(() =>
          window.location.href = `/instructor/courses/${props.courseId}/show`, 600)
      } catch (e) {
        toastr.error('Error saving scores')
      }
    }

    onMounted(fetchData)
    return { course, skills, progressTest, globalLoading, ready, submitProgressTest }
  }
}
</script>

<style scoped>
:root{--primary:#6f42c1;--shadow:0 6px 14px rgba(0,0,0,.08)}
.progress-test-component{background:#fff;border-radius:12px;box-shadow:var(--shadow);padding:22px;max-width:1080px;margin:auto}
.info-icon{margin-right:4px}
.global-spinner-overlay{position:fixed;inset:0;background:rgba(255,255,255,.7);display:flex;align-items:center;justify-content:center;z-index:9999}
.spinner{width:28px;height:28px;border:4px solid #bbb;border-top:4px solid #333;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.table th,.table td{vertical-align:middle}
</style>
