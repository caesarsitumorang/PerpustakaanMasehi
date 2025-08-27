<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
    echo "<script>alert('ID buku tidak ditemukan!'); window.location='index_admin.php?page_admin=buku/buku_admin';</script>";
    exit;
}

$id = $_GET['id'];

// Cek apakah buku ini ada dalam peminjaman
$cek_peminjaman = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku = '$id'");
$terkait_peminjaman = mysqli_num_rows($cek_peminjaman) > 0;

if ($terkait_peminjaman && !isset($_GET['force'])) {
    echo "<script>
        if (confirm('Buku ini masih terhubung dengan data peminjaman. Yakin ingin menghapus?')) {
            window.location = 'index_admin.php?page_admin=buku/hapus_buku&id=$id&force=1';
        } else {
            window.location = 'index_admin.php?page_admin=buku/buku_admin';
        }
    </script>";
    exit;
}

// Lanjutkan proses hapus
$result = mysqli_query($koneksi, "SELECT cover FROM buku WHERE id_buku = '$id'");
$data = mysqli_fetch_assoc($result);

if ($data) {
    // Hapus file cover jika ada
    if (!empty($data['cover']) && file_exists("upload/" . $data['cover'])) {
        unlink("upload/" . $data['cover']);
    }

    // Hapus data dari database
    $delete = mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku = '$id'");

    if ($delete) {
        echo "<script>alert('Buku berhasil dihapus.'); window.location='index_admin.php?page_admin=buku/buku_admin';</script>";
    } else {
        echo "<script>alert('Gagal menghapus buku.'); window.location='index_admin.php?page_admin=buku/buku_admin';</script>";
    }
} else {
    echo "<script>alert('Data buku tidak ditemukan.'); window.location='index_admin.php?page_admin=buku/buku_admin';</script>";
}
?>
