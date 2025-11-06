<?php
/*
|--------------------------------------------------------------------------
| Halaman Reset Password (reset_password.php)
|--------------------------------------------------------------------------
|
| 1. Panggil config.php
| 2. SATPAM: Cek apakah session 'id_user_reset' ada. Kalau nggak, tendang.
| 3. Cek jika (POST):
|    - Validasi password baru & konfirmasi.
|    - Hash password baru.
|    - UPDATE password di tb_user.
|    - Hapus session 'id_user_reset'.
|
*/

// 1. Panggil Koneksi
require_once 'config/config.php';

// 2. SATPAM
// Cek apakah user sudah lolos dari lupa_password.php
if (!isset($_SESSION['id_user_reset'])) {
    // Kalo belum, tendang balik ke login
    header("Location: ./"); // (./ artinya index.php)
    exit;
}

// Ambil ID user yang mau di-reset dari session
$id_user_to_reset = $_SESSION['id_user_reset'];

$error_msg = ''; 
$sukses_msg = '';

// 3. Proses Ganti Password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi
    if (empty($password_baru) || empty($konfirmasi_password)) {
        $error_msg = "Password tidak boleh kosong!";
    } else if ($password_baru != $konfirmasi_password) {
        $error_msg = "Konfirmasi password tidak cocok!";
    } else if (strlen($password_baru) < 5) {
        $error_msg = "Password minimal 5 karakter.";
    } else {
        // --- PROSES UPDATE ---
        try {
            // Hash password baru
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            
            // Update ke database
            $sql = "UPDATE tb_user SET password = ? WHERE id_user = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hashed_password, $id_user_to_reset]);
            
            // Berhasil!
            $sukses_msg = "Password Anda telah berhasil diperbarui!";
            
            // Hapus session reset-nya biar aman
            unset($_SESSION['id_user_reset']);
            
        } catch (PDOException $e) {
            $error_msg = "Terjadi masalah koneksi: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SIM Klinik</title>
    <link rel="icon" type="image/png" href="/klinik-dikri/assets/image/favicon1.png">
    <link rel="apple-touch-icon" href="/klinik-dikri/assets/image/apple-touch-icon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <style>
        body {
            background-color: #f8f9fa; 
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>

    <main class="login-card">
        <div class="card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                
                <div class="text-center mb-4">
                    <h2 class="h3 fw-bold">Buat Password Baru</h2>
                    <p class="text-muted">Masukkan password baru Anda.</p>
                </div>
                
                <?php if (empty($sukses_msg)): ?>
                
                    <form action="reset_password" method="POST" autocomplete="off">
                        
                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru</label>
                            <input type="password" id="password_baru" name="password_baru" class="form-control" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="form-control" required>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Simpan Password Baru</button>
                        </div>
                    </form>

                <?php endif; ?>


                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger mt-4 text-center">
                        <?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($sukses_msg)): ?>
                    <div class="alert alert-success mt-4 text-center">
                        <?php echo htmlspecialchars($sukses_msg); ?>
                    </div>
                    <div class="d-grid">
                        <a href="./" class="btn btn-success">Kembali ke Halaman Login</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
    
    <script src="assets/js/bootstrap.bundle.min.js"></script> 
</body>
</html>