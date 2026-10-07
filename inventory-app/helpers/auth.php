<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth() {
    // Kalau belum login, lempar kembali ke halaman login
    if (!isset($_SESSION['user_id'])) {
        header('Location: /inventory-app/public/login.php');
        exit;
    }
}
?>