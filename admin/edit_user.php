<?php
/*
|--------------------------------------------------------------------------
| EDIT USER (FORM & LOGIC UPDATE)
|--------------------------------------------------------------------------
|
| 1. Panggil header admin
| 2. Ambil ID dari URL
| 3. Ambil Data Lama (SELECT)
| 4. Cek jika form disubmit (POST) -> Lakukan UPDATE
| 5. Tampilkan form dengan data lama
|
*/

// 1. Panggil header
include 'header.php'; 

$error_msg = '';
$id_user = (int)($_GET['id'] ?? 0); // Ambil ID dari URL

// 2. Cek ID
if ($id_user === 0) {
    echo '<div class="alert alert-danger">ID User tidak valid. <a href="manajemen_user" class="btn-link">Kembali</a></div>';
    include 'footer.php';
    exit;
}

// 3. Ambil Data Lama (READ) untuk ditampilkan di form
try {
    $sql_read = "SELECT nm_lengkap, username, role, no_sip FROM tb_user WHERE id_user = ?";
    $stmt_read = $pdo->prepare($sql_read);
    $stmt_read->execute([$id_user]);
    $user_lama = $stmt_read->fetch(PDO::FETCH_ASSOC);

    if (!$user_lama) {
        echo '<div class="alert alert-danger">Data user tidak ditemukan. <a href="manajemen_user" class="btn-link">Kembali</a></div>';
        include 'footer.php';
        exit;
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    include 'footer.php';
    exit;
}


// 4. Logika UPDATE (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data dari form
    $nm_lengkap = trim($_POST['nm_lengkap']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $no_sip = trim($_POST['no_sip']); // Opsional
    
    // Ambil password (Opsional)
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi
    if (empty($nm_lengkap) || empty($username) || empty($role)) {
        $error_msg = 'Nama, Username, dan Role wajib diisi.';
    } else {
        
        // --- Mulai Persiapan Query ---
        $params = []; // Array untuk nampung data query
        
        // Query dasarnya
        $sql_update = "UPDATE tb_user SET 
                        nm_lengkap = ?, 
                        username = ?, 
                        role = ?, 
                        no_sip = ?";
        
        // Data dasar
        $params = [
            $nm_lengkap, 
            $username, 
            $role,
            ($role == 'dokter') ? $no_sip : null // Set no_sip jadi NULL jika bukan dokter
        ];

        // --- Logika Ganti Password (HANYA JIKA DIISI) ---
        if (!empty($password)) {
            if ($password != $konfirmasi_password) {
                $error_msg = 'Password dan Konfirmasi Password tidak cocok!';
            } else if (strlen($password) < 5) {
                $error_msg = 'Password minimal 5 karakter.';
            } else {
                // Password valid, HASH dan tambahkan ke query
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql_update .= ", password = ?"; // Tambahin query password
                $params[] = $hashed_password;   // Tambahin data password ke array
            }
        }
        
        // --- Eksekusi Query (JIKA TIDAK ADA ERROR) ---
        if (empty($error_msg)) {
            try {
                // Tambahkan ID user di akhir query
                $sql_update .= " WHERE id_user = ?";
                $params[] = $id_user;
                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute($params);

                // Redirect dengan pesan sukses
                $_SESSION['success_msg'] = "User '" . htmlspecialchars($username) . "' berhasil di-update!";
                header("Location: manajemen_user");
                exit;

            } catch (PDOException $e) {
                // Cek error duplikat username (Kode 23000)
                if($e->getCode() == 23000){
                    $error_msg = "Gagal: Username '" . htmlspecialchars($username) . "' sudah dipakai. Silakan gunakan username lain.";
                } else {
                    $error_msg = "Gagal meng-update data: " . $e->getMessage();
                }
            }
        }
    }
}


// (Ini biar data di form tetep pakai data baru kalo validasinya error)
$data_form = $_POST ?: $user_lama;

?>

<h1 class="h3 mb-4 text-gray-800">Edit User: <?php echo htmlspecialchars($user_lama['username']); ?></h1>

<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form action="edit_user?id=<?php echo $id_user; ?>" method="POST" autocomplete="off">
    
    <div class="row">
    
        <div class="col-md-6">
            <div class="mb-3">
                <label for="nm_lengkap" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nm_lengkap" name="nm_lengkap" required 
                       value="<?php echo htmlspecialchars($data_form['nm_lengkap'] ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($data_form['username'] ?? ''); ?>">
                <small class="text-muted">Ini akan dipakai untuk login.</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-bold">Password Baru (Opsional)</label>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Kosongkan jika tidak ingin ganti password">
                <small class="text-muted">Minimal 5 karakter.</small>
            </div>
            
            <div class="mb-3">
                <label for="konfirmasi_password" class="form-label fw-bold">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password"
                       placeholder="Kosongkan jika tidak ingin ganti password">
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="role" class="form-label fw-bold">Role / Level Akses <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="petugas" <?php echo (($data_form['role'] ?? '') == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                    <option value="dokter" <?php echo (($data_form['role'] ?? '') == 'dokter') ? 'selected' : ''; ?>>Dokter</option>
                    <option value="admin" <?php echo (($data_form['role'] ?? '') == 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="mb-3" id="form-no-sip" style="display: none;"> <label for="no_sip" class="form-label fw-bold">No. SIP (Surat Izin Praktik)</label>
                <input type="text" class="form-control" id="no_sip" name="no_sip" 
                       placeholder="Wajib diisi jika role = Dokter"
                       value="<?php echo htmlspecialchars($data_form['no_sip'] ?? ''); ?>">
            </div>
        </div>
    </div>
    
    <hr>
    
    <div class="d-flex justify-content-end">
        <a href="manajemen_user" class="btn btn-secondary me-2">Batal</a>
        <button type="submit" class="btn btn-warning">Update Data User</button>
    </div>
    
</form>

<?php 
// Kita panggil footer, tapi kita tambahin script JS dikit
// untuk nampilin/sembunyiin form No. SIP
?>

<script>
    // Ambil elemen select 'role' dan form 'no_sip'
    const roleSelect = document.getElementById('role');
    const noSipForm = document.getElementById('form-no-sip');
    const noSipInput = document.getElementById('no_sip');

    // Bikin fungsi untuk nge-cek
    function toggleNoSipForm() {
        if (roleSelect.value === 'dokter') {
            noSipForm.style.display = 'block'; // Tampilkan
            noSipInput.required = true; // Wajib diisi
        } else {
            noSipForm.style.display = 'none'; // Sembunyikan
            noSipInput.required = false; // Nggak wajib
            noSipInput.value = ''; // Kosongkan nilainya jika bukan dokter
        }
    }

    // Panggil fungsi-nya pas halaman di-load (PENTING untuk EDIT)
    toggleNoSipForm();

    // Panggil fungsi-nya setiap kali pilihan 'role' diganti
    roleSelect.addEventListener('change', toggleNoSipForm);
</script>


<?php
include 'footer.php'; // Panggil Footer Admin
?>