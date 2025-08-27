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
<div style="border-bottom: 3px solid #667eea; padding-bottom: 15px; margin-bottom: 20px;">
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
                    Sistem Perpustakaan Digital
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

// Hitung statistik buku digital
$total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total_buku FROM buku_digital");
$total_data = mysqli_fetch_assoc($total_query);

$kategori_query = mysqli_query($koneksi, "SELECT COUNT(DISTINCT kategori) as total_kategori FROM buku_digital");
$kategori_data = mysqli_fetch_assoc($kategori_query);

$akses_query = mysqli_query($koneksi, "
    SELECT 
        SUM(CASE WHEN akses = 'Gratis' THEN 1 ELSE 0 END) as gratis,
        SUM(CASE WHEN akses = 'Premium' THEN 1 ELSE 0 END) as premium
    FROM buku_digital
");
$akses_data = mysqli_fetch_assoc($akses_query);

$html = '
<style>
    .report-title {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 15px;
        text-align: center;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .info-box {
        background-color: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        border-left: 4px solid #667eea;
    }
    
    .stat-box {
        background-color: #ffffff;
        border: 2px solid #667eea;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        width: 22%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: inline-block;
        margin: 0 1%;
    }
    
    .data-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 10px;
        margin-top: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .data-table th {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 10px 6px;
        text-align: center;
        font-weight: bold;
        border: 1px solid #5a6fd8;
    }
    
    .data-table td {
        padding: 8px 6px;
        border: 1px solid #bdc3c7;
        vertical-align: middle;
    }
    
    .data-table tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .data-table tr:hover {
        background-color: #e8f0ff;
    }
    
    .cover-img {
        width: 35px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .access-badge {
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .access-gratis {
        background-color: #27ae60;
        color: white;
    }
    
    .access-premium {
        background-color: #f39c12;
        color: white;
    }
    
    .category-tag {
        background-color: #667eea;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
    }
</style>

<div class="report-title">
    <h2 style="margin: 0; font-size: 20px; font-weight: bold;">LAPORAN DATA BUKU DIGITAL</h2>
    <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Koleksi E-Book Perpustakaan Digital</p>
    <p style="margin: 3px 0 0 0; font-size: 12px; opacity: 0.8;">Periode: ' . date('F Y') . '</p>
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
            <td><strong> Penanggung Jawab:</strong></td>
            <td>' . $nama_admin . '</td>
        </tr>
    </table>
</div>

<h3 style="color: #2c3e50; border-bottom: 2px solid #667eea; padding-bottom: 8px; margin-bottom: 15px;">
     Daftar Lengkap Buku Digital
</h3>
';

$query = "SELECT * FROM buku_digital ORDER BY kategori ASC, judul ASC";
$sql = mysqli_query($koneksi, $query);

// Tabel dengan styling yang lebih baik
$html .= '
<table class="data-table">
<thead>
<tr>
    <th style="width: 4%;">No</th>
    <th style="width: 8%;">Cover</th>
    <th style="width: 12%;">Kode Buku</th>
    <th style="width: 28%;">Judul Buku</th>
    <th style="width: 18%;">Pengarang</th>
    <th style="width: 15%;">Penerbit</th>
    <th style="width: 6%;">Tahun</th>
    <th style="width: 12%;">Kategori</th>
    <th style="width: 8%;">Akses</th>
</tr>
</thead>
<tbody>
';

$no = 1;
$current_category = '';
while ($buku = mysqli_fetch_assoc($sql)) {
    // Tambahkan separator kategori jika berbeda
    if ($current_category != $buku['kategori']) {
        $current_category = $buku['kategori'];
        $html .= '<tr style="background: linear-gradient(135deg, #ecf0f1, #d5dbdb);">
            <td colspan="9" style="text-align: left; font-weight: bold; color: #2c3e50; padding: 8px;">
                Kategori: ' . strtoupper($current_category) . '
            </td>
        </tr>';
    }
    
    // Path cover dengan fallback
    $coverPath = (!empty($buku['cover']) && file_exists("upload/" . $buku['cover'])) 
        ? "upload/" . $buku['cover'] 
        : "img/cover/default.jpg";
    
    // Badge akses
    $accessClass = ($buku['akses'] == 'Gratis') ? 'access-gratis' : 'access-premium';
    $accessIcon = ($buku['akses'] == 'Gratis') ? '🆓' : '💎';
    
    $html .= '<tr>
        <td style="text-align: center; font-weight: bold;">' . $no++ . '</td>
        <td style="text-align: center;">
            <img src="' . $coverPath . '" class="cover-img" alt="Cover">
        </td>
        <td style="text-align: center; font-family: monospace; background-color: #f7f7f7;">
            ' . $buku['kode_buku'] . '
        </td>
        <td style="font-weight: 500; line-height: 1.3;">
            ' . $buku['judul'] . '
        </td>
        <td style="line-height: 1.2;">
            ' . $buku['pengarang'] . '
        </td>
        <td style="line-height: 1.2;">
            ' . $buku['penerbit'] . '
        </td>
        <td style="text-align: center; font-weight: bold;">
            ' . $buku['tahun_terbit'] . '
        </td>
        <td style="text-align: center;">
            <span class="category-tag">' . $buku['kategori'] . '</span>
        </td>
        <td style="text-align: center;">
            <span class="access-badge ' . $accessClass . '">' . $accessIcon . ' ' . $buku['akses'] . '</span>
        </td>
    </tr>';
}

$html .= '
</tbody>
</table>

<div style="margin-top: 30px; padding: 15px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; border-left: 4px solid #667eea;">
    <h4 style="margin: 0 0 10px 0; color: #667eea;"> Ringkasan Laporan Digital</h4>
    <p style="margin: 5px 0; font-size: 12px;">
        • Koleksi buku digital perpustakaan SMP-SMK Swasta Masehi Sibolangit<br>
        • Total koleksi: <strong>' . $total_data['total_buku'] . ' e-book</strong> dalam <strong>' . $kategori_data['total_kategori'] . ' kategori</strong><br>
        • Data diurutkan berdasarkan kategori dan judul secara alfabetis<br>
        • Data valid per tanggal: <strong>' . date('d F Y, H:i') . ' WIB</strong>
    </p>
</div>

<div style="margin-top: 40px;">
    <table width="100%" style="border: none;">
        <tr>
            <td width="60%">
                <div style="padding: 10px; background-color: #e8f0ff; border-radius: 6px; border-left: 4px solid #667eea;">
                </div>
            </td>
            <td width="40%" style="text-align: center;">
                <p style="margin: 0; font-size: 12px;">Sibolangit, ' . date('d F Y') . '</p>
                <p style="margin: 0; font-size: 12px; font-weight: bold;">Penanggung Jawab Digital</p>
                <br><br><br>
                <p style="margin: 0; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px;">
                    ' . $nama_admin . '
                </p>
                <p style="margin: 0; font-size: 11px; color: #7f8c8d;">Pustakawan Digital</p>
            </td>
        </tr>
    </table>
</div>
';

$mpdf->WriteHTML($html);

// Set properties dokumen
$mpdf->SetTitle('Laporan Buku Digital - SMP-SMK Swasta Masehi Sibolangit');
$mpdf->SetAuthor($nama_admin);
$mpdf->SetCreator('Sistem Perpustakaan Digital');
$mpdf->SetSubject('Laporan Buku Digital');

$mpdf->Output("Laporan_Buku_Digital_" . date('Y-m-d') . ".pdf", "I");
?>

