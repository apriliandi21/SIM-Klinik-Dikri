<?php
/*
|--------------------------------------------------------------------------
| Halaman Daftar Kunjungan (petugas/daftar_kunjungan.php)
|--------------------------------------------------------------------------
|
| VERSI BOOTSTRAP LENGKAP (PHP + HTML)
|
| 1. Panggil header.php
| 2. Logika PHP (GET & POST) - Tidak berubah, sudah benar.
| 3. Tampilan HTML (form keluhan) - Diperbaiki pakai Bootstrap.
| 4. Panggil footer.php
|
*/

// 1. Panggil header
include 'header.php';

// 2. Siapkan variabel
$pasien = null; // Variabel untuk menyimpan data pasien
$error_msg = '';
$sukses_msg = '';

// -------------------------------------------------------------------
// LOGIKA PHP (GET & POST) - INI SEMUA TIDAK BERUBAH
// -------------------------------------------------------------------

// 1. Cek jika form disubmit (METHOD POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id_pasien_post = (int)$_POST['id_pasien'];
    $keluhan = trim($_POST['keluhan']);
    
    // Validasi
    if (empty($keluhan)) {
        $error_msg = "Keluhan wajib diisi!";
        // Kita perlu ambil data pasien lagi biar form-nya tetap tampil
        $sql_pasien = "SELECT * FROM tb_pasien WHERE id_pasien = ?";
        $stmt_pasien = $pdo->prepare($sql_pasien);
        $stmt_pasien->execute([$id_pasien_post]);
        $pasien = $stmt_pasien->fetch(PDO::FETCH_ASSOC);
    
    } else {
        // INSERT ke tb_pendaftaran
        try {
            $sql_insert = "INSERT INTO tb_pendaftaran (id_pasien, tgl_kunjungan, keluhan, status) 
                           VALUES (?, ?, ?, ?)";
            
            $stmt_insert = $pdo->prepare($sql_insert);
            
            $stmt_insert->execute([
                $id_pasien_post,
                date('Y-m-d H:i:s'), // Waktu kunjungan adalah sekarang
                $keluhan,
                'Menunggu'           // Status awal
            ]);
            
            // Tampilkan pesan sukses
            $sql_pasien = "SELECT nm_pasien FROM tb_pasien WHERE id_pasien = ?";
            $stmt_pasien = $pdo->prepare($sql_pasien);
            $stmt_pasien->execute([$id_pasien_post]);
            $nama_pasien_sukses = $stmt_pasien->fetchColumn();
            
            $sukses_msg = "Pasien <strong>" . htmlspecialchars($nama_pasien_sukses) . "</strong> berhasil didaftarkan untuk kunjungan hari ini!";
            
            $pasien = null; // Set null agar form-nya hilang setelah sukses
            
        } catch (PDOException $e) {
            $error_msg = "Gagal menyimpan data kunjungan: " . $e->getMessage();
        }
    }

// 2. JIKA BUKAN POST, cek apakah ada 'id' di URL (METHOD GET)
} else if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $id_pasien_dari_url = (int)$_GET['id'];
    
    // Query ke tb_pasien
    try {
        $sql_pasien = "SELECT * FROM tb_pasien WHERE id_pasien = ?";
        $stmt_pasien = $pdo->prepare($sql_pasien);
        $stmt_pasien->execute([$id_pasien_dari_url]);
        $pasien = $stmt_pasien->fetch(PDO::FETCH_ASSOC); 
        
        if (!$pasien) {
            $error_msg = "Data pasien dengan ID " . $id_pasien_dari_url . " tidak ditemukan.";
        }
        
    } catch (PDOException $e) {
        $error_msg = "Error query: " . $e->getMessage();
    }

// 3. JIKA BUKAN POST ATAU GET DENGAN ID
} else {
    $error_msg = "ID Pasien tidak valid atau tidak ditemukan di URL.";
}

?>

<h1 class="h3 mb-4 text-gray-800">Daftarkan Kunjungan Pasien</h1>
<p class="text-muted">Konfirmasi data pasien dan masukkan keluhan utama hari ini.</p>
<hr>

<?php if (!empty($sukses_msg)): ?>
    <div class="alert alert-success">
        <?php echo $sukses_msg; ?>
        <br><br>
        <a href="cari_pasien" class="btn-link">Kembali ke Pencarian Pasien</a>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error_msg); ?>
        <br><br>
        <a href="cari_pasien" class="btn-link">Kembali ke Pencarian Pasien</a>
    </div>
<?php endif; ?>


<?php 
/*
| Tampilkan Form HANYA JIKA:
| 1. TIDAK ada pesan sukses (artinya belum disubmit/gagal)
| 2. DAN data $pasien berhasil ditemukan (pasien != null)
*/
if (empty($sukses_msg) && $pasien): 
?>

    <div class="card bg-light border-secondary mb-4">
        <div class="card-header fw-bold">Data Pasien (Konfirmasi)</div>
        <div class="card-body">
            <table class="table table-borderless table-sm mb-0">
                <tr>
                    <td style="width: 150px;"><strong>No. Rekam Medis</strong></td>
                    <td>: <?php echo htmlspecialchars($pasien['no_rekam_medis']); ?></td>
                </tr>
                <tr>
                    <td><strong>Nama Pasien</strong></td>
                    <td>: <?php echo htmlspecialchars($pasien['nm_pasien']); ?></td>
                </tr>
                <tr>
                    <td><strong>Tanggal Lahir</strong></td>
                    <td>: <?php echo htmlspecialchars($pasien['tgl_lahir']); ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <form action="daftar_kunjungan" method="POST" autocomplete="off">
    
        <input type="hidden" name="id_pasien" value="<?php echo htmlspecialchars($pasien['id_pasien']); ?>">
        
        <div class="mb-3">
            <label for="keluhan" class="form-label fw-bold">Keluhan Utama Pasien Hari Ini</label>
            <textarea id="keluhan" name="keluhan" class="form-control" rows="4" required autofocus></textarea>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Simpan Kunjungan & Masukkan ke Antrean</button>
        </div>

    </form>

<?php 
endif; // Ini adalah penutup dari "if (empty($sukses_msg) && $pasien):"
?>


<?php
// Panggil footer
include 'footer.php';
?>