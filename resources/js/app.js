import { createApp } from 'vue';
import vSelect from 'vue-select';
import CreateCourse from './components/CreateCourse.vue';
import 'vue-select/dist/vue-select.css';

// Import Toastr
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

const app = createApp({});

// Set Axios globally

// ✅ Register Toastr globally
app.config.globalProperties.$toastr = toastr;

// Configure default Toastr settings
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 3000,
    extendedTimeOut: 1000,
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
    preventDuplicates: true,
};

// Register components
app.component('v-select', vSelect);
app.component('create-course', CreateCourse);
// Mount the app
app.mount('#app');
