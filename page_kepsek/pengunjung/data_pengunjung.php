<?php
include "config/koneksi.php";

// Ambil semua data pengunjung
$query = "SELECT p.*, m.id_member 
          FROM pengunjung p 
          LEFT JOIN member m ON m.id_pengunjung = p.id 
          ORDER BY p.id ASC";
$sql = mysqli_query($koneksi, $query);
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
html, body {
    font-family: 'Poppins', sans-serif;
    margin:0; padding:0;
    min-height:100vh;
    background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
}
.container { padding:25px; }
.top-action-bar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:1rem; }
.top-action-bar h3 { color:#fff; margin:0; font-size:1.6rem; font-weight:600; }
.top-action-bar p { color:rgba(255,255,255,0.85); margin:0; font-size:0.9rem; }
.btn-action { background:linear-gradient(to right,#021f9f,#00365c); color:#fff; padding:0.55rem 1.2rem; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:0.3s; font-size:0.9rem; }
.btn-action:hover { background:linear-gradient(to right,#5a67d8,#6b46c1); transform:translateY(-1px); }

.table-wrapper { width:100%; overflow-x:auto; background:#fff; border-radius:12px; padding:1rem; box-shadow:0 4px 20px rgba(0,0,0,0.08);}
.table-pengunjung { width:100%; border-collapse:collapse; min-width:1000px; }
.table-pengunjung thead th { background:linear-gradient(to right,#000b3a,#00365c); color:#fff; font-weight:500; padding:14px 16px; text-align:left; }
.table-pengunjung th, .table-pengunjung td { padding:12px 16px; vertical-align:middle; font-size:0.92rem; color:#444; border-bottom:1px solid #f0f0f0; white-space:nowrap; }
.table-pengunjung tbody tr:nth-child(even) { background:#fafafa; }
.table-pengunjung tbody tr:hover { background:#f2f6ff; transition:0.2s; }
.foto-img { width:50px; height:70px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }
.badge-member { background:#28a745; color:#fff; padding:4px 10px; border-radius:6px; font-size:0.8rem; font-weight:bold; }

.pagination { margin-top:1rem; text-align:center; }
.pagination button { padding:6px 12px; margin:0 3px; border:none; border-radius:6px; background:#eee; font-weight:600; cursor:pointer; transition:0.2s; }
.pagination button.active { background:#2575fc; color:#fff; }
.pagination button:hover { background:#38f9d7; color:#fff; }
</style>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3>👥 Data Pengunjung</h3>
      <p>Lihat dan kelola semua data pengunjung.</p>
    </div>
    <div>
      <a href="page_kepsek/pengunjung/cetak_pengunjung.php" target="_blank" class="btn-action">
        <i class="fas fa-print"></i> Cetak PDF
      </a>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="table-pengunjung" id="pengunjungTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Foto</th>
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>Email</th>
          <th>No HP</th>
          <th>Alamat</th>
          <th>Tanggal Daftar</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="pagination" id="pagination"></div>
</div>

<script>
// Ambil semua data dari PHP
const data = <?php
$allData = [];
$no = 1;
while($row = mysqli_fetch_assoc($sql)){
    $foto = (!empty($row['foto']) && file_exists("upload/".$row['foto'])) ? "upload/".$row['foto'] : "img/cover/default.jpg";
    $status = !empty($row['id_member']) ? "<span class='badge-member'>Member</span>" : "Non Member";
    $allData[] = [
        'no'=>$no,
        'foto'=>$foto,
        'username'=>$row['username'],
        'nama_lengkap'=>$row['nama_lengkap'],
        'email'=>$row['email'],
        'no_hp'=>$row['no_hp'],
        'alamat'=>$row['alamat'],
        'tanggal_daftar'=>$row['tanggal_daftar'],
        'status'=>$status
    ];
    $no++;
}
echo json_encode($allData);
?>;

const rowsPerPage =5  ;
let currentPage = 1;

function renderTable(page){
    const tbody = document.querySelector('#pengunjungTable tbody');
    tbody.innerHTML = '';
    const start = (page-1)*rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = data.slice(start,end);
    pageData.forEach(row=>{
        tbody.innerHTML += `<tr>
            <td>${row.no}</td>
            <td><img src="${row.foto}" class="foto-img"></td>
            <td>${row.username}</td>
            <td>${row.nama_lengkap}</td>
            <td>${row.email}</td>
            <td>${row.no_hp}</td>
            <td>${row.alamat}</td>
            <td>${row.tanggal_daftar}</td>
            <td>${row.status}</td>
        </tr>`;
    });
}

function renderPagination(){
    const totalPages = Math.ceil(data.length/rowsPerPage);
    const container = document.getElementById('pagination');
    container.innerHTML = '';
    for(let i=1;i<=totalPages;i++){
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = (i===currentPage)?'active':'';
        btn.addEventListener('click', ()=>{
            currentPage = i;
            renderTable(currentPage);
            renderPagination();
        });
        container.appendChild(btn);
    }
}

// Inisialisasi
renderTable(currentPage);
renderPagination();
</script>
