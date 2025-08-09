<!-- EditCourse.vue -->
<template>
   <div>
     <div v-if="globalLoading" class="global-spinner-overlay"><div class="spinner"></div></div>
 
     <!-- ────── Top Selects ────── -->
     <div class="row">
       <div class="col-md-4">
         <label>Select Course Type</label>
         <div v-if="loading" class="spinner-container"><div class="spinner"></div></div>
         <v-select v-else v-model="selectedCourseType" :options="courseTypes" label="name" track-by="id" :disabled="loading" @update:modelValue="updateFields" />
       </div>
       <div class="col-md-4">
         <label>Select Group Type</label>
         <div v-if="loading" class="spinner-container"><div class="spinner"></div></div>
         <v-select v-else v-model="selectedGroupType" :options="groupTypes" label="name" track-by="id" :disabled="loading" @update:modelValue="updateFields" />
       </div>
       <div class="col-md-4">
         <label>Select Instructor</label>
         <div v-if="loading" class="spinner-container"><div class="spinner"></div></div>
         <v-select v-else v-model="selectedInstructor" :options="filteredInstructors" label="name" track-by="id" :disabled="loading" />
       </div>
     </div>
 
     <!-- Levels -->
     <div class="row mt-2">
       <div class="col-md-4">
         <label>Select Levels</label>
         <v-select v-model="selectedLevels" :options="levels" label="name" track-by="id" multiple />
       </div>
     </div>
 
     <!-- Match Instructor Skills -->
     <div class="row mt-2" v-if="showFields">
       <div class="col-md-12">
         <div class="form-check form-switch">
           <input class="form-check-input" type="checkbox" v-model="matchInstructorSkills" />
           <label class="form-check-label">Only show instructors that match this course type's skills</label>
         </div>
       </div>
     </div>
 
     <!-- General Info -->
     <div v-if="showFields" class="row mt-3">
       <div class="col-md-4">
         <label>Start Date</label>
         <flatpickr v-model="startDate" :config="dateConfig" class="form-control" />
       </div>
       <div class="col-md-4">
         <label>Student Capacity</label>
         <input type="number" v-model="studentCapacity" class="form-control" />
       </div>
       <div class="col-md-4 mt-2">
         <label>Meeting Platform</label>
         <v-select v-model="selectedMeetingPlatform" :options="meetingPlatforms" label="name" track-by="id" />
       </div>
       <div class="col-md-12 mt-2">
         <label>Whatsapp Group Link</label>
         <input type="text" v-model="whatsappGroupLink" class="form-control" />
       </div>
     </div>
 
     <!-- ────── Schedule Card ────── -->
     <div v-if="showFields" class="card mt-4">
       <div class="card-header"><h5>Schedule</h5></div>
       <div class="card-body">
         <div class="row">
           <div class="col-md-4">
             <label>Progress Test day (weekly)</label>
             <v-select v-model="progressTestDay" :options="selectableDays" label="label" track-by="value" :reduce="d=>d.value" />
           </div>
           <div class="col-md-4">
             <label>Select Days</label>
             <v-select v-model="selectedDays" :options="selectableDays" label="label" track-by="value" multiple />
           </div>
           <div class="col-md-4">
             <label>From Time</label>
             <input type="time" step="60" v-model="fromTime" class="form-control" @change="updateToTime" />
           </div>
           <div class="col-md-4">
             <label>To Time</label>
             <input type="time" step="60" v-model="toTime" class="form-control" />
           </div>
         </div>
 
         <button class="btn btn-primary mt-2" @click="generateSchedule">Generate Schedule</button>
 
         <div class="table-responsive mt-3" :class="{ 'border border-3 border-danger rounded shadow-sm': isScheduleIncomplete }" v-if="scheduleList.length || showPreTest || showMidExam || showFinalExam">
           <table class="table table-bordered">
             <thead><tr><th>#</th><th>Day</th><th>Date</th><th>From</th><th>To</th><th>Actions</th></tr></thead>
             <tbody>
               <tr v-if="showPreTest && preTestDate" class="bg-primary text-light align-middle">
                 <td colspan="6">
                   <div class="d-flex justify-content-between align-items-center">
                     <span>Pre test: <flatpickr v-model="preTestDate" :config="dateConfigPre" class="mx-2" /> ({{ getDayName(preTestDate) }})</span>
                     <button class="btn btn-danger btn-sm" @click="deletePreTest">Delete</button>
                   </div>
                 </td>
               </tr>
 
               <template v-for="(row,idx) in scheduleList" :key="idx">
                 <tr v-if="row.progress" class="bg-warning text-dark align-middle">
                   <td colspan="6">
                     <div class="d-flex justify-content-between align-items-center">
                       <span><strong>Progress Test – Week {{ row.week }}</strong> ({{ row.day }} – {{ row.date }})</span>
                       <button class="btn btn-danger btn-sm" @click="removeSchedule(idx)">Delete</button>
                     </div>
                   </td>
                 </tr>
                 <tr v-else>
                   <td>{{ lectureIndex(idx) }}</td>
                   <td>{{ row.day }}</td>
                   <td>{{ row.date }}</td>
                   <td><input type="time" step="60" v-model="row.fromTime" class="form-control" /></td>
                   <td><input type="time" step="60" v-model="row.toTime" class="form-control" /></td>
                   <td>
                     <button class="btn btn-danger" @click="removeSchedule(idx)">Delete</button>
                     <button v-if="isScheduleIncomplete" class="btn btn-sm btn-success" @click="addScheduleAfter(idx)">+ Day</button>
                   </td>
                 </tr>
                 <tr v-if="showMidExam && midExamDate && idx==Math.floor(scheduleList.length/2)-1" class="bg-primary text-light align-middle">
                   <td colspan="6">
                     <div class="d-flex justify-content-between align-items-center">
                       <span>MID exam: <flatpickr v-model="midExamDate" :config="dateConfig" class="mx-2" /> ({{ getDayName(midExamDate) }})</span>
                       <button class="btn btn-danger btn-sm" @click="deleteMidExam">Delete</button>
                     </div>
                   </td>
                 </tr>
               </template>
 
               <tr v-if="showFinalExam && finalExamDate" class="bg-primary text-light align-middle">
                 <td colspan="6">
                   <div class="d-flex justify-content-between align-items-center">
                     <span>Final exam: <flatpickr v-model="finalExamDate" :config="dateConfig" class="mx-2" /> ({{ getDayName(finalExamDate) }})</span>
                     <button class="btn btn-danger btn-sm" @click="deleteFinalExam">Delete</button>
                   </div>
                 </td>
               </tr>
             </tbody>
           </table>
         </div>
       </div>
     </div>
 
     <!-- ────── Students Card ────── -->
     <div v-if="showFields" class="card mt-4">
       <div class="card-header">
         <h5>Students</h5>
         <div class="ms-auto">
           <div class="form-check form-switch d-inline-block me-3">
             <input class="form-check-input" type="checkbox" v-model="matchStudentSkills" />
             <label class="form-check-label">Match Student Skills?</label>
           </div>
           <v-select v-model="selectedStudent" :options="filteredStudents" label="name" track-by="id" @update:modelValue="onStudentSelected" />
           <button class="btn btn-success ms-2" @click="showStudentModal = true">Create New Student</button>
         </div>
       </div>
       <div class="card-body">
         <div class="table-responsive" v-if="studentsList.length">
           <table class="table table-bordered">
             <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Books Due</th><th>Actions</th></tr></thead>
             <tbody>
               <tr v-for="(student,idx) in studentsList" :key="idx">
                 <td>{{ idx+1 }}</td><td>{{ student.name }}</td><td>{{ student.phone }}</td><td>{{ student.booksDue ? 'Yes' : 'No' }}</td>
                 <td><button class="btn btn-danger btn-sm" @click="removeStudent(idx)">Delete</button></td>
               </tr>
             </tbody>
           </table>
         </div>
       </div>
     </div>
 
     <button v-if="showFields" class="btn btn-primary mt-3" @click="saveCourse">Update Course</button>
 
     <!-- Student Modal -->
     <div v-if="showStudentModal" class="modal" style="display:block;background:rgba(0,0,0,.5)">
       <div class="modal-dialog" style="margin:10% auto;max-width:500px">
         <div class="modal-content">
           <div class="modal-header"><h5>New Student</h5><button class="btn-close" @click="showStudentModal=false"></button></div>
           <div class="modal-body">
             <div class="mb-3"><label>Name</label><input class="form-control" v-model="newStudentName" /></div>
             <div class="mb-3"><label>Phone</label><input class="form-control" v-model="newStudentPhone" /></div>
             <div class="mb-3"><label>Gender</label><select class="form-control" v-model="newStudentGender"><option disabled value="">Select Gender</option><option value="male">Male</option><option value="female">Female</option></select></div>
             <div class="mb-3"><label>Age</label><input type="number" class="form-control" v-model="newStudentAge" /></div>
             <div class="mb-3"><label>City</label><input class="form-control" v-model="newStudentCity" /></div>
             <div class="mb-3"><label>Specialization</label><input class="form-control" v-model="newStudentSpecialization" /></div>
             <div class="mb-3"><label>Emergency Phone</label><input class="form-control" v-model="newStudentEmergencyPhone" /></div>
             <div class="form-check form-switch"><input class="form-check-input" type="checkbox" v-model="newStudentBooksDue" /><label class="form-check-label">Books Due?</label></div>
           </div>
           <div class="modal-footer"><button class="btn btn-secondary" @click="showStudentModal=false">Close</button><button class="btn btn-primary" @click="addStudent">Add Student</button></div>
         </div>
       </div>
     </div>
   </div>
 </template>
 
 <script>
 import { defineComponent, ref, onMounted, watch, computed, getCurrentInstance } from 'vue'
 import vSelect from 'vue-select'; import 'vue-select/dist/vue-select.css'
 import Flatpickr from 'vue-flatpickr-component'; import 'flatpickr/dist/flatpickr.css'
 import instance from '../instance'
 
 const pad=n=>String(n).padStart(2,'0')
 const fmtDate=d=>`${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`
 const getLabel=(d)=>['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][d.getDay()]
 const skipFriday=d=>{if(d.getDay()==5)d.setDate(d.getDate()+1);return d}
 function nextFreeDay(base,occ){const d=new Date(base);while(true){skipFriday(d);const s=fmtDate(d);if(!occ.has(s))return s;d.setDate(d.getDate()+1)}}
 
 export default defineComponent({
   name:'EditCourse',
   components:{'v-select':vSelect,Flatpickr},
   props:{id:{type:[Number,String],required:true}},
   setup(props){
     const{appContext}=getCurrentInstance(),$toastr=appContext.config.globalProperties.$toastr
     const courseTypes=ref([]),groupTypes=ref([]),instructors=ref([]),meetingPlatforms=ref([]),levels=ref([]),allStudents=ref([])
     const selectedCourseType=ref(null),selectedGroupType=ref(null),selectedInstructor=ref(null),selectedMeetingPlatform=ref(null),selectedLevels=ref([])
     const startDate=ref(''),fromTime=ref(''),toTime=ref(''),preTestDate=ref(''),midExamDate=ref(''),finalExamDate=ref('')
     const showPreTest=ref(true),showMidExam=ref(true),showFinalExam=ref(true)
     const studentCapacity=ref(''),whatsappGroupLink=ref('')
     const days=ref([{label:'Sat',value:6},{label:'Sun',value:0},{label:'Mon',value:1},{label:'Tue',value:2},{label:'Wed',value:3},{label:'Thu',value:4},{label:'Fri',value:5}])
     const selectableDays=computed(()=>days.value.filter(d=>d.value!=5))
     const selectedDays=ref([]),storedSelectedDays=ref([]),scheduleList=ref([]),progressTests=ref([])
     const matchInstructorSkills=ref(false),matchStudentSkills=ref(false),showFields=ref(false),loading=ref(true),globalLoading=ref(false)
     const studentsList=ref([]),selectedStudent=ref(null),showStudentModal=ref(false)
     const newStudentName=ref(''),newStudentPhone=ref(''),newStudentGender=ref(''),newStudentAge=ref(''),newStudentCity=ref(''),newStudentSpecialization=ref(''),newStudentEmergencyPhone=ref(''),newStudentBooksDue=ref(false)
     const progressTestDay=ref(null),dateConfig=ref({dateFormat:'Y-m-d',allowInput:true}),dateConfigPre=ref({dateFormat:'Y-m-d',allowInput:true})
     const initialized=ref(false)
 
     onMounted(()=>{selectedDays.value=days.value.filter(d=>d.value!=5);fetchRequirements()})
     async function fetchRequirements(){globalLoading.value=true
       try{const{data}=await instance.get('/course-requirements',{params:{id:props.id}})
         courseTypes.value=data.courseTypes||[];groupTypes.value=data.groupTypes||[];instructors.value=data.instructors||[];meetingPlatforms.value=data.meeting_platforms||[];levels.value=data.levels||[];allStudents.value=data.students||[]
         if(data.course)populateCourse(data.course)
       }finally{loading.value=false;globalLoading.value=false}}
     function populateCourse(c){
       selectedCourseType.value=courseTypes.value.find(x=>x.id==c.course_type_id)||null
       selectedGroupType.value=groupTypes.value.find(x=>x.id==c.group_type_id)||null
       selectedInstructor.value=instructors.value.find(x=>x.id==c.instructor_id)||null
       selectedMeetingPlatform.value=meetingPlatforms.value.find(x=>x.id==c.meeting_platform_id)||null
       startDate.value=c.start_date||'';[fromTime.value,toTime.value]=c.time.split(' - ')
       preTestDate.value=c.pre_test_date||'';midExamDate.value=c.mid_exam_date||'';finalExamDate.value=c.final_exam_date||''
       showPreTest.value=!!c.pre_test_date;showMidExam.value=!!c.mid_exam_date;showFinalExam.value=!!c.final_exam_date
       progressTestDay.value=c.progress_test_day||null;studentCapacity.value=c.student_capacity||'';whatsappGroupLink.value=c.whatsapp_group_link||''
       if(c.days){const parts=c.days.split('-');selectedDays.value=days.value.filter(d=>parts.includes(d.label));storedSelectedDays.value=parts.map(l=>days.value.find(d=>d.label==l).value)}
       scheduleList.value=[]
       if(c.schedules){c.schedules.forEach(s=>scheduleList.value.push({day:s.day,date:s.date,fromTime:s.from_time,toTime:s.to_time,progress:false}))}
       if(c.progress_tests){progressTests.value=c.progress_tests.map(pt=>({week:pt.week,date:pt.date,day:getLabel(new Date(pt.date))}))
         progressTests.value.forEach(pt=>scheduleList.value.push({progress:true,week:pt.week,day:pt.day,date:pt.date,fromTime:fromTime.value,toTime:toTime.value}))}
       if(c.students){studentsList.value=c.students.map(s=>({id:s.id,name:s.name,phone:s.phone,booksDue:s.books_due}))}
       scheduleList.value.sort((a,b)=>new Date(a.date)-new Date(b.date))
       showFields.value=true;initialized.value=true}
     watch(startDate,()=>{if(initialized.value)generateSchedule()});watch(fromTime,()=>{if(initialized.value)updateToTime()})
     watch(progressTestDay,()=>{if(initialized.value)generateSchedule()})
     watch(preTestDate,val=>{if(initialized.value&&startDate.value&&new Date(val)>new Date(startDate.value)){preTestDate.value=startDate.value;$toastr.error('Pre-test date cannot be after the course start date')}})
     watch(midExamDate,()=>{if(initialized.value)generateSchedule()})
     const filteredInstructors=computed(()=>{if(!matchInstructorSkills.value||!selectedCourseType.value?.skills)return instructors.value;const ids=selectedCourseType.value.skills.map(s=>s.id);return instructors.value.filter(i=>i.skills?.some(s=>ids.includes(s.id)))})
     const filteredStudents=computed(()=>{const available=allStudents.value.filter(s=>!studentsList.value.some(ss=>ss.id==s.id));if(!matchStudentSkills.value||!selectedCourseType.value?.skills)return available;const ids=selectedCourseType.value.skills.map(s=>s.id);return available.filter(s=>s.skills?.some(sk=>ids.includes(sk.id)))})
     const isScheduleIncomplete=computed(()=>scheduleList.value.filter(r=>!r.progress).length<+(selectedCourseType.value?.duration||0))
     function updateToTime(){if(!fromTime.value||!selectedGroupType.value?.lesson_duration){toTime.value='';return}const plus=+selectedGroupType.value.lesson_duration;const[h,m]=fromTime.value.split(':').map(Number);const tot=h*60+m+plus;toTime.value=`${pad(Math.floor(tot/60)%24)}:${pad(tot%60)}`}
     function lectureIndex(idx){let n=0;for(let i=0;i<=idx;i++)if(!scheduleList.value[i].progress)n++;return n}
     function pushLecture(d){scheduleList.value.push({day:getLabel(d),date:fmtDate(d),fromTime:fromTime.value,toTime:toTime.value,progress:false})}
     function validateScheduleInputs(){
       const errs=[]
       if(!selectedCourseType.value)errs.push('Select Course Type')
       if(!startDate.value)errs.push('Select Start Date')
       if(!fromTime.value||!toTime.value)errs.push('Set From/To times')
       if(!selectedDays.value.length)errs.push('Choose at least one day')
       if(errs.length){errs.forEach(e=>$toastr.error(e));return false}
       return true
          }
          function generateSchedule() {
  console.log('=== Generate Schedule Called ===')
  console.log('showPreTest:', showPreTest.value)
  console.log('showMidExam:', showMidExam.value)
  console.log('showFinalExam:', showFinalExam.value)
  
  if (!validateScheduleInputs()) return
  
  const totalLessons = +selectedCourseType.value.duration || 0
  if (!totalLessons) return

  // Clear existing non-progress schedule items
  scheduleList.value = scheduleList.value.filter(r => r.progress)
  progressTests.value = [] // Reset progress tests
  
  const occupied = new Set(scheduleList.value.map(r => r.date))

  // ========== Handle Pre-test ==========
  if (showPreTest.value) {
    console.log('Processing pre-test...')
    
    // Always set a pre-test date if showPreTest is true
    let preDate = new Date(startDate.value)
    preDate.setDate(preDate.getDate() - 1) // Day before start date
    
    // Skip Friday if needed
    if (preDate.getDay() === 5) { // If Friday, go to Thursday
      preDate.setDate(preDate.getDate() - 1)
    }
    
    // Skip Saturday if needed (go to Thursday)
    if (preDate.getDay() === 6) { // If Saturday, go to Thursday  
      preDate.setDate(preDate.getDate() - 2)
    }
    
    preTestDate.value = fmtDate(preDate)
    occupied.add(preTestDate.value)
    
    console.log('Pre-test date set to:', preTestDate.value)
  } else {
    preTestDate.value = ''
    console.log('Pre-test disabled')
  }

  // ========== Generate regular lessons ==========
  let currentDate = new Date(startDate.value)
  skipFriday(currentDate) // Start on the actual start date, but skip if Friday

  let lessonsGenerated = 0
  let weekNumber = 1
  let lessonsThisWeek = 0

  while (lessonsGenerated < totalLessons) {
    const dayOfWeek = currentDate.getDay()
    const dateString = fmtDate(currentDate)

    // Check if this day is selected and not occupied
    if (storedSelectedDays.value.includes(dayOfWeek) && !occupied.has(dateString)) {
      // Add regular lesson
      pushLecture(currentDate)
      occupied.add(dateString)
      lessonsGenerated++
      lessonsThisWeek++

      // Check if we need to add a progress test
      if (progressTestDay.value !== null && 
          lessonsThisWeek >= 3 && // At least 3 lessons per week before progress test
          dayOfWeek === progressTestDay.value) {
        
        // Find next available day for progress test
        let progressDate = new Date(currentDate)
        progressDate.setDate(progressDate.getDate() + 1)
        skipFriday(progressDate)

        // Find the next available day that matches progressTestDay
        while (progressDate.getDay() !== progressTestDay.value || occupied.has(fmtDate(progressDate))) {
          progressDate.setDate(progressDate.getDate() + 1)
          skipFriday(progressDate)
        }

        const progressDateString = fmtDate(progressDate)
        
        // Add progress test
        const progressTest = {
          week: weekNumber,
          date: progressDateString,
          day: getLabel(progressDate)
        }
        
        progressTests.value.push(progressTest)
        scheduleList.value.push({
          progress: true,
          week: weekNumber,
          day: progressTest.day,
          date: progressTest.date,
          fromTime: fromTime.value,
          toTime: toTime.value
        })
        
        occupied.add(progressDateString)
        weekNumber++
        lessonsThisWeek = 0 // Reset weekly lesson count
      }
    }

    // Move to next day
    currentDate.setDate(currentDate.getDate() + 1)
    skipFriday(currentDate)
  }

  // ========== Handle Mid-exam ==========
  if (showMidExam.value) {
    console.log('Processing mid-exam...')
    
    const regularLessons = scheduleList.value.filter(r => !r.progress)
    
    if (regularLessons.length > 0) {
      const midPoint = Math.floor(regularLessons.length / 2)
      
      // Always generate a new mid-exam date based on the mid-point
      if (midPoint < regularLessons.length) {
        const midLessonDate = new Date(regularLessons[midPoint].date)
        
        // Set mid-exam to the day after the mid-point lesson
        let midExamCandidate = new Date(midLessonDate)
        midExamCandidate.setDate(midExamCandidate.getDate() + 1)
        skipFriday(midExamCandidate)
        
        // Find next free day that's not occupied
        while (occupied.has(fmtDate(midExamCandidate))) {
          midExamCandidate.setDate(midExamCandidate.getDate() + 1)
          skipFriday(midExamCandidate)
        }
        
        midExamDate.value = fmtDate(midExamCandidate)
        occupied.add(midExamDate.value)
        
        console.log('Mid-exam date set to:', midExamDate.value)
      }
    }
  } else {
    midExamDate.value = ''
    console.log('Mid-exam disabled')
  }

  // ========== Handle Final exam ==========
  if (showFinalExam.value) {
    console.log('Processing final exam...')
    
    const regularLessons = scheduleList.value.filter(r => !r.progress)
    
    if (regularLessons.length > 0) {
      // Always generate final exam after the last lesson
      const lastLessonDate = new Date(regularLessons[regularLessons.length - 1].date)
      let finalExamCandidate = new Date(lastLessonDate)
      finalExamCandidate.setDate(finalExamCandidate.getDate() + 1)
      skipFriday(finalExamCandidate)
      
      // Find next free day for final exam
      while (occupied.has(fmtDate(finalExamCandidate))) {
        finalExamCandidate.setDate(finalExamCandidate.getDate() + 1)
        skipFriday(finalExamCandidate)
      }
      
      finalExamDate.value = fmtDate(finalExamCandidate)
      
      console.log('Final exam date set to:', finalExamDate.value)
    }
  } else {
    finalExamDate.value = ''
    console.log('Final exam disabled')
  }

  // Sort schedule by date
  scheduleList.value.sort((a, b) => new Date(a.date) - new Date(b.date))
  
  console.log('=== Schedule Generation Complete ===')
  console.log('Final preTestDate:', preTestDate.value)
  console.log('Final midExamDate:', midExamDate.value)  
  console.log('Final finalExamDate:', finalExamDate.value)
  console.log('Schedule items:', scheduleList.value.length)
}
            
     const deletePreTest=()=>{showPreTest.value=false;preTestDate.value='';generateSchedule()}
     const deleteMidExam=()=>{showMidExam.value=false;midExamDate.value='';generateSchedule()}
     const deleteFinalExam=()=>{showFinalExam.value=false;finalExamDate.value='';generateSchedule()}
     function removeSchedule(i){if(scheduleList.value[i].progress){const wk=scheduleList.value[i].week;progressTests.value=progressTests.value.filter(t=>t.week!=wk)}scheduleList.value.splice(i,1)}
     function addScheduleAfter(idx){if(!isScheduleIncomplete.value)return;const cur=new Date(scheduleList.value[idx].date);cur.setDate(cur.getDate()+1);skipFriday(cur)
       scheduleList.value.splice(idx+1,0,{day:getLabel(cur),date:fmtDate(cur),fromTime:fromTime.value,toTime:toTime.value,progress:false})}
     function onStudentSelected(v){if(v){studentsList.value.push({...v});selectedStudent.value=null}}
     async function addStudent(){if(!newStudentName.value||!newStudentPhone.value)return;globalLoading.value=true
       try{const{data}=await instance.post('/students',{name:newStudentName.value,phone:newStudentPhone.value,gender:newStudentGender.value,age:newStudentAge.value,city:newStudentCity.value,specialization:newStudentSpecialization.value,emergency_phone:newStudentEmergencyPhone.value,booksDue:newStudentBooksDue.value})
         studentsList.value.push(data.student??data);showStudentModal.value=false;newStudentName.value=newStudentPhone.value=newStudentGender.value=newStudentAge.value=newStudentCity.value=newStudentSpecialization.value=newStudentEmergencyPhone.value='';newStudentBooksDue.value=false}finally{globalLoading.value=false}}
     function removeStudent(i){studentsList.value.splice(i,1)}
     async function saveCourse(){const errs=[]
       if(!selectedCourseType.value)errs.push('Course Type is required')
       if(!selectedGroupType.value)errs.push('Group Type is required')
       if(!selectedInstructor.value)errs.push('Instructor is required')
       if(!startDate.value)errs.push('Start Date is required')
       if(!scheduleList.value.filter(r=>!r.progress).length)errs.push('Schedule cannot be empty')
       if(!studentsList.value.length)errs.push('At least one student is required')
       if(errs.length){errs.forEach(e=>$toastr.error(e));return}
       const payload={course_type_id:selectedCourseType.value.id,group_type_id:selectedGroupType.value.id,instructor_id:selectedInstructor.value.id,start_date:startDate.value,pre_test_date:showPreTest.value?preTestDate.value:null,mid_exam_date:showMidExam.value?midExamDate.value:null,final_exam_date:showFinalExam.value?finalExamDate.value:null,progress_tests:progressTests.value.map(t=>({week:t.week,date:t.date})),student_capacity:studentCapacity.value,whatsapp_group_link:whatsappGroupLink.value||null,time:`${fromTime.value} - ${toTime.value}`,meeting_platform_id:selectedMeetingPlatform.value?selectedMeetingPlatform.value.id:null,selected_days:storedSelectedDays.value,progress_test_day:progressTestDay.value,levels:selectedLevels.value.map(l=>l.id),schedule:scheduleList.value.filter(r=>!r.progress).map(r=>({day:r.day,date:r.date,fromTime:r.fromTime,toTime:r.toTime})),students:studentsList.value.map(s=>s.id)}
       try{globalLoading.value=true;await instance.put(`/courses/${props.id}`,payload);$toastr.success('Course updated successfully')}catch(e){$toastr.error(e.response?.data?.message||'Save failed')}finally{globalLoading.value=false}}
     return{courseTypes,groupTypes,instructors,meetingPlatforms,levels,allStudents,selectedCourseType,selectedGroupType,selectedInstructor,selectedMeetingPlatform,selectedLevels,startDate,fromTime,toTime,preTestDate,midExamDate,finalExamDate,showPreTest,showMidExam,showFinalExam,studentCapacity,whatsappGroupLink,days,selectedDays,storedSelectedDays,scheduleList,matchInstructorSkills,matchStudentSkills,showFields,loading,globalLoading,studentsList,selectedStudent,showStudentModal,newStudentName,newStudentPhone,newStudentGender,newStudentAge,newStudentCity,newStudentSpecialization,newStudentEmergencyPhone,newStudentBooksDue,progressTestDay,progressTests,dateConfig,dateConfigPre,filteredInstructors,filteredStudents,isScheduleIncomplete,updateFields:()=>{showFields.value=!!(selectedCourseType.value&&selectedGroupType.value);if(selectedGroupType.value)studentCapacity.value=selectedGroupType.value.student_capacity||''},updateToTime,generateSchedule,deletePreTest,deleteMidExam,deleteFinalExam,removeSchedule,addScheduleAfter,onStudentSelected,addStudent,removeStudent,saveCourse,selectableDays,getDayName:d=>d?['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][new Date(d).getDay()]:'',lectureIndex}
   }
 })
 </script>
 
 <style scoped>
 .spinner-container{margin-top:10px}
 .spinner{width:25px;height:25px;border:4px solid #bbb;border-top:4px solid #333;border-radius:50%;animation:spin 1s linear infinite}
 .global-spinner-overlay{position:fixed;inset:0;background:rgba(255,255,255,0.7);z-index:9999;display:flex;align-items:center;justify-content:center}
 @keyframes spin{to{transform:rotate(360deg)}}
 </style>
 