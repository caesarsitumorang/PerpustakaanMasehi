<?php
require_once __DIR__ . '../../../vendor/autoload.php'; 
include "../../config/koneksi.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 35, 
    'margin_bottom' => 15
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

$header = '
<div style="text-align:center; border-bottom: 2px solid #000; padding-bottom:8px;">
    <img src="http://localhost/perpustakaan/img/logo_masehi.jpg" 
         style="width:60px; height:auto; float:left; margin-right:10px;">
    <div style="text-align:center;">
        <h2 style="margin:0; font-size:16px;">SMP-SMK SWASTA MASEHI SIBOLANGIT</h2>
        <p style="margin:0; font-size:12px;">JL. Jamin Ginting KM.39,5, Sibolangit</p>
    </div>
</div>
';

$mpdf->SetHTMLHeader($header);

// Judul laporan
$html = '<h3 style="text-align:center; margin-top:20px;">Laporan Data Pengunjung</h3>
<p style="text-align:right; margin:10px 0;">Penanggung Jawab Laporan: ' . $nama_admin . '</p>';

// Ambil data pengunjung
$query = "
  SELECT p.*, m.id_member 
  FROM pengunjung p
  LEFT JOIN member m ON m.id_pengunjung = p.id
  ORDER BY p.id ASC
";
$sql = mysqli_query($koneksi, $query);

$html .= '
<table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:12px; margin-top:20px;">
<thead>
<tr style="background-color:#f2f2f2; text-align:center;">
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

    $html .= '<tr>
        <td style="text-align:center;">' . $no++ . '</td>
        <td>' . $data['nama_lengkap'] . '</td>
        <td>' . $data['email'] . '</td>
        <td>' . $data['no_hp'] . '</td>
        <td>' . $data['alamat'] . '</td>
        <td style="text-align:center;">' . $data['tanggal_daftar'] . '</td>
        <td style="text-align:center;">' . $status . '</td>
    </tr>';
}

$html .= '
</tbody>
</table>
';

// Tulis ke PDF
$mpdf->WriteHTML($html);
$mpdf->Output("laporan_pengunjung.pdf", "I");
