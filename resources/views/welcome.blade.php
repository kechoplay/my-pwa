<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Laravel PWA</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
    const vapidKey = "BPMgoZWm-lUS1FQXjO_P_BQ3AQCCRDPp56eZz0DHxx9ZJMn-CHgfwv7aRlG0oJwB38vekA3C0OUS_wpgpRWY6oM"; // Từ Firebase Console
</script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
