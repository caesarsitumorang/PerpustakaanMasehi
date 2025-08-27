<?php
include "config/koneksi.php";

// Ambil semua data buku digital
$query = "SELECT * FROM buku_digital ORDER BY judul ASC";
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
.table-buku { width:100%; border-collapse:collapse; min-width:1200px; }
.table-buku thead th { background:linear-gradient(to right,#000b3a,#00365c); color:#fff; font-weight:500; padding:14px 16px; text-align:left; }
.table-buku th, .table-buku td { padding:12px 16px; vertical-align:middle; font-size:0.92rem; color:#444; border-bottom:1px solid #f0f0f0; white-space:nowrap; }
.table-buku tbody tr:nth-child(even) { background:#fafafa; }
.table-buku tbody tr:hover { background:#f2f6ff; transition:0.2s; }
.cover-img { width:50px; height:70px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }

.pagination { margin-top:1rem; text-align:center; }
.pagination button { padding:6px 12px; margin:0 3px; border:none; border-radius:6px; background:#eee; font-weight:600; cursor:pointer; transition:0.2s; }
.pagination button.active { background:#2575fc; color:#fff; }
.pagination button:hover { background:#38f9d7; color:#fff; }
</style>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3>📚 Data Buku Digital</h3>
      <p>Lihat dan kelola semua data buku digital.</p>
    </div>
    <div>
      <a href="page_kepsek/buku_digital/cetak_buku_digital.php" target="_blank" class="btn-action">
        <i class="fas fa-print"></i> Cetak PDF
      </a>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="table-buku" id="bukuTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Cover</th>
          <th>Kode Buku</th>
          <th>Judul</th>
          <th>Pengarang</th>
          <th>Penerbit</th>
          <th>Tahun</th>
          <th>Kategori</th>
          <th>Deskripsi</th>
          <th>File Ebook</th>
          <th>Akses</th>
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
while($buku = mysqli_fetch_assoc($sql)){
    $cover = (!empty($buku['cover']) && file_exists("upload/".$buku['cover'])) ? "upload/".$buku['cover'] : "img/cover/default.jpg";
    $filePath = (!empty($buku['file_ebook']) && file_exists("upload/".$buku['file_ebook'])) ? "<a href='upload/{$buku['file_ebook']}' target='_blank'>Lihat File</a>" : "<span style='color:#888;'>Tidak ada file</span>";
    $allData[] = [
        'no'=>$no,
        'cover'=>$cover,
        'kode_buku'=>$buku['kode_buku'],
        'judul'=>$buku['judul'],
        'pengarang'=>$buku['pengarang'],
        'penerbit'=>$buku['penerbit'],
        'tahun'=>$buku['tahun_terbit'],
        'kategori'=>$buku['kategori'],
        'deskripsi'=>$buku['deskripsi'],
        'file_ebook'=>$filePath,
        'akses'=>$buku['akses']
    ];
    $no++;
}
echo json_encode($allData);
?>;

const rowsPerPage = 5;
let currentPage = 1;

function renderTable(page){
    const tbody = document.querySelector('#bukuTable tbody');
    tbody.innerHTML = '';
    const start = (page-1)*rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = data.slice(start,end);
    pageData.forEach(row=>{
        tbody.innerHTML += `<tr>
            <td>${row.no}</td>
            <td><img src="${row.cover}" class="cover-img"></td>
            <td>${row.kode_buku}</td>
            <td>${row.judul}</td>
            <td>${row.pengarang}</td>
            <td>${row.penerbit}</td>
            <td>${row.tahun}</td>
            <td>${row.kategori}</td>
            <td>${row.deskripsi}</td>
            <td>${row.file_ebook}</td>
            <td>${row.akses}</td>
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
