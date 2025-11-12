<?php
/*
|--------------------------------------------------------------------------
| HAPUS USER (DELETE LOGIC)
|--------------------------------------------------------------------------
|
| 1. Panggil config.php (LANGSUNG)
| 2. Cek Keamanan (WAJIB!)
| 3. Ambil ID dari URL
| 4. Cek biar nggak hapus diri sendiri
| 5. Jalankan query DELETE
| 6. Redirect kembali ke manajemen_user
|
*/

// 1. Panggil config (Pakai path absolut biar aman)
require_once $_SERVER['DOCUMENT_ROOT'] . '/klinik-dikri/config/config.php';

// 2. Cek Keamanan: HANYA 'admin' yang boleh hapus
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    // Kalau bukan admin, tendang keluar
    die("Akses ditolak. Anda bukan admin."); 
}

// 3. Ambil ID dari URL
$id_user = (int)($_GET['id'] ?? 0);

if ($id_user === 0) {
    $_SESSION['error_msg'] = "ID User tidak valid.";
    header("Location: manajemen_user");
    exit;
}

// 4. Cek Biar Nggak Hapus Diri Sendiri (SANGAT PENTING!)
if ($id_user == $_SESSION['id_user']) {
    $_SESSION['error_msg'] = "Tidak bisa menghapus akun Anda sendiri!";
    header("Location: manajemen_user");
    exit;
}

// 5. Jalankan query DELETE
try {
    $sql = "UPDATE tb_user SET status_akun = 'non-aktif' WHERE id_user = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_user]);
    
    // 6. Redirect dengan pesan sukses;
    $_SESSION['success_msg'] = "User berhasil di-nonaktifkan.";
    header("Location: manajemen_user");
    exit;

} catch (PDOException $e) {
    // Jika gagal (misal user-nya punya relasi data lain)
    $_SESSION['error_msg'] = "Gagal menghapus user: " . $e->getMessage();
    header("Location: manajemen_user");
    exit;
}
?>