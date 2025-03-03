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
    const vapidKey = "BCl_JcvmY9dVoI6b-aYjPUTc3gn1BEfiULEN0EOEfByy-fkxN1p-d4YCyw7PNPaFUyuGadmsu90bjle0Nzu0Idw"; // Từ Firebase Console
</script>
<script src="/js/app.js"></script>
</body>
</html>
