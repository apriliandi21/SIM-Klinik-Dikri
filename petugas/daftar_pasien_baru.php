<?php
/*
|--------------------------------------------------------------------------
| Halaman Daftar Pasien Baru (petugas/daftar_pasien_baru.php)
|--------------------------------------------------------------------------
|
| VERSI FINAL (PHP + HTML 2 Kolom + Tombol Kunjungan)
|
| 1. Panggil header.php (otomatis panggil Bootstrap CSS & cek login).
| 2. Logika simpan data POST (termasuk berat_badan).
| 3. TANGKAP ID PASIEN BARU ($pdo->lastInsertId()).
| 4. Tampilan form (HTML baru pakai class Bootstrap 2 kolom).
| 5. Tampilkan pesan sukses DENGAN tombol "Daftarkan Kunjungan".
| 6. Panggil footer.php (otomatis panggil Bootstrap JS).
|
*/

// 1. Panggil header
include 'header.php';

// 2. Siapkan variabel pesan
$sukses_msg = '';
$error_msg = '';
$id_pasien_baru = 0; // Siapkan variabel untuk menampung ID pasien baru

// 3. (LOGIKA PHP KAMU) Cek jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form (Pastikan 'name' di HTML sesuai)
    $nama_pasien = trim($_POST['nm_pasien']);
    $alamat = trim($_POST['alamat']);
    $tanggal_lahir = $_POST['tgl_lahir'];
    $no_telepon = trim($_POST['no_telp']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $berat_badan = trim($_POST['berat_badan']); // Ambil berat badan
    
    // Validasi dasar (berat badan opsional, jadi tidak masuk sini)
    if (empty($nama_pasien) || empty($alamat) || empty($tanggal_lahir) || empty($no_telepon) || empty($jenis_kelamin)) {
        $error_msg = "Semua kolom wajib diisi (kecuali berat badan)!";
    } else {
        
        $prefix = 'RM' . date('ym'); 
        
        // --- Mulai Transaksi Database ---
        try {
            $pdo->beginTransaction();

            // Query 1: Cek No. RM terakhir
            $sql_count = "SELECT no_rekam_medis FROM tb_pasien 
                          WHERE no_rekam_medis LIKE ? 
                          ORDER BY no_rekam_medis DESC LIMIT 1";
            
            $stmt_count = $pdo->prepare($sql_count);
            $stmt_count->execute([$prefix . '%']);
            $last_rm = $stmt_count->fetchColumn(); 
            
            if ($last_rm) {
                $last_num = (int) substr($last_rm, -3); 
                $nomor_urut = $last_num + 1;
            } else {
                $nomor_urut = 1;
            }
            
            $no_rekam_medis = $prefix . str_pad($nomor_urut, 3, '0', STR_PAD_LEFT);
            
            
            // Query 2: INSERT data pasien baru (termasuk berat_badan)
            $sql_insert = "INSERT INTO tb_pasien 
                           (no_rekam_medis, nm_pasien, alamat, tgl_lahir, no_telp, jenis_kelamin, berat_badan) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_insert = $pdo->prepare($sql_insert);
            
            $stmt_insert->execute([
                $no_rekam_medis,
                $nama_pasien,
                $alamat,
                $tanggal_lahir,
                $no_telepon,
                $jenis_kelamin,
                empty($berat_badan) ? null : $berat_badan // Simpan NULL jika kosong
            ]);

            // --- INI TAMBAHAN PENTING (SESUAI IDEMU) ---
            // A. Ambil ID pasien baru SEBELUM commit
            $id_pasien_baru = $pdo->lastInsertId();
            // --- AKHIR TAMBAHAN PENTING ---
            
            // Simpan permanen
            $pdo->commit();
            
            // Tampilkan pesan sukses
            $sukses_msg = "Pasien baru berhasil ditambahkan! 
                           <br> Nama: <strong>" . htmlspecialchars($nama_pasien) . "</strong>
                           <br> No. Rekam Medis: <strong>" . $no_rekam_medis . "</strong>";
            
        } catch (PDOException $e) {
            $pdo->rollBack(); // Batalkan jika ada error
            $error_msg = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>

<h1 class="h3 mb-4 text-gray-800">Formulir Pendaftaran Pasien Baru</h1>

<?php if (!empty($sukses_msg)): ?>
    <div class="alert alert-success">
        <?php echo $sukses_msg; ?>
        
        <hr> <p class="mb-0 fw-bold">Apa yang ingin Anda lakukan selanjutnya?</p>
        
        <a href="daftar_kunjungan?id=<?php echo $id_pasien_baru; ?>" class="btn btn-success mt-2">
            ➡️ Daftarkan Kunjungan Pasien Ini
        </a>
        
        <a href="daftar_pasien_baru" class="btn btn-secondary mt-2 ms-2">
            + Daftar Pasien Baru Lainnya
        </a>
        </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error_msg); ?>
    </div>
<?php endif; ?>


<?php if (empty($sukses_msg)): // <-- Form HANYA TAMPIL jika BELUM sukses ?>
    
    <p class="text-muted">Silakan isi data pasien dengan lengkap dan benar.</p>
    <hr>
    
    <form action="daftar_pasien_baru" method="POST" autocomplete="off">
        
        <div class="row">
        
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="nm_pasien" class="form-label">Nama Lengkap Pasien</label>
                    <input type="text" id="nm_pasien" name="nm_pasien" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                    <input type="date" id="tgl_lahir" name="tgl_lahir" class="form-control" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="no_telp" class="form-label">No. Telepon (WhatsApp)</label>
                    <input type="tel" id="no_telp" name="no_telp" class="form-control" placeholder="Contoh: 08123456789" required>
                </div>

                <div class="mb-3">
                    <label for="berat_badan" class="form-label">Berat Badan (Opsional)</label>
                    <div class="input-group"> <input type="number" step="0.1" id="berat_badan" name="berat_badan" class="form-control" placeholder="Contoh: 50.5">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label d-block">Jenis Kelamin</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" required>
                        <label class="form-check-label" for="jk_l">Laki-laki</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" required>
                        <label class="form-check-label" for="jk_p">Perempuan</label>
                    </div>
                </div>
            </div>
            
        </div> <hr> 

        <div class="d-flex justify-content-end"> <button type="submit" class="btn btn-primary">Simpan Data Pasien</button>
        </div>

    </form>
    
<?php endif; // <-- Penutup "if (empty($sukses_msg))" ?>


<?php
// 4. Panggil footer
include 'footer.php';
?>