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
