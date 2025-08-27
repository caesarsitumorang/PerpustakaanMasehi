<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

$id_buku = $_GET['id_buku'] ?? null;

if ($id_buku) {
    // simpan log baca
    mysqli_query($koneksi, "INSERT INTO total_baca_buku (id_buku) VALUES ('$id_buku')");

    header("Location: index.php?page=buku_digital/baca_buku&id_buku=$id_buku");
    exit;
} else {
    echo "ID Buku tidak ditemukan!";
}
?>
