importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyAkqFEvZznlbpc6nfgS8z9msRxyMlWANwI",
    authDomain: "fir-bfbd2.firebaseapp.com",
    projectId: "fir-bfbd2",
    storageBucket: "fir-bfbd2.firebasestorage.app",
    messagingSenderId: "183972630362",
    appId: "1:183972630362:web:419dbd05e8ffde97d32d63",
    measurementId: "G-CZGDHBNCLB"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/icon-192.png'
    };
    self.registration.showNotification(notificationTitle, notificationOptions);
});