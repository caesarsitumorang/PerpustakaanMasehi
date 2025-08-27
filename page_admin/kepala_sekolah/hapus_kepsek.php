<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
  echo "<script>alert('ID kepala sekolah tidak ditemukan.'); window.location='index_admin.php?page_admin=kepsek/data_kepsek';</script>";
  exit;
}

$id = $_GET['id'];

// Ambil data kepala sekolah terlebih dahulu
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM kepala_sekolah WHERE id_kepala_sekolah = '$id'"));

if (!$data) {
  echo "<script>alert('Data tidak ditemukan.'); window.location='index_admin.php?page_admin=kepala_sekolah/data_kepsek';</script>";
  exit;
}

// Hapus foto jika ada
if (!empty($data['foto']) && file_exists("upload/" . $data['foto'])) {
  unlink("upload/" . $data['foto']);
}

// Hapus dari tabel kepala_sekolah
$delete = mysqli_query($koneksi, "DELETE FROM kepala_sekolah WHERE id_kepala_sekolah = '$id'");

// Hapus juga dari tabel users jika ada username yang sama
if (!empty($data['username'])) {
  mysqli_query($koneksi, "DELETE FROM users WHERE username = '{$data['username']}' AND role = 'kepala_sekolah'");
}

if ($delete) {
  echo "<script>alert('Data kepala sekolah berhasil dihapus.'); window.location='index_admin.php?page_admin=kepala_sekolah/data_kepsek';</script>";
} else {
  echo "<script>alert('Gagal menghapus data.'); window.location='index_admin.php?page_admin=kepala_sekolah/data_kepsek';</script>";
}
?>
