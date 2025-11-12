<?php
/*
|--------------------------------------------------------------------------
| TAMBAH DATA OBAT (FORM & LOGIC)
|--------------------------------------------------------------------------
|
| 1. Panggil header admin (header.php)
| 2. Cek jika form disubmit (POST)
| 3. Validasi data
| 4. INSERT data ke tbl_obat
| 5. Redirect kembali ke data_obat.php dengan pesan sukses
|
*/

// 1. Panggil header
include 'header.php'; // Panggil header.php (otomatis cek login & panggil config)

$success_msg = '';
$error_msg = '';

// 2. Logika INSERT (CREATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data dari form
    $nama_obat = trim($_POST['nama_obat']);
    $satuan = trim($_POST['satuan']);
    $dosis_per_unit = trim($_POST['dosis_per_unit']);
    $indikasi = trim($_POST['indikasi']);
    $efek_samping = trim($_POST['efek_samping']);
    $stok = (int)$_POST['stok'];

    // 3. Validasi sederhana
    if (empty($nama_obat) || empty($satuan) || empty($dosis_per_unit)) {
        $error_msg = 'Nama Obat, Satuan, dan Dosis per Unit wajib diisi.';
    } else {
        // 4. INSERT data ke database
        try {
            $sql = "INSERT INTO tb_obat (nama_obat, satuan, dosis_per_unit, indikasi, efek_samping, stok)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nama_obat, 
                $satuan, 
                $dosis_per_unit, 
                $indikasi, 
                $efek_samping, 
                $stok
            ]);

            // 5. Redirect dengan pesan sukses
            // Kita simpan pesannya di SESSION biar bisa dibaca di data_obat.php
            $_SESSION['success_msg'] = "Data obat '" . htmlspecialchars($nama_obat) . "' berhasil ditambahkan!";
            header("Location: data_obat"); // (FIX: Redirect ke URL bersih)
            exit;

        } catch (PDOException $e) {
            $error_msg = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>

<h1 class="h3 mb-4 text-gray-800">Tambah Data Obat Baru</h1>

<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form action="tambah_obat" method="POST" autocomplete="off">
    
    <div class="row">
    
        <div class="col-md-6">
            <div class="mb-3">
                <label for="nama_obat" class="form-label fw-bold">Nama Obat <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_obat" name="nama_obat" required 
                       value="<?php echo htmlspecialchars($_POST['nama_obat'] ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label for="satuan" class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                <select class="form-select" id="satuan" name="satuan" required>
                    <option value="">-- Pilih Satuan --</option>
                    <?php 
                    $satuan_opts = ['Tablet', 'Kapsul', 'Sirup 60ml', 'Sirup 100ml', 'Salep 5g', 'Injeksi', 'Botol', 'Lainnya'];
                    $selected_satuan = $_POST['satuan'] ?? '';
                    foreach ($satuan_opts as $opt):
                        $selected = ($opt == $selected_satuan) ? 'selected' : '';
                        echo "<option value=\"$opt\" $selected>$opt</option>";
                    endforeach;
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="dosis_per_unit" class="form-label fw-bold">Dosis / Kekuatan Unit <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="dosis_per_unit" name="dosis_per_unit" required 
                       placeholder="Contoh: 500 mg, 100 mg/5 ml, 5%"
                       value="<?php echo htmlspecialchars($_POST['dosis_per_unit'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="stok" class="form-label fw-bold">Stok Awal</label>
                <input type="number" class="form-control" id="stok" name="stok" value="<?php echo htmlspecialchars($_POST['stok'] ?? 0); ?>" min="0" required>
                <small class="text-muted">Stok akan otomatis berkurang saat dibuat resep.</small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="indikasi" class="form-label fw-bold">Indikasi (Kegunaan)</label>
                <textarea class="form-control" id="indikasi" name="indikasi" rows="4" 
                          placeholder="Misal: Untuk meredakan demam dan nyeri ringan..."><?php echo htmlspecialchars($_POST['indikasi'] ?? ''); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="efek_samping" class="form-label fw-bold">Efek Samping (Opsional)</label>
                <textarea class="form-control" id="efek_samping" name="efek_samping" rows="4"
                          placeholder="Misal: Dapat menyebabkan kantuk, mual, dan pusing..."><?php echo htmlspecialchars($_POST['efek_samping'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
    
    <hr>
    
    <div class="d-flex justify-content-end">
        <a href="data_obat" class="btn btn-secondary me-2">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan Data Obat</button>
    </div>
    
</form>

<?php 
include 'footer.php'; // Panggil Footer Admin
?>