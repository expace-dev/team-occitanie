import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

// Create an instance of Notyf
const notyf = new Notyf({
    duration: 5000,
    ripple: true,
    position: {
        x: 'right',
        y: 'top',
    },
    types: [
        {
            type: 'info',
            background: '#00bfff',
            icon: true
        },
        {
            type: 'warning',
            background: 'orange',
            icon: false
        },
        {
            type: 'success',
            background: 'dodgerblue',
            duration: 5000,
            dismissible: true
        },
        {
            type: 'error',
            background: 'red',
            duration: 5000,
            dismissible: true,
            ripple: true
        },
    ]
});

let messages = document.querySelectorAll('#notyf-message');

messages.forEach(message => {
    if (message.className === 'success') {
        notyf.success(message.innerHTML);
    }

    if (message.className === 'error') {
        notyf.error(message.innerHTML);
    }

    if (message.className === 'info') {
        notyf.open({
            type: 'info',
            message: '<b>Info</b> - ' + message.innerHTML,
        });
    }

    if (message.className === 'warning') {
        notyf.open({
            type: 'warning',
            message: '<b>Warning</b> - ' + message.innerHTML
        });
    }
});