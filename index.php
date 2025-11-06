<?php
/*
|--------------------------------------------------------------------------
| Halaman Login Utama (index.php)
|--------------------------------------------------------------------------
|
| VERSI BOOTSTRAP (PHP + HTML)
|
| 1. Logika PHP (Sudah benar, path redirect sudah absolut)
| 2. Tampilan HTML (BARU, pakai Bootstrap Card)
|
*/

// 1. Panggil Koneksi
require_once 'config/config.php';

// 2. Cek jika sudah login, lempar ke dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'petugas') {
        header("Location: /klinik-dikri/petugas/");
        exit;
    } else if ($_SESSION['role'] == 'dokter') {
        header("Location: /klinik-dikri/dokter/");
        exit;
    }
}

// 3. Proses Login
$error_msg = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_msg = "Username dan password tidak boleh kosong!";
    } else {
        try {
            $sql = "SELECT * FROM tb_user WHERE username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['nama_lengkap'] = $user['nm_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] == 'petugas') {
                    header("Location: /klinik-dikri/petugas/");
                    exit;
                } else if ($user['role'] == 'dokter') {
                    $_SESSION['no_sip'] = $user['no_sip'];
                    header("Location: /klinik-dikri/dokter/");
                    exit;
                }

            } else {
                $error_msg = "Username atau password salah!";
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
    <title>Login - SIM Klinik dr. Dikri</title>
    <link rel="icon" type="image/png" href="/klinik-dikri/assets/image/favicon1.png">
    <link rel="apple-touch-icon" href="/klinik-dikri/assets/image/apple-touch-icon.png">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css"> 
    
    <style>
        body {
            /* Warna abu-abu muda dari Bootstrap */
            background-color: #f8f9fa; 
            /* Bikin form-nya di tengah layar */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px; /* Lebar maksimum box login */
        }
    </style>
</head>
<body>

    <main class="login-card">
        <div class="card shadow-lg border-0">
            <div class="card-body p-4 p-md-5"> <div class="text-center mb-4">
                    <h2 class="h3 fw-bold">SIM KLINIK</h2>
                    <p class="text-muted">Klinik dr. Dikri</p>
                </div>
                
                <form action="" method="POST" autocomplete="off">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="d-grid mt-4"> <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>

                <?php
                // Tampilkan pesan error jika ada
                if (!empty($error_msg)) {
                    // Pakai style alert Bootstrap
                    echo '<div class="alert alert-danger mt-4 text-center">' . htmlspecialchars($error_msg) . '</div>';
                }
                ?>
                
                <div class="text-center mt-4">
                    <a href="lupa_password" class="btn-link text-decoration-none">Lupa Password?</a>
                </div>

            </div>
        </div>
    </main>
    
    <script src="assets/js/bootstrap.bundle.min.js"></script> 

</body>
</html>