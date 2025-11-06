<?php
/*
|--------------------------------------------------------------------------
| Halaman Detail Riwayat (dokter/detail_riwayat.php)
|--------------------------------------------------------------------------
|
| VERSI BOOTSTRAP LENGKAP (PHP + HTML FINAL + TOMBOL CETAK ULANG)
|
| 1. Panggil header.php
| 2. Logika PHP (FIX: Query riwayat di-update untuk ambil id_rekam_medis)
| 3. Tampilan HTML (FIX: Tabel riwayat ditambah kolom Aksi)
| 4. Panggil footer.php
|
*/

// 1. Panggil header
include 'header.php';

// 2. Siapkan variabel
$pasien_info = null;
$riwayat_medis = [];
$error_msg = '';

// 3. Cek apakah ada 'id' di URL (Logika PHP ini sudah benar)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $id_pasien = (int)$_GET['id'];
    
    try {
        // Query 1: Ambil data pasien (Termasuk berat_badan)
        $sql_pasien = "SELECT * FROM tb_pasien WHERE id_pasien = ?";
        $stmt_pasien = $pdo->prepare($sql_pasien);
        $stmt_pasien->execute([$id_pasien]);
        $pasien_info = $stmt_pasien->fetch(PDO::FETCH_ASSOC);

        if ($pasien_info) {
            
            // Query 2: Ambil SEMUA riwayat medis pasien ini
            // (FIX: TAMBAHKAN rm.id_rekam_medis)
            $sql_riwayat = "SELECT 
                                rm.id_rekam_medis, -- <-- INI TAMBAHAN PENTING
                                rm.diagnosa, 
                                rm.tindakan, 
                                rm.catatan_dokter,
                                rm.tgl_pemeriksaan,
                                u.nm_lengkap AS nama_dokter
                            FROM 
                                tb_rekam_medis AS rm
                            JOIN 
                                tb_pendaftaran AS p ON rm.id_pendaftaran = p.id_pendaftaran
                            JOIN 
                                tb_user AS u ON rm.id_user_dokter = u.id_user
                            WHERE 
                                p.id_pasien = ?
                            ORDER BY 
                                rm.tgl_pemeriksaan DESC"; // Riwayat terbaru di atas
            
            $stmt_riwayat = $pdo->prepare($sql_riwayat);
            $stmt_riwayat->execute([$id_pasien]);
            $riwayat_medis = $stmt_riwayat->fetchAll(PDO::FETCH_ASSOC);
            
        } else {
            $error_msg = "Data pasien tidak ditemukan.";
        }
        
    } catch (PDOException $e) {
        $error_msg = "Error query: " . $e->getMessage();
    }
    
} else {
    $error_msg = "ID Pasien tidak valid atau tidak ditemukan di URL.";
}

?>

<h1 class="h3 mb-4 text-gray-800">Detail Riwayat Medis Pasien</h1>
<p>
    <a href="riwayat_pasien" class="btn-link text-decoration-none">
        &laquo; Kembali ke Pencarian Pasien
    </a>
</p>
<hr>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error_msg); ?>
    </div>
<?php endif; ?>


<?php 
/*
| Tampilkan info HANYA JIKA data $pasien_info berhasil ditemukan
*/
if ($pasien_info): 
?>

    <div class="card bg-light border-secondary mb-4">
        <div class="card-header fw-bold">Data Pasien</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td style="width: 150px;"><strong>No. RM</strong></td>
                            <td>: <?php echo htmlspecialchars($pasien_info['no_rekam_medis']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Nama Pasien</strong></td>
                            <td>: <?php echo htmlspecialchars($pasien_info['nm_pasien']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tgl. Lahir</strong></td>
                            <td>: <?php echo htmlspecialchars($pasien_info['tgl_lahir']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Kelamin</strong></td>
                            <td>: <?php echo ($pasien_info['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td style="width: 150px;"><strong>Berat Badan</strong></td>
                            <td>: 
                                <?php 
                                echo !empty($pasien_info['berat_badan']) 
                                     ? htmlspecialchars($pasien_info['berat_badan']) . ' kg' 
                                     : '-'; 
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Alamat</strong></td>
                            <td>: <?php echo htmlspecialchars($pasien_info['alamat']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>No. Telepon</strong></td>
                            <td>: <?php echo htmlspecialchars($pasien_info['no_telp']); ?></td>
                        </tr>
                    </table>
                </div>
            </div> </div>
    </div>
    
    
    <div class.="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold">Semua Riwayat Kunjungan</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%;">Tanggal Kunjungan</th>
                        <th style="width: 15%;">Dokter</th>
                        <th>Diagnosa</th>
                        <th>Tindakan</th>
                        <th>Catatan / Resep</th>
                        <th style="width: 10%;">Aksi</th> </tr>
                </thead>
                <tbody>
                    <?php if (!empty($riwayat_medis)): ?>
                        <?php foreach ($riwayat_medis as $riwayat): ?>
                            <tr>
                                <td><?php echo date('d M Y, H:i', strtotime($riwayat['tgl_pemeriksaan'])); ?></td>
                                <td><?php echo htmlspecialchars($riwayat['nama_dokter']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($riwayat['diagnosa'])); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($riwayat['tindakan'])); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($riwayat['catatan_dokter'])); ?></td>
                                
                                <td>
                                    <a href="cetak_resume?id=<?php echo $riwayat['id_rekam_medis']; ?>" 
                                       target="_blank" 
                                       class="btn btn-primary btn-sm w-100">
                                        Cetak Ulang
                                    </a>
                                </td>
                                
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-4">
                                Tidak ada riwayat rekam medis yang tersimpan untuk pasien ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php 
endif; // Ini adalah penutup dari "if ($pasien_info):"
?>


<?php
// Panggil footer
include 'footer.php';
?>