importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyD384SYE1y9KQltxbmQpkSAx07G1G7YcDo",
    authDomain: "telegram2-214b2.firebaseapp.com",
    databaseURL: "https://telegram2-214b2.firebaseio.com",
    projectId: "telegram2-214b2",
    storageBucket: "telegram2-214b2.firebasestorage.app",
    messagingSenderId: "790813086222",
    appId: "1:790813086222:web:005fd7d38ff274e40a625f"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

