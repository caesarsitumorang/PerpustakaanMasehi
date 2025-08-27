<?php
require_once __DIR__ . '../../../vendor/autoload.php'; 
include "../../config/koneksi.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 45, 
    'margin_bottom' => 25
]);

// Ambil nama admin
$nama_admin = '-';
$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_lengkap FROM admin LIMIT 1"));
if ($admin) {
    $nama_admin = $admin['nama_lengkap'];
}

// Header ala kop surat fisik
$header = '
<div style="display:flex; align-items:center; justify-content:center; border-bottom:2px solid #000; padding:6px 0;">
    <!-- Logo Sekolah -->
    <img src="http://localhost/perpustakaan/perpustakaan/img/logo_masehi.jpg" 
         style="width:60px; height:auto; margin-right:12px;">
    
    <!-- Identitas Sekolah -->
    <div style="text-align:center; line-height:1.3;">
        <h2 style="margin:0; font-size:16px;">SMP-SMK SWASTA MASEHI SIBOLANGIT</h2>
        <p style="margin:0; font-size:11px;">JL. Jamin Ginting KM.39,5, Sibolangit</p>
    </div>
</div>
';
$mpdf->SetHTMLHeader($header);

// Judul laporan
$html = '
<h2 style="text-align:center; margin-top:20px; font-size:16px; 
           font-family:Trebuchet MS, Arial; letter-spacing:1px; color:#333;">
    LAPORAN DATA PENGUNJUNG PERPUSTAKAAN
</h2>
<hr style="border:0; border-top:1px solid #aaa; width:60%; margin:auto; margin-bottom:20px;">
';

// Ambil data pengunjung
$query = "
  SELECT p.*, m.id_member 
  FROM pengunjung p
  LEFT JOIN member m ON m.id_pengunjung = p.id
  ORDER BY p.id ASC
";
$sql = mysqli_query($koneksi, $query);

// Tabel data
$html .= '
<table border="1" cellpadding="7" cellspacing="0" width="100%" 
       style="border-collapse:collapse; font-size:12px; font-family:Calibri;">
<thead>
<tr style="background-color:#4CAF50; color:#fff; text-align:center;">
    <th>No</th>
    <th>Nama Lengkap</th>
    <th>Email</th>
    <th>No HP</th>
    <th>Alamat</th>
    <th>Tanggal Daftar</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
';

$no = 1;
while ($data = mysqli_fetch_assoc($sql)) {
    $status = !empty($data['id_member']) ? "Member" : "Non Member";
    $bg = ($no % 2 == 0) ? "#f9f9f9" : "#ffffff"; // striping

    $html .= '<tr style="background-color:'.$bg.';">
        <td style="text-align:center;">' . $no++ . '</td>
        <td>' . $data['nama_lengkap'] . '</td>
        <td>' . $data['email'] . '</td>
        <td style="text-align:center;">' . $data['no_hp'] . '</td>
        <td>' . $data['alamat'] . '</td>
        <td style="text-align:center;">' . $data['tanggal_daftar'] . '</td>
        <td style="text-align:center;">' . $status . '</td>
    </tr>';
}

$html .= '
</tbody>
</table>
';

// Tanda tangan elegan
$html .= '
<div style="margin-top:10px; width:100%; font-size:13px; font-family:Georgia, serif;">
    <div style="text-align:right; margin-right:80px; line-height:1.8;">
        Sibolangit, '.date("d F Y").'
        <i>Mengetahui,</i><br>
        <b>Penanggung Jawab Perpustakaan</b><br><br>
        <b><u>'.$nama_admin.'</u></b>
    </div>
</div>
';

// Output PDF
$mpdf->WriteHTML($html);
$mpdf->Output("laporan_pengunjung.pdf", "I");
?>
