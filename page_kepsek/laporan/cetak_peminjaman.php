<?php
require_once __DIR__ . '../../../vendor/autoload.php'; 
include "../../config/koneksi.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mpdf = new \Mpdf\Mpdf([
    'format' => 'A4',
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
            <img src="../../img/logo_masehi.jpg" 
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

// Hitung statistik
$total_peminjaman = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman"));
$dipinjam = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE status = 'dipinjam'"));
$dikembalikan = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE status = 'dikembalikan'"));
$terlambat = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE status = 'terlambat'"));

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

.stats-section {
    margin: 10px 0;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    text-align: right;
}

.stats-row {
    display: table;
    width: 100%;
}

.stats-col {
    display: table-cell;
    width: 25%;
    text-align: center;
    padding: 10px;
    border-right: 1px solid #dee2e6;
}

.stats-col:last-child {
    border-right: none;
}

.stats-number {
    font-size: 24px;
    font-weight: bold;
    color: #2c5aa0;
    display: block;
}

.stats-label {
    font-size: 11px;
    color: #666;
    text-transform: uppercase;
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

.id-col {
    text-align: center;
    font-family: "Courier New", monospace;
    background: #f1f3f4;
    font-weight: bold;
    font-size: 10px;
}

.nama-col {
    font-weight: bold;
    line-height: 1.3;
}

.judul-col {
    line-height: 1.3;
}

.tanggal-col {
    text-align: center;
    font-size: 10px;
    width: 80px;
}

.status-col {
    text-align: center;
    width: 80px;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-dipinjam {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-dikembalikan {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-terlambat {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.denda-col {
    text-align: right;
    font-weight: bold;
    width: 80px;
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
    <h3>Laporan Data Peminjaman Buku</h3>
    <p style="margin: 0; font-size: 12px; color: #666;">
        Perpustakaan SMP-SMK Swasta Masehi Sibolangit
    </p>
</div>

<div class="info-bar">
    <strong>Tanggal Cetak:</strong> ' . $tanggal_cetak . ' &nbsp;&nbsp;&nbsp;
    <strong>Waktu:</strong> ' . $waktu_cetak . ' &nbsp;&nbsp;&nbsp;
    <strong>Penanggung Jawab:</strong> ' . $nama_admin . '
</div>

<div class="stats-section">
    <div class="stats-row">
        <div class="stats-col">
            <span class="stats-number">' . $total_peminjaman . '</span>
            <span class="stats-label">Total Peminjaman</span>
        </div>
        <div class="stats-col">
            <span class="stats-number">' . $dipinjam . '</span>
            <span class="stats-label">Sedang Dipinjam</span>
        </div>
        <div class="stats-col">
            <span class="stats-number">' . $dikembalikan . '</span>
            <span class="stats-label">Sudah Dikembalikan</span>
        </div>
        <div class="stats-col">
            <span class="stats-number">' . $terlambat . '</span>
            <span class="stats-label">Terlambat</span>
        </div>
    </div>
</div>
';

// Ambil data peminjaman
$query = "
    SELECT p.id_peminjaman, p.tanggal_pinjam, p.tanggal_kembali, p.status, p.denda, 
           b.judul, pg.nama_lengkap 
    FROM peminjaman p
    LEFT JOIN buku b ON p.kode_buku = b.kode_buku
    LEFT JOIN pengunjung pg ON p.id_pengunjung = pg.id
    ORDER BY p.tanggal_pinjam DESC
";
$result = mysqli_query($koneksi, $query);

$html .= '
<table>
<thead>
<tr>
    <th style="width: 4%;">No</th>
    <th style="width: 15%;">ID Peminjaman</th>
    <th style="width: 20%;">Nama Peminjam</th>
    <th style="width: 25%;">Judul Buku</th>
    <th style="width: 12%;">Tanggal Pinjam</th>
    <th style="width: 12%;">Tanggal Kembali</th>
    <th style="width: 8%;">Status</th>
    <th style="width: 10%;">Denda</th>
</tr>
</thead>
<tbody>
';

$no = 1;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tgl_kembali = $row['tanggal_kembali'] ? date('d-m-Y', strtotime($row['tanggal_kembali'])) : '-';
        $denda = $row['denda'] > 0 ? 'Rp ' . number_format($row['denda'], 0, ',', '.') : '-';
        
        // Tentukan class untuk status badge
        $status_class = 'status-dipinjam';
        if ($row['status'] == 'dikembalikan') {
            $status_class = 'status-dikembalikan';
        } elseif ($row['status'] == 'terlambat') {
            $status_class = 'status-terlambat';
        }
        
        $html .= '<tr>
            <td class="no-col">' . $no++ . '</td>
            <td class="id-col">' . htmlspecialchars($row['id_peminjaman']) . '</td>
            <td class="nama-col">' . htmlspecialchars($row['nama_lengkap']) . '</td>
            <td class="judul-col">' . htmlspecialchars($row['judul']) . '</td>
            <td class="tanggal-col">' . date('d-m-Y', strtotime($row['tanggal_pinjam'])) . '</td>
            <td class="tanggal-col">' . $tgl_kembali . '</td>
            <td class="status-col">
                <span class="status-badge ' . $status_class . '">' . ucfirst($row['status']) . '</span>
            </td>
            <td class="denda-col">' . $denda . '</td>
        </tr>';
    }
} else {
    $html .= '<tr>
        <td colspan="8" style="text-align:center; padding:20px; color: #666; font-style: italic;">
            Tidak ada data peminjaman yang tersedia
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
$mpdf->Output("Laporan_Peminjaman_" . date('Ymd') . ".pdf", "I");
?>