if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then(registration => {
            console.log('Service Worker registered:', registration);
        })
        .catch(error => {
            console.log('Service Worker registration failed:', error);
        });
}

document.getElementById('subscribeBtn').addEventListener('click', () => {
    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            messaging.getToken({ vapidKey }).then(token => {
                console.log('FCM Token:', token);
                fetch('/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ token })
                }).then(response => response.json())
                    .then(data => {
                        document.getElementById('message').textContent = 'Subscribed successfully!';
                    });
            }).catch(err => console.error('Error getting token:', err));
        }
    });
});


messaging.onMessage((payload) => {
    console.log("Message received:", payload);
    new Notification(payload.notification.title, {
        body: payload.notification.body,
        icon: '/images/favicon/favicon-96x96.png',
        data: {
            count_data: '1'
        }
    });
});

// self.addEventListener('notificationclick', (event) => {
//     console.log('Notification clicked:', event.notification);
//     event.notification.close(); // Đóng thông báo sau khi nhấp
//
//     // Lấy URL từ data (nếu có) hoặc dùng mặc định
//     const data = event.notification.data;
//     // Focus vào tab hiện có hoặc mở tab mới
//     event.waitUntil(
//         clients.matchAll({ type: 'window', includeUncontrolled: true })
//             .then((clientList) => {
//                 // Tìm tab đã mở với URL khớp
//                 for (const client of clientList) {
//                     client.postMessage({
//                         type: 'NOTIFICATION_CLICK',
//                         data: data
//                     });
//                     if (client.url === '/' && 'focus' in client) {
//                         return client.focus();
//                     }
//                 }
//                 // Nếu không tìm thấy, mở tab mới
//                 if (clients.openWindow) {
//                     return clients.openWindow(urlToOpen);
//                 }
//             })
//             .catch((error) => {
//                 console.error('Error handling notification click:', error);
//             })
//     );
// });

navigator.serviceWorker.addEventListener('message', (event) => {
    if (event.data.type === 'NOTIFICATION_CLICKED') {
        const data = event.data.data;
        document.getElementById('message').textContent = 'test';
    }
});
