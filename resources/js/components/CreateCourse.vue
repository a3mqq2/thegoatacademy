<template>
  <div>
    <div v-if="globalLoading" class="global-spinner-overlay">
      <div class="spinner"></div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <label>Select Course Type</label>
        <div v-if="loading" class="spinner-container">
          <div class="spinner"></div>
        </div>
        <v-select
          v-else
          v-model="selectedCourseType"
          :options="courseTypes"
          label="name"
          track-by="id"
          placeholder="Select a Course Type"
          :disabled="loading"
          @update:modelValue="updateFields"
        ></v-select>
      </div>

      <div class="col-md-4">
        <label>Select Group Type</label>
        <div v-if="loading" class="spinner-container">
          <div class="spinner"></div>
        </div>
        <v-select
          v-else
          v-model="selectedGroupType"
          :options="groupTypes"
          label="name"
          track-by="id"
          placeholder="Select a Group Type"
          :disabled="loading"
          @update:modelValue="updateFields"
        ></v-select>
      </div>

      <div class="col-md-4">
        <label>Select Instructor</label>
        <div v-if="loading" class="spinner-container">
          <div class="spinner"></div>
        </div>
        <v-select
          v-else
          v-model="selectedInstructor"
          :options="filteredInstructors"
          label="name"
          track-by="id"
          placeholder="Select an Instructor"
          :disabled="loading"
        ></v-select>
      </div>
    </div>

    <div class="row mt-2" v-if="showFields">
      <div class="col-md-12">
        <div class="form-check form-switch">
          <input
            class="form-check-input"
            type="checkbox"
            id="matchSkillsSwitch"
            v-model="matchInstructorSkills"
          />
          <label class="form-check-label" for="matchSkillsSwitch">
            Only show instructors that match this course type's skills
          </label>
        </div>
      </div>
    </div>

    <div v-if="showFields" class="row mt-3">
      <div class="col-md-3">
        <label>Start Date</label>
        <input
          type="date"
          v-model="startDate"
          class="form-control"
          @change="updateExamDates"
        />
      </div>
      <div class="col-md-3">
        <label>Mid Exam Date</label>
        <input type="date" v-model="midExamDate" class="form-control" />
      </div>
      <div class="col-md-3">
        <label>Final Exam Date</label>
        <input type="date" v-model="finalExamDate" class="form-control" />
      </div>
      <div class="col-md-3">
        <label>Student Capacity</label>
        <input type="number" v-model="studentCapacity" class="form-control" />
      </div>

      <div class="col-md-3 mt-2">
        <label>Meeting Platform</label>
        <v-select
          v-model="selectedMeetingPlatform"
          :options="meetingPlatforms"
          label="name"
          track-by="id"
          placeholder="Select a Meeting Platform"
        />
      </div>

      <div class="col-md-9 mt-2">
        <label>Whatsapp Group Link</label>
        <input
          type="text"
          class="form-control"
          v-model="whatsappGroupLink"
          placeholder="Enter WhatsApp group link"
        />
      </div>
    </div>

    <div v-if="showFields" class="card mt-4">
      <div class="card-header">
        <h5>Schedule</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <label>Select Days</label>
            <v-select
              v-model="selectedDays"
              :options="days"
              label="label"
              track-by="value"
              multiple
              placeholder="Choose Days"
            />
          </div>
          <div class="col-md-4">
            <label>From Time <small>(24-hour format)</small></label>
            <input
              type="time"
              step="60"
              v-model="fromTime"
              class="form-control"
              @change="updateToTime"
            />
          </div>
          <div class="col-md-4">
            <label>To Time <small>(24-hour format)</small></label>
            <input
              type="time"
              step="60"
              v-model="toTime"
              class="form-control"
            />
          </div>
        </div>
        <button class="btn btn-primary mt-2" @click="generateSchedule">
          Generate Schedule
        </button>

        <table class="table table-bordered mt-3" v-if="scheduleList.length">
          <thead>
            <tr>
              <th>#</th>
              <th>Day</th>
              <th>Date</th>
              <th>From Time</th>
              <th>To Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in scheduleList" :key="index">
              <td>{{ index + 1 }}</td>
              <td>{{ item.day }}</td>
              <td>{{ item.date }}</td>
              <td>
                <input
                  type="time"
                  step="60"
                  v-model="item.fromTime"
                  class="form-control"
                />
              </td>
              <td>
                <input
                  type="time"
                  step="60"
                  v-model="item.toTime"
                  class="form-control"
                />
              </td>
              <td>
                <button class="btn btn-danger" @click="removeSchedule(index)">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showFields" class="card mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Students</h5>
        <div>
          <div class="form-check form-switch d-inline-block me-3">
            <input
              class="form-check-input"
              type="checkbox"
              id="matchStudentSkillsSwitch"
              v-model="matchStudentSkills"
            />
            <label class="form-check-label" for="matchStudentSkillsSwitch">
              Match Student Skills?
            </label>
          </div>
          <v-select
            v-model="selectedStudent"
            :options="filteredStudents"
            label="name"
            track-by="id"
            placeholder="Select a Student"
            @update:modelValue="onStudentSelected"
          />
          <button class="btn btn-success ms-2" @click="showStudentModal = true">
            Create New Student
          </button>
        </div>
      </div>

      <div class="card-body">
        <table class="table table-bordered" v-if="studentsList.length">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Books Due</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(student, idx) in studentsList" :key="idx">
              <td>{{ idx + 1 }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.phone }}</td>
              <td>{{ student.booksDue ? 'Yes' : 'No' }}</td>
              <td>
                <button class="btn btn-danger" @click="removeStudent(idx)">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <button
      v-if="showFields"
      class="btn btn-primary mt-3"
      @click="saveCourse"
    >
      {{ id ? 'Update Course' : 'Create Course' }}
    </button>

    <div
      class="modal"
      v-if="showStudentModal"
      style="display: block; background: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog" style="margin: 10% auto; max-width: 500px">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">New Student</h5>
            <button
              type="button"
              class="btn-close"
              @click="showStudentModal = false"
            ></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>Name</label>
              <input type="text" v-model="newStudentName" class="form-control" />
            </div>
            <div class="mb-3">
              <label>Phone</label>
              <input type="text" v-model="newStudentPhone" class="form-control" />
            </div>
            <div class="mb-3">
              <label>Gender</label>
              <select v-model="newStudentGender" class="form-control">
                <option disabled value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="mb-3">
              <label>Age</label>
              <input type="number" v-model="newStudentAge" class="form-control" />
            </div>
            <div class="mb-3">
              <label>City</label>
              <input type="text" v-model="newStudentCity" class="form-control" />
            </div>
            <div class="mb-3">
              <label>Specialization</label>
              <input type="text" v-model="newStudentSpecialization" class="form-control" />
            </div>
            <div class="mb-3">
              <label>Emergency Phone</label>
              <input type="text" v-model="newStudentEmergencyPhone" class="form-control" />
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" v-model="newStudentBooksDue" />
              <label class="form-check-label">Books Due?</label>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showStudentModal = false">
              Close
            </button>
            <button class="btn btn-primary" @click="addStudent">
              Add Student
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted, watch, computed, getCurrentInstance } from "vue";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import instance from "../instance";
import Flatpickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";

export default defineComponent({
  name: "CreateOrEditCourse",
  components: { "v-select": vSelect, Flatpickr },
  props: {
    id: {
      type: [Number, String],
      default: null
    }
  },
  setup(props) {
    const { appContext } = getCurrentInstance();
    const $toastr = appContext.config.globalProperties.$toastr;

    const courseTypes         = ref([]);
    const instructors         = ref([]);
    const groupTypes          = ref([]);
    const meetingPlatforms    = ref([]);
    const allStudents         = ref([]);

    const selectedCourseType  = ref(null);
    const selectedGroupType   = ref(null);
    const selectedInstructor  = ref(null);
    const selectedMeetingPlatform = ref(null);

    const startDate           = ref("");
    const midExamDate         = ref("");
    const finalExamDate       = ref("");
    const studentCapacity     = ref("");
    const whatsappGroupLink   = ref("");

    const showFields          = ref(false);
    const loading             = ref(true);
    const globalLoading       = ref(false);

    const days                = ref([
      { label: "Sunday", value: 0 },
      { label: "Monday", value: 1 },
      { label: "Tuesday", value: 2 },
      { label: "Wednesday", value: 3 },
      { label: "Thursday", value: 4 },
      { label: "Friday", value: 5 },
      { label: "Saturday", value: 6 }
    ]);
    const selectedDays        = ref([]);
    const fromTime            = ref("");
    const toTime              = ref("");
    const scheduleList        = ref([]);
    const storedSelectedDays  = ref([]);

    const studentsList        = ref([]);
    const selectedStudent     = ref(null);
    const showStudentModal    = ref(false);

    const newStudentName           = ref("");
    const newStudentPhone          = ref("");
    const newStudentGender         = ref("");
    const newStudentAge            = ref("");
    const newStudentCity           = ref("");
    const newStudentSpecialization = ref("");
    const newStudentEmergencyPhone = ref("");
    const newStudentBooksDue       = ref(false);

    const matchInstructorSkills = ref(false);
    const matchStudentSkills    = ref(false);

    const errors = ref([]);

    const formatDateLocal = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    };

    const getRequirements = async () => {
      globalLoading.value = true;
      try {
        const params = {};
        if (props.id) {
          params.id = props.id;
        }
        const response = await instance.get("/course-requirements", { params });

        courseTypes.value       = response.data.courseTypes       || [];
        instructors.value       = response.data.instructors       || [];
        groupTypes.value        = response.data.groupTypes        || [];
        allStudents.value       = response.data.students          || [];
        meetingPlatforms.value  = response.data.meeting_platforms || [];

        if (response.data.course) {
          populateCourse(response.data.course);
        }
      } catch (error) {
      } finally {
        loading.value = false;
        globalLoading.value = false;
      }
    };

    const populateCourse = (course) => {
      selectedCourseType.value = courseTypes.value.find(ct => ct.id === course.course_type_id) || null;
      selectedGroupType.value  = groupTypes.value.find(gt => gt.id === course.group_type_id)   || null;
      selectedInstructor.value = instructors.value.find(i => i.id === course.instructor_id)    || null;
      selectedMeetingPlatform.value =
        meetingPlatforms.value.find(mp => mp.id === course.meeting_platform_id) || null;

      startDate.value        = course.start_date        || "";
      midExamDate.value      = course.mid_exam_date     || "";
      finalExamDate.value    = course.final_exam_date   || "";
      studentCapacity.value  = course.student_capacity  || "";
      whatsappGroupLink.value= course.whatsapp_group_link || "";

      if (course.days) {
        const splitted = course.days.split("-");
        selectedDays.value = days.value.filter(d => splitted.includes(d.label));
      }

      if (course.time) {
        const parts = course.time.split("-");
        if (parts.length === 2) {
          fromTime.value = parts[0].trim();
          toTime.value   = parts[1].trim();
        }
      }

      scheduleList.value = [];
      if (course.schedules?.length) {
        course.schedules.forEach(sch => {
          scheduleList.value.push({
            day: sch.day,
            date: sch.date,
            fromTime: sch.from_time,
            toTime: sch.to_time
          });
        });
      }

      studentsList.value = [];
      if (course.students?.length) {
        course.students.forEach(st => {
          studentsList.value.push({
            id: st.id,
            name: st.name,
            phone: st.phone,
            booksDue: st.books_due
          });
        });
      }

      showFields.value = true;
    };

    const updateFields = () => {
      showFields.value = !!(selectedCourseType.value && selectedGroupType.value);
      updateStudentCapacity();
      updateExamDates();
    };

    const updateStudentCapacity = () => {
      if (selectedGroupType.value) {
        studentCapacity.value = selectedGroupType.value.student_capacity || "";
      }
    };

    const updateExamDates = () => {
      if (!startDate.value || !selectedCourseType.value) {
        midExamDate.value   = "";
        finalExamDate.value = "";
        return;
      }
      const weeks = parseInt(selectedCourseType.value.duration || 0, 10);
      if (!isNaN(weeks) && weeks > 0) {
        const totalDays = weeks * 7;
        const start = new Date(`${startDate.value}T00:00:00`);
        const mid   = new Date(start);
        mid.setDate(start.getDate() + totalDays / 2);
        const final = new Date(start);
        final.setDate(start.getDate() + totalDays);

        midExamDate.value   = formatDateLocal(mid);
        finalExamDate.value = formatDateLocal(final);
      } else {
        midExamDate.value   = "";
        finalExamDate.value = "";
      }
    };

    const updateToTime = () => {
      if (!fromTime.value || !selectedGroupType.value?.lesson_duration) {
        toTime.value = "";
        return;
      }
      const lessonDuration = Number(selectedGroupType.value.lesson_duration);
      let [hours, minutes] = fromTime.value.split(":").map(Number);
      let totalMinutes = hours * 60 + minutes + lessonDuration;
      let newHours = Math.floor(totalMinutes / 60) % 24;
      let newMinutes = totalMinutes % 60;
      toTime.value = `${String(newHours).padStart(2,"0")}:${String(newMinutes).padStart(2,"0")}`;
    };

    watch(fromTime, updateToTime);

    const generateSchedule = () => {
      if (
        !fromTime.value ||
        !toTime.value ||
        !startDate.value ||
        !finalExamDate.value ||
        !selectedDays.value.length
      ) {
        $toastr.error("Please fill in From Time, To Time, Dates, and Days before generating.");
        return;
      }
      const start = new Date(`${startDate.value}T00:00:00`);
      const end   = new Date(`${finalExamDate.value}T00:00:00`);
      if (end < start) {
        $toastr.error("Final Exam Date cannot be before Start Date.");
        return;
      }

      storedSelectedDays.value = selectedDays.value.map(day => day.value);
      scheduleList.value = [];

      let current = new Date(start);
      while (current <= end) {
        if (selectedDays.value.some(d => d.value === current.getDay())) {
          scheduleList.value.push({
            day: days.value.find(x => x.value === current.getDay()).label,
            date: formatDateLocal(current),
            fromTime: fromTime.value,
            toTime: toTime.value
          });
        }
        current.setDate(current.getDate() + 1);
      }
    };

    const removeSchedule = (index) => {
      scheduleList.value.splice(index, 1);
    };

    const filteredInstructors = computed(() => {
      if (!matchInstructorSkills.value || !selectedCourseType.value?.skills) {
        return instructors.value;
      }
      const courseTypeSkillIds = selectedCourseType.value.skills.map(s => s.id);
      return instructors.value.filter(inst => {
        if (!inst.skills?.length) return false;
        const instSkillIds = inst.skills.map(s => s.id);
        return instSkillIds.some(skillId => courseTypeSkillIds.includes(skillId));
      });
    });

    const availableStudents = computed(() => {
      return allStudents.value.filter(std => {
        return !studentsList.value.some(s => s.id === std.id);
      });
    });

    const filteredStudents = computed(() => {
      if (!matchStudentSkills.value || !selectedCourseType.value?.skills) {
        return availableStudents.value;
      }
      const courseSkillIds = selectedCourseType.value.skills.map(s => s.id);
      return availableStudents.value.filter(std => {
        if (!std.skills?.length) return false;
        const stSkillIds = std.skills.map(s => s.id);
        return stSkillIds.some(skillId => courseSkillIds.includes(skillId));
      });
    });

    const onStudentSelected = (value) => {
      if (!value) return;
      const found = studentsList.value.some(s => s.id === value.id);
      if (!found) {
        studentsList.value.push({ ...value });
      }
      selectedStudent.value = null;
    };

    const addStudent = async () => {
      if (!newStudentName.value || !newStudentPhone.value) {
        $toastr.error("Name and Phone are required.");
        return;
      }
      const payload = {
        name: newStudentName.value,
        phone: newStudentPhone.value,
        booksDue: newStudentBooksDue.value,
        gender: newStudentGender.value,
        age: newStudentAge.value,
        city: newStudentCity.value,
        specialization: newStudentSpecialization.value,
        emergency_phone: newStudentEmergencyPhone.value
      };

      globalLoading.value = true;
      try {
        const { data } = await instance.post("/students", payload);
        const student = data.student ?? data;
        if (student) {
          studentsList.value.push(student);
          $toastr.success("Student created successfully");
          showStudentModal.value = false;
          newStudentName.value           = "";
          newStudentPhone.value          = "";
          newStudentBooksDue.value       = false;
          newStudentGender.value         = "";
          newStudentAge.value            = "";
          newStudentCity.value           = "";
          newStudentSpecialization.value = "";
          newStudentEmergencyPhone.value = "";
        } else {
          $toastr.error("No student data returned");
        }
      } catch (err) {
        if (err.response?.data?.message) {
          $toastr.error(err.response.data.message);
        } else {
          $toastr.error("An error occurred while creating the student.");
        }
      } finally {
        globalLoading.value = false;
      }
    };

    const removeStudent = (index) => {
      studentsList.value.splice(index, 1);
    };

    const validateCourseData = () => {
      errors.value = [];
      if (!selectedCourseType.value) {
        errors.value.push("Course Type is required");
      }
      if (!selectedGroupType.value) {
        errors.value.push("Group Type is required");
      }
      if (!selectedInstructor.value) {
        errors.value.push("Instructor is required");
      }
      if (!startDate.value) {
        errors.value.push("Start Date is required");
      }
      if (!midExamDate.value) {
        errors.value.push("Mid Exam Date is required");
      }
      if (!finalExamDate.value) {
        errors.value.push("Final Exam Date is required");
      }
      if (!studentCapacity.value || Number(studentCapacity.value) <= 0) {
        errors.value.push("Student Capacity must be greater than 0");
      }
      if (!scheduleList.value.length) {
        errors.value.push("Schedule cannot be empty");
      }
      if (!studentsList.value.length) {
        errors.value.push("At least one student is required");
      }

      if (errors.value.length) {
        errors.value.forEach(err => $toastr.error(err));
        return false;
      }
      return true;
    };

    const saveCourse = async () => {
      if (!validateCourseData()) return;

      const payload = {
        course_type_id:       selectedCourseType.value.id,
        group_type_id:        selectedGroupType.value.id,
        instructor_id:        selectedInstructor.value.id,
        start_date:           startDate.value,
        mid_exam_date:        midExamDate.value,
        final_exam_date:      finalExamDate.value,
        student_capacity:     studentCapacity.value,
        schedule:             scheduleList.value,
        students:             studentsList.value.map(s => s.id),
        selected_days:        storedSelectedDays.value,
        time:                 `${fromTime.value} - ${toTime.value}`,
        meeting_platform_id:  selectedMeetingPlatform.value
          ? selectedMeetingPlatform.value.id
          : null,
        whatsapp_group_link:  whatsappGroupLink.value || ""
      };

      globalLoading.value = true;
      try {
        let courseId = null;
        if (!props.id) {
          const response = await instance.post("/courses", payload);
          $toastr.success("Course created successfully");
          courseId = response.data.course?.id;
        } else {
          const response = await instance.put(`/courses/${props.id}`, payload);
          $toastr.success("Course updated successfully");
          courseId = props.id;
        }
        resetFields();
        setTimeout(() => {
          if (courseId) {
            window.location.href = `/admin/courses/${courseId}/print`;
          } else {
            window.location.href = "/admin/courses";
          }
        }, 800);
      } catch (err) {
        if (err.response?.data?.message) {
          $toastr.error(err.response.data.message);
        } else {
          $toastr.error("An error occurred while saving the course.");
        }
      } finally {
        globalLoading.value = false;
      }
    };

    const resetFields = () => {
      selectedCourseType.value     = null;
      selectedGroupType.value      = null;
      selectedInstructor.value     = null;
      selectedMeetingPlatform.value= null;

      startDate.value        = "";
      midExamDate.value      = "";
      finalExamDate.value    = "";
      studentCapacity.value  = "";
      whatsappGroupLink.value= "";
      scheduleList.value     = [];
      studentsList.value     = [];

      showStudentModal.value = false;
    };

    watch(startDate, updateExamDates);

    onMounted(getRequirements);

    return {
      props,
      courseTypes,
      instructors,
      groupTypes,
      meetingPlatforms,
      allStudents,

      selectedCourseType,
      selectedGroupType,
      selectedInstructor,
      selectedMeetingPlatform,

      startDate,
      midExamDate,
      finalExamDate,
      studentCapacity,
      whatsappGroupLink,

      showFields,
      loading,
      globalLoading,

      days,
      selectedDays,
      fromTime,
      toTime,
      scheduleList,
      storedSelectedDays,

      studentsList,
      selectedStudent,
      showStudentModal,

      newStudentName,
      newStudentPhone,
      newStudentGender,
      newStudentAge,
      newStudentCity,
      newStudentSpecialization,
      newStudentEmergencyPhone,
      newStudentBooksDue,

      matchInstructorSkills,
      matchStudentSkills,

      errors,

      filteredInstructors,
      availableStudents,
      filteredStudents,

      formatDateLocal,
      getRequirements,
      populateCourse,
      updateFields,
      updateStudentCapacity,
      updateExamDates,
      updateToTime,
      generateSchedule,
      removeSchedule,
      onStudentSelected,
      addStudent,
      removeStudent,
      validateCourseData,
      saveCourse,
      resetFields
    };
  }
});
</script>

<style scoped>
.spinner-container {
  margin-top: 10px;
}
.spinner {
  width: 25px;
  height: 25px;
  border: 4px solid #bbb;
  border-top: 4px solid #333;
  border-radius: 50%;
  animation: spin 1s linear infinite;
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
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
