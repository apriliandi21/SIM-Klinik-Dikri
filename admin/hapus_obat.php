<?php
/*
|--------------------------------------------------------------------------
| HAPUS DATA OBAT (DELETE LOGIC)
|--------------------------------------------------------------------------
|
| 1. Panggil config.php & cek login admin
| 2. Ambil ID dari URL
| 3. Jalankan query DELETE
| 4. Redirect kembali ke data_obat.php dengan pesan
|
*/

// 1. Panggil config (Pakai path absolut)
require_once $_SERVER['DOCUMENT_ROOT'] . '/klinik-dikri/config/config.php';

// 2. Cek Keamanan: HANYA 'admin' yang boleh hapus
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: /klinik-dikri/");
    exit;
}

// 3. Ambil ID dari URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_obat = (int)$_GET['id'];
    
    try {
        // 4. Jalankan query DELETE
        $sql = "DELETE FROM tb_obat WHERE id_obat = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_obat]);

        // 5. Redirect ke halaman index dengan pesan sukses
        $_SESSION['success_msg'] = "Data obat berhasil dihapus.";
        header("Location: data_obat");
        exit;

    } catch (PDOException $e) {
        // Jika gagal (misal data sedang dipakai di tbl_resep_detail)
        $_SESSION['error_msg'] = "Gagal menghapus obat! Kemungkinan obat ini sudah pernah dipakai di rekam medis pasien.";
        header("Location: data_obat");
        exit;
    }
} else {
    // Jika tidak ada ID
    $_SESSION['error_msg'] = "Gagal menghapus obat, ID tidak ditemukan.";
    header("Location: data_obat");
    exit;
}
?>