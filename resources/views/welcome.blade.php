<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Laravel PWA</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/app.css">
    <!-- Tải Firebase Compat SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js"></script>
</head>
<body>
<div class="container">
    <h1>Welcome to My Laravel PWA</h1>
    <button id="subscribeBtn">Subscribe to Notifications</button>
    <p id="message"></p>
</div>
<script>
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

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/firebase-messaging-sw.js')
            .then((reg) => console.log('Service Worker registered:', reg));
    }

    function subscribeToPush() {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                messaging.getToken({ vapidKey: '{{ env('FIREBASE_VAPID_KEY') }}' })
                    .then((token) => {
                        console.log('Token:', token);
                        fetch('/save-token', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ token })
                        }).then(res => res.json())
                            .then(data => console.log('Token saved:', data));
                    })
                    .catch((err) => console.error('Error:', err));
            }
        });
    }
    messaging.onMessage((payload) => {
        console.log('Foreground:', payload);
        navigator.serviceWorker.ready.then((reg) =>
            reg.showNotification(payload.notification.title, {
                body: payload.notification.body,
                icon: '/images/favicon/favicon-96x96.png'
            })
        );
    });
    navigator.serviceWorker.addEventListener('message', (event) => {
        if (event.data.type === 'NOTIFICATION_CLICK') {
            const data = event.data.data;
            document.getElementById('message').textContent = data.count_data;
        }
    });
    const vapidKey = "BHxUOXVdEMbqXfbDKwvjHoKfRA2W-nLWd2TlHqmQNYSPIl2eo7LY39su6bDYFPutNlIpILoBDFjdB9rSNdZ-Euw"; // Từ Firebase Console
</script>
<script src="/js/app.js"></script>
</body>
</html>
