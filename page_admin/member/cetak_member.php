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
<div style="border-bottom: 3px solid #e74c3c; padding-bottom: 15px; margin-bottom: 20px;">
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
                    Sistem Keanggotaan Perpustakaan
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

// Hitung statistik member
$total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total_member FROM member");
$total_data = mysqli_fetch_assoc($total_query);

$status_query = mysqli_query($koneksi, "
    SELECT 
        SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN status = 'Tidak Aktif' THEN 1 ELSE 0 END) as tidak_aktif,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
    FROM member
");
$status_data = mysqli_fetch_assoc($status_query);

$gender_query = mysqli_query($koneksi, "
    SELECT 
        SUM(CASE WHEN pengunjung.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as laki,
        SUM(CASE WHEN pengunjung.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as perempuan
    FROM member 
    JOIN pengunjung ON member.id_pengunjung = pengunjung.id
");
$gender_data = mysqli_fetch_assoc($gender_query);

// Member baru bulan ini
$new_member_query = mysqli_query($koneksi, "
    SELECT COUNT(*) as new_member 
    FROM member 
    WHERE MONTH(tanggal_bergabung) = MONTH(CURRENT_DATE()) 
    AND YEAR(tanggal_bergabung) = YEAR(CURRENT_DATE())
");
$new_member_data = mysqli_fetch_assoc($new_member_query);

$html = '
<style>
    .report-title {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
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
        border-left: 4px solid #e74c3c;
    }
    
    .stat-box {
        background-color: #ffffff;
        border: 2px solid #e74c3c;
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
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        padding: 10px 6px;
        text-align: center;
        font-weight: bold;
        border: 1px solid #c0392b;
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
        background-color: #ffe8e8;
    }
    
    .foto-img {
        width: 35px;
        height: 45px;
        object-fit: cover;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .status-badge {
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .status-aktif {
        background-color: #27ae60;
        color: white;
    }
    
    .status-tidak-aktif {
        background-color: #e74c3c;
        color: white;
    }
    
    .status-pending {
        background-color: #f39c12;
        color: white;
    }
    
    .gender-male {
        color: #3498db;
        font-weight: bold;
    }
    
    .gender-female {
        color: #e91e63;
        font-weight: bold;
    }
    
    .no-photo {
        width: 35px;
        height: 45px;
        background: linear-gradient(135deg, #bdc3c7, #95a5a6);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: white;
        font-size: 8px;
        text-align: center;
        font-weight: bold;
    }
</style>

<div class="report-title">
    <h2 style="margin: 0; font-size: 20px; font-weight: bold;">LAPORAN DATA MEMBER PERPUSTAKAAN</h2>
    <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Database Keanggotaan Perpustakaan</p>
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
            <td><strong>Penanggung Jawab:</strong></td>
            <td>' . $nama_admin . '</td>
        </tr>
    </table>
</div>

<h3 style="color: #2c3e50; border-bottom: 2px solid #e74c3c; padding-bottom: 8px; margin-bottom: 15px;">
    Daftar Lengkap Member Perpustakaan
</h3>
';

$query = "SELECT member.*, pengunjung.* 
          FROM member 
          JOIN pengunjung ON member.id_pengunjung = pengunjung.id 
          ORDER BY member.status DESC, member.tanggal_bergabung DESC";
$sql = mysqli_query($koneksi, $query);

// Tabel dengan styling yang lebih baik
$html .= '
<table class="data-table">
<thead>
<tr>
    <th style="width: 4%;">No</th>
    <th style="width: 8%;">Foto</th>
    <th style="width: 12%;">Username</th>
    <th style="width: 20%;">Nama Lengkap</th>
    <th style="width: 8%;">L/P</th>
    <th style="width: 13%;">No HP</th>
    <th style="width: 15%;">Email</th>
    <th style="width: 10%;">Tgl Gabung</th>
    <th style="width: 10%;">Status</th>
</tr>
</thead>
<tbody>
';

$no = 1;
$current_status = '';
while ($row = mysqli_fetch_assoc($sql)) {
    // Separator berdasarkan status
    if ($current_status != $row['status']) {
        $current_status = $row['status'];
        $statusColor = '#27ae60'; // Default hijau
        if ($current_status == 'Tidak Aktif') $statusColor = '#e74c3c';
        if ($current_status == 'Pending') $statusColor = '#f39c12';
        
        $html .= '<tr style="background: linear-gradient(135deg, #ecf0f1, #d5dbdb);">
            <td colspan="9" style="text-align: left; font-weight: bold; color: ' . $statusColor . '; padding: 8px;">
                [STATUS: ' . strtoupper($current_status) . ']
            </td>
        </tr>';
    }
    
    // Path foto dengan fallback
    $foto = '';
    if (!empty($row['foto']) && file_exists(__DIR__ . "/../../upload/" . $row['foto'])) {
        $fotoFullPath = __DIR__ . "/../../upload/" . $row['foto'];
        $imageData = file_get_contents($fotoFullPath);
        $imageType = pathinfo($fotoFullPath, PATHINFO_EXTENSION);
        $base64 = base64_encode($imageData);
        $foto = "data:image/{$imageType};base64,{$base64}";
    }
    
    // Badge status
    $statusClass = 'status-aktif';
    if ($row['status'] == 'Tidak Aktif') $statusClass = 'status-tidak-aktif';
    if ($row['status'] == 'Pending') $statusClass = 'status-pending';
    
    // Gender styling
    $genderClass = ($row['jenis_kelamin'] == 'Laki-laki') ? 'gender-male' : 'gender-female';
    $genderShort = ($row['jenis_kelamin'] == 'Laki-laki') ? 'L' : 'P';
    
    // Format tanggal
    $tanggal_bergabung = ($row['tanggal_bergabung']) ? date('d/m/Y', strtotime($row['tanggal_bergabung'])) : '-';
    
    $html .= '<tr>
        <td style="text-align: center; font-weight: bold;">' . $no++ . '</td>
        <td style="text-align: center;">';
    
    if ($foto) {
        $html .= '<img src="' . $foto . '" class="foto-img" alt="Foto Member">';
    } else {
        $html .= '<div class="no-photo">NO<br>PHOTO</div>';
    }
    
    $html .= '</td>
        <td style="font-family: monospace; background-color: #f7f7f7;">
            ' . ($row['username'] ?: '-') . '
        </td>
        <td style="font-weight: 500;">
            ' . ($row['nama_lengkap'] ?: '-') . '
        </td>
        <td style="text-align: center;">
            <span class="' . $genderClass . '">' . $genderShort . '</span>
        </td>
        <td style="font-family: monospace;">
            ' . ($row['no_hp'] ?: '-') . '
        </td>
        <td style="font-size: 9px;">
            ' . ($row['email'] ?: '-') . '
        </td>
        <td style="text-align: center; font-size: 9px;">
            ' . $tanggal_bergabung . '
        </td>
        <td style="text-align: center;">
            <span class="status-badge ' . $statusClass . '">' . ($row['status'] ?: '-') . '</span>
        </td>
    </tr>';
}

$html .= '
</tbody>
</table>

<div style="margin-top: 30px; padding: 15px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; border-left: 4px solid #e74c3c;">
    <h4 style="margin: 0 0 10px 0; color: #e74c3c;">Ringkasan Data Member</h4>
    <table width="100%" style="border: none; font-size: 11px;">
        <tr>
            <td width="50%">
                <strong>Total Keseluruhan:</strong> ' . $total_data['total_member'] . ' member<br>
                <strong>Member Aktif:</strong> ' . ($status_data['aktif'] ?: 0) . ' orang<br>
                <strong>Member Tidak Aktif:</strong> ' . ($status_data['tidak_aktif'] ?: 0) . ' orang
            </td>
            <td width="50%">
                <strong>Anggota Laki-laki:</strong> ' . ($gender_data['laki'] ?: 0) . ' orang<br>
                <strong>Anggota Perempuan:</strong> ' . ($gender_data['perempuan'] ?: 0) . ' orang<br>
                <strong>Member Baru Bulan Ini:</strong> ' . ($new_member_data['new_member'] ?: 0) . ' orang
            </td>
        </tr>
    </table>
    <p style="margin: 10px 0 0 0; font-size: 10px; color: #7f8c8d;">
        Data diurutkan berdasarkan status dan tanggal bergabung terbaru | Valid per: ' . date('d F Y, H:i') . ' WIB
    </p>
</div>

<div style="margin-top: 40px;">
    <table width="100%" style="border: none;">
        <tr>
            <td width="60%">
                <div style="padding: 10px; background-color: #ffe8e8; border-radius: 6px; border-left: 4px solid #e74c3c;">
                </div>
            </td>
            <td width="40%" style="text-align: center;">
                <p style="margin: 0; font-size: 12px;">Sibolangit, ' . date('d F Y') . '</p>
                <p style="margin: 0; font-size: 12px; font-weight: bold;">Koordinator Keanggotaan</p>
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
$mpdf->SetTitle('Laporan Data Member - SMP-SMK Swasta Masehi Sibolangit');
$mpdf->SetAuthor($nama_admin);
$mpdf->SetCreator('Sistem Keanggotaan Perpustakaan');
$mpdf->SetSubject('Laporan Data Member');

$mpdf->Output("Laporan_Data_Member_" . date('Y-m-d') . ".pdf", "I");
?>