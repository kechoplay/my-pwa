if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then(registration => {
            console.log('Service Worker registered:', registration);
            navigator.serviceWorker.addEventListener('message', event => {
                const notificationData = event.data;
                alert(JSON.stringify(notificationData))
                console.log('Notification clicked with data:', notificationData);
                if (notificationData.messageType == 'notification-clicked') {
                    window.location.href = notificationData.notification.click_action
                }
            });
        })
        .catch(error => {
            console.log('Service Worker registration failed:', error);
        });
}

document.getElementById('subscribeBtn').addEventListener('click', () => {
    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            messaging.getToken({vapidKey}).then(token => {
                console.log('FCM Token:', token);
                fetch('/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({token})
                }).then(response => response.json())
                    .then(data => {
                        document.getElementById('message').textContent = 'Subscribed successfully!';
                    });
            }).catch(err => console.error('Error getting token:', err));
        }
    });
});
