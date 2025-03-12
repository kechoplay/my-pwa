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

self.addEventListener('push', event => {
    const clickedNotification = event.notification;
    const notificationData = clickedNotification.data;
    clickedNotification.close(); // Close the notification pop-up
    const urlToOpen = notificationData.notification.click_action;
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            const isAppOpen = clientList.length > 0;

            if (isAppOpen) {
                // App đang mở → gửi message cho app xử lý nội bộ (không show notification)
                clientList.forEach(client => {
                    client.postMessage({
                        type: 'PUSH_MESSAGE',
                    });
                });
                console.log('📱 App đang mở → không show notification');
            } else {
                // App không mở → hiển thị notification như bình thường

            }
        })
    );
});
