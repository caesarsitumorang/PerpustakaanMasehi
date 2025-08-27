<?php
require_once __DIR__ . '../../../vendor/autoload.php'; 
include "../../config/koneksi.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 30, 
    'margin_bottom' => 20,
    'margin_left' => 15,
    'margin_right' => 15
]);

$nama_admin = '-';
$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_lengkap FROM admin LIMIT 1"));
if ($admin) {
    $nama_admin = $admin['nama_lengkap'];
}

// Header yang bersih dan profesional
$header = '
<table width="100%" style="border-bottom: 3px solid #2c5aa0; padding-bottom: 10px;">
    <tr>
        <td width="80px">
            <img src="http://localhost/perpustakaan/perpustakaan/img/logo_masehi.jpg" 
                 style="width:65px; height:65px; object-fit: cover;">
        </td>
        <td style="vertical-align: middle; padding-left: 15px;">
            <h2 style="margin:0; font-size:18px; color: #2c5aa0; font-weight: bold;">
                SMP-SMK SWASTA MASEHI SIBOLANGIT
            </h2>
            <p style="margin:3px 0; font-size:12px; color: #666;">
                JL. Jamin Ginting KM.39,5, Sibolangit
            </p>
            <p style="margin:3px 0; font-size:12px; color: #666;">
                Sistem Perpustakaan Digital
            </p>
        </td>
    </tr>
</table>
';

$mpdf->SetHTMLHeader($header);

$tanggal_cetak = date('d F Y');
$waktu_cetak = date('H:i');
$total_buku = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM buku_digital"));

$html = '
<style>
body {
    font-family: Arial, sans-serif;
    color: #333;
}

.title-section {
    text-align: center;
    margin: 20px 0 30px 0;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

.title-section h3 {
    margin: 0 0 5px 0;
    font-size: 20px;
    color: #2c5aa0;
    text-transform: uppercase;
    font-weight: bold;
}

.info-bar {
    background: #e9ecef;
    padding: 12px 20px;
    margin: 20px 0;
    border-left: 4px solid #2c5aa0;
    font-size: 12px;
}

.info-row {
    display: table;
    width: 100%;
}

.info-col {
    display: table-cell;
    width: 33.33%;
    vertical-align: middle;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 11px;
}

table th {
    background: #2c5aa0;
    color: white;
    padding: 12px 8px;
    text-align: center;
    font-weight: bold;
    font-size: 10px;
    text-transform: uppercase;
}

table td {
    padding: 10px 8px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

table tr:nth-child(even) {
    background-color: #f8f9fa;
}

.no-col {
    text-align: center;
    font-weight: bold;
    color: #2c5aa0;
    width: 40px;
}

.cover-col {
    text-align: center;
    width: 60px;
}

.cover-img {
    width: 35px;
    height: 50px;
    object-fit: cover;
    border: 1px solid #ddd;
}

.kode-col {
    text-align: center;
    width: 100px;
}

.kode-text {
    font-family: "Courier New", monospace;
    background: #f1f3f4;
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
}

.judul-col {
    font-weight: bold;
    line-height: 1.3;
}

.tahun-col {
    text-align: center;
    width: 60px;
    font-weight: bold;
}

.kategori-col {
    text-align: center;
    width: 100px;
}

.akses-col {
    text-align: center;
    width: 80px;
}

.akses-badge {
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
}

.akses-publik {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.akses-terbatas {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.signature-section {
    margin-top: 40px;
    text-align: right;
}

.signature-box {
    display: inline-block;
    text-align: center;
    border: 1px solid #dee2e6;
    padding: 20px;
    background: #f8f9fa;
}
</style>

<div class="title-section">
    <h3>Laporan Data Buku Digital</h3>
    <p style="margin: 0; font-size: 12px; color: #666;">
        Perpustakaan SMP-SMK Swasta Masehi Sibolangit
    </p>
</div>

<div class="info-bar">
    <div class="info-row">
        <div class="info-col">
            <strong>Total Buku:</strong> ' . $total_buku . ' buku
        </div>
        <div class="info-col">
            <strong>Tanggal Cetak:</strong> ' . $tanggal_cetak . '
        </div>
        <div class="info-col">
            <strong>Penanggung Jawab:</strong> ' . $nama_admin . '
        </div>
    </div>
</div>
';

// Ambil data buku
$query = "SELECT * FROM buku_digital ORDER BY judul ASC";
$sql = mysqli_query($koneksi, $query);

$html .= '
<table>
<thead>
<tr>
    <th style="width: 5%;">No</th>
    <th style="width: 8%;">Cover</th>
    <th style="width: 12%;">Kode Buku</th>
    <th style="width: 30%;">Judul Buku</th>
    <th style="width: 18%;">Pengarang</th>
    <th style="width: 15%;">Penerbit</th>
    <th style="width: 7%;">Tahun</th>
    <th style="width: 12%;">Kategori</th>
    <th style="width: 8%;">Akses</th>
</tr>
</thead>
<tbody>
';

$no = 1;
while ($buku = mysqli_fetch_assoc($sql)) {
    $coverPath = (!empty($buku['cover']) && file_exists("upload/" . $buku['cover'])) 
        ? "upload/" . $buku['cover'] 
        : "img/cover/default.jpg";

    $akses_class = ($buku['akses'] == 'Publik') ? 'akses-publik' : 'akses-terbatas';
    
    $html .= '<tr>
        <td class="no-col">' . $no++ . '</td>
        <td class="cover-col">
            <img src="' . $coverPath . '" class="cover-img">
        </td>
        <td class="kode-col">
            <span class="kode-text">' . htmlspecialchars($buku['kode_buku']) . '</span>
        </td>
        <td class="judul-col">' . htmlspecialchars($buku['judul']) . '</td>
        <td>' . htmlspecialchars($buku['pengarang']) . '</td>
        <td>' . htmlspecialchars($buku['penerbit']) . '</td>
        <td class="tahun-col">' . $buku['tahun_terbit'] . '</td>
        <td class="kategori-col">' . htmlspecialchars($buku['kategori']) . '</td>
        <td class="akses-col">
            <span class="akses-badge ' . $akses_class . '">' . $buku['akses'] . '</span>
        </td>
    </tr>';
}

$html .= '</tbody>
</table>

<div class="signature-section">
    <div class="signature-box">
        <p style="margin: 0 0 10px 0; font-size: 12px;">Sibolangit, ' . $tanggal_cetak . '</p>
        <p style="margin: 0 0 50px 0; font-size: 12px; font-weight: bold;">Penanggung Jawab</p>
        <p style="margin: 0; font-size: 12px; font-weight: bold; border-top: 1px solid #333; padding-top: 5px;">
            ' . $nama_admin . '
        </p>
    </div>
</div>
';

// Output PDF
$mpdf->WriteHTML($html);
$mpdf->Output("Laporan_Buku_Digital_" . date('Ymd') . ".pdf", "I");
?>