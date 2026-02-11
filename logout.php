<?php
session_start();

// Hapus semua session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Hancurkan session
session_destroy();

// Set flag bahwa user baru logout
setcookie('just_logged_out', '1', time() + 60, '/'); // Cookie berlaku 60 detik

// Redirect ke index.php
header("Location: index.php");
exit();
?>