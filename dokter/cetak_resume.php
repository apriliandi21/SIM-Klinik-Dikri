<?php
/*
|--------------------------------------------------------------------------
| Halaman Cetak Resume Medis (dokter/cetak_resume.php)
|--------------------------------------------------------------------------
|
| (FIXED: Favicon, Layout Tabel Resep, dan Resep Digital dari tbl_resep_detail)
|
*/

require_once '../config/config.php';

$rekam_medis_data = null;
$resep_data = []; // (BARU) Array untuk nampung resep
$error_msg = '';

// Pastikan ada ID rekam medis di URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_rekam_medis = (int)$_GET['id'];

    try {
        // Query 1: Ambil data rekam medis utama
        $sql = "SELECT 
                    rm.tgl_pemeriksaan, 
                    rm.diagnosa, 
                    rm.tindakan, 
                    rm.catatan_dokter, -- Ini adalah Catatan Tambahan (Non-Resep)
                    p.no_rekam_medis, 
                    p.nm_pasien, 
                    p.tgl_lahir,
                    p.berat_badan,
                    d.keluhan,
                    u.nm_lengkap AS nama_dokter_pemeriksa,
                    u.no_sip
                FROM 
                    tb_rekam_medis AS rm
                JOIN 
                    tb_pendaftaran AS d ON rm.id_pendaftaran = d.id_pendaftaran
                JOIN 
                    tb_pasien AS p ON d.id_pasien = p.id_pasien
                JOIN 
                    tb_user AS u ON rm.id_user_dokter = u.id_user
                WHERE 
                    rm.id_rekam_medis = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_rekam_medis]);
        $rekam_medis_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rekam_medis_data) {
            $error_msg = "Data rekam medis tidak ditemukan.";
        } else {
            // === LANGKAH BARU: Ambil data resep digital ===
            $sql_resep = "SELECT 
                            o.nama_obat, 
                            o.dosis_per_unit,
                            rd.jumlah_diberikan,
                            rd.aturan_pakai
                          FROM 
                            tb_detail_obat AS rd
                          JOIN 
                            tb_obat AS o ON rd.id_obat = o.id_obat
                          WHERE 
                            rd.id_rekam_medis = ?";
            
            $stmt_resep = $pdo->prepare($sql_resep);
            $stmt_resep->execute([$id_rekam_medis]);
            $resep_data = $stmt_resep->fetchAll(PDO::FETCH_ASSOC);
            // === AKHIR LANGKAH BARU ===
        }

    } catch (PDOException $e) {
        $error_msg = "Error: " . $e->getMessage();
    }

} else {
    $error_msg = "ID Rekam Medis tidak valid.";
}

// Jika ada error, tampilkan pesan dan keluar
if ($error_msg) {
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; text-align: center; color: red;'>";
    echo "<h2>Kesalahan!</h2>";
    echo "<p>" . htmlspecialchars($error_msg) . "</p>";
    echo "<p><a href='javascript:history.back()'>Kembali</a></p>";
    echo "</div></body></html>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resume Medis - <?php echo htmlspecialchars($rekam_medis_data['nm_pasien'] ?? 'Pasien'); ?></title>
    
    
    <link rel="icon" type="image/png" href="/klinik-dikri/assets/image/favicon1.png">
    <link rel="apple-touch-icon" href="/klinik-dikri/assets/image/apple-touch-icon.png">
    
    <link rel="stylesheet" href="../assets/css/print.css">
    
</head>
<body onload="window.print()">
    <div class="container">
        
        <div class="header">
            <h1><?php echo NAMA_KLINIK; ?></h1>
            <p>
                <?php echo ALAMAT_KLINIK; ?> | 
                Telp: <?php echo TELPON_KLINIK; ?> | 
                Email: <?php echo EMAIL_KLINIK; ?>
            </p>
        </div>

        <div class="section-title">Resume Hasil Pemeriksaan</div>

        <table class="info-pasien">
            <tr>
                <td style="width: 50%;">
                    <span class="info-label">No. Rekam Medis</span> : <?php echo htmlspecialchars($rekam_medis_data['no_rekam_medis']); ?>
                </td>
                <td style="width: 50%;">
                    <span class="info-label">Tgl. Pemeriksaan</span> : <?php echo date('d F Y', strtotime($rekam_medis_data['tgl_pemeriksaan'])); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">Nama Pasien</span> : <?php echo htmlspecialchars($rekam_medis_data['nm_pasien']); ?>
                </td>
                <td>
                    <span class="info-label">Tgl. Lahir</span> : <?php echo date('d F Y', strtotime($rekam_medis_data['tgl_lahir'])); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">Berat Badan</span> : 
                    <?php 
                    echo !empty($rekam_medis_data['berat_badan']) 
                         ? htmlspecialchars($rekam_medis_data['berat_badan']) . ' kg' 
                         : '-'; 
                    ?>
                </td>
                <td>
                </td>
            </tr>
        </table>

        
        <div class="section-title">Hasil Pemeriksaan</div>
        
        <table class="hasil-pemeriksaan">
            <tr>
                <td class="label-tabel">Keluhan Utama</td>
                <td class="content-tabel"><?php echo nl2br(htmlspecialchars($rekam_medis_data['keluhan'])); ?></td>
            </tr>
            <tr>
                <td class="label-tabel">Diagnosa</td>
                <td class="content-tabel"><?php echo nl2br(htmlspecialchars($rekam_medis_data['diagnosa'])); ?></td>
            </tr>
            <tr>
                <td class="label-tabel">Tindakan</td>
                <td class="content-tabel"><?php echo nl2br(htmlspecialchars($rekam_medis_data['tindakan'])); ?></td>
            </tr>
        </table>

        <div class="section-title" style="margin-top: 15px;">Resep Obat</div>
        <table class="hasil-pemeriksaan">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th style="width: 35%; padding: 8px 12px; border: 1px solid #000;">Nama Obat (Dosis)</th>
                    <th style="width: 10%; padding: 8px 12px; border: 1px solid #000; text-align: center;">Jumlah</th>
                    <th style="width: 55%; padding: 8px 12px; border: 1px solid #000;">Aturan Pakai</th>
                    </tr>
            </thead>
            <tbody>
                <?php if (!empty($resep_data)): ?>
                    <?php foreach ($resep_data as $resep): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($resep['nama_obat'] . ' (' . $resep['dosis_per_unit'] . ')'); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($resep['jumlah_diberikan']); ?></td>
                            <td><?php echo htmlspecialchars($resep['aturan_pakai']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">- Tidak ada resep obat -</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($rekam_medis_data['catatan_dokter'])): ?>
            <div class="section-title" style="margin-top: 15px;">Catatan Tambahan</div>
            <div class="content-box" style="border: 1px solid #ccc; padding: 10px; font-size: 11pt;">
                <?php echo nl2br(htmlspecialchars($rekam_medis_data['catatan_dokter'])); ?>
            </div>
        <?php endif; ?>
        
        
        <div class="signature">
            <div class="date">Sukabumi, <?php echo date('d F Y'); ?></div>
            <div class="position">Dokter Pemeriksa,</div>
            
            <br><br><br> <div class="name">( <?php echo htmlspecialchars($rekam_medis_data['nama_dokter_pemeriksa']); ?> )</div>
            <div class="sip">SIP: <?php echo htmlspecialchars($rekam_medis_data['no_sip'] ?? '123/SIP/2025'); ?></div>
        </div>
        
    </div>
</body>
</html>