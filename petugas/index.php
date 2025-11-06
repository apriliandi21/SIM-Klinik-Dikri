<?php
// 1. Panggil header (Satpam Keamanan & Menu)
include 'header.php';

/*
|--------------------------------------------------------------------------
| LOGIKA STATISTIK & ANTREAN
|--------------------------------------------------------------------------
|
| 1. Ambil data total kunjungan HARI INI (yang sudah kamu minta).
| 2. Ambil data DAFTAR ANTREAN (status = 'Menunggu').
|
*/

// 1. Ambil data KUNJUNGAN HARI INI (untuk kartu statistik)
$total_kunjungan_hari_ini = 0; 
try {
    // (Pastikan nama tabel & kolom sudah benar)
    $sql_count = "SELECT COUNT(id_pendaftaran) FROM tb_pendaftaran 
                  WHERE DATE(tgl_kunjungan) = CURDATE()";
    
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute();
    $total_kunjungan_hari_ini = $stmt_count->fetchColumn(); 

} catch (PDOException $e) {
     // Jangan hentikan script, biarkan $total_kunjungan_hari_ini = 0
     // echo "Error: " . $e->getMessage(); 
}


// 2. Ambil data DAFTAR ANTREAN (UNTUK TABEL DI BAWAH)
$antrean_pasien = []; // Siapkan array kosong
try {
    // Query ini ngambil data pasien (JOIN)
    // yang statusnya 'Menunggu'
    // diurutkan dari yang paling LAMA daftar (ASC)
    $sql_antrean = "SELECT 
                        p.nm_pasien, 
                        p.no_rekam_medis,
                        d.tgl_kunjungan,
                        d.keluhan
                    FROM 
                        tb_pendaftaran AS d
                    JOIN 
                        tb_pasien AS p ON d.id_pasien = p.id_pasien
                    WHERE 
                        d.status = 'Menunggu'
                    ORDER BY 
                        d.tgl_kunjungan ASC"; // ASC = Antrean terlama di atas
            
    $stmt_antrean = $pdo->prepare($sql_antrean);
    $stmt_antrean->execute();
    $antrean_pasien = $stmt_antrean->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Gagal mengambil data antrean: ' . $e->getMessage() . '</div>';
}

?>

<h1 class="h3 mb-4 text-gray-800">Dashboard Petugas Pendaftaran</h1>

<div class="row">

    <div class="col-md-7 mb-4">
        <div class="card border-left-primary shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col">
                        <h4 class="card-title">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h4>
                        <p class="card-text text-muted">
                            Anda login sebagai <strong><?php echo $_SESSION['role']; ?></strong>.
                            <br>
                            Tugas Anda adalah mendaftarkan pasien baru, mencari pasien lama, dan memanggil antrean.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5 mb-4">
        <div class="card border-left-success shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                            Total Kunjungan Hari Ini
                        </div>
                        <div class="h2 mb-0 fw-bold text-gray-800">
                            <?php echo $total_kunjungan_hari_ini; ?> Orang
                        </div>
                    </div>
                    <div class="col-auto">
                        <span style="font-size: 3rem; color: #e0e0e0;">👥</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> <div class="row mt-3">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Antrean Pasien (Menunggu Pemeriksaan)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No. Antrean</th>
                                <th>Waktu Daftar</th>
                                <th>No. RM</th>
                                <th>Nama Pasien</th>
                                <th>Keluhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($antrean_pasien)): ?>
                                <?php $nomor_antrean = 1; ?>
                                <?php foreach ($antrean_pasien as $pasien): ?>
                                    <tr>
                                        <td><strong><?php echo $nomor_antrean++; ?></strong></td> 
                                        <td><?php echo date('H:i', strtotime($pasien['tgl_kunjungan'])); ?> WIB</td>
                                        <td><?php echo htmlspecialchars($pasien['no_rekam_medis']); ?></td>
                                        <td><?php echo htmlspecialchars($pasien['nm_pasien']); ?></td>
                                        <td><?php echo htmlspecialchars($pasien['keluhan']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada pasien dalam antrean saat ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// Panggil footer
include 'footer.php';
?>