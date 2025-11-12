<?php
/*
|--------------------------------------------------------------------------
| AKTIFKAN USER (Logic "Re-activation")
|--------------------------------------------------------------------------
| (Ini MENGUBAH status jadi 'aktif')
*/

// 1. Panggil config (Pakai path absolut biar aman)
require_once $_SERVER['DOCUMENT_ROOT'] . '/klinik-dikri/config/config.php';

// 2. Cek Keamanan: HANYA 'admin' yang boleh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin."); 
}

// 3. Ambil ID dari URL
$id_user = (int)($_GET['id'] ?? 0);

if ($id_user === 0) {
    $_SESSION['error_msg'] = "ID User tidak valid.";
    header("Location: manajemen_user");
    exit;
}

// 4. Jalankan query UPDATE (Re-activate)
try {
    $sql = "UPDATE tb_user SET status_akun = 'aktif' WHERE id_user = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_user]);
    
    // 5. Redirect dengan pesan sukses
    $_SESSION['success_msg'] = "User berhasil di-aktifkan kembali.";
    header("Location: manajemen_user");
    exit;

} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Gagal mengaktifkan user: " . $e->getMessage();
    header("Location: manajemen_user");
    exit;
}
?>