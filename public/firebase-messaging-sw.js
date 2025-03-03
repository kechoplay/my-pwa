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
    console.log('Background message received:', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/favicon/favicon-96x96.png'
    };
    self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('push', function(event) {
    const data = event.data.json();  // Assuming the server sends JSON
    const options = {
        body: data.body,
        icon: 'icon.png',
        badge: 'badge.png'
    };
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

messaging.onMessage((payload) => {
    console.log("Message received:", payload);
    new Notification(payload.notification.title, {
        body: payload.notification.body,
        icon: "/logo.png",
    });
});
