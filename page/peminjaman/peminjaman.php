<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

// Ambil username dari users
$id_user = $_SESSION['id_user'];
$getUsername = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user'");
$dataUser = mysqli_fetch_assoc($getUsername);
$username = $dataUser['username'] ?? null;

// Ambil id_pengunjung berdasarkan username
$getPengunjung = mysqli_query($koneksi, "SELECT id FROM pengunjung WHERE username = '$username'");
$dataPengunjung = mysqli_fetch_assoc($getPengunjung);
$id_pengunjung = $dataPengunjung['id'] ?? null;

if (!$id_pengunjung) {
    echo "<script>alert('Data pengunjung tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Pagination dan search
$hal = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$limit = 5;
$halaman_awal = ($hal - 1) * $limit;

$where = "WHERE p.id_pengunjung = '$id_pengunjung' AND p.status IN ('Diajukan','Dipinjam')";
if($search != '') {
    $where .= " AND b.judul LIKE '%$search%'";
}

// Total data
$total_data = mysqli_num_rows(mysqli_query($koneksi, "
    SELECT p.id_peminjaman FROM peminjaman p
    JOIN buku b ON p.kode_buku = b.kode_buku
    $where
"));
$total_halaman = ceil($total_data / $limit);

// Data per halaman
$query = "
SELECT p.*, b.judul FROM peminjaman p
JOIN buku b ON p.kode_buku = b.kode_buku
$where
ORDER BY p.tanggal_pinjam DESC
LIMIT $halaman_awal, $limit
";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Peminjaman</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
html, body {  
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Poppins', sans-serif;
    margin:0; padding:0;
}
.container { padding: 25px; }
.top-action-bar { display:flex; justify-content:space-between; flex-wrap:wrap; margin-bottom:1.5rem; }
.top-action-bar h3 { margin:0; font-size:1.6rem; font-weight:600; color:#fff; }
.top-action-bar p { margin-top:4px; color:rgba(255,255,255,0.85); font-size:0.9rem; }

#search-box { padding:6px 12px; border-radius:6px; border:1px solid #ccc; margin-bottom:15px; width:250px; }

.card-custom { background:#fff; border-radius:12px; padding:25px; box-shadow:0 4px 20px rgba(0,0,0,0.08); }

.table-wrapper { width:100%; overflow-x:auto; }
.table-member { width:100%; border-collapse: collapse; min-width:900px; }
.table-member thead th { background: linear-gradient(to right, #000b3a, #00365c); color:white; font-weight:500; padding:14px 16px; text-align:left; }
.table-member th, .table-member td { padding:12px 16px; text-align:left; vertical-align:middle; font-size:0.92rem; color:#444; border-bottom:1px solid #f0f0f0; }
.table-member tbody tr:nth-child(even) { background:#fafafa; }
.table-member tbody tr:hover { background:#f2f6ff; transition:0.2s; }

.badge-warning { background-color:#ffe08a; color:#856404; font-weight:500; padding:6px 10px; border-radius:8px; }
.badge-success { background-color:#a8dfc1; color:#155724; font-weight:500; padding:6px 10px; border-radius:8px; }
.badge-danger { background-color:#f8cfcf; color:#721c24; font-weight:500; padding:6px 10px; border-radius:8px; }
.text-muted { font-style:italic; color:#777; }

.pagination { margin-top:15px; text-align:center; }
.pagination a { padding:6px 12px; margin:0 4px; background:#eee; color:#333; border-radius:6px; text-decoration:none; font-weight:600; }
.pagination a.active { background:#2575fc; color:white; }
.pagination a:hover { background:#38f9d7; color:#000; }

@media (max-width:768px){
  .top-action-bar { flex-direction:column; align-items:flex-start; gap:1rem; }
  .table-member { min-width:100%; }
  .table-member thead { display:none; }
  .table-member tbody td { display:block; text-align:right; padding-left:50%; position:relative; font-size:0.9rem; }
  .table-member tbody td::before { content: attr(data-label); position:absolute; left:0; width:50%; padding-left:15px; font-weight:600; text-align:left; color:#555; }
}
</style>
</head>
<body>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3>📖 Riwayat Peminjaman</h3>
      <p>Lihat riwayat peminjaman buku Anda di perpustakaan digital.</p>
    </div>
  </div>

  <input type="text" id="search-box" placeholder="Cari judul buku...">

  <div class="card-custom table-wrapper">
    <table class="table-member">
      <thead>
        <tr>
          <th>No</th>
          <th>Judul Buku</th>
          <th>Tanggal Pinjam</th>
          <th>Jatuh Tempo</th>
          <th>Tanggal Kembali</th>
          <th>Status</th>
          <th>Denda</th>
        </tr>
      </thead>
      <tbody id="table-data">
        <?php
        $no = $halaman_awal + 1;
        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
                $tanggal_pinjam = new DateTime($row['tanggal_pinjam']);
                $jatuh_tempo = (clone $tanggal_pinjam)->modify('+30 days');
                $hari_ini = new DateTime();
                $denda = 0;

                if($row['status']=='Dipinjam' && $hari_ini>$jatuh_tempo){
                    $selisih = $hari_ini->diff($jatuh_tempo)->days;
                    $denda = $selisih*1000;
                } else {
                    $denda = $row['denda'];
                }
        ?>
        <tr>
          <td data-label="No"><?= $no++ ?></td>
          <td data-label="Judul"><?= htmlspecialchars($row['judul']) ?></td>
          <td data-label="Tgl Pinjam"><?= date('d-m-Y', strtotime($row['tanggal_pinjam'])) ?></td>
          <td data-label="Jatuh Tempo"><?= $jatuh_tempo->format('d-m-Y') ?></td>
          <td data-label="Tgl Kembali"><?= $row['tanggal_kembali'] ? date('d-m-Y', strtotime($row['tanggal_kembali'])) : '-' ?></td>
          <td data-label="Status">
            <?php if($row['status']=='Dipinjam'): ?>
              <span class="badge badge-success">Dipinjam</span>
            <?php elseif($row['status']=='Diajukan'): ?>
              <span class="badge badge-warning">Diajukan</span>
            <?php else: ?>
              <span class="badge badge-danger"><?= htmlspecialchars($row['status']) ?></span>
            <?php endif; ?>
          </td>
          <td data-label="Denda"><?= $denda>0 ? "Rp ".number_format($denda,0,',','.') : '-' ?></td>
        </tr>
        <?php
            }
        } else {
            echo '<tr><td colspan="7" class="text-center text-muted">Belum ada data peminjaman buku.</td></tr>';
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="pagination" id="pagination">
    <?php
    for($i=1;$i<=$total_halaman;$i++){
        $active = ($i==$hal) ? 'active' : '';
        echo "<a href='#' class='{$active}' data-page='{$i}'>$i</a>";
    }
    ?>
  </div>
</div>

<script>
$(document).ready(function(){
    function loadData(search='', pageNum=1){
        $.ajax({
            url: '',
            type: 'GET',
            data: { search: search, hal: pageNum },
            success: function(response){
                let html = $(response).find('#table-data').html();
                let pag = $(response).find('#pagination').html();
                $('#table-data').html(html);
                $('#pagination').html(pag);
            }
        });
    }

    $('#search-box').on('keyup', function(){
        let val = $(this).val();
        loadData(val, 1);
    });

    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        let page = $(this).data('page');
        let val = $('#search-box').val();
        loadData(val, page);
    });
});
</script>
</body>
</html>
