if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then(registration => {
            console.log('Service Worker registered:', registration);
        })
        .catch(error => {
            console.log('Service Worker registration failed:', error);
        });
    navigator.serviceWorker.addEventListener('message', event => {
        document.getElementById('message').textContent = `${JSON.stringify(event.data)}`;
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


messaging.onMessage((payload) => {
    console.log("Message received:", payload);
    const notification = new Notification(payload.notification.title, {
        body: payload.notification.body,
        icon: '/images/favicon/favicon-96x96.png',
        data: payload.data
    });
    notification.onclick = function (event) {
        event.preventDefault();
        const data = event.currentTarget.data
        document.getElementById('message').textContent = data.url;
    }
});

navigator.serviceWorker.addEventListener('message', event => {
    const notificationData = event.data;
    console.log('Notification clicked with data:', notificationData);
    // window.history.replaceState({}, '', notificationData.data.url);
    // document.getElementById('message').textContent = `${notificationData.data.url}`;
});
