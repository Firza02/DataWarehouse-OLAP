<?php
session_start();

// Hapus semua data session
session_unset();
session_destroy();

// (opsional) hapus cookie session kalau ingin benar-benar bersih
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Arahkan ke halaman login
header("Location: index.php");
exit;
