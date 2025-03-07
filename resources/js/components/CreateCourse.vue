<template>
   <div>
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
           :options="instructors"
           label="name"
           track-by="id"
           placeholder="Select an Instructor"
           :disabled="loading"
         ></v-select>
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
         <input
           type="number"
           v-model="studentCapacity"
           class="form-control"
         />
       </div>
     </div>
 
     <!-- Schedule Section -->
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
             <label>From Time</label>
             <input type="time" v-model="fromTime" class="form-control" />
           </div>
           <div class="col-md-4">
             <label>To Time</label>
             <input type="time" v-model="toTime" class="form-control" />
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
                   v-model="item.fromTime"
                   class="form-control"
                 />
               </td>
               <td>
                 <input
                   type="time"
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
 
     <!-- Students Section -->
     <div v-if="showFields" class="card mt-4">
       <div class="card-header d-flex justify-content-between align-items-center">
         <h5 class="mb-0">Students</h5>
         <div>
           <v-select
             v-model="selectedStudent"
             :options="allStudents"
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
 
     <!-- Create / Update Course Button -->
     <button
       v-if="showFields"
       class="btn btn-primary mt-3"
       @click="saveCourse"
     >
       {{ id ? 'Update Course' : 'Create Course' }}
     </button>
 
     <!-- Modal for creating a new student -->
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
 import { defineComponent, ref, onMounted, watch, getCurrentInstance } from "vue"
 import vSelect from "vue-select"
 import "vue-select/dist/vue-select.css"
 import instance from "../instance"
 
 export default defineComponent({
   name: "CreateOrEditCourse",
   components: { "v-select": vSelect },
 
   // Accept "id" prop for edit mode
   props: {
     id: {
       type: [Number, String],
       default: null
     }
   },
 
   setup(props) {
     const { appContext } = getCurrentInstance()
     const $toastr = appContext.config.globalProperties.$toastr
 
     // Data references
     const courseTypes        = ref([])
     const instructors        = ref([])
     const groupTypes         = ref([])
     const selectedCourseType = ref(null)
     const selectedInstructor = ref(null)
     const selectedGroupType  = ref(null)
     const startDate          = ref("")
     const midExamDate        = ref("")
     const finalExamDate      = ref("")
     const studentCapacity    = ref("")
     const showFields         = ref(false)
     const loading            = ref(true)
     const days               = ref([
       { label: "Sunday", value: 0 },
       { label: "Monday", value: 1 },
       { label: "Tuesday", value: 2 },
       { label: "Wednesday", value: 3 },
       { label: "Thursday", value: 4 },
       { label: "Friday", value: 5 },
       { label: "Saturday", value: 6 }
     ])
     const selectedDays   = ref([])
     const fromTime       = ref("")
     const toTime         = ref("")
     const scheduleList   = ref([])
     const allStudents    = ref([])
     const studentsList   = ref([])
     const selectedStudent= ref(null)
     const showStudentModal = ref(false)
     const newStudentName   = ref("")
     const newStudentPhone  = ref("")
     const newStudentBooksDue = ref(false)
     const errors            = ref([])
 
     // Fetch data from /course-requirements
     // If props.id exists, the API also returns existing course details
     const getRequirements = async () => {
       try {
         const params = {}
         if (props.id) {
           params.id = props.id // pass ID to load course details in edit mode
         }
         const response = await instance.get("/course-requirements", { params })
 
         courseTypes.value   = response.data.courseTypes   || []
         instructors.value   = response.data.instructors   || []
         groupTypes.value    = response.data.groupTypes    || []
         allStudents.value   = response.data.students      || []
 
         // If a course property is returned, populate the fields
         if (response.data.course) {
           populateCourse(response.data.course)
         }
       } catch (error) {
         // handle error if needed
       } finally {
         loading.value = false
       }
     }
 
     // Populate fields from existing course data in edit mode
     const populateCourse = (course) => {
       // Find matching courseType, groupType, instructor in the arrays
       selectedCourseType.value = courseTypes.value.find(ct => ct.id === course.course_type_id) || null
       selectedGroupType.value  = groupTypes.value.find(gt => gt.id === course.group_type_id)   || null
       selectedInstructor.value = instructors.value.find(i => i.id === course.instructor_id)    || null
 
       startDate.value     = course.start_date      || ""
       midExamDate.value   = course.mid_exam_date   || ""
       finalExamDate.value = course.final_exam_date || ""
       studentCapacity.value = course.student_capacity || ""
 
       // Rebuild scheduleList from course.schedules
       scheduleList.value = []
       if (course.schedules && course.schedules.length) {
         course.schedules.forEach(sch => {
           scheduleList.value.push({
             day: sch.day,
             date: sch.date,
             fromTime: sch.from_time,
             toTime: sch.to_time
           })
         })
       }
       // Rebuild studentsList from course.students
       studentsList.value = []
       if (course.students && course.students.length) {
         // we assume each student includes { id, name, phone, booksDue }
         course.students.forEach(st => {
           studentsList.value.push({
             id: st.id,
             name: st.name,
             phone: st.phone,
             booksDue: st.books_due
           })
         })
       }
 
       // Show fields now that we have a course to edit
       showFields.value = true
     }
 
     // Called after Course Type or Group Type changes
     const updateFields = () => {
       // Show the rest of the fields if both are selected
       showFields.value = !!(selectedCourseType.value && selectedGroupType.value)
       updateStudentCapacity()
       updateExamDates()
     }
 
     const updateStudentCapacity = () => {
       if (selectedGroupType.value) {
         studentCapacity.value = selectedGroupType.value.student_capacity
       }
     }
 
     const updateExamDates = () => {
       if (!startDate.value || !selectedCourseType.value) {
         midExamDate.value   = ""
         finalExamDate.value = ""
         return
       }
       if (selectedCourseType.value.duration) {
         const mapping = { week: 7, month: 30, half_year: 180 }
         const durationInDays = mapping[selectedCourseType.value.duration] || 0
 
         const start = new Date(startDate.value + "T00:00:00")
         const mid   = new Date(start)
         mid.setDate(start.getDate() + durationInDays / 2)
         start.setDate(start.getDate() + durationInDays)
 
         midExamDate.value   = mid.toISOString().split("T")[0]
         finalExamDate.value = start.toISOString().split("T")[0]
       } else {
         midExamDate.value   = ""
         finalExamDate.value = ""
       }
     }
 
     // Generate schedule rows for the selected days between start & final exam date
     const generateSchedule = () => {
       if (!fromTime.value || !toTime.value || !startDate.value || !finalExamDate.value || !selectedDays.value.length)
         return
 
       scheduleList.value = []
       const start = new Date(startDate.value + "T00:00:00")
       const end   = new Date(finalExamDate.value + "T00:00:00")
       let current = new Date(start)
 
       while (current <= end) {
         if (selectedDays.value.some(d => d.value === current.getDay())) {
           scheduleList.value.push({
             day: days.value.find(x => x.value === current.getDay()).label,
             date: current.toISOString().split("T")[0],
             fromTime: fromTime.value,
             toTime: toTime.value
           })
         }
         current.setDate(current.getDate() + 1)
       }
       // Clear the selected days/time
       selectedDays.value = []
       fromTime.value     = ""
       toTime.value       = ""
     }
 
     const removeSchedule = (i) => {
       scheduleList.value.splice(i, 1)
     }
 
     const onStudentSelected = (value) => {
       if (!value) return
       const found = studentsList.value.some(s => s.id === value.id)
       if (!found) studentsList.value.push({ ...value })
       selectedStudent.value = null
     }
 
     // Create a new student
     const addStudent = async () => {
       if (!newStudentName.value || !newStudentPhone.value) {
         $toastr.error("Name and Phone are required")
         return
       }
       const payload = {
         name: newStudentName.value,
         phone: newStudentPhone.value,
         booksDue: newStudentBooksDue.value
       }
       try {
         const { data } = await instance.post("/students", payload)
         studentsList.value.push(data.student)
         allStudents.value.push(data.student)
         newStudentName.value   = ""
         newStudentPhone.value  = ""
         newStudentBooksDue.value = false
         showStudentModal.value = false
         $toastr.success("Student created successfully")
       } catch (err) {
         if (err.response && err.response.data && err.response.data.message) {
           $toastr.error(err.response.data.message)
         } else {
           $toastr.error("An error occurred while creating the student")
         }
       }
     }
 
     const removeStudent = (i) => {
       studentsList.value.splice(i, 1)
     }
 
     // Basic validation
     const validateCourseData = () => {
       errors.value = []
       if (!selectedCourseType.value) errors.value.push("Course Type is required")
       if (!selectedGroupType.value)  errors.value.push("Group Type is required")
       if (!selectedInstructor.value) errors.value.push("Instructor is required")
       if (!startDate.value)         errors.value.push("Start Date is required")
       if (!midExamDate.value)       errors.value.push("Mid Exam Date is required")
       if (!finalExamDate.value)     errors.value.push("Final Exam Date is required")
       if (!studentCapacity.value || Number(studentCapacity.value) <= 0) {
         errors.value.push("Student Capacity must be greater than 0")
       }
       if (!scheduleList.value.length) {
         errors.value.push("Schedule cannot be empty")
       }
       if (!studentsList.value.length) {
         errors.value.push("At least one student is required")
       }
 
       if (errors.value.length) {
         errors.value.forEach(err => $toastr.error(err))
         return false
       }
       return true
     }
 
     // Create / Update
     const saveCourse = async () => {
       if (!validateCourseData()) return
 
       const payload = {
         course_type_id: selectedCourseType.value.id,
         group_type_id:  selectedGroupType.value.id,
         instructor_id:  selectedInstructor.value.id,
         start_date:     startDate.value,
         mid_exam_date:  midExamDate.value,
         final_exam_date: finalExamDate.value,
         student_capacity: studentCapacity.value,
         schedule: scheduleList.value,
         students: studentsList.value.map(s => s.id)
       }
 
       try {
         if (!props.id) {
           // Create mode
           await instance.post("/courses", payload)
           $toastr.success("Course created successfully")
         } else {
           // Edit mode
           await instance.put(`/courses/${props.id}`, payload)
           $toastr.success("Course updated successfully")
         }
         // If successful, reset fields (or navigate away)
         resetFields()

         setTimeout(() => {
            // redirect to courses 
            window.location.href = '/admin/courses'
         }, 800);

       } catch (err) {
         if (err.response && err.response.data && err.response.data.message) {
           $toastr.error(err.response.data.message)
         } else {
           $toastr.error("An error occurred while saving the course")
         }
       }
     }
 
     const resetFields = () => {
       selectedCourseType.value = null
       selectedGroupType.value  = null
       selectedInstructor.value = null
       startDate.value          = ""
       midExamDate.value        = ""
       finalExamDate.value      = ""
       studentCapacity.value    = ""
       scheduleList.value       = []
       studentsList.value       = []
       showStudentModal.value   = false
     }
 
     watch(startDate, updateExamDates)
     onMounted(getRequirements)
 
     return {
       // Props
       props,
 
       // Data references
       courseTypes,
       instructors,
       groupTypes,
       selectedCourseType,
       selectedInstructor,
       selectedGroupType,
       startDate,
       midExamDate,
       finalExamDate,
       studentCapacity,
       showFields,
       loading,
       days,
       selectedDays,
       fromTime,
       toTime,
       scheduleList,
       allStudents,
       studentsList,
       selectedStudent,
       showStudentModal,
       newStudentName,
       newStudentPhone,
       newStudentBooksDue,
       errors,
 
       // Methods
       updateFields,
       updateStudentCapacity,
       updateExamDates,
       generateSchedule,
       removeSchedule,
       onStudentSelected,
       addStudent,
       removeStudent,
       validateCourseData,
       saveCourse,
       resetFields,
       populateCourse,
     }
   }
 })
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
 @keyframes spin {
   to {
     transform: rotate(360deg);
   }
 }
 </style>
 