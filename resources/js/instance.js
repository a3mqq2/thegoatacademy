import axios from 'axios';
import { getMeta } from './functions';
let token = getMeta('ast');
 const instance = axios.create({
    baseURL: '/api',
    timeout: 50000,
    responseType: 'json',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Access-Control-Allow-Credential': true
    }
});

export default instance;