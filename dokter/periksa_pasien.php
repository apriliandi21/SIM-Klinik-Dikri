<?php
/*
|--------------------------------------------------------------------------
| Halaman Periksa Pasien (dokter/periksa_pasien.php)
|--------------------------------------------------------------------------
|
| VERSI "RESEP DIGITAL" (PHP + HTML)
|
| 1. Panggil header.php
| 2. (BARU) Query daftar obat untuk dropdown
| 3. Logika POST (FIXED: Pakai Transaction, INSERT ke resep_detail, UPDATE stok)
| 4. Logika GET (Sama, nampilin info pasien & riwayat)
| 5. Tampilan HTML (FIXED: Form diganti jadi Resep Digital)
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

// === (LANGKAH BARU) AMBIL DATA OBAT UNTUK DROPDOWN ===
$obat_list = [];
try {
    $sql_obat = "SELECT id_obat, nama_obat, satuan, dosis_per_unit, stok FROM tb_obat WHERE stok > 0 ORDER BY nama_obat ASC";
    $stmt_obat = $pdo->query($sql_obat);
    $obat_list = $stmt_obat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Gagal memuat daftar obat: " . $e->getMessage();
}
// === AKHIR LANGKAH BARU ===


// 3. --- LOGIKA SIMPAN DATA (POST) ---
// (Logika PHP ini SUDAH BENAR dan TIDAK PERLU DIUBAH)
// Dia akan menerima array dari 'id_obat' dan 'jumlah'
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id_pendaftaran = (int)$_POST['id_pendaftaran'];
    $diagnosa = trim($_POST['diagnosa']);
    $tindakan = trim($_POST['tindakan']);
    $catatan_dokter = trim($_POST['catatan_dokter']); 
    $id_user_dokter = (int)$_SESSION['id_user'];
    
    // Ambil data dari form resep (ini adalah ARRAY)
    $id_obat_array = $_POST['id_obat'] ?? []; 
    $jumlah_array = $_POST['jumlah'] ?? [];   
    $aturan_array = $_POST['aturan_pakai'] ?? [];

    if (empty($diagnosa)) {
        $error_msg = "Diagnosa wajib diisi!";
        $id_pendaftaran_get = $id_pendaftaran; 
    } else {
        
        try {
            $pdo->beginTransaction();
            
            // Aksi 1: INSERT ke tb_rekam_medis
            $sql_insert_rm = "INSERT INTO tb_rekam_medis 
                              (id_pendaftaran, id_user_dokter, diagnosa, tindakan, catatan_dokter, tgl_pemeriksaan)
                              VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert_rm = $pdo->prepare($sql_insert_rm);
            $stmt_insert_rm->execute([
                $id_pendaftaran, $id_user_dokter, $diagnosa,
                $tindakan, $catatan_dokter, date('Y-m-d H:i:s')
            ]);
            
            $id_rm_baru = $pdo->lastInsertId();
            
            // Aksi 2: UPDATE status di tb_pendaftaran
            $sql_update_pendaftaran = "UPDATE tb_pendaftaran SET status = 'Selesai' 
                                       WHERE id_pendaftaran = ?";
            $stmt_update_pendaftaran = $pdo->prepare($sql_update_pendaftaran);
            $stmt_update_pendaftaran->execute([$id_pendaftaran]);

            // Aksi 3 & 4: LOOPING & INSERT RESEP + UPDATE STOK
            $sql_insert_resep = "INSERT INTO tb_resep_detail 
                                 (id_rm, id_obat, jumlah_diberikan, aturan_pakai) 
                                 VALUES (?, ?, ?, ?)";
            $stmt_insert_resep = $pdo->prepare($sql_insert_resep);

            $sql_update_stok = "UPDATE tb_obat SET stok = stok - ? WHERE id_obat = ?";
            $stmt_update_stok = $pdo->prepare($sql_update_stok);

            if (!empty($id_obat_array)) { // Pastikan array-nya ada
                foreach ($id_obat_array as $key => $id_obat) {
                    $jumlah = (int)$jumlah_array[$key];
                    $aturan_pakai = trim($aturan_array[$key]);
                    
                    if ($jumlah > 0 && !empty($aturan_pakai)) {
                        $stmt_insert_resep->execute([$id_rm_baru, $id_obat, $jumlah, $aturan_pakai]);
                        $stmt_update_stok->execute([$jumlah, $id_obat]);
                    }
                }
            }
            
            $pdo->commit();
            
            $sukses_msg = "Rekam medis dan resep berhasil disimpan!";
            $pasien_info = null; 
            $id_rekam_medis_baru = $id_rm_baru;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Gagal menyimpan data (Transaksi Dibatalkan): " . $e->getMessage();
            $id_pendaftaran_get = $id_pendaftaran;
        }
    }
}


// 4. --- LOGIKA TAMPILAN AWAL (GET atau jika POST error) ---
// (Logika ini tidak berubah, sudah benar)
if (isset($id_pendaftaran_get) || (isset($_GET['id']) && is_numeric($_GET['id']))) {
    
    $id_pendaftaran = isset($id_pendaftaran_get) ? $id_pendaftaran_get : (int)$_GET['id'];

    try {
        // Query 1: Ambil data pasien (Termasuk berat_badan)
        $sql_pasien_info = "SELECT 
                                p.nm_pasien, p.no_rekam_medis, p.tgl_lahir, 
                                p.jenis_kelamin, p.berat_badan,
                                d.keluhan, d.id_pasien, d.id_pendaftaran
                            FROM tb_pendaftaran AS d
                            JOIN tb_pasien AS p ON d.id_pasien = p.id_pasien
                            WHERE d.id_pendaftaran = ?";
        
        $stmt_info = $pdo->prepare($sql_pasien_info);
        $stmt_info->execute([$id_pendaftaran]);
        $pasien_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($pasien_info) {
            $id_pasien = $pasien_info['id_pasien'];
            
            // Query 2: Ambil SEMUA riwayat medis pasien ini
            $sql_riwayat = "SELECT 
                                rm.id_rekam_medis, rm.diagnosa, rm.tindakan, rm.catatan_dokter,
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

    <form action="periksa_pasien" method="POST" autocomplete="off">
        <input type="hidden" name="id_pendaftaran" value="<?php echo htmlspecialchars($pasien_info['id_pendaftaran']); ?>">
        
        <div class="row">

            <div class="col-md-6">
                
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
                                <td><strong>Berat Badan</strong></td>
                                <td>: 
                                    <?php 
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
                
                <div class="mb-3">
                    <label for="diagnosa" class="form-label fw-bold">Diagnosa Dokter <span class="text-danger">*</span></label>
                    <textarea id="diagnosa" name="diagnosa" class="form-control" rows="3" required autofocus></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tindakan" class="form-label fw-bold">Tindakan Medis</label>
                    <textarea id="tindakan" name="tindakan" class="form-control" rows="3"></textarea>
                </div>

            </div> <div class="col-md-6">
                
                <div class="card border-info mb-3">
                    <div class="card-header fw-bold text-info">Resep</div>
                    <div class="card-body" style="background-color: #fcfdff;">
                        
                        <div class="row gx-2">
                            <div class="col-md-12">
                                <label class="form-label">Pilih Obat (dari Master Data)</label>
                                <select id="obat_select" class="form-select">
                                    <option value="">-- Pilih Obat --</option>
                                    <?php foreach ($obat_list as $obat): ?>
                                        <option value="<?php echo $obat['id_obat']; ?>" 
                                                data-nama-obat="<?php echo htmlspecialchars($obat['nama_obat'] . ' (' . $obat['dosis_per_unit'] . ')'); ?>"
                                                data-stok="<?php echo $obat['stok']; ?>">
                                            <?php echo htmlspecialchars($obat['nama_obat']); ?> 
                                            (<?php echo htmlspecialchars($obat['dosis_per_unit']); ?>)
                                            - [Stok: <?php echo $obat['stok']; ?>]
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row gx-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Jumlah</label>
                                <input type="number" id="obat_jumlah" class="form-control" min="1">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Aturan Pakai</label>
                                <input type="text" id="obat_aturan_pakai" class="form-control" placeholder="Contoh: 3x1 sesudah makan">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="btn_tambah_obat" class="btn btn-primary w-100">Add</button>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="fw-bold">Daftar Resep:</h6>
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Obat</th>
                                    <th>Jumlah</th>
                                    <th>Aturan Pakai</th>
                                    <th style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="resep_list_area">
                                </tbody>
                        </table>
                        
                        <div id="hidden_inputs_area">
                            </div>
                        
                    </div>
                </div>
                <div class="mb-3">
                    <label for="catatan_dokter" class="form-label fw-bold">Catatan</label>
                    <textarea id="catatan_dokter" name="catatan_dokter" class="form-control" rows="2" 
                              placeholder="Contoh: Istirahat 3 hari, Hindari makanan pedas, Kontrol 3 hari lagi..."></textarea>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-success btn-lg">Simpan Rekam Medis & Resep</button>
                </div>

            </div> </div> </form>


    <div class="row mt-4">
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


<script>
// Tunggu sampai halaman selesai di-load
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Ambil semua elemen penting
    const btnTambah = document.getElementById('btn_tambah_obat');
    const obatSelect = document.getElementById('obat_select');
    const obatJumlah = document.getElementById('obat_jumlah');
    const obatAturan = document.getElementById('obat_aturan_pakai');
    
    const tabelResepArea = document.getElementById('resep_list_area');
    const hiddenInputArea = document.getElementById('hidden_inputs_area');

    // 2. "Dengerin" tombol "Add"
    btnTambah.addEventListener('click', function() {
        
        // 3. Ambil data dari form input
        const selectedOption = obatSelect.options[obatSelect.selectedIndex];
        const idObat = selectedOption.value;
        const namaObat = selectedOption.getAttribute('data-nama-obat');
        const stok = parseInt(selectedOption.getAttribute('data-stok'));
        
        const jumlah = parseInt(obatJumlah.value);
        const aturan = obatAturan.value;

        // 4. Validasi Sederhana
        if (!idObat || idObat === "") {
            alert('Silakan pilih obat terlebih dahulu.');
            return;
        }
        if (isNaN(jumlah) || jumlah <= 0) {
            alert('Silakan masukkan jumlah obat yang valid.');
            return;
        }
        if (!aturan || aturan.trim() === "") {
            alert('Silakan masukkan aturan pakai.');
            return;
        }
        if (jumlah > stok) {
            alert('Stok obat tidak mencukupi! Stok tersisa: ' + stok);
            return;
        }

        // 5. Buat Tampilan Tabel (yang cantik)
        // (Ini adalah DOM Manipulation)
        const newRow = document.createElement('tr');
        // (Kita kasih ID unik ke baris ini biar gampang dihapus)
        newRow.setAttribute('id', `tabel_row_${idObat}`); 
        newRow.innerHTML = `
            <td>${namaObat}</td>
            <td>${jumlah}</td>
            <td>${aturan}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm btn_hapus_resep" data-id-obat-hapus="${idObat}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        
        // 6. Buat Input Tersembunyi (yang dibaca PHP)
        const hiddenInputs = `
            <div id="resep_row_${idObat}"> <input type="hidden" name="id_obat[]" value="${idObat}">
                <input type="hidden" name="jumlah[]" value="${jumlah}">
                <input type="hidden" name="aturan_pakai[]" value="${aturan}">
            </div>
        `;

        // 7. Tempel!
        tabelResepArea.appendChild(newRow);
        hiddenInputArea.insertAdjacentHTML('beforeend', hiddenInputs);
        
        // 8. Bersihkan form input
        obatSelect.selectedIndex = 0;
        obatJumlah.value = '';
        obatAturan.value = '';

        // (Bonus: Nonaktifkan obat yang sudah dipilih)
        selectedOption.disabled = true;
    });

    // 9. "Dengerin" tombol Hapus
    tabelResepArea.addEventListener('click', function(e) {
        const tombolHapus = e.target.closest('.btn_hapus_resep');
        
        if (tombolHapus) {
            
            const idObatToRemove = tombolHapus.getAttribute('data-id-obat-hapus');
            
            // Hapus 1: Hapus tampilan tabel <tr>
            const rowToRemove = document.getElementById(`tabel_row_${idObatToRemove}`);
            if (rowToRemove) {
                rowToRemove.remove();
            }
            
            // Hapus 2: Hapus input hidden-nya
            const hiddenRowToRemove = document.getElementById(`resep_row_${idObatToRemove}`);
            if (hiddenRowToRemove) {
                hiddenRowToRemove.remove();
            }

            // (Bonus: Aktifkan lagi obatnya di dropdown)
            const optionToEnable = obatSelect.querySelector(`option[value="${idObatToRemove}"]`);
            if (optionToEnable) {
                optionToEnable.disabled = false;
            }
        }
    });

});
</script>