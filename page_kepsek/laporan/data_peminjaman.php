<?php
include "config/koneksi.php";

// Ambil parameter AJAX
$hal = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$limit = 10;
$halaman_awal = ($hal - 1) * $limit;

// Query jumlah total
$where = "";
if ($search != "") {
    $where = "WHERE pg.nama_lengkap LIKE '%$search%' OR b.judul LIKE '%$search%'";
}
$total_data = mysqli_num_rows(mysqli_query($koneksi, "
    SELECT p.id_peminjaman 
    FROM peminjaman p
    LEFT JOIN buku b ON p.kode_buku = b.kode_buku
    LEFT JOIN pengunjung pg ON p.id_pengunjung = pg.id
    $where
"));
$total_halaman = ceil($total_data / $limit);

// Query data per halaman
$query = "
SELECT p.id_peminjaman, p.tanggal_pinjam, p.tanggal_kembali, p.status, p.denda, 
       b.judul, pg.nama_lengkap 
FROM peminjaman p
LEFT JOIN buku b ON p.kode_buku = b.kode_buku
LEFT JOIN pengunjung pg ON p.id_pengunjung = pg.id
$where
ORDER BY p.tanggal_pinjam DESC
LIMIT $halaman_awal, $limit
";
$result = mysqli_query($koneksi, $query);
?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
html, body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #333;
    margin: 0;
    padding: 0;
}
.container { padding: 25px; }
.top-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.top-action-bar h3 { margin:0; color:#fff; font-weight:600; }
.top-action-bar p { margin:4px 0 0; color:rgba(255,255,255,0.85); font-size:0.9rem; }
.btn-pdf { display:inline-flex; align-items:center; gap:6px; background:linear-gradient(to right,#ff4d4d,#c70039); color:#fff; padding:0.55rem 1.2rem; border-radius:8px; font-weight:500; text-decoration:none; transition:all 0.3s ease; }
.btn-pdf:hover { transform:translateY(-1px); background:linear-gradient(to right,#b02a37,#800000); }

#search-box { padding: 6px 12px; border-radius:6px; border:1px solid #ccc; margin-bottom:15px; width:250px; }

.table-buku { width:100%; border-collapse: collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); min-width:1000px; font-size:0.92rem; }
.table-buku thead th { background:linear-gradient(to right,#000b3a,#00365c); color:white; font-weight:500; padding:14px 16px; text-align:center; }
.table-buku th, .table-buku td { padding:12px 16px; text-align:center; vertical-align:middle; border-bottom:1px solid #e2e2e2; }
.table-buku tbody tr:nth-child(even){ background:#f9f9f9; }
.table-buku tbody tr:hover{ background:#f2f6ff; transition: background 0.2s ease; }

.pagination { margin-top:15px; text-align:center; }
.pagination a {
    padding:6px 12px;
    margin:0 4px;
    background:#eee;
    color:#333;
    border-radius:6px;
    text-decoration:none;
    font-weight:600;
}
.pagination a.active { background:#2575fc; color:white; }
.pagination a:hover { background:#38f9d7; color:#000; }

@media (max-width:768px){
    .top-action-bar { flex-direction:column; align-items:flex-start; gap:0.8rem; }
    .btn-pdf { width:100%; justify-content:center; }
    .table-buku { font-size:0.85rem; }
}
</style>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3>📚 Data Peminjaman Buku</h3>
      <p>Lihat dan kelola semua data peminjaman di sistem.</p>
    </div>
    <a href="page_kepsek/laporan/cetak_peminjaman.php" target="_blank" class="btn-pdf">
      <i class="fas fa-file-pdf"></i> Cetak PDF
    </a>
  </div>

  <input type="text" id="search-box" placeholder="Cari nama peminjam atau judul buku...">

  <div class="table-responsive">
    <table class="table-buku">
      <thead>
        <tr>
          <th>No</th>
          <th>ID Peminjaman</th>
          <th>Nama Peminjam</th>
          <th>Judul Buku</th>
          <th>Tanggal Pinjam</th>
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
                $nama = isset($row['nama_lengkap']) ? $row['nama_lengkap'] : '';
                $judul = isset($row['judul']) ? $row['judul'] : '';
                $tgl_kembali = $row['tanggal_kembali'] ? date('d-m-Y', strtotime($row['tanggal_kembali'])) : '<span style="color:#888;">-</span>';
                $denda = $row['denda'] > 0 ? 'Rp ' . number_format($row['denda'],0,',','.') : '<span style="color:#888;">-</span>';
                echo "<tr>
                    <td>{$no}</td>
                    <td>{$row['id_peminjaman']}</td>
                    <td>".htmlspecialchars($nama)."</td>
                    <td>".htmlspecialchars($judul)."</td>
                    <td>".date('d-m-Y', strtotime($row['tanggal_pinjam']))."</td>
                    <td>{$tgl_kembali}</td>
                    <td>{$row['status']}</td>
                    <td>{$denda}</td>
                </tr>";
                $no++;
            }
        }else{
            echo "<tr><td colspan='8' style='text-align:center; color:#888;'>Tidak ada data peminjaman.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="pagination" id="pagination">
    <?php
    for($i=1;$i<=$total_halaman;$i++){
        $active = ($i == $hal) ? 'active' : '';
        echo "<a href='#' class='{$active}' data-page='{$i}'>$i</a>";
    }
    ?>
  </div>
</div>

<script>
$(document).ready(function(){
    const limit = <?= $limit ?>;
    let page = <?= $hal ?>;

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
        const val = $(this).val();
        loadData(val, 1);
    });

    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        const pageNum = $(this).data('page');
        const val = $('#search-box').val();
        loadData(val, pageNum);
    });
});
</script>
