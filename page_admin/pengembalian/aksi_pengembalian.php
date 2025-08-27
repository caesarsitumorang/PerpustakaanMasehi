<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$id = intval($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';
$kondisi = $_POST['kondisi_buku_dikembalikan'] ?? '';

try {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) throw new Exception("Token tidak valid");
    if ($id <= 0 || $aksi !== 'selesaikan') throw new Exception("Aksi tidak valid");
    if (!in_array($kondisi, ['baik','rusak'])) throw new Exception("Kondisi buku tidak valid");

    $stmt = mysqli_prepare($koneksi, "SELECT tanggal_pinjam, status, kode_buku FROM peminjaman WHERE id_peminjaman=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) === 0) throw new Exception("Data tidak ditemukan");

    $data = mysqli_fetch_assoc($result);
    if ($data['status'] !== 'Dipinjam') throw new Exception("Peminjaman belum disetujui atau sudah selesai");

    $tgl_pinjam = new DateTime($data['tanggal_pinjam']);
    $tgl_kembali = new DateTime();
    $selisih = $tgl_kembali->diff($tgl_pinjam)->days;
    $denda = $selisih > 30 ? ($selisih-30)*1000 : 0;

    if (!empty($data['kode_buku'])){
        $upd = mysqli_prepare($koneksi, "UPDATE buku SET jumlah=jumlah+1 WHERE kode_buku=?");
        mysqli_stmt_bind_param($upd,"s",$data['kode_buku']);
        mysqli_stmt_execute($upd);
    }

    $upd2 = mysqli_prepare($koneksi, "UPDATE peminjaman SET status='Dikembalikan', tanggal_kembali=?, denda=?, kondisi_buku_dikembalikan=? WHERE id_peminjaman=?");
    $tgl = $tgl_kembali->format('Y-m-d H:i:s');
    mysqli_stmt_bind_param($upd2,"sisi",$tgl,$denda,$kondisi,$id);
    mysqli_stmt_execute($upd2);

    $_SESSION['message']="Peminjaman berhasil diselesaikan, kondisi buku: $kondisi";
    $_SESSION['message_type']="success";
} catch (Exception $e) {
    $_SESSION['message']=$e->getMessage();
    $_SESSION['message_type']="danger";
}

header("Location: pengembalian.php");
exit;
