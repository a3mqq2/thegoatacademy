import axios from 'axios';
import { getMeta } from './functions';

const apiToken = getMeta('ast');
const csrfToken = getMeta('csrf-token');

const instance = axios.create({
  baseURL: '/api',
  timeout: 50000,
  responseType: 'json',
  withCredentials: true,              // send cookies for session/CSRF
  headers: {
    'Authorization': `Bearer ${apiToken}`,
    'X-CSRF-TOKEN': csrfToken,       // Laravel’s CSRF header
    'X-Requested-With': 'XMLHttpRequest'
  }
});

export default instance;
