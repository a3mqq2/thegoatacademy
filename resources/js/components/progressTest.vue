<template>
   <div class="progress-test-component">
     <!-- Global Loading Spinner -->
     <div v-if="globalLoading" class="global-spinner-overlay">
       <div class="spinner"></div>
     </div>
 
     <div class="progress-test-header">
       <h3>
         <i class="fa fa-pencil-alt info-icon"></i> Enter Progress Test Scores
       </h3>
       <p class="progress-test-date">
         <i class="fa fa-calendar info-icon"></i> Date: {{ progressTest.date }}
       </p>
     </div>
 
     <!-- Display grid if course is loaded -->
     <div class="progress-test-grid" v-if="course && course.students">
       <div
         v-for="(student, index) in course.students"
         :key="student.id"
         class="student-progress-card"
       >
         <div class="card-top-row">
           <div class="student-info-section">
             <div class="student-details">
               <h6>
                 <i class="fa fa-user info-icon"></i> {{ student.name }}
               </h6>
               <p>
                 <i class="fa fa-phone info-icon"></i> {{ student.phone }}
               </p>
             </div>
           </div>
         </div>
         <!-- Input fields for score and note -->
         <div class="progress-inputs">
           <div class="form-group">
             <label>Score:</label>
             <input
               type="number"
               v-model.number="student.score"
               class="form-control"
               placeholder="Enter score"
             />
           </div>
         </div>
       </div>
     </div>
 
     <button
       class="btn btn-primary submit-btn"
       @click="submitProgressTest"
       :disabled="!anyStudent"
     >
       <i class="fa fa-save info-icon"></i> Save Scores
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
   name: "ProgressTest",
   props: {
     courseId: { type: Number, required: true }
   },
   setup(props) {
     const $toastr = toastr;
     const globalLoading = ref(false);
     const course = ref(null);
     // Initialize a new progress test with today's date
     const progressTest = ref({
       date: new Date().toISOString().substring(0, 10)
     });
 
     // Check if there are students available
     const anyStudent = computed(() => {
       return course.value?.students?.length > 0;
     });
 
     // Submit new progress test scores to the API
     const submitProgressTest = async () => {
       if (!course.value?.students) return;
 
       const payload = course.value.students.map((student) => ({
         student_id: student.id,
         score: student.score || 0,
         note: student.note || "",
         existing_id: student.existing_id || null
       }));
 
       try {
         await instance.post(`/courses/${props.courseId}/progress-tests`, {
           // Sending new progress test details
           date: progressTest.value.date,
           students: payload
         });
         $toastr.success("Progress test scores have been saved successfully!");
         setTimeout(() => {
             // Redirect to the course details page
             window.location.href = `/instructor/courses/${props.courseId}/show`;
         }, 600);
       } catch (error) {
         console.error("Error saving progress test scores", error);
         $toastr.error("Error saving progress test scores. Please try again later.");
       }
     };
 
     // Fetch course details from the API
     const fetchData = async () => {
       globalLoading.value = true;
       try {
         const { data } = await instance.get(`/courses/${props.courseId}?progress_test_id=true`);
         course.value = data.course;
         // Map students with additional fields for score and note
         course.value.students = course.value.students.map((student) => ({
           id: student.id,
           name: student.name,
           phone: student.phone,
           score: null,
           note: "",
           existing_id: null
         }));
       } catch (error) {
         console.error("Error fetching course data", error);
         $toastr.error("Failed to load course data.");
       } finally {
         globalLoading.value = false;
       }
     };
 
     onMounted(fetchData);
 
     return {
       course,
       progressTest,
       globalLoading,
       anyStudent,
       submitProgressTest
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
 
 .progress-test-component {
   padding: 20px;
   background: #fff;
   border-radius: 15px;
   box-shadow: var(--card-shadow);
   max-width: 1000px;
   margin: 0 auto;
 }
 .progress-test-header {
   text-align: center;
   margin-bottom: 20px;
 }
 .progress-test-header h2 {
   font-size: 2rem;
   color: var(--primary-color);
   margin-bottom: 5px;
 }
 .progress-test-date {
   font-size: 1.2rem;
   color: var(--secondary-color);
 }
 
 /* Progress Test Grid */
 .progress-test-grid {
   display: grid;
   grid-template-columns: 1fr;
   gap: 15px;
   margin-bottom: 20px;
 }
 @media (min-width: 768px) {
   .progress-test-grid {
     grid-template-columns: 1fr;
   }
 }
 
 /* Student Progress Card */
 .student-progress-card {
   background: linear-gradient(135deg, #fdfdfd, #f0f2f5);
   border-radius: 10px;
   padding: 15px;
   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
   display: flex;
   flex-direction: column;
   gap: 10px;
   position: relative;
   z-index: 1;
 }
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
 
 /* Progress Inputs */
 .progress-inputs {
   display: flex;
   gap: 10px;
   flex-wrap: wrap;
 }
 .progress-inputs .form-group {
   flex: 1;
   min-width: 150px;
 }
 
 /* Button Styles */
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
 