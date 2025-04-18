<template>
  <div>
    <div v-if="globalLoading" class="global-spinner-overlay">
      <div class="spinner"></div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <label>Select Course Type</label>
        <div v-if="loading" class="spinner-container"><div class="spinner"></div></div>
        <v-select
          v-else
          v-model="selectedCourseType"
          :options="courseTypes"
          label="name"
          track-by="id"
          placeholder="Select a Course Type"
          :disabled="loading"
          @update:modelValue="updateFields"
        />
      </div>
      <div class="col-md-4">
        <label>Select Group Type</label>
        <div v-if="loading" class="spinner-container"><div class="spinner"></div></div>
        <v-select
          v-else
          v-model="selectedGroupType"
          :options="groupTypes"
          label="name"
          track-by="id"
          placeholder="Select a Group Type"
          :disabled="loading"
          @update:modelValue="updateFields"
        />
      </div>
      <div class="col-md-4">
        <label>Select Instructor</label>
        <div v-if="loading" class="spinner-container"><div class="spinner"></div></div>
        <v-select
          v-else
          v-model="selectedInstructor"
          :options="filteredInstructors"
          label="name"
          track-by="id"
          placeholder="Select an Instructor"
          :disabled="loading"
        />
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-md-4">
        <label>Select Levels</label>
        <v-select
          v-model="selectedLevels"
          :options="levels"
          label="name"
          track-by="id"
          multiple
          placeholder="Select Levels"
        />
      </div>
    </div>

    <div class="row mt-2" v-if="showFields">
      <div class="col-md-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="matchSkillsSwitch" v-model="matchInstructorSkills"/>
          <label class="form-check-label" for="matchSkillsSwitch">Only show instructors that match this course type's skills</label>
        </div>
      </div>
    </div>

    <div v-if="showFields" class="row mt-3">
      <div class="col-md-4">
        <label>Start Date</label>
        <flatpickr v-model="startDate" :config="dateConfig" class="form-control"/>
      </div>
      <div class="col-md-4">
        <label>Student Capacity</label>
        <input type="number" v-model="studentCapacity" class="form-control"/>
      </div>
      <div class="col-md-4 mt-2">
        <label>Meeting Platform</label>
        <v-select
          v-model="selectedMeetingPlatform"
          :options="meetingPlatforms"
          label="name"
          track-by="id"
          placeholder="Select a Meeting Platform"
        />
      </div>
      <div class="col-md-12 mt-2">
        <label>Whatsapp Group Link</label>
        <input type="text" v-model="whatsappGroupLink" class="form-control" placeholder="Enter WhatsApp group link"/>
      </div>
    </div>

    <div v-if="showFields" class="card mt-4">
      <div class="card-header"><h5>Schedule</h5></div>
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
            <label>From Time (24‑h)</label>
            <input type="time" step="60" v-model="fromTime" class="form-control" @change="updateToTime"/>
          </div>
          <div class="col-md-4">
            <label>To Time (24‑h)</label>
            <input type="time" step="60" v-model="toTime" class="form-control"/>
          </div>
        </div>
        <button class="btn btn-primary mt-2" @click="generateSchedule">Generate Schedule</button>
        <div class="table-responsive mt-3" v-if="scheduleList.length">
          <table class="table table-bordered">
            <thead>
              <tr><th>#</th><th>Day</th><th>Date</th><th>From</th><th>To</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <tr v-if="showPreTest && preTestDate" class="bg-primary text-center text-light align-middle">
                <td colspan="6">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-light">Pre test:
                      <flatpickr v-model="preTestDate" :config="dateConfig" class="d-inline-block mx-2"/>
                      ({{ getDayName(preTestDate) }})
                    </span>
                    <button class="btn btn-danger btn-sm" @click="deletePreTest">Delete</button>
                  </div>
                </td>
              </tr>
              <template v-for="(item, idx) in scheduleList" :key="idx">
                <tr>
                  <td>{{ idx + 1 }}</td>
                  <td>{{ item.day }}</td>
                  <td>{{ item.date }}</td>
                  <td><input type="time" step="60" v-model="item.fromTime" class="form-control"/></td>
                  <td><input type="time" step="60" v-model="item.toTime" class="form-control"/></td>
                  <td><button class="btn btn-danger" @click="removeSchedule(idx)">Delete</button></td>
                </tr>
                <tr v-if="showMidExam && midExamDate && idx === Math.floor(scheduleList.length/2)-1"
                    class="bg-primary text-center text-light align-middle">
                  <td colspan="6">
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-light">MID exam:
                        <flatpickr v-model="midExamDate" :config="dateConfig" class="d-inline-block mx-2"/>
                        ({{ getDayName(midExamDate) }})
                      </span>
                      <button class="btn btn-danger btn-sm" @click="deleteMidExam">Delete</button>
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-if="showFinalExam && finalExamDate" class="bg-primary text-center text-light align-middle">
                <td colspan="6">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-light">Final exam:
                      <flatpickr v-model="finalExamDate" :config="dateConfig" class="d-inline-block mx-2"/>
                      ({{ getDayName(finalExamDate) }})
                    </span>
                    <button class="btn btn-danger btn-sm" @click="deleteFinalExam">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showFields" class="card mt-4">
      <div class="card-header">
        <h5>Students</h5>
        <div class="ms-auto">
          <div class="form-check form-switch d-inline-block me-3">
            <input class="form-check-input" type="checkbox" v-model="matchStudentSkills"/>
            <label class="form-check-label">Match Student Skills?</label>
          </div>
          <v-select
            v-model="selectedStudent"
            :options="filteredStudents"
            label="name"
            track-by="id"
            placeholder="Select a Student"
            @update:modelValue="onStudentSelected"
          />
          <button class="btn btn-success ms-2" @click="showStudentModal=true">Create New Student</button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive" v-if="studentsList.length">
          <table class="table table-bordered">
            <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Books Due</th><th>Actions</th></tr></thead>
            <tbody>
              <tr v-for="(student, idx) in studentsList" :key="idx">
                <td>{{ idx+1 }}</td>
                <td>{{ student.name }}</td>
                <td>{{ student.phone }}</td>
                <td>{{ student.booksDue ? 'Yes':'No' }}</td>
                <td><button class="btn btn-danger btn-sm" @click="removeStudent(idx)">Delete</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <button v-if="showFields" class="btn btn-primary mt-3" @click="saveCourse">{{ id? 'Update Course':'Create Course' }}</button>

    <div v-if="showStudentModal" class="modal" style="display:block; background:rgba(0,0,0,0.5)">
      <div class="modal-dialog" style="margin:10% auto; max-width:500px">
        <div class="modal-content">
          <div class="modal-header"><h5>New Student</h5><button class="btn-close" @click="showStudentModal=false"></button></div>
          <div class="modal-body">
            <div class="mb-3"><label>Name</label><input class="form-control" v-model="newStudentName"/></div>
            <div class="mb-3"><label>Phone</label><input class="form-control" v-model="newStudentPhone"/></div>
            <div class="mb-3">
              <label>Gender</label>
              <select class="form-control" v-model="newStudentGender"><option disabled value="">Select Gender</option><option value="male">Male</option><option value="female">Female</option></select>
            </div>
            <div class="mb-3"><label>Age</label><input type="number" class="form-control" v-model="newStudentAge"/></div>
            <div class="mb-3"><label>City</label><input class="form-control" v-model="newStudentCity"/></div>
            <div class="mb-3"><label>Specialization</label><input class="form-control" v-model="newStudentSpecialization"/></div>
            <div class="mb-3"><label>Emergency Phone</label><input class="form-control" v-model="newStudentEmergencyPhone"/></div>
            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" v-model="newStudentBooksDue"/><label class="form-check-label">Books Due?</label></div>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" @click="showStudentModal=false">Close</button><button class="btn btn-primary" @click="addStudent">Add Student</button></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted, watch, computed, getCurrentInstance, nextTick } from "vue";
import vSelect from "vue-select"; import "vue-select/dist/vue-select.css";
import Flatpickr from "vue-flatpickr-component"; import "flatpickr/dist/flatpickr.css";
import instance from "../instance";

function formatDateLocal(d) {
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;
}
function skipIfFriday(d) {
  if (d.getDay()===5) d.setDate(d.getDate()+1);
  return d;
}
function nextFreeDay(base, occupied) {
  const d=new Date(base);
  while(true){
    skipIfFriday(d);
    const s=formatDateLocal(d);
    if(!occupied.has(s)) return s;
    d.setDate(d.getDate()+1);
  }
}

export default defineComponent({
  name:"CreateOrEditCourse",
  components:{"v-select":vSelect,Flatpickr},
  props:{id:[Number,String]},
  setup(props){
    const { appContext } = getCurrentInstance();
    const $toastr = appContext.config.globalProperties.$toastr;

    const courseTypes=ref([]),groupTypes=ref([]),instructors=ref([]),
          meetingPlatforms=ref([]),levels=ref([]),allStudents=ref([]);

    const selectedCourseType=ref(null),selectedGroupType=ref(null),
          selectedInstructor=ref(null),selectedMeetingPlatform=ref(null),
          selectedLevels=ref([]);

    const startDate=ref(""),fromTime=ref(""),toTime=ref(""),
          preTestDate=ref(""),midExamDate=ref(""),finalExamDate=ref("");

    const showPreTest=ref(true),showMidExam=ref(true),showFinalExam=ref(true);

    const studentCapacity=ref(""),whatsappGroupLink=ref("");

    const days=ref([
      {label:"Sat",value:6},{label:"Sun",value:0},{label:"Mon",value:1},
      {label:"Tue",value:2},{label:"Wed",value:3},{label:"Thu",value:4},
      {label:"Fri",value:5}
    ]);
    const selectedDays=ref([]),storedSelectedDays=ref([]),scheduleList=ref([]);

    const matchInstructorSkills=ref(false),matchStudentSkills=ref(false);
    const showFields=ref(false),loading=ref(true),globalLoading=ref(false);

    const studentsList=ref([]),selectedStudent=ref(null);
    const showStudentModal=ref(false);
    const newStudentName=ref(""),newStudentPhone=ref(""),
          newStudentGender=ref(""),newStudentAge=ref(""),
          newStudentCity=ref(""),newStudentSpecialization=ref(""),
          newStudentEmergencyPhone=ref(""),newStudentBooksDue=ref(false);

    const dateConfig=ref({dateFormat:"Y-m-d",allowInput:true});

    onMounted(()=>{
      selectedDays.value=days.value.filter(d=>d.value!==5);
      fetchRequirements();
    });

    async function fetchRequirements(){
      globalLoading.value=true;
      try{
        const params=props.id?{id:props.id}:{};
        const {data}=await instance.get("/course-requirements",{params});
        courseTypes.value=data.courseTypes||[];
        groupTypes.value=data.groupTypes||[];
        instructors.value=data.instructors||[];
        meetingPlatforms.value=data.meeting_platforms||[];
        levels.value=data.levels||[];
        allStudents.value=data.students||[];
        if(data.course) populateCourse(data.course);
      }finally{loading.value=false;globalLoading.value=false;}
    }

    function populateCourse(c){
      selectedCourseType.value=courseTypes.value.find(x=>x.id===c.course_type_id)||null;
      selectedGroupType.value=groupTypes.value.find(x=>x.id===c.group_type_id)||null;
      selectedInstructor.value=instructors.value.find(x=>x.id===c.instructor_id)||null;
      selectedMeetingPlatform.value=meetingPlatforms.value.find(x=>x.id===c.meeting_platform_id)||null;

      startDate.value=c.start_date||"";
      [fromTime.value,toTime.value]=c.time.split(" - ");
      preTestDate.value=c.pre_test_date||"";
      midExamDate.value=c.mid_exam_date||"";
      finalExamDate.value=c.final_exam_date||"";

      showPreTest.value=!!c.pre_test_date;
      showMidExam.value=!!c.mid_exam_date;
      showFinalExam.value=!!c.final_exam_date;

      studentCapacity.value=c.student_capacity||"";
      whatsappGroupLink.value=c.whatsapp_group_link||"";

      if(c.days){
        const parts=c.days.split("-");
        selectedDays.value=days.value.filter(d=>parts.includes(d.label));
        storedSelectedDays.value=parts.map(l=>days.value.find(d=>d.label===l).value);
      }
      if(c.schedules){
        scheduleList.value=c.schedules.map(s=>({day:s.day,date:s.date,fromTime:s.from_time,toTime:s.to_time}));
      }
      if(c.students){
        studentsList.value=c.students.map(s=>({id:s.id,name:s.name,phone:s.phone,booksDue:s.books_due}));
      }
      showFields.value=true;
      nextTick(() => {
          generateSchedule();
      });
    }

    function updateFields(){
      showFields.value=!!(selectedCourseType.value&&selectedGroupType.value);
      if(selectedGroupType.value) studentCapacity.value=selectedGroupType.value.student_capacity||"";
    }

    const updateToTime=()=>{
      if(!fromTime.value||!selectedGroupType.value?.lesson_duration) return toTime.value="";
      const plus=Number(selectedGroupType.value.lesson_duration);
      const [h,m]=fromTime.value.split(":").map(Number);
      const tot=h*60+m+plus;
      toTime.value=`${String(Math.floor(tot/60)%24).padStart(2,"0")}:${String(tot%60).padStart(2,"0")}`;
    };
    watch(fromTime,updateToTime);

    function pushClass(d){
      scheduleList.value.push({day:days.value.find(x=>x.value===d.getDay()).label,date:formatDateLocal(d),fromTime:fromTime.value,toTime:toTime.value});
    }

    function generateSchedule(){
      if(!(selectedCourseType.value&&startDate.value&&fromTime.value&&toTime.value&&selectedDays.value.length)) return;
      const total=Number(selectedCourseType.value.duration||0);
      if(!total)return;
      scheduleList.value=[];storedSelectedDays.value=selectedDays.value.map(d=>d.value);
      const occupied=new Set();
      if(showPreTest.value){
        if(!preTestDate.value){
          const p=new Date(`${startDate.value}T00:00:00`);p.setDate(p.getDate()-1);
          preTestDate.value=formatDateLocal(skipIfFriday(p));
        }
        occupied.add(preTestDate.value);
      }else preTestDate.value="";
      let cur=preTestDate.value?new Date(`${preTestDate.value}T00:00:00`):new Date(`${startDate.value}T00:00:00`);
      if(showPreTest.value)cur.setDate(cur.getDate()+1);
      cur=skipIfFriday(cur);
      const half=Math.floor(total/2);
      while(scheduleList.value.length<half){
        if(storedSelectedDays.value.includes(cur.getDay())&&!occupied.has(formatDateLocal(cur))){pushClass(cur);occupied.add(formatDateLocal(cur));}
        cur.setDate(cur.getDate()+1);
      }
      if(showMidExam.value){
        if(!midExamDate.value)midExamDate.value=nextFreeDay(cur,occupied);
        occupied.add(midExamDate.value);
        cur=new Date(`${midExamDate.value}T00:00:00`);cur.setDate(cur.getDate()+1);
      }else midExamDate.value="";
      const needed=total-half;let gen=0;
      while(gen<needed){
        if(storedSelectedDays.value.includes(cur.getDay())&&!occupied.has(formatDateLocal(cur))){pushClass(cur);occupied.add(formatDateLocal(cur));gen++;}
        cur.setDate(cur.getDate()+1);
      }
      if(showFinalExam.value){
        if(!finalExamDate.value)finalExamDate.value=nextFreeDay(cur,occupied);
      }else finalExamDate.value="";
    }

    function deletePreTest(){showPreTest.value=false;preTestDate.value="";generateSchedule();}
    function deleteMidExam(){showMidExam.value=false;midExamDate.value="";generateSchedule();}
    function deleteFinalExam(){showFinalExam.value=false;finalExamDate.value="";generateSchedule();}
    function removeSchedule(i){scheduleList.value.splice(i,1);}

    watch(preTestDate,generateSchedule);
    watch(midExamDate,generateSchedule);
    watch(finalExamDate,generateSchedule);

    const filteredInstructors=computed(()=>{
      if(!matchInstructorSkills.value||!selectedCourseType.value?.skills)return instructors.value;
      const ids=selectedCourseType.value.skills.map(s=>s.id);
      return instructors.value.filter(ins=>ins.skills?.some(s=>ids.includes(s.id)));
    });

    const filteredStudents=computed(()=>{
      const avail=allStudents.value.filter(s=>!studentsList.value.some(ss=>ss.id===s.id));
      if(!matchStudentSkills.value||!selectedCourseType.value?.skills)return avail;
      const ids=selectedCourseType.value.skills.map(s=>s.id);
      return avail.filter(s=>s.skills?.some(sk=>ids.includes(sk.id)));
    });

    function onStudentSelected(v){if(!v)return;studentsList.value.push({...v});selectedStudent.value=null;}
    async function addStudent(){if(!newStudentName.value||!newStudentPhone.value)return;globalLoading.value=true;try{const{data}=await instance.post("/students",{name:newStudentName.value,phone:newStudentPhone.value,gender:newStudentGender.value,age:newStudentAge.value,city:newStudentCity.value,specialization:newStudentSpecialization.value,emergency_phone:newStudentEmergencyPhone.value,booksDue:newStudentBooksDue.value});studentsList.value.push(data.student??data);showStudentModal.value=false;newStudentName.value=newStudentPhone.value=newStudentGender.value=newStudentAge.value=newStudentCity.value=newStudentSpecialization.value=newStudentEmergencyPhone.value="";newStudentBooksDue.value=false;}finally{globalLoading.value=false;}}

    function removeStudent(i){studentsList.value.splice(i,1);}

    async function saveCourse(){
      const errs=[];if(!selectedCourseType.value)errs.push("Course Type is required");if(!selectedGroupType.value)errs.push("Group Type is required");if(!selectedInstructor.value)errs.push("Instructor is required");if(!startDate.value)errs.push("Start Date is required");if(!scheduleList.value.length)errs.push("Schedule cannot be empty");if(!studentsList.value.length)errs.push("At least one student is required");if(errs.length){errs.forEach(e=>$toastr.error(e));return;}
      const payload={course_type_id:selectedCourseType.value.id,group_type_id:selectedGroupType.value.id,instructor_id:selectedInstructor.value.id,start_date:startDate.value,pre_test_date:showPreTest.value?preTestDate.value:null,mid_exam_date:showMidExam.value?midExamDate.value:null,final_exam_date:showFinalExam.value?finalExamDate.value:null,student_capacity:studentCapacity.value,whatsapp_group_link:whatsappGroupLink.value||null,time:`${fromTime.value} - ${toTime.value}`,meeting_platform_id:selectedMeetingPlatform.value?selectedMeetingPlatform.value.id:null,selected_days:storedSelectedDays.value,levels:selectedLevels.value.map(l=>l.id),schedule:scheduleList.value.map(s=>({day:s.day,date:s.date,fromTime:s.fromTime,toTime:s.toTime})),students:studentsList.value.map(s=>s.id)};
      try{globalLoading.value=true;if(props.id){await instance.put(`/courses/${props.id}`,payload);$toastr.success("Course updated successfully");}else{const{data}=await instance.post("/courses",payload);$toastr.success("Course created successfully");if(data?.course?.id)window.location.href=`/admin/courses/${data.course.id}/print`;}}catch(e){$toastr.error(e.response?.data?.message||"Save failed");}finally{globalLoading.value=false;}}
    function getDayName(d){if(!d)return"";return["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"][new Date(d).getDay()];}

    return{courseTypes,groupTypes,instructors,meetingPlatforms,levels,allStudents,selectedCourseType,selectedGroupType,selectedInstructor,selectedMeetingPlatform,selectedLevels,startDate,fromTime,toTime,preTestDate,midExamDate,finalExamDate,showPreTest,showMidExam,showFinalExam,studentCapacity,whatsappGroupLink,days,selectedDays,storedSelectedDays,scheduleList,matchInstructorSkills,matchStudentSkills,showFields,loading,globalLoading,dateConfig,filteredInstructors,filteredStudents,updateFields,updateToTime,generateSchedule,deletePreTest,deleteMidExam,deleteFinalExam,removeSchedule,studentsList,selectedStudent,showStudentModal,newStudentName,newStudentPhone,newStudentGender,newStudentAge,newStudentCity,newStudentSpecialization,newStudentEmergencyPhone,newStudentBooksDue,onStudentSelected,addStudent,removeStudent,saveCourse,getDayName};
  }
});
</script>

<style scoped>
.spinner-container{margin-top:10px;}
.spinner{width:25px;height:25px;border:4px solid #bbb;border-top:4px solid #333;border-radius:50%;animation:spin 1s linear infinite;}
.global-spinner-overlay{position:fixed;inset:0;background:rgba(255,255,255,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;}
@keyframes spin{to{transform:rotate(360deg);}}
</style>
