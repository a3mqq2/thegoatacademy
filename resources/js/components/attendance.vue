<template>
   <div class="attendance-component">
     <!-- Global Loading Spinner -->
     <div v-if="globalLoading" class="global-spinner-overlay">
       <div class="spinner"></div>
     </div>
 
     <div class="attendance-header">
       <h2>
         <i class="fa fa-check-square info-icon"></i> Take Attendance
       </h2>
       <p class="attendance-date" v-if="schedule">
         <i class="fa fa-calendar info-icon"></i> Date: {{ schedule.date }}
       </p>
     </div>
 
     <!-- Removed the warning message for non-today attendance -->
 
   
 
     <!-- Only display grid if course is loaded -->
     <div class="attendance-grid" v-if="course && course.students">
       <div
         v-for="(student, index) in course.students"
         :key="student.id"
         class="student-attendance-card"
         :class="{ disabled: !canModify(student) || cannotMark(student) }"
       >
         <!-- Top Row: Student Info, Status Badge & Attendance Toggle and Homework Switch -->
         <div class="card-top-row">
           <div class="student-info-section" @click="toggleAttendance(student)">
             <div class="student-details">
               <h6>
                 <i class="fa fa-user info-icon"></i> {{ student.name }}
               </h6>
               <p>
                 <i class="fa fa-phone info-icon"></i> {{ student.phone }}
               </p>
               <p>
                 <strong>Status:</strong>
                 <span class="badge" :class="badgeClass(student.pivot.status)">
                   {{ student.pivot.status ? student.pivot.status.charAt(0).toUpperCase() + student.pivot.status.slice(1) : 'N/A' }}
                 </span>
               </p>
             </div>
             <div class="attendance-status" :class="statusClass(student.attendance)">
               <i v-if="student.attendance === 'present'" class="fa fa-check-circle"></i>
               <i v-else-if="student.attendance === 'absent'" class="fa fa-times-circle"></i>
               <i v-else class="fa fa-question-circle"></i>
             </div>
           </div>
           <div class="homework-switch-section">
             <label class="switch">
               <input
                 type="checkbox"
                 v-model="student.homeworkSubmitted"
                 :disabled="!canModify(student)"
               />
               <span class="slider round"></span>
             </label>
             <span class="switch-label">Homework Submitted</span>
           </div>
         </div>
         <!-- Bottom Row: Notes Textarea -->
         <div class="student-notes-section">
           <textarea
             v-model="student.notes"
             class="form-control"
             :disabled="!canModify(student) || cannotMark(student)"
             placeholder="Enter notes..."
           ></textarea>
         </div>
       </div>
     </div>
 
     <button
       class="btn btn-primary submit-btn"
       @click="submitAttendance"
       :disabled="!anyModifiable"
     >
       <i class="fa fa-save info-icon"></i> Save Attendance
     </button>
 
     <!-- Back Button -->
     <a href="/instructor/courses?status=ongoing" class="btn btn-secondary mt-4 btn-sm text-light">
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
     scheduleId: { type: Number, default: null }
   },
   setup(props) {
     const $toastr = toastr;
     const globalLoading = ref(false);
     const course = ref(null);
     const schedule = ref(null);
 
     const formattedDate = schedule.value?.date ? new Date(schedule.value.date).toDateString() : ""; 
 
     // Removed isToday computed property
 
     const isAfterToTime = computed(() => {
       if (schedule.value?.to_time) {
         const [hours, minutes] = schedule.value.to_time.split(":");
         const now = new Date();
         const scheduleDateTime = new Date(
           now.getFullYear(),
           now.getMonth(),
           now.getDate(),
           parseInt(hours),
           parseInt(minutes)
         );
         return now >= scheduleDateTime;
       }
       return true;
     });
 
     // Modified isEntryAllowed to remove the "isToday" check
     const isEntryAllowed = computed(() => isAfterToTime.value);
 
     const cannotMark = (student) => student.absencesCount >= 6;
 
     const canModify = (student) => {
       return !["withdrawn", "excluded"].includes(student.pivot?.status);
     };
 
     const badgeClass = (status) => {
       return {
         ongoing: "bg-success",
         withdrawn: "bg-danger",
         excluded: "bg-warning"
       }[status] || "bg-secondary";
     };
 
     const anyModifiable = computed(() => {
       return course.value?.students?.some(student => canModify(student));
     });
 
     const toggleAttendance = (student) => {
       if (!canModify(student) || cannotMark(student)) return;
       student.attendance = student.attendance === null ? "present"
                           : student.attendance === "present" ? "absent"
                           : null;
     };
 
     const statusClass = (status) => {
       return {
         present: "present",
         absent: "absent"
       }[status] || "unmarked";
     };
 
     const submitAttendance = async () => {
     
       if (!course.value?.students) return;
 
       const payload = course.value.students.map((student) => ({
         student_id: student.id,
         attendance: student.attendance || "absent",
         notes: student.notes || "",
         homework_submitted: student.homeworkSubmitted ? 1 : 0,
         existing_id: student.existing_id || null
       }));
 
       try {
         await instance.post(`/courses/${props.courseId}/attendance`, {
           course_schedule_id: schedule.value.id,
           students: payload
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
         const { data } = await instance.get(`/courses/${props.courseId}?schedule_id=${props.scheduleId}`);
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
             homeworkSubmitted: existing.homework_submitted === 0 ? false : true,
             attendance: existing.attendance ?? null,
             notes: existing.notes ?? "",
             existing_id: existing.id || null,
             pivot: student.pivot || {}
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
       formattedDate,
       isAfterToTime,
       isEntryAllowed,
       canModify,
       cannotMark,
       badgeClass,
       toggleAttendance,
       statusClass,
       anyModifiable,
       submitAttendance
     };
   }
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
   text-align: center;
   margin-bottom: 20px;
 }
 .attendance-header h2 {
   font-size: 2rem;
   color: var(--primary-color);
   margin-bottom: 5px;
 }
 .attendance-date {
   font-size: 1.2rem;
   color: var(--secondary-color);
 }
 
 /* Warning Message */
 .attendance-warning {
   text-align: center;
   color: red;
   margin: 20px 0;
 }
 .attendance-warning i {
   margin-bottom: 10px;
 }
 
 /* Attendance Grid */
 .attendance-grid {
   display: grid;
   grid-template-columns: 1fr;
   gap: 15px;
   margin-bottom: 20px;
 }
 @media (min-width: 768px) {
   .attendance-grid {
     grid-template-columns: 1fr;
   }
 }
 
 /* Student Attendance Card - Horizontal Layout */
 .student-attendance-card {
   background: linear-gradient(135deg, #fdfdfd, #f0f2f5);
   border-radius: 10px;
   padding: 15px;
   box-shadow: 0 4px 10px rgba(0,0,0,0.1);
   display: flex;
   flex-direction: column;
   gap: 10px;
   cursor: pointer;
   position: relative;
   z-index: 1;
 }
 .student-attendance-card.disabled {
   opacity: 0.5;
   cursor: not-allowed;
 }
 .student-attendance-card:hover:not(.disabled) {
   transform: translateY(-3px);
   box-shadow: 0 8px 20px rgba(0,0,0,0.2);
 }
 
 /* Top Row: Student Info and Homework Switch */
 .card-top-row {
   display: flex;
   justify-content: space-between;
   align-items: center;
 }
 .student-info-section {
   flex: 3;
   display: flex;
   align-items: center;
 }
 .student-details h6 {
   margin: 0;
   font-size: 1.1rem;
   color: #333;
   font-weight: 600;
 }
 .student-details p {
   margin: 0;
   font-size: 0.9rem;
   color: #777;
 }
 .attendance-status {
   flex-shrink: 0;
   font-size: 2rem;
   margin-left: 10px;
 }
 .attendance-status.present {
   color: green;
 }
 .attendance-status.absent {
   color: red;
 }
 .attendance-status.unmarked {
   color: #ccc;
 }
 
 /* Homework Switch Section */
 .homework-switch-section {
   flex: 1;
   display: flex;
   flex-direction: column;
   align-items: flex-end;
 }
 .switch {
   position: relative;
   display: inline-block;
   width: 50px;
   height: 24px;
   margin-bottom: 5px;
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
 .switch-label {
   font-size: 0.8rem;
   color: #333;
 }
 
 /* Notes Textarea */
 .student-notes-section {
   margin-top: 5px;
 }
 .student-notes-section textarea {
   width: 100%;
   border: 1px solid #ccc;
   border-radius: 5px;
   padding: 8px 10px;
   font-size: 0.9rem;
   resize: vertical;
   transition: border 0.3s ease;
 }
 .student-notes-section textarea:focus {
   outline: none;
   border-color: var(--secondary-color);
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
   to { transform: rotate(360deg); }
 }
 </style>
 