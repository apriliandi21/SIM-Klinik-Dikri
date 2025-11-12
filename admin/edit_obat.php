<?php
/*
|--------------------------------------------------------------------------
| EDIT DATA OBAT (FORM & LOGIC)
|--------------------------------------------------------------------------
|
| 1. Panggil header admin
| 2. Ambil ID dari URL
| 3. SELECT data lama dari tbl_obat berdasarkan ID
| 4. Cek jika form disubmit (POST) -> Lakukan UPDATE
| 5. Tampilkan form dengan data lama
|
*/

// 1. Panggil header
include 'header.php'; 

$error_msg = '';
$id_obat = (int)($_GET['id'] ?? 0); // Ambil ID dari URL

// 2. Cek ID
if ($id_obat === 0) {
    echo '<div class="alert alert-danger">ID Obat tidak valid.</div>';
    include 'footer.php';
    exit;
}

// 4. Logika UPDATE (Kita taruh di atas biar datanya ke-refresh)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data dari form
    $nama_obat = trim($_POST['nama_obat']);
    $satuan = trim($_POST['satuan']);
    $dosis_per_unit = trim($_POST['dosis_per_unit']);
    $indikasi = trim($_POST['indikasi']);
    $efek_samping = trim($_POST['efek_samping']);
    $stok = (int)$_POST['stok'];

    // Validasi sederhana
    if (empty($nama_obat) || empty($satuan) || empty($dosis_per_unit)) {
        $error_msg = 'Nama Obat, Satuan, dan Dosis per Unit wajib diisi.';
    } else {
        // UPDATE data ke database
        try {
            $sql_update = "UPDATE tb_obat SET 
                                nama_obat = ?, 
                                satuan = ?, 
                                dosis_per_unit = ?, 
                                indikasi = ?, 
                                efek_samping = ?, 
                                stok = ?
                           WHERE id_obat = ?";
            
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                $nama_obat, 
                $satuan, 
                $dosis_per_unit, 
                $indikasi, 
                $efek_samping, 
                $stok,
                $id_obat // ID obat yg mau di-update
            ]);

            // Redirect dengan pesan sukses
            $_SESSION['success_msg'] = "Data obat '" . htmlspecialchars($nama_obat) . "' berhasil di-update!";
            header("Location: data_obat");
            exit;

        } catch (PDOException $e) {
            $error_msg = "Gagal meng-update data: " . $e->getMessage();
        }
    }
}


// 3. Ambil Data Lama (READ) untuk ditampilkan di form
try {
    $sql_read = "SELECT * FROM tb_obat WHERE id_obat = ?";
    $stmt_read = $pdo->prepare($sql_read);
    $stmt_read->execute([$id_obat]);
    $obat_lama = $stmt_read->fetch(PDO::FETCH_ASSOC);

    if (!$obat_lama) {
        echo '<div class="alert alert-danger">Data obat tidak ditemukan di database.</div>';
        include 'footer.php';
        exit;
    }
} catch (PDOException $e) {
    // Jika query gagal (misal $id_obat bukan angka)
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    include 'footer.php';
    exit;
}

// (Ini biar data di form tetep pakai data baru kalo validasinya error)
$data_form = $_POST ?: $obat_lama;

?>

<h1 class="h3 mb-4 text-gray-800">Edit Data Obat: <?php echo htmlspecialchars($obat_lama['nama_obat']); ?></h1>

<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form action="edit_obat?id=<?php echo $id_obat; ?>" method="POST" autocomplete="off">
    
    <div class="row">
    
        <div class="col-md-6">
            <div class="mb-3">
                <label for="nama_obat" class="form-label fw-bold">Nama Obat <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_obat" name="nama_obat" required 
                       value="<?php echo htmlspecialchars($data_form['nama_obat'] ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label for="satuan" class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                <select class="form-select" id="satuan" name="satuan" required>
                    <option value="">-- Pilih Satuan --</option>
                    <?php 
                    $satuan_opts = ['Tablet', 'Kapsul', 'Sirup 60ml', 'Sirup 100ml', 'Salep 5g', 'Injeksi', 'Botol', 'Lainnya'];
                    $selected_satuan = $data_form['satuan'] ?? '';
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
                       value="<?php echo htmlspecialchars($data_form['dosis_per_unit'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="stok" class="form-label fw-bold">Stok Saat Ini</label>
                <input type="number" class="form-control" id="stok" name="stok" value="<?php echo htmlspecialchars($data_form['stok'] ?? 0); ?>" min="0" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="indikasi" class="form-label fw-bold">Indikasi (Kegunaan)</label>
                <textarea class="form-control" id="indikasi" name="indikasi" rows="4" 
                          placeholder="Misal: Untuk meredakan demam dan nyeri ringan..."><?php echo htmlspecialchars($data_form['indikasi'] ?? ''); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="efek_samping" class="form-label fw-bold">Efek Samping (Opsional)</label>
                <textarea class="form-control" id="efek_samping" name="efek_samping" rows="4"
                          placeholder="Misal: Dapat menyebabkan kantuk, mual, dan pusing..."><?php echo htmlspecialchars($data_form['efek_samping'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
    
    <hr>
    
    <div class="d-flex justify-content-end">
        <a href="data_obat" class="btn btn-secondary me-2">Batal</a>
        <button type="submit" class="btn btn-warning">Update Data Obat</button>
    </div>
    
</form>

<?php 
include 'footer.php'; // Panggil Footer Admin
?>