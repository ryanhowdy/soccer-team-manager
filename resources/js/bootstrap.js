try {
    window.$ = window.jQuery = require('jquery');
} catch (e) {}

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
