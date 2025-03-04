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
                icon: '/icon.png'
            })
        );
    });
    const vapidKey = "BB0N4rY74v2hmv9DskN1HCd0dumaeeVyvkiVJRSmSQNIZef32xKXVoiPeVu3OPH-kdXSww3_VTPdbx0bPT99dZw"; // Từ Firebase Console
</script>
<script src="/js/app.js"></script>
</body>
</html>
