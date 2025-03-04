importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyDwl3GCmSxDofUhZOi4knT8jcakQ7Vk2E8",
    authDomain: "pwa-firebase-d30dc.firebaseapp.com",
    projectId: "pwa-firebase-d30dc",
    storageBucket: "pwa-firebase-d30dc.firebasestorage.app",
    messagingSenderId: "1043174958885",
    appId: "1:1043174958885:web:22391757de46441bf01ccc",
    measurementId: "G-32FLFT13BT"
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
