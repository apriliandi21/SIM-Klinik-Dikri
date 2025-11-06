<?php
/*
|--------------------------------------------------------------------------
| File Logout (logout.php)
|--------------------------------------------------------------------------
|
| 1. Mulai session.
| 2. Hancurkan semua data session (unset).
| 3. Hancurkan session-nya (destroy).
| 4. Tendang user kembali ke halaman login (index.php).
|
*/

// 1. Mulai session
session_start();

// 2. Hapus semua variabel session
$_SESSION = array();

// 3. Hancurkan session
session_destroy();

// 4. Alihkan (redirect) ke halaman login
header("Location: /klinik-dikri/");
exit;
?>