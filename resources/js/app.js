import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import $ from 'jquery';
window.$ = window.jQuery = $;

import * as Popper from '@popperjs/core';
window.Popper = Popper;

import Choices from 'choices.js';
window.Choices = Choices;

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import { initFlowbite } from 'flowbite';

document.addEventListener('DOMContentLoaded', () => {
    initFlowbite();
});

import './script';
