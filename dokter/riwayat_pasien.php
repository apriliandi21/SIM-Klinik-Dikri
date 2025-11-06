<?php
/*
|--------------------------------------------------------------------------
| Halaman Riwayat Pasien (dokter/riwayat_pasien.php)
|--------------------------------------------------------------------------
|
| VERSI BOOTSTRAP LENGKAP (PHP + HTML)
|
| 1. Panggil header.php (yang baru, hijau)
| 2. Logika pencarian pasien (sama seperti sebelumnya).
| 3. Tampilan form & tabel (HTML baru pakai class Bootstrap).
| 4. Panggil footer.php (yang baru)
|
*/

// 1. Panggil header
include 'header.php';

// 2. Siapkan variabel
$keyword = '';
$hasil_pencarian = []; 
$judul_tabel = 'Hasil Pencarian';

// 3. Cek jika form pencarian disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['keyword'])) {
    
    $keyword = trim($_POST['keyword']);
    $judul_tabel = 'Hasil Pencarian untuk "' . htmlspecialchars($keyword) . '"';
    
    if (!empty($keyword)) {
        try {
            // (Pastikan nama tabel & kolom sudah benar)
            $sql = "SELECT * FROM tb_pasien 
                    WHERE nm_pasien LIKE ? OR no_rekam_medis LIKE ?";
            
            $stmt = $pdo->prepare($sql);
            $keyword_like = "%" . $keyword . "%";
            $stmt->execute([$keyword_like, $keyword_like]);
            $hasil_pencarian = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger m-4">Pencarian gagal: ' . $e->getMessage() . '</div>';
        }
    }

// 4. Jika halaman baru dibuka (belum cari apa-apa)
} else {
    $judul_tabel = 'Silakan lakukan pencarian di atas';
}
?>

<h1 class="h3 mb-4 text-gray-800">Riwayat Medis Pasien</h1>
<p class="text-muted">Gunakan form di bawah untuk mencari pasien dan melihat seluruh riwayat kunjungannya.</p>

<form action="riwayat_pasien" method="POST" class="mb-4">
    <label for="keyword" class="form-label fw-bold">Cari Pasien</label>
    <div class="input-group">
        <input type="text" id="keyword" name="keyword" class="form-control" placeholder="Ketik Nama atau No. RM Pasien..." value="<?php echo htmlspecialchars($keyword); ?>" required>
        <button type="submit" class="btn btn-primary">Cari Pasien</button>
    </div>
</form>

<hr>

<h3 class="h5 mb-3"><?php echo $judul_tabel; ?></h3>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered">
        <thead class="table-light">
            <tr>
                <th>No. RM</th>
                <th>Nama Pasien</th>
                <th>Alamat</th>
                <th>Tgl. Lahir</th>
                <th style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Cek apakah ADA HASIL dari pencarian
            if (!empty($hasil_pencarian)) {
                
                // Loop datanya satu per satu
                foreach ($hasil_pencarian as $pasien) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($pasien['no_rekam_medis']) . "</td>";
                    echo "<td>" . htmlspecialchars($pasien['nm_pasien']) . "</td>";
                    echo "<td>" . htmlspecialchars($pasien['alamat']) . "</td>";
                    echo "<td>" . htmlspecialchars($pasien['tgl_lahir']) . "</td>";
                    
                    // INI BEDANYA: Tombolnya 'Lihat Riwayat' (warna biru/primary)
                    echo '<td>
                            <a href="detail_riwayat?id=' . htmlspecialchars($pasien['id_pasien']) . '" 
                               class="btn btn-primary btn-sm w-100"> 👁️ Lihat Riwayat
                            </a>
                          </td>';
                    
                    echo "</tr>";
                }
                
            } else if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($hasil_pencarian)) {
                // Jika sudah nyari tapi nggak ketemu
                echo '<tr><td colspan="5" class="text-center p-4">Data pasien tidak ditemukan.</td></tr>';
            } else {
                // Jika halaman baru dibuka (belum nyari)
                echo '<tr><td colspan="5" class="text-center p-4">Silakan lakukan pencarian di atas.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>


<?php
// Panggil footer
include 'footer.php';
?>