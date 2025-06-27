<template>
  <div class="attendance-component">
    <!-- Global spinner -->
    <div v-if="globalLoading" class="global-spinner-overlay">
      <div class="spinner"></div>
    </div>

    <!-- Header with admin indicator -->
    <header class="text-center mb-4" v-if="isAdmin || isClosed">
      <div v-if="isAdmin" class="mb-2">
        <span class="badge bg-warning text-dark">
          <i class="fa fa-shield-alt me-1" /> Admin Mode - Time Limit Bypassed
        </span>
      </div>
    </header>

    <!-- Closed notice (only show if not admin) -->
    <div v-if="isClosed && !isAdmin" class="alert alert-warning text-center">
      <i class="fa fa-lock me-1" />
      Editing window has closed. You cannot modify attendance anymore.
    </div>

    <!-- Admin override notice (show if closed but admin) -->
    <div v-if="isClosed && isAdmin" class="alert alert-info text-center">
      <i class="fa fa-info-circle me-1" />
      Editing window has closed, but you can still modify attendance as an administrator.
    </div>

    <div v-if="course && course.students">
      <div class="table-responsive">
        <table class="table attendance-table table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Student Name</th>
              <th class="text-center">Is Attend</th>
              <th class="text-center">Homework Submitted</th>
              <th class="text-center">Notes</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(student, index) in course.students"
              :key="student.id"
              :class="{ 
                disabled: !canModify(student) || (!canEdit && !isAdmin),
                'admin-override-row': isClosed && isAdmin && canModify(student)
              }"
            >
              <td>{{ index + 1 }}</td>
              <td>
                {{ student.name }}
                <div v-if="isClosed && isAdmin && canModify(student)" class="admin-indicator">
                  <small class="text-warning">
                    <i class="fa fa-unlock-alt"></i> Admin Override
                  </small>
                </div>
              </td>
              <td class="text-center">
                <label class="switch m-0">
                  <input
                    type="checkbox"
                    v-model="student.attendancePresent"
                    :disabled="!canModify(student) || !canEdit"
                    :class="{ 'admin-override': isClosed && isAdmin }"
                  />
                  <span class="slider round" :class="{ 'admin-override-slider': isClosed && isAdmin }"></span>
                </label>
              </td>
              <td class="text-center">
                <label class="switch m-0">
                  <input
                    type="checkbox"
                    v-model="student.homeworkSubmitted"
                    :disabled="!canModify(student) || !canEdit"
                    :class="{ 'admin-override': isClosed && isAdmin }"
                  />
                  <span class="slider round" :class="{ 'admin-override-slider': isClosed && isAdmin }"></span>
                </label>
              </td>
              <td>
                <input
                  type="text"
                  v-model="student.notes"
                  class="form-control form-control-sm"
                  :disabled="!canModify(student) || !canEdit"
                  :class="{ 'admin-override': isClosed && isAdmin }"
                  placeholder="Notes..."
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <button
      class="btn btn-primary submit-btn"
      @click="submitAttendance"
      :disabled="!anyModifiable || !canEdit"
      :class="{ 'btn-warning': isClosed && isAdmin }"
    >
      <i class="fa fa-save info-icon"></i> 
      {{ isClosed && isAdmin ? 'Save Attendance (Admin Override)' : 'Save Attendance' }}
    </button>

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
    
  </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import instance from "../instance";
import toastr from "toastr";

export default {
  name: "Attendance",
  props: {
    courseId: { type: Number, required: true },
    date: {
      type: String,
      default: () => new Date().toISOString().substring(0, 10),
    },
    scheduleId: { type: Number, default: null },
    isAdmin: { type: Boolean, default: false }
  },
  setup(props) {
    const globalLoading = ref(false);
    const course = ref(null);
    const schedule = ref(null);

    const today = computed(() => {
      const d = new Date(props.date);
      d.setHours(0, 0, 0, 0);
      return d;
    });

    const todayWeekday = computed(() => new Date(props.date).getDay());

    const sixDaysAgo = computed(() => {
      const d = new Date(props.date);
      d.setDate(d.getDate() - 6);
      d.setHours(0, 0, 0, 0);
      return d;
    });

    const isProgressTestDay = computed(() => {
      return course.value.progress_test_day == todayWeekday.value;
    });

    const isCatchup = computed(() => {
      if (!schedule.value) return false;
      const lec = new Date(schedule.value.date);
      return (
        lec < today.value &&
        lec.getTime() > sixDaysAgo.value.getTime() &&
        isProgressTestDay.value &&
        !schedule.value.attendance_taken_at
      );
    });

    const isClosed = computed(() => {
      if (isCatchup.value) return false;
      const rawClose = schedule.value?.close_at;
      if (!rawClose) return false;
      const closeMs = new Date(rawClose.replace(" ", "T")).getTime();
      return Date.now() >= closeMs;
    });

    // New computed property to determine if editing is allowed
    const canEdit = computed(() => {
      return !isClosed.value || props.isAdmin;
    });

    const cannotMark = (student) => student.absencesCount >= 6;
    const canModify = (student) =>
      !["withdrawn", "excluded"].includes(student.pivot?.status);

    const anyModifiable = computed(() =>
      course.value?.students?.some(
        (st) => canModify(st) && canEdit.value
      )
    );

    const submitAttendance = async () => {
      if (!anyModifiable.value || !canEdit.value) return;
      
      const payload = course.value.students.map((st) => ({
        student_id: st.id,
        attendance: st.attendancePresent ? "present" : "absent",
        notes: st.notes || "",
        homework_submitted: st.homeworkSubmitted ? 1 : 0,
        existing_id: st.existing_id || null,
      }));

      try {
        await instance.post(`/courses/${props.courseId}/attendance`, {
          course_schedule_id: schedule.value.id,
          students: payload,
          admin_override: props.isAdmin && isClosed.value // Flag for backend
        });
        
        const message = isClosed.value && props.isAdmin 
          ? 'Attendance has been saved successfully (Admin Override)!'
          : 'Attendance has been saved successfully!';
        
        toastr.success(message);
      } catch (error) {
        console.error("Error saving attendance", error);
        toastr.error("Error saving attendance. Please try again later.");
      }
    };

    const fetchData = async () => {
      globalLoading.value = true;
      try {
        const { data } = await instance.get(
          `/courses/${props.courseId}?schedule_id=${props.scheduleId}`
        );
        course.value = data.course;
        schedule.value = data.schedule;

        const attendanceMap = {};
        (schedule.value.attendances || []).forEach((att) => {
          attendanceMap[att.student_id] = att;
        });

        course.value.students = course.value.students.map((st) => {
          const ex = attendanceMap[st.id] || {};
          return {
            id: st.id,
            name: st.name,
            phone: st.phone,
            absencesCount: st.absencesCount ?? 0,
            attendancePresent: ex.attendance === "present",
            homeworkSubmitted: ex.homework_submitted === 1,
            notes: ex.notes || "",
            existing_id: ex.id || null,
            pivot: st.pivot || {},
          };
        });
      } catch (error) {
        console.error("Error fetching data", error);
        toastr.error("Failed to load attendance data.");
      } finally {
        globalLoading.value = false;
      }
    };

    onMounted(fetchData);

    return {
      globalLoading,
      course,
      schedule,
      isClosed,
      canEdit,
      canModify,
      cannotMark,
      anyModifiable,
      submitAttendance,
      isAdmin: props.isAdmin
    };
  },
};
</script>

<style scoped>
:root {
  --primary-color: #6f42c1;
  --secondary-color: #007bff;
  --bg-color: #f0f2f5;
  --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  --transition-speed: 0.3s;
}

.attendance-component {
  padding: 20px;
  background: #fff;
  border-radius: 15px;
  box-shadow: var(--card-shadow);
  max-width: 1000px;
  margin: 0 auto;
}

.attendance-table th,
.attendance-table td {
  vertical-align: middle !important;
}

.attendance-table .disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.admin-override-row {
  background-color: #fff3cd;
  border: 1px solid #ffc107;
}

.admin-indicator {
  margin-top: 2px;
}

.submit-btn {
  display: block;
  margin: 20px auto 0;
  font-size: 1.1rem;
  padding: 10px 20px;
}

.global-spinner-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.7);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.spinner {
  width: 25px;
  height: 25px;
  border: 4px solid #bbb;
  border-top: 4px solid #333;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px; width: 18px;
  left: 3px; bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}
.switch input:checked + .slider {
  background-color: var(--primary-color);
}
.switch input:checked + .slider:before {
  transform: translateX(26px);
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

.admin-override-slider {
  border: 2px solid #ffc107;
  background-color: #fff3cd !important;
}

.admin-override-slider:before {
  background-color: #ffc107 !important;
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

.badge {
  font-size: 0.75em;
}

@keyframes spin { 
  to { transform: rotate(360deg); } 
}
</style>