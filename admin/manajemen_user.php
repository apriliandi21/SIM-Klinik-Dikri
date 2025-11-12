<?php
/*
|--------------------------------------------------------------------------
| MANAJEMEN USER (TAMPILKAN DATA)
|--------------------------------------------------------------------------
|
| (FIXED: Nampilin Status Akun & Tombol Aksi Dinamis)
|
*/

// 1. Panggil header
include 'header.php'; 

// 2. Ambil pesan sukses/error dari session (Flash Message)
$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']); 

$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['error_msg']); 
?>

<h1 class="h3 mb-4 text-gray-800">Manajemen User</h1>

<?php if ($success_msg): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($success_msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($error_msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>


<div class="d-flex justify-content-end mb-3">
    <a href="/klinik-dikri/admin/tambah_user" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Tambah User Baru
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role / Level</th>
                        <th>Status Akun</th> <th>No. SIP (Jika Dokter)</th>
                        <th style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // (FIX: Query ditambah 'status_akun')
                        $sql = "SELECT id_user, nm_lengkap, username, role, no_sip, status_akun FROM tb_user ORDER BY role ASC, nm_lengkap ASC";
                        $stmt = $pdo->query($sql);
                        $no = 1;
                        
                        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            
                            // Bikin "badge" warna-warni buat role
                            $badge_role_color = 'secondary';
                            if ($user['role'] == 'admin') $badge_role_color = 'danger';
                            if ($user['role'] == 'dokter') $badge_role_color = 'success';
                            if ($user['role'] == 'petugas') $badge_role_color = 'primary';
                            
                            // (FIX: Bikin badge untuk Status Akun)
                            $badge_status_color = ($user['status_akun'] == 'aktif') ? 'success' : 'secondary';

                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($user['nm_lengkap']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                            echo '<td><span class="badge bg-' . $badge_role_color . '">' . htmlspecialchars(ucfirst($user['role'])) . '</span></td>';
                            echo '<td><span class="badge bg-' . $badge_status_color . '">' . htmlspecialchars(ucfirst($user['status_akun'])) . '</span></td>';
                            echo "<td>" . htmlspecialchars($user['no_sip'] ?? '-') . "</td>";
                            
                            // === (FIX: LOGIKA TOMBOL AKSI DINAMIS) ===
                            echo '<td>
                                    <a href="/klinik-dikri/admin/edit_user?id=' . $user['id_user'] . '" class="btn btn-sm btn-warning me-2" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>';
                            
                            // Cek biar nggak bisa non-aktifin diri sendiri
                            if ($_SESSION['id_user'] == $user['id_user']) {
                                echo '<button class="btn btn-sm btn-secondary" disabled title="Tidak bisa mengubah status diri sendiri">
                                        <i class="fas fa-ban"></i>
                                      </button>';
                            } else {
                                // Jika akunnya 'aktif', tampilkan tombol "Non-aktifkan" (Merah)
                                if ($user['status_akun'] == 'aktif') {
                                    echo '<a href="/klinik-dikri/admin/hapus_user?id=' . $user['id_user'] . '" class="btn btn-sm btn-danger" title="Non-aktifkan User" onclick="return confirm(\'Yakin ingin NONAKTIFKAN user ' . htmlspecialchars($user['username']) . ' ini?\')">
                                            <i class="fas fa-trash-alt"></i>
                                          </a>';
                                } else {
                                // Jika akunnya 'non-aktif', tampilkan tombol "Aktifkan" (Hijau)
                                    echo '<a href="/klinik-dikri/admin/aktifkan_user?id=' . $user['id_user'] . '" class="btn btn-sm btn-success" title="Aktifkan User" onclick="return confirm(\'Yakin ingin MENGAKTIFKAN user ' . htmlspecialchars($user['username']) . ' ini?\')">
                                            <i class="fas fa-check"></i>
                                          </a>';
                                }
                            }
                            
                            echo '</td>';
                            echo "</tr>";
                        }

                    } catch (PDOException $e) {
                        // (FIX: Colspan jadi 7)
                        echo '<tr><td colspan="7" class="text-center text-danger p-4">Gagal mengambil data: ' . $e->getMessage() . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// 6. Panggil footer
include 'footer.php'; 
?>