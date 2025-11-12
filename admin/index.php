<?php
/*
|--------------------------------------------------------------------------
| DASHBOARD ADMIN (Versi "Live" dengan Statistik)
|--------------------------------------------------------------------------
|
| 1. Panggil header admin
| 2. Query 1: Hitung total user di tb_user
| 3. Query 2: Hitung total obat di tbl_obat
| 4. Tampilkan di kartu statistik
| 5. Panggil footer
|
*/

// 1. Panggil header
include 'header.php'; 

// 2. Query 1: Hitung Total User
$total_user = 0; // Siapkan variabel
try {
    // (Variabel $pdo dari config.php)
    $sql_user = "SELECT COUNT(id_user) FROM tb_user";
    $stmt_user = $pdo->query($sql_user);
    $total_user = $stmt_user->fetchColumn(); // Ambil 1 data kolom

} catch (PDOException $e) {
    // Biarkan 0 jika error
}

// 3. Query 2: Hitung Total Obat
$total_obat = 0; // Siapkan variabel
try {
    $sql_obat = "SELECT COUNT(id_obat) FROM tb_obat";
    $stmt_obat = $pdo->query($sql_obat);
    $total_obat = $stmt_obat->fetchColumn(); 

} catch (PDOException $e) {
    // Biarkan 0 jika error
}

?>

<h1 class="h3 mb-4 text-gray-800">Dashboard Admin </h1>

<div class="card border-left-primary shadow-sm mb-4">
    <div class="card-body">
        <h4 class="card-title">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h4>
        <p class="card-text text-muted">
            Anda login sebagai <strong><?php echo $_SESSION['role']; ?></strong>.
            <br>
            Anda memiliki akses penuh untuk mengelola data master dan akun user. Silakan gunakan menu navigasi.
        </p>
    </div>
</div>


<div class="row">

    <div class="col-md-6 mb-4">
        <div class="card border-left-info shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">
                            Jumlah Akun Aktif (Dokter, Petugas, Admin)
                        </div>
                        <div class="h2 mb-0 fw-bold text-gray-800">
                            <?php echo $total_user; ?> User </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card border-left-success shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                            Total Data Obat
                        </div>
                        <div class="h2 mb-0 fw-bold text-gray-800">
                            <?php echo $total_obat; ?> Jenis Obat </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-pills fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <?php
// Panggil footer
include 'footer.php';
?>