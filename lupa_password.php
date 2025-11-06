<?php
/*
|--------------------------------------------------------------------------
| Halaman Lupa Password (lupa_password.php)
|--------------------------------------------------------------------------
|
| 1. Panggil config.php
| 2. Cek jika (POST):
|    - Validasi username ada di tb_user.
|    - Jika ADA, simpan ID-nya ke session & lempar ke reset_password.php
|
*/

// 1. Panggil Koneksi
require_once 'config/config.php';

$error_msg = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);

    if (empty($username)) {
        $error_msg = "Username tidak boleh kosong!";
    } else {
        try {
            // Cek username-nya ada nggak
            $sql = "SELECT id_user FROM tb_user WHERE username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // === USERNAME DITEMUKAN ===
                
                // 1. Simpan ID user ini ke session khusus
                $_SESSION['id_user_reset'] = $user['id_user'];
                
                // 2. Lempar ke halaman reset (tanpa .php)
                header("Location: reset_password");
                exit;

            } else {
                // === USERNAME GAGAL ===
                $error_msg = "Username tidak ditemukan di database!";
            }

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
    <title>Lupa Password - SIM Klinik</title>
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
                    <h2 class="h3 fw-bold">Lupa Password</h2>
                    <p class="text-muted">Masukkan username Anda untuk melanjutkan.</p>
                </div>
                
                <form action="lupa_password" method="POST" autocomplete="off">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required autofocus>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Cari Akun</button>
                    </div>
                </form>

                <?php
                if (!empty($error_msg)) {
                    echo '<div class="alert alert-danger mt-4 text-center">' . htmlspecialchars($error_msg) . '</div>';
                }
                ?>
                
                <div class="text-center mt-4">
                    <a href="./" class="btn-link text-decoration-none">&laquo; Kembali ke Login</a>
                </div>

            </div>
        </div>
    </main>
    
    <script src="assets/js/bootstrap.bundle.min.js"></script> 
</body>
</html>