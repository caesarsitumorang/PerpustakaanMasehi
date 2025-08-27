<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
    echo "<script>alert('ID pengunjung tidak ditemukan.'); window.location='index_admin.php?page_admin=pengunjung/data_pengunjung';</script>";
    exit;
}

$id = $_GET['id'];

// Ambil data pengunjung
$query = mysqli_query($koneksi, "SELECT * FROM pengunjung WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data pengunjung tidak ditemukan.'); window.location='index_admin.php?page_admin=pengunjung/data_pengunjung';</script>";
    exit;
}

// Hapus foto jika ada
if (!empty($data['foto']) && file_exists("upload/" . $data['foto'])) {
    unlink("upload/" . $data['foto']);
}

// Hapus data dari tabel pengunjung
$hapus = mysqli_query($koneksi, "DELETE FROM pengunjung WHERE id = '$id'");

// Hapus juga dari tabel user jika ada username yang sama
mysqli_query($koneksi, "DELETE FROM users WHERE username = '{$data['username']}'");

if ($hapus) {
    echo "<script>alert('Pengunjung berhasil dihapus.'); window.location='index_admin.php?page_admin=pengunjung/data_pengunjung';</script>";
} else {
    echo "<script>alert('Gagal menghapus pengunjung.'); window.location='index_admin.php?page_admin=pengunjung/data_pengunjung';</script>";
}
?>
