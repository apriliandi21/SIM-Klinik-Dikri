<?php
/*
|--------------------------------------------------------------------------
| File Konfigurasi Utama (config.php)
|--------------------------------------------------------------------------
|
| 1. Setting Waktu (Timezone)
| 2. Setting Koneksi Database
| 3. Membuat Koneksi (kita pakai PDO)
| 4. Memulai Session
|
*/

// 1. Setting Waktu (Timezone)
// Ini penting agar semua fungsi tanggal & waktu di PHP sesuai Waktu Indonesia Barat
date_default_timezone_set('Asia/Jakarta');


// 2. Setting Koneksi Database
// Sesuaikan ini dengan setting XAMPP kamu
$db_host = 'localhost';     // Host database, biasanya 'localhost'
$db_user = 'root';          // User database, default XAMPP itu 'root'
$db_pass = '';              // Password database, default XAMPP itu kosong ''
$db_name = 'klinik';        // Nama database kamu di phpMyAdmin


// 3. Buat Koneksi Database (Pakai PDO yang lebih modern & aman)
try {
    // Buat objek PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    
    // Set mode error PDO ke "exception"
    // Ini penting biar kalau ada query yg error, programnya berhenti & kasih tahu errornya
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Opsi tambahan (best practice)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // Kalau koneksi GAGAL, hentikan semua proses dan tampilkan pesan error
    // Ini akan sangat membantumu saat debugging
    die("Koneksi ke database GAGAL: " . $e->getMessage());
}


// 4. Mulai Session
// Ini WAJIB ada agar kita bisa pakai $_SESSION
// $_SESSION dipakai untuk "mengingat" siapa yang login di semua halaman
if (!session_id()) {
    session_start();
}

/*
// CATATAN:
// Baris di bawah ini bisa kamu nyalakan (hapus tanda //)
// untuk ngetes koneksi. Buka: localhost/klinik-dickri/config/config.php
// Kalau muncul "Koneksi berhasil!", berarti sukses.
// Jangan lupa dihapus/comment lagi kalau sudah berhasil.

// echo "Koneksi ke database '$db_name' berhasil!"; 
*/
// ( ... kodingan session_start() kamu ... )

// =============================================
// PENGATURAN INFO KLINIK (UNTUK KOP SURAT, DLL)
// =============================================
// Ganti info di bawah ini jika ada perubahan alamat, telp, dll.
// Ini akan otomatis ter-update di semua halaman cetak.

define('NAMA_KLINIK', 'KLINIK DOKTER DIKRI');
define('ALAMAT_KLINIK', 'Jl. Raya Purabaya No.Km.39, Kabupaten Sukabumi');
define('TELPON_KLINIK', '0815-7361-7871');
define('EMAIL_KLINIK', 'info@klinikdikri.com');
?>