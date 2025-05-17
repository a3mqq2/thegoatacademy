<template>
  <div class="attendance-component">
    <!-- Global spinner -->
    <div v-if="globalLoading" class="global-spinner-overlay">
      <div class="spinner"></div>
    </div>

    <!-- Closed notice -->
    <div v-if="isClosed" class="alert alert-warning text-center">
      Editing window has closed. You cannot modify attendance anymore.
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
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(student, index) in course.students"
              :key="student.id"
              :class="{ disabled: !canModify(student) || isClosed }"
            >
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td class="text-center">
                <label class="switch m-0">
                  <input
                    type="checkbox"
                    v-model="student.attendancePresent"
                    :disabled="!canModify(student) || isClosed"
                  />
                  <span class="slider round"></span>
                </label>
              </td>
              <td class="text-center">
                <label class="switch m-0">
                  <input
                    type="checkbox"
                    v-model="student.homeworkSubmitted"
                    :disabled="!canModify(student) || isClosed"
                  />
                  <span class="slider round"></span>
                </label>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <button
      class="btn btn-primary submit-btn"
      @click="submitAttendance"
      :disabled="!anyModifiable || isClosed"
    >
      <i class="fa fa-save info-icon"></i> Save Attendance
    </button>

    <a
      href="/instructor/courses/?status=ongoing"
      class="btn btn-secondary mt-4 btn-sm text-light"
    >
      <i class="fa fa-arrow-left"></i> Back
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
  },
  setup(props) {
    const globalLoading = ref(false);
    const course = ref(null);
    const schedule = ref(null);

    // today as JS Date (midnight)
    const today = computed(() => {
      const d = new Date(props.date);
      d.setHours(0,0,0,0);
      return d;
    });

    // what weekday is today? 0=Sunday…6=Saturday
    const todayWeekday = computed(() => new Date(props.date).getDay());

    // six days ago at midnight
    const sixDaysAgo = computed(() => {
      const d = new Date(props.date);
      d.setDate(d.getDate() - 6);
      d.setHours(0,0,0,0);
      return d;
    });

    // is today the course’s configured “catch-up” weekday?
    const isProgressTestDay = computed(() => {
      return (
        course.value.progress_test_day == todayWeekday.value
      );
    });

    // can still “catch up” on a lecture if:
    // – lecture date is before today but strictly after sixDaysAgo
    // – today matches progress_test_day
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

    // Determine if the editing window has closed
    const isClosed = computed(() => {
      if (isCatchup.value) return false;
      const rawClose = schedule.value?.close_at;
      if (!rawClose) return false;
      // convert "YYYY-MM-DD HH:mm:ss" → ISO
      const closeMs = new Date(rawClose.replace(" ", "T")).getTime();
      return Date.now() >= closeMs;
    });

    const cannotMark = (student) => student.absencesCount >= 6;
    const canModify = (student) =>
      !["withdrawn", "excluded"].includes(student.pivot?.status);

    const anyModifiable = computed(() =>
      course.value?.students?.some(
        (st) => canModify(st) && !isClosed.value
      )
    );

    const submitAttendance = async () => {
      if (!anyModifiable.value) return;
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
        });
        toastr.success("Attendance has been saved successfully!");
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

        // map existing attendance
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
      canModify,
      cannotMark,
      anyModifiable,
      submitAttendance,
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
@keyframes spin { to { transform: rotate(360deg); } }
</style>
