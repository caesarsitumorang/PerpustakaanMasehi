<?php
require_once __DIR__ . '../../../vendor/autoload.php'; 
include "../../config/koneksi.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 40, 
    'margin_bottom' => 25
]);

// Ambil nama penanggung jawab
$nama_admin = '-';
$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_lengkap FROM admin LIMIT 1"));
if ($admin) {
    $nama_admin = $admin['nama_lengkap'];
}

// Header PDF
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

// Footer PDF
$footer = '
<div style="border-top:1px solid #000; font-size:10px; text-align:center; padding-top:4px;">
    Dicetak pada: '.date("d-m-Y H:i").' | Halaman {PAGENO}/{nb}
</div>';
$mpdf->SetHTMLFooter($footer);

// ====== Ambil Data Buku ======
$query = "SELECT * FROM buku ORDER BY kategori ASC, judul ASC";
$sql = mysqli_query($koneksi, $query);

// Hitung statistik
$total_judul = mysqli_num_rows($sql);
$total_eksemplar = 0;
$kategori_list = [];

mysqli_data_seek($sql, 0); // reset pointer
while ($row = mysqli_fetch_assoc($sql)) {
    $total_eksemplar += $row['jumlah'];
    $kategori_list[$row['kategori']] = true;
}
$total_kategori = count($kategori_list);

// ====== HTML Konten ======
$html = '
<h3 style="text-align:center; margin:15px 0; padding:8px; 
           background:linear-gradient(45deg,#0052D4,#65C7F7); 
           color:#fff; border-radius:6px;">
    Laporan Data Buku Fisik
</h3>

<!-- Statistik -->
<table width="100%" style="margin-bottom:12px; font-size:12px;">
    <tr>
        <td style="padding:8px; background:#e8f0fe; border:1px solid #ccc; border-radius:6px;">
            <b>Total Judul:</b> '.$total_judul.'
        </td>
        <td style="padding:8px; background:#e8f0fe; border:1px solid #ccc; border-radius:6px;">
            <b>Total Eksemplar:</b> '.$total_eksemplar.'
        </td>
        <td style="padding:8px; background:#e8f0fe; border:1px solid #ccc; border-radius:6px;">
            <b>Kategori:</b> '.$total_kategori.'
        </td>
    </tr>
</table>

<!-- Tabel Buku -->
<table border="1" cellpadding="6" cellspacing="0" width="100%" 
       style="border-collapse:collapse; font-size:11px; margin-top:5px;">
<thead>
<tr style="background-color:#f2f2f2; text-align:center;">
    <th style="width:30px;">No</th>
    <th style="width:70px;">Kode Buku</th>
    <th>Judul</th>
    <th>Pengarang</th>
    <th>Penerbit</th>
    <th style="width:50px;">Tahun</th>
    <th>Kategori</th>
    <th style="width:50px;">Jumlah</th>
</tr>
</thead>
<tbody>
';

$no = 1;
mysqli_data_seek($sql, 0); // ulangi ambil data
while ($row = mysqli_fetch_assoc($sql)) {
    $html .= '<tr>
        <td style="text-align:center;">' . $no++ . '</td>
        <td>' . $row['kode_buku'] . '</td>
        <td>' . $row['judul'] . '</td>
        <td>' . $row['pengarang'] . '</td>
        <td>' . $row['penerbit'] . '</td>
        <td style="text-align:center;">' . $row['tahun_terbit'] . '</td>
        <td style="text-align:center;">' . $row['kategori'] . '</td>
        <td style="text-align:center;">' . $row['jumlah'] . '</td>
    </tr>';
}

$html .= '
</tbody>
</table>

<!-- Ringkasan & Tanda Tangan -->
<div style="margin-top:25px; font-size:11px;">
    <p><b>Ringkasan:</b> Laporan ini memuat seluruh data buku fisik yang tercatat pada sistem perpustakaan. 
    Diharapkan laporan ini dapat menjadi acuan dalam manajemen koleksi buku sekolah.</p>
</div>

<div style="margin-top:30px; width:100%; font-size:12px;">
    <div style="float:right; text-align:right; margin-right:30px;">
        Sibolangit, '.date("d F Y").'<br>
        Penanggung Jawab,<br><br><br>
        <b><u>'.$nama_admin.'</u></b>
    </div>
    <div style="clear:both;"></div>
</div>
';

// Tulis ke PDF
$mpdf->WriteHTML($html);
$mpdf->Output("daftar_buku.pdf", "I");
?>
