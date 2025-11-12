<?php
/*
|--------------------------------------------------------------------------
| TAMBAH USER BARU (FORM & LOGIC)
|--------------------------------------------------------------------------
|
| 1. Panggil header admin
| 2. Cek jika form disubmit (POST)
| 3. Validasi (cek username, password cocok)
| 4. HASH PASSWORD (PENTING!)
| 5. INSERT data ke tbl_user
| 6. Redirect kembali ke manajemen_user.php dengan pesan sukses
|
*/

// 1. Panggil header
include 'header.php'; 

$error_msg = '';

// 2. Logika INSERT (CREATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data dari form
    $nm_lengkap = trim($_POST['nm_lengkap']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $role = $_POST['role'];
    $no_sip = trim($_POST['no_sip']); // Opsional

    // 3. Validasi
    if (empty($nm_lengkap) || empty($username) || empty($password) || empty($role)) {
        $error_msg = 'Nama, Username, Password, dan Role wajib diisi.';
    } else if ($password != $konfirmasi_password) {
        $error_msg = 'Password dan Konfirmasi Password tidak cocok!';
    } else if (strlen($password) < 5) {
        $error_msg = 'Password minimal 5 karakter.';
    } else {
        
        // Cek dulu apakah username sudah ada
        try {
            $sql_cek = "SELECT id_user FROM tb_user WHERE username = ?";
            $stmt_cek = $pdo->prepare($sql_cek);
            $stmt_cek->execute([$username]);
            
            if ($stmt_cek->rowCount() > 0) {
                $error_msg = "Username '" . htmlspecialchars($username) . "' sudah dipakai. Silakan gunakan username lain.";
            } else {
                
                // --- Username aman, lanjut INSERT ---
                
                // 4. HASH PASSWORD (WAJIB!)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Jika role bukan dokter, no_sip harus NULL
                if ($role != 'dokter') {
                    $no_sip = null;
                }

                // 5. INSERT data ke database
                $sql = "INSERT INTO tb_user (nm_lengkap, username, password, role, no_sip)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nm_lengkap, 
                    $username, 
                    $hashed_password, 
                    $role,
                    $no_sip
                ]);

                // 6. Redirect dengan pesan sukses
                $_SESSION['success_msg'] = "User '" . htmlspecialchars($username) . "' (" . $role . ") berhasil ditambahkan!";
                header("Location: manajemen_user"); // Redirect ke halaman tabel
                exit;
            }

        } catch (PDOException $e) {
            $error_msg = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>

<h1 class="h3 mb-4 text-gray-800">Tambah User Baru (Petugas / Dokter)</h1>

<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form action="tambah_user" method="POST" autocomplete="off">
    
    <div class="row">
    
        <div class="col-md-6">
            <div class="mb-3">
                <label for="nm_lengkap" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nm_lengkap" name="nm_lengkap" required 
                       value="<?php echo htmlspecialchars($_POST['nm_lengkap'] ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <small class="text-muted">Ini akan dipakai untuk login.</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small class="text-muted">Minimal 5 karakter.</small>
            </div>
            
            <div class="mb-3">
                <label for="konfirmasi_password" class="form-label fw-bold">Konfirmasi Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="role" class="form-label fw-bold">Role / Level Akses <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="petugas" <?php echo (($_POST['role'] ?? '') == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                    <option value="dokter" <?php echo (($_POST['role'] ?? '') == 'dokter') ? 'selected' : ''; ?>>Dokter</option>
                    </select>
            </div>

            <div class="mb-3" id="form-no-sip" style="display: none;"> <label for="no_sip" class="form-label fw-bold">No. SIP (Surat Izin Praktik)</label>
                <input type="text" class="form-control" id="no_sip" name="no_sip" 
                       placeholder="Wajib diisi jika role = Dokter"
                       value="<?php echo htmlspecialchars($_POST['no_sip'] ?? ''); ?>">
            </div>
        </div>
    </div>
    
    <hr>
    
    <div class="d-flex justify-content-end">
        <a href="manajemen_user" class="btn btn-secondary me-2">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan User Baru</button>
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

    // Panggil fungsi-nya pas halaman di-load (jaga-jaga kalau error validasi)
    toggleNoSipForm();

    // Panggil fungsi-nya setiap kali pilihan 'role' diganti
    roleSelect.addEventListener('change', toggleNoSipForm);
</script>


<?php
include 'footer.php'; // Panggil Footer Admin
?>