<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID buku tidak ditemukan.'); window.location='index_admin.php?page_admin=buku/data_buku_digital';</script>";
    exit;
}

$id = intval($_GET['id']);

// Ambil data buku untuk hapus file
$query = mysqli_query($koneksi, "SELECT cover, file_ebook FROM buku_digital WHERE id_buku = $id");
$data = mysqli_fetch_assoc($query);

if ($data) {
    // Hapus file cover jika ada
    if (!empty($data['cover']) && file_exists('../../upload/' . $data['cover'])) {
        unlink('../../upload/' . $data['cover']);
    }

    // Hapus file ebook jika ada
    if (!empty($data['file_ebook']) && file_exists('../../upload/' . $data['file_ebook'])) {
        unlink('../../upload/' . $data['file_ebook']);
    }

    // Hapus dari database
    $hapus = mysqli_query($koneksi, "DELETE FROM buku_digital WHERE id_buku = $id");

    if ($hapus) {
        echo "<script>alert('Buku berhasil dihapus.'); window.location='index_admin.php?page_admin=buku_digital/buku_digital_admin';</script>";
    } else {
        echo "<script>alert('Gagal menghapus buku.'); window.location='index_admin.php?page_admin=buku_digital/buku_digital_admin';</script>";
    }
} else {
    echo "<script>alert('Data buku tidak ditemukan.'); window.location='index_admin.php?page_admin=buku_digital/buku_digital_admin';</script>";
}
?>
