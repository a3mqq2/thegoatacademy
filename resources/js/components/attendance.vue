<template>
  <div class="attendance-component">
    <!-- Global spinner -->
    <div v-if="globalLoading" class="global-spinner-overlay">
      <div class="spinner"></div>
    </div>

    <!-- Header with admin indicator -->
    <header class="text-center mb-4" v-if="isAdmin">
      <div class="mb-2">
        <span class="badge bg-success text-white">
          <i class="fa fa-shield-alt me-1" /> Admin Mode - Unlimited Access
        </span>
      </div>
    </header>

    <!-- Debug info (remove in production) -->
    <div v-if="isAdmin" class="alert alert-secondary">
      <strong>Debug Info:</strong><br>
      isAdmin: {{ isAdmin }}<br>
      isClosed: {{ isClosed }}<br>
      canEdit: {{ canEdit }}
    </div>

    <!-- Closed notice (only show if not admin) -->
    <div v-if="isClosed && !isAdmin" class="alert alert-warning text-center">
      <i class="fa fa-lock me-1" />
      Editing window has closed. You cannot modify attendance anymore.
    </div>

    <!-- Admin unlimited access notice -->
    <div v-if="isAdmin && isClosed" class="alert alert-info text-center">
      <i class="fa fa-unlock-alt me-1" />
      Session editing window is normally closed, but you have unlimited admin access.
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
                disabled: !canModify(student) || (!isAdmin && !canEdit),
                'admin-override-row': isAdmin && isClosed
              }"
            >
              <td>{{ index + 1 }}</td>
              <td>
                {{ student.name }}
                <div v-if="isAdmin && isClosed" class="admin-indicator">
                  <small class="text-success">
                    <i class="fa fa-unlock-alt"></i> Admin Access
                  </small>
                </div>
              </td>
              <td class="text-center">
                <label class="switch m-0">
                  <input
                    type="checkbox"
                    v-model="student.attendancePresent"
                    :disabled="!canModify(student) || (!isAdmin && !canEdit)"
                    :class="{ 'admin-override': isAdmin && isClosed }"
                  />
                  <span class="slider round" :class="{ 'admin-override-slider': isAdmin && isClosed }"></span>
                </label>
              </td>
              <td class="text-center">
                <label class="switch m-0">
                  <input
                    type="checkbox"
                    v-model="student.homeworkSubmitted"
                    :disabled="!canModify(student) || (!isAdmin && !canEdit)"
                    :class="{ 'admin-override': isAdmin && isClosed }"
                  />
                  <span class="slider round" :class="{ 'admin-override-slider': isAdmin && isClosed }"></span>
                </label>
              </td>
              <td>
                <input
                  type="text"
                  v-model="student.notes"
                  class="form-control form-control-sm"
                  :disabled="!canModify(student) || (!isAdmin && !canEdit)"
                  :class="{ 'admin-override': isAdmin && isClosed }"
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
      :disabled="false"
      :class="{ 'btn-success': isAdmin, 'btn-warning': isAdmin && isClosed }"
    >
      <i class="fa fa-save info-icon"></i> 
      {{ isAdmin && isClosed ? 'Save Attendance (Admin Override)' : 'Save Attendance' }}
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

    // منطق التحرير البسيط والواضح مع debugging
    const canEdit = computed(() => {
      console.log('Admin status:', props.isAdmin);
      console.log('isClosed:', isClosed.value);
      
      // الـ Admin يقدر يعدل أي وقت
      if (props.isAdmin) {
        console.log('Admin can always edit');
        return true;
      }
      
      // الـ Instructor يقدر يعدل فقط قبل الإغلاق
      const canInstructorEdit = !isClosed.value;
      console.log('Instructor can edit:', canInstructorEdit);
      return canInstructorEdit;
    });

    const cannotMark = (student) => student.absencesCount >= 6;
    const canModify = (student) => {
      return !["withdrawn", "excluded"].includes(student.pivot?.status);
    };

    const submitAttendance = async () => {
      // التحقق الأساسي المبسط
      if (!course.value?.students?.length) {
        toastr.error('No students found.');
        return;
      }
      
      const payload = course.value.students.map((st) => ({
        student_id: st.id,
        attendance: st.attendancePresent ? "present" : "absent",
        notes: st.notes || "",
        homework_submitted: st.homeworkSubmitted ? 1 : 0,
        existing_id: st.existing_id || null,
      }));

      console.log('Sending payload:', payload);
      console.log('Current schedule ID:', schedule.value.id);

      try {
        const response = await instance.post(`/courses/${props.courseId}/attendance`, {
          course_schedule_id: schedule.value.id,
          students: payload,
          admin_override: props.isAdmin,
          is_admin_edit: props.isAdmin && isClosed.value
        });
        
        console.log('Server response:', response.data);
        
        // تحديث schedule.id إذا تم إنشاء schedule جديد
        if (response.data.schedule_id && response.data.schedule_id !== schedule.value.id) {
          console.log('Schedule ID changed from', schedule.value.id, 'to', response.data.schedule_id);
          schedule.value.id = response.data.schedule_id;
          
          // تحديث الـ URL أيضاً إذا لزم الأمر
          if (window.history.replaceState) {
            const newUrl = window.location.pathname.replace(
              /schedule_id=\d+/, 
              `schedule_id=${response.data.schedule_id}`
            );
            window.history.replaceState({}, '', newUrl);
          }
        }
        
        let message = 'Attendance has been saved successfully!';
        if (props.isAdmin && isClosed.value) {
          message = 'Attendance has been saved successfully (Admin Override)!';
        } else if (props.isAdmin) {
          message = 'Attendance has been saved successfully (Admin)!';
        }
        
        toastr.success(message);
        
        // تحديث existing_id للطلاب من الـ response
        if (response.data.attendance_records) {
          response.data.attendance_records.forEach(record => {
            const student = course.value.students.find(s => s.id == record.student_id);
            if (student) {
              student.existing_id = record.id;
              student.attendancePresent = record.attendance == 'present';
              // تحويل homework_submitted إلى boolean
              student.homeworkSubmitted = Boolean(record.homework_submitted);
              student.notes = record.notes || '';
            }
          });
        }
        
        // تحديث schedule.attendances
        if (schedule.value && response.data.attendance_records) {
          schedule.value.attendances = response.data.attendance_records;
        }
        
        // في حالة تغيير الـ schedule، قد نحتاج لإعادة تحميل البيانات
        if (response.data.schedule_changed) {
          console.log('Schedule changed, reloading data...');
          await fetchData();
        }
        
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
            attendancePresent: ex.attendance == "present",
            homeworkSubmitted: ex.homework_submitted == 1,
            notes: ex.notes || "",
            existing_id: ex.id || null, // هذا مهم!
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
  background-color: #d1ecf1;
  border: 1px solid #bee5eb;
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
  border: 2px solid #17a2b8 !important;
  background-color: #d1ecf1 !important;
}

.admin-override:focus {
  border-color: #138496 !important;
  box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
}

.admin-override-slider {
  border: 2px solid #17a2b8;
  background-color: #d1ecf1 !important;
}

.admin-override-slider:before {
  background-color: #17a2b8 !important;
}

.btn-success {
  background-color: #28a745;
  border-color: #28a745;
}

.btn-success:hover {
  background-color: #218838;
  border-color: #1e7e34;
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