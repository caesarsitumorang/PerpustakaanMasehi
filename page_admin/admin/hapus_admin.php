<?php
include "config/koneksi.php";

$id_admin = $_GET['id'] ?? null;

if (!$id_admin) {
    echo "<script>alert('ID admin tidak ditemukan.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
    exit;
}

// Ambil data admin berdasarkan ID
$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM admin WHERE id = '$id_admin'"));
if (!$admin) {
    echo "<script>alert('Data admin tidak ditemukan.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
    exit;
}

$username = $admin['username'];

// Hapus dari tabel users
mysqli_query($koneksi, "DELETE FROM users WHERE username = '$username'");

// Hapus dari tabel admin
$hapus = mysqli_query($koneksi, "DELETE FROM admin WHERE id = '$id_admin'");

if ($hapus) {
    echo "<script>alert('Admin berhasil dihapus.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
} else {
    echo "<script>alert('Gagal menghapus admin.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
}
?>
