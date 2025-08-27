<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

// Pastikan user sudah login dan punya hak admin
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Akses ditolak!'); window.location='login.php';</script>";
    exit;
}

// Validasi parameter
if (!isset($_GET['username']) || !isset($_GET['role'])) {
    echo "<script>alert('Parameter tidak lengkap!'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    exit;
}

$username = mysqli_real_escape_string($koneksi, $_GET['username']);
$role = mysqli_real_escape_string($koneksi, $_GET['role']);

try {
    // Cek apakah user ada
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' AND role = '$role'");
    if (mysqli_num_rows($cek) === 0) {
        throw new Exception("Data pengguna tidak ditemukan.");
    }

    // Hapus data detail sesuai role
    if ($role === 'admin') {
        mysqli_query($koneksi, "DELETE FROM admin WHERE username = '$username'");
    } elseif ($role === 'kepala_sekolah') {
        mysqli_query($koneksi, "DELETE FROM kepala_sekolah WHERE username = '$username'");
    }

    // Hapus data di tabel users
    mysqli_query($koneksi, "DELETE FROM users WHERE username = '$username'");

    $_SESSION['message'] = "Adminstrator berhasil dihapus.";
    $_SESSION['message_type'] = "success";
} catch (Exception $e) {
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "danger";
}
echo "<script>window.location.href='index_admin.php?page_admin=administrator/data_administrator';</script>";
exit;

?>
