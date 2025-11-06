<?php
/*
|--------------------------------------------------------------------------
| Halaman Periksa Pasien (dokter/periksa_pasien.php)
|--------------------------------------------------------------------------
|
| VERSI BOOTSTRAP LENGKAP (PHP + HTML LAYOUT BARU)
|
| 1. Panggil header.php
| 2. Logika PHP (GET & POST) - (Query GET di-update untuk ambil berat_badan)
| 3. Tampilan HTML (LAYOUT BARU: Kiri=Info, Kanan=Form, Bawah=Riwayat)
| 4. Panggil footer.php
|
*/

// 1. Panggil header
include 'header.php';

// 2. Siapkan variabel
$pasien_info = null;    // Info pasien & keluhan hari ini
$riwayat_medis = [];  // Array untuk riwayat medis sebelumnya
$error_msg = '';
$sukses_msg = '';
$id_rekam_medis_baru = 0; 

// 3. --- LOGIKA SIMPAN DATA (POST) ---
// (Blok ini tidak perlu diubah, sudah benar)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id_pendaftaran = (int)$_POST['id_pendaftaran'];
    $diagnosa = trim($_POST['diagnosa']);
    $tindakan = trim($_POST['tindakan']);
    $catatan_dokter = trim($_POST['catatan_dokter']);
    $id_user_dokter = (int)$_SESSION['id_user'];
    
    if (empty($diagnosa)) {
        $error_msg = "Diagnosa wajib diisi!";
        $id_pendaftaran_get = $id_pendaftaran; 
    } else {
        try {
            $pdo->beginTransaction();
            
            $sql_insert = "INSERT INTO tb_rekam_medis 
                           (id_pendaftaran, id_user_dokter, diagnosa, tindakan, catatan_dokter, tgl_pemeriksaan)
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                $id_pendaftaran, $id_user_dokter, $diagnosa,
                $tindakan, $catatan_dokter, date('Y-m-d H:i:s')
            ]);
            
            $id_rekam_medis_baru = $pdo->lastInsertId();
            
            $sql_update = "UPDATE tb_pendaftaran SET status = 'Selesai' 
                           WHERE id_pendaftaran = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$id_pendaftaran]);
            
            $pdo->commit();
            
            $sukses_msg = "Rekam medis berhasil disimpan!";
            $pasien_info = null; 
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Gagal menyimpan data pemeriksaan: " . $e->getMessage();
            $id_pendaftaran_get = $id_pendaftaran;
        }
    }
}


// 4. --- LOGIKA TAMPILAN AWAL (GET atau jika POST error) ---
if (isset($id_pendaftaran_get) || (isset($_GET['id']) && is_numeric($_GET['id']))) {
    
    $id_pendaftaran = isset($id_pendaftaran_get) ? $id_pendaftaran_get : (int)$_GET['id'];

    try {
        // Query 1: Ambil data pasien (FIX: TAMBAH p.berat_badan)
        $sql_pasien_info = "SELECT 
                                p.nm_pasien, p.no_rekam_medis, p.tgl_lahir, 
                                p.jenis_kelamin, p.berat_badan, -- <-- INI TAMBAHANNYA
                                d.keluhan, d.id_pasien, d.id_pendaftaran
                            FROM tb_pendaftaran AS d
                            JOIN tb_pasien AS p ON d.id_pasien = p.id_pasien
                            WHERE d.id_pendaftaran = ?";
        
        $stmt_info = $pdo->prepare($sql_pasien_info);
        $stmt_info->execute([$id_pendaftaran]);
        $pasien_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($pasien_info) {
            $id_pasien = $pasien_info['id_pasien'];
            
            // Query 2: Ambil SEMUA riwayat medis pasien ini (Query ini tetap sama)
            $sql_riwayat = "SELECT 
                                rm.diagnosa, rm.tindakan, rm.catatan_dokter,
                                rm.tgl_pemeriksaan, u.nm_lengkap as nama_dokter
                            FROM tb_rekam_medis AS rm
                            JOIN tb_pendaftaran AS p ON rm.id_pendaftaran = p.id_pendaftaran
                            JOIN tb_user AS u ON rm.id_user_dokter = u.id_user
                            WHERE p.id_pasien = ?
                            ORDER BY rm.tgl_pemeriksaan DESC"; 
            
            $stmt_riwayat = $pdo->prepare($sql_riwayat);
            $stmt_riwayat->execute([$id_pasien]);
            $riwayat_medis = $stmt_riwayat->fetchAll(PDO::FETCH_ASSOC);
            
        } else {
            $error_msg = "Data pendaftaran tidak ditemukan.";
        }
        
    } catch (PDOException $e) {
        $error_msg = "Error query: " . $e->getMessage();
    }
    
} else if (empty($error_msg) && empty($sukses_msg)) {
    $error_msg = "ID Pendaftaran tidak valid atau tidak ditemukan di URL.";
}

?>

<h1 class="h3 mb-4 text-gray-800">Formulir Pemeriksaan Pasien</h1>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error_msg); ?>
        <br><br>
        <a href="./" class="btn-link">Kembali ke Antrean</a>
    </div>
<?php endif; ?>

<?php if (!empty($sukses_msg)): ?>
    <div class="alert alert-success">
        <?php echo $sukses_msg; ?>
    </div>
    <div class="form-group mb-4">
        <a href="cetak_resume?id=<?php echo $id_rekam_medis_baru; ?>" target="_blank" class="btn btn-primary">
            🖨️ Cetak Resume Medis / Resep
        </a>
        <a href="./" class="btn btn-success ms-2">
            Kembali ke Antrean
        </a>
    </div>
<?php endif; ?>


<?php 
/*
| Tampilkan Form HANYA JIKA $pasien_info ADA (artinya belum disubmit)
*/
if ($pasien_info): 
?>

    <div class="row">

        <div class="col-md-5">
            
            <div class="card bg-light border-secondary mb-4">
                <div class="card-header fw-bold">Data Pasien</div>
                <div class="card-body">
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
                        <tr>
                            <td><strong>Berat Badan</strong></td>
                            <td>: 
                                <?php 
                                // Tampilkan "kg" hanya jika datanya ada
                                echo !empty($pasien_info['berat_badan']) 
                                     ? htmlspecialchars($pasien_info['berat_badan']) . ' kg' 
                                     : '-'; 
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card bg-warning bg-opacity-10 border-warning mb-4">
                <div class="card-header fw-bold">Keluhan Hari Ini</div>
                <div class="card-body">
                    <p class="card-text fs-5">
                        <?php echo nl2br(htmlspecialchars($pasien_info['keluhan'])); ?>
                    </p>
                </div>
            </div>

        </div> <div class="col-md-7">
            
            <form action="periksa_pasien" method="POST" autocomplete="off">
                <input type="hidden" name="id_pendaftaran" value="<?php echo htmlspecialchars($pasien_info['id_pendaftaran']); ?>">
                
                <div class="mb-3">
                    <label for="diagnosa" class="form-label fw-bold">Diagnosa Dokter</label>
                    <textarea id="diagnosa" name="diagnosa" class="form-control" rows="4" required autofocus></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tindakan" class="form-label fw-bold">Tindakan Medis</label>
                    <textarea id="tindakan" name="tindakan" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="catatan_dokter" class="form-label fw-bold">Catatan Tambahan / Resep</label>
                    <textarea id="catatan_dokter" name="catatan_dokter" class="form-control" rows="4"></textarea>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-success btn-lg">Simpan Rekam Medis & Selesaikan</button>
                </div>
            </form>

        </div> </div> <div class="row mt-4">
        <div class="col-md-12">
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold">Riwayat Medis Sebelumnya</h6>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    
                    <?php if (!empty($riwayat_medis)): ?>
                        <?php foreach ($riwayat_medis as $riwayat): ?>
                            <div class="riwayat-item">
                                <div class="tanggal">
                                    Kunjungan: <?php echo date('d M Y, H:i', strtotime($riwayat['tgl_pemeriksaan'])); ?>
                                </div>
                                <p><strong>Dokter:</strong> <?php echo htmlspecialchars($riwayat['nama_dokter']); ?></p>
                                <p><strong>Diagnosa:</strong> <?php echo nl2br(htmlspecialchars($riwayat['diagnosa'])); ?></p>
                                <p><strong>Tindakan:</strong> <?php echo nl2br(htmlspecialchars($riwayat['tindakan'])); ?></p>
                                <p><strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($riwayat['catatan_dokter'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            Tidak ada riwayat kunjungan sebelumnya untuk pasien ini.
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>

        </div> </div> <?php 
endif; // Ini adalah penutup dari "if ($pasien_info):"
?>


<?php
// Panggil footer
include 'footer.php';
?>