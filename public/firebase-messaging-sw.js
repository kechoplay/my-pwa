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

self.addEventListener('notificationclick', event => {
    const clickedNotification = event.notification;
    const notificationData = clickedNotification.data;
    clickedNotification.close(); // Close the notification pop-up

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            // Check if any window is already open
            for (let client of windowClients) {
                // If app is open, focus it and post message
                if (client.url === '/' && 'focus' in client) {
                    client.postMessage(notificationData); // send data
                    return client.focus();
                }
            }

            // If not open, open new window with data URL
            if (notificationData && notificationData.url) {
                return clients.openWindow('/');
            }

            // Fallback: open homepage
            clients.postMessage(notificationData); // send data
            return clients.openWindow('/');
        })
    );
});

messaging.onMessage((payload) => {
    console.log("Message received:", payload);
    const notification = new Notification(payload.notification.title, {
        body: payload.notification.body,
        icon: '/images/favicon/favicon-96x96.png',
        data: {
            count_data: '1'
        }
    });
    notification.onclick = function (event) {
        event.preventDefault();
        const data = event.currentTarget.data
        document.getElementById('message').textContent = data.count_data;
    }
});
