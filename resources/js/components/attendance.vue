<!-- resources/js/components/Attendance.vue -->
<template>
  <div class="attendance-component">
    <div v-if="globalLoading" class="global-spinner-overlay">
      <div class="spinner"></div>
    </div>

    <div class="attendance-header text-left">
      <p class="attendance-date" v-if="schedule">
        <i class="fa fa-calendar info-icon"></i> Date: {{ schedule.date }}
      </p>
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
              :class="{ disabled: !canModify(student) || cannotMark(student) }"
            >
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td class="text-center">
                <label class="switch m-0">
                  <input
                    type="checkbox"
                    v-model="student.attendancePresent"
                    :disabled="!canModify(student)"
                  />
                  <span class="slider round"></span>
                </label>
              </td>
              <td class="text-center">
                <label class="switch m-0">
                  <input
                    type="checkbox"
                    v-model="student.homeworkSubmitted"
                    :disabled="!canModify(student)"
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
      :disabled="!anyModifiable"
    >
      <i class="fa fa-save info-icon"></i> Save Attendance
    </button>

    <a
      href="/instructor/courses/?status=ongoing"
      class="btn btn-secondary mt-4 btn-sm text-light"
    >
      <i class="fa fa-arrow-left"></i> back
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
    date: { type: String, default: () => new Date().toISOString().substring(0, 10) },
    scheduleId: { type: Number, default: null },
  },
  setup(props) {
    const $toastr = toastr;
    const globalLoading = ref(false);
    const course = ref(null);
    const schedule = ref(null);

    const cannotMark = (student) => student.absencesCount >= 6;
    const canModify = (student) => !["withdrawn", "excluded"].includes(student.pivot?.status);

    const anyModifiable = computed(() =>
      course.value?.students?.some((student) => canModify(student))
    );

    const submitAttendance = async () => {
      if (!course.value?.students) return;

      const payload = course.value.students.map((student) => ({
        student_id: student.id,
        attendance: student.attendancePresent ? "present" : "absent",
        notes: student.notes || "",
        homework_submitted: student.homeworkSubmitted ? 1 : 0,
        existing_id: student.existing_id || null,
      }));

      try {
        await instance.post(`/courses/${props.courseId}/attendance`, {
          course_schedule_id: schedule.value.id,
          students: payload,
        });
        $toastr.success("Attendance has been saved successfully!");
      } catch (error) {
        console.error("Error saving attendance", error);
        $toastr.error("Error saving attendance. Please try again later.");
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
        if (schedule.value?.attendances?.length) {
          schedule.value.attendances.forEach((attendance) => {
            attendanceMap[attendance.student_id] = attendance;
          });
        }

        course.value.students = course.value.students.map((student) => {
          const existing = attendanceMap[student.id] || {};
          return {
            id: student.id,
            name: student.name,
            phone: student.phone,
            absencesCount: student.absencesCount ?? 0,
            homeworkSubmitted: existing.homework_submitted !== 0,
            attendancePresent: existing.attendance === "present",
            notes: existing.notes ?? "",
            existing_id: existing.id || null,
            pivot: student.pivot || {},
          };
        });
      } catch (error) {
        console.error("Error fetching data", error);
        $toastr.error("Failed to load attendance data.");
      } finally {
        globalLoading.value = false;
      }
    };

    onMounted(fetchData);

    return {
      course,
      schedule,
      globalLoading,
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

.attendance-header {
  margin-bottom: 20px;
}

.attendance-date {
  font-size: 1.1rem;
  color: var(--secondary-color);
}

.attendance-table th,
.attendance-table td {
  vertical-align: middle !important;
}

.attendance-table .disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.attendance-table tbody tr:hover:not(.disabled) {
  background: #f8f9fa;
  transition: background var(--transition-speed);
}

.submit-btn {
  display: block;
  margin: 20px auto 0;
  font-size: 1.1rem;
  padding: 10px 20px;
}

.info-icon {
  margin-right: 5px;
}

.global-spinner-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
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

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
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
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
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
</style>
