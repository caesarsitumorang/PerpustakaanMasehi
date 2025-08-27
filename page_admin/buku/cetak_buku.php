<?php
require_once __DIR__ . '../../../vendor/autoload.php'; 
include "../../config/koneksi.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 45, 
    'margin_bottom' => 25,
    'margin_left' => 20,
    'margin_right' => 20,
    'format' => 'A4'
]);

// Ambil data admin yang login
$id_admin = $_SESSION['id_user'] ?? null;
$nama_admin = '-';
if ($id_admin) {
    $admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id_user = '$id_admin'"));
    if ($admin) {
        $nama_admin = $admin['nama_lengkap'];
    }
}

// Header yang lebih profesional
$header = '
<div style="border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px;">
    <table width="100%" style="border: none;">
        <tr>
            <td width="80px" style="vertical-align: middle;">
                <img src="http://localhost/perpustakaan/perpustakaan/img/logo_masehi.jpg" 
                     style="width: 70px; height: auto;">
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <h1 style="margin: 0; color: #2c3e50; font-size: 18px; font-weight: bold;">
                    SMP-SMK SWASTA MASEHI SIBOLANGIT
                </h1>
                <p style="margin: 3px 0; color: #34495e; font-size: 13px;">
                    JL. Jamin Ginting KM.39,5, Sibolangit
                </p>
                <p style="margin: 0; color: #7f8c8d; font-size: 12px; font-style: italic;">
                    Sistem Informasi Perpustakaan
                </p>
            </td>
            <td width="80px"></td>
        </tr>
    </table>
</div>
';

$mpdf->SetHTMLHeader($header);

// Footer dengan nomor halaman
$footer = '
<div style="border-top: 1px solid #bdc3c7; padding-top: 8px; text-align: center; font-size: 10px; color: #7f8c8d;">
    <p style="margin: 0;">Dicetak pada: ' . date('d F Y, H:i') . ' WIB | Halaman {PAGENO} dari {nbpg}</p>
</div>
';
$mpdf->SetHTMLFooter($footer);

// Hitung total buku dan kategori
$total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total_judul, SUM(jumlah) as total_eksemplar FROM buku");
$total_data = mysqli_fetch_assoc($total_query);

$kategori_query = mysqli_query($koneksi, "SELECT COUNT(DISTINCT kategori) as total_kategori FROM buku");
$kategori_data = mysqli_fetch_assoc($kategori_query);

$html = '
<style>
    .report-title {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        padding: 15px;
        text-align: center;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .info-box {
        background-color: #ecf0f1;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        border-left: 4px solid #3498db;
    }
    
    .stats-container {
        display: flex;
        justify-content: space-around;
        margin-bottom: 20px;
    }
    
    .stat-box {
        background-color: #ffffff;
        border: 2px solid #3498db;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        width: 30%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .data-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
        margin-top: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .data-table th {
        background: linear-gradient(135deg, #34495e, #2c3e50);
        color: white;
        padding: 12px 8px;
        text-align: center;
        font-weight: bold;
        border: 1px solid #2c3e50;
    }
    
    .data-table td {
        padding: 10px 8px;
        border: 1px solid #bdc3c7;
        vertical-align: middle;
    }
    
    .data-table tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .data-table tr:hover {
        background-color: #e3f2fd;
    }
</style>

<div class="report-title">
    <h2 style="margin: 0; font-size: 20px; font-weight: bold;">LAPORAN DATA BUKU FISIK</h2>
    <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Periode: ' . date('F Y') . '</p>
</div>

<div class="info-box">
    <table width="100%" style="border: none; font-size: 12px;">
        <tr>
            <td width="30%"><strong>Tanggal Cetak:</strong></td>
            <td width="70%">' . date('d F Y') . '</td>
        </tr>
        <tr>
            <td><strong>Waktu Cetak:</strong></td>
            <td>' . date('H:i:s') . ' WIB</td>
        </tr>
        <tr>
            <td><strong>Penanggung Jawab:</strong></td>
            <td>' . $nama_admin . '</td>
        </tr>
    </table>
</div>
<h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px; margin-bottom: 15px;">
     Daftar Lengkap Buku Perpustakaan
</h3>
';

$query = "SELECT * FROM buku ORDER BY kategori ASC, judul ASC";
$sql = mysqli_query($koneksi, $query);

// Tabel dengan styling yang lebih baik
$html .= '
<table class="data-table">
<thead>
<tr>
    <th style="width: 5%;">No</th>
    <th style="width: 10%;">Kode Buku</th>
    <th style="width: 25%;">Judul Buku</th>
    <th style="width: 18%;">Pengarang</th>
    <th style="width: 15%;">Penerbit</th>
    <th style="width: 8%;">Tahun</th>
    <th style="width: 12%;">Kategori</th>
    <th style="width: 7%;">Jumlah</th>
</tr>
</thead>
<tbody>
';

$no = 1;
$current_category = '';
while ($row = mysqli_fetch_assoc($sql)) {
    // Tambahkan separator kategori jika berbeda
    if ($current_category != $row['kategori']) {
        $current_category = $row['kategori'];
        $html .= '<tr style="background-color: #d5dbdb;">
            <td colspan="8" style="text-align: left; font-weight: bold; color: #2c3e50; padding: 8px;">
                 Kategori: ' . strtoupper($current_category) . '
            </td>
        </tr>';
    }
    
    $html .= '<tr>
        <td style="text-align: center; font-weight: bold;">' . $no++ . '</td>
        <td style="text-align: center; font-family: monospace; background-color: #f7f7f7;">' . $row['kode_buku'] . '</td>
        <td style="font-weight: 500;">' . $row['judul'] . '</td>
        <td>' . $row['pengarang'] . '</td>
        <td>' . $row['penerbit'] . '</td>
        <td style="text-align: center;">' . $row['tahun_terbit'] . '</td>
        <td style="text-align: center;">
            <span style="background-color: #3498db; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">
                ' . $row['kategori'] . '
            </span>
        </td>
        <td style="text-align: center; font-weight: bold; color: #e74c3c;">' . $row['jumlah'] . '</td>
    </tr>';
}

$html .= '
</tbody>
</table>

<div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 6px; border-left: 4px solid #27ae60;">
    <h4 style="margin: 0 0 10px 0; color: #27ae60;"> Ringkasan Laporan</h4>
    <p style="margin: 5px 0; font-size: 12px;">
        • Laporan ini berisi data lengkap koleksi buku fisik di Perpustakaan SMP-SMK Swasta Masehi Sibolangit<br>
        • Data disusun berdasarkan kategori dan diurutkan secara alfabetis<br>
        • Total koleksi: <strong>' . $total_data['total_judul'] . ' judul</strong> dengan <strong>' . $total_data['total_eksemplar'] . ' eksemplar</strong><br>
        • Data valid per tanggal: <strong>' . date('d F Y') . '</strong>
    </p>
</div>

<div style="margin-top: 40px;">
    <table width="100%" style="border: none;">
        <tr>
            <td width="60%"></td>
            <td width="40%" style="text-align: center;">
                <p style="margin: 0; font-size: 12px;">Sibolangit, ' . date('d F Y') . '</p>
                <p style="margin: 0; font-size: 12px; font-weight: bold;">Penanggung Jawab</p>
                <br><br><br>
                <p style="margin: 0; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px;">
                    ' . $nama_admin . '
                </p>
                <p style="margin: 0; font-size: 11px; color: #7f8c8d;">Pustakawan</p>
            </td>
        </tr>
    </table>
</div>
';

$mpdf->WriteHTML($html);

// Set properties dokumen
$mpdf->SetTitle('Laporan Data Buku Fisik - SMP-SMK Swasta Masehi Sibolangit');
$mpdf->SetAuthor($nama_admin);
$mpdf->SetCreator('Sistem Perpustakaan');
$mpdf->SetSubject('Laporan Data Buku');

$mpdf->Output("Laporan_Buku_Fisik_" . date('Y-m-d') . ".pdf", "I");
?>