<?php
/*
|--------------------------------------------------------------------------
| MASTER DATA OBAT (TAMPILKAN DATA)
|--------------------------------------------------------------------------
|
| (FIXED: Layout FINAL Sesuai Idemu)
| - Kolom Preview (Indikasi/Efek) TETAP ADA.
| - Tombol "Lihat Detail" (Modal) MASUK ke kolom "Aksi".
| - Modal (Pop-up) nampilin SEMUA data.
|
*/

// 1. Panggil header
include 'header.php'; 

// 2. Ambil pesan sukses/error dari session
$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']); 

$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['error_msg']); 
?>

<h1 class="h3 mb-4 text-gray-800">Data Obat</h1>

<?php if ($success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<div class="d-flex justify-content-end mb-3">
    <a href="/klinik-dikri/admin/tambah_obat" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Obat 
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Obat</th>
                        <th>Satuan</th>
                        <th>Dosis</th>
                        <th>Stok</th>
                        <th>Indikasi (Preview)</th> <th>Efek Samping (Preview)</th> <th style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // (Query sudah benar, ambil semua kolom)
                        $sql = "SELECT * FROM tb_obat ORDER BY nama_obat ASC";
                        $stmt = $pdo->query($sql);
                        $no = 1;
                        
                        while ($obat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($obat['nama_obat']) . "</td>";
                            echo "<td>" . htmlspecialchars($obat['satuan']) . "</td>";
                            echo "<td>" . htmlspecialchars($obat['dosis_per_unit']) . "</td>";
                            echo "<td>" . htmlspecialchars($obat['stok']) . "</td>";
                            
                            // === TETAP TAMPILKAN PREVIEW (pakai substr) ===
                            $indikasi_preview = htmlspecialchars(substr($obat['indikasi'], 0, 30));
                            if (strlen($obat['indikasi']) > 30) $indikasi_preview .= '...';
                            
                            $efek_preview = htmlspecialchars(substr($obat['efek_samping'], 0, 30));
                            if (strlen($obat['efek_samping']) > 30) $efek_preview .= '...';
                            
                            echo '<td>' . $indikasi_preview . '</td>';
                            echo '<td>' . $efek_preview . '</td>';
                            
                            
                            // === SEMUA AKSI DIGABUNG DI SINI ===
                            echo '<td>
                                    <button type="button" class="btn btn-sm btn-info me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalObatDetail"
                                            data-nama-obat="' . htmlspecialchars($obat['nama_obat']) . '"
                                            data-satuan="' . htmlspecialchars($obat['satuan']) . '"
                                            data-dosis="' . htmlspecialchars($obat['dosis_per_unit']) . '"
                                            data-stok="' . htmlspecialchars($obat['stok']) . '"
                                            data-indikasi="' . htmlspecialchars($obat['indikasi']) . '"
                                            data-efek="' . htmlspecialchars($obat['efek_samping']) . '"
                                            title="Lihat Detail Lengkap">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <a href="/klinik-dikri/admin/edit_obat?id=' . $obat['id_obat'] . '" class="btn btn-sm btn-warning me-1" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <a href="/klinik-dikri/admin/hapus_obat?id=' . $obat['id_obat'] . '" class="btn btn-sm btn-danger" title="Hapus Data" onclick="return confirm(\'Yakin ingin menghapus obat ini?\')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                  </td>';
                            echo "</tr>";
                        }

                        if ($stmt->rowCount() == 0) {
                            echo '<tr><td colspan="8" class="text-center p-4">Belum ada data obat. Silakan tambah data baru.</td></tr>';
                        }

                    } catch (PDOException $e) {
                        echo '<tr><td colspan="8" class="text-center text-danger p-4">Gagal mengambil data: ' . $e->getMessage() . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="modalObatDetail" tabindex="-1" aria-labelledby="modalObatLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"> <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNamaObat">[Nama Obat]</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <table class="table table-sm table-borderless">
            <tr>
                <td style="width: 150px;"><strong>Satuan</strong></td>
                <td>: <span id="modalSatuan"></span></td>
            </tr>
            <tr>
                <td><strong>Dosis / Unit</strong></td>
                <td>: <span id="modalDosis"></span></td>
            </tr>
            <tr>
                <td><strong>Stok Saat Ini</strong></td>
                <td>: <span id="modalStok"></span></td>
            </tr>
        </table>

        <hr>
        <h6 class="fw-bold">Indikasi (Kegunaan)</h6>
        <p id="modalIndikasi" style="white-space: pre-wrap;">[Indikasi akan muncul di sini]</p>
        
        <hr>
        <h6 class="fw-bold">Efek Samping</h6>
        <p id="modalEfekSamping" style="white-space: pre-wrap;">[Efek Samping akan muncul di sini]</p>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php 
// 6. Panggil footer
include 'footer.php'; 
?>