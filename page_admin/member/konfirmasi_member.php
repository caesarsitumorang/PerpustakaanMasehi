<?php
include "config/koneksi.php";

// SETUJUI MEMBER
if (isset($_GET['setujui_id'])) {
  $id_pengunjung = intval($_GET['setujui_id']);
  mysqli_query($koneksi, "UPDATE pendaftaran_member SET status='diterima' WHERE id_pengunjung=$id_pengunjung");

  $cek = mysqli_query($koneksi, "SELECT * FROM member WHERE id_pengunjung = $id_pengunjung");
  if (mysqli_num_rows($cek) == 0) {
    $tanggal = date("Y-m-d");
    mysqli_query($koneksi, "INSERT INTO member (id_pengunjung, tanggal_bergabung, status, created_at, updated_at)
                            VALUES ($id_pengunjung, '$tanggal', 'aktif', NOW(), NOW())");
  }

  echo "<script>alert('Disetujui sebagai member.');window.location='index_admin.php?page_admin=member/konfirmasi_member';</script>";
  exit;
}

// TOLAK MEMBER
if (isset($_GET['tolak_id'])) {
  $id_pengunjung = intval($_GET['tolak_id']);
  mysqli_query($koneksi, "UPDATE pendaftaran_member SET status='ditolak' WHERE id_pengunjung=$id_pengunjung");
  echo "<script>alert('Pendaftaran ditolak.');window.location='index_admin.php?page_admin=member/konfirmasi_member';</script>";
  exit;
}

// Ambil data untuk JS
$members = [];
$sql = mysqli_query($koneksi, "
  SELECT p.*, pm.identitas
  FROM pengunjung p
  INNER JOIN pendaftaran_member pm ON p.id = pm.id_pengunjung
  WHERE pm.status = 'pending'
  ORDER BY pm.id_pendaftaran DESC
");
if ($sql && mysqli_num_rows($sql) > 0) {
  while ($row = mysqli_fetch_assoc($sql)) {
    $members[] = $row;
  }
}
?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Poppins', sans-serif;
  }
  .container { padding: 20px; }
  .top-action-bar { margin-bottom: 1.5rem; }
  .top-action-bar h3 { font-size: 1.8rem; margin-bottom: 0.25rem; color:#fff; }
  .top-action-bar p { font-size: 0.95rem; color: rgba(255,255,255,0.8); }
  .search-bar { margin-bottom: 1rem; display:flex; }
  .search-bar input {
    padding: 0.5rem 1rem;
    border-radius: 12px;
    border: 1px solid #ccc;
    width: 250px;
    font-size: 0.9rem;
  }
  .table-wrapper { overflow-x: auto; }
  .table-kepsek {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    min-width: 1000px;
  }
  .table-kepsek thead th {
    background: linear-gradient(to right, #4a6cf7, #657ef8);
    color: white;
    text-align: left;
    padding: 14px 18px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .table-kepsek th, .table-kepsek td {
    padding: 14px 18px;
    vertical-align: middle;
    font-size: 0.95rem;
    color: #333;
    white-space: nowrap;
  }
  .table-kepsek tbody tr:nth-child(even) { background-color: #f9f9f9; }
  .foto-img {
    width: 60px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ddd;
  }
  .btn-edit {
    display: inline-block;
    background: #28a745;
    color: white;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.85rem;
    transition: background 0.3s ease;
  }
  .btn-edit:hover { background: #218838; }
  .btn-danger { background: #dc3545; }
  .btn-danger:hover { background: #c82333; }
  .text-center { text-align: center; }
  /* Pagination */
  #pagination {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1.5rem;
  }
  #pagination button {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 0.5rem 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  #pagination button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
  }
  #pagination button.active {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 6px 20px rgba(4, 120, 87, 0.3);
  }
  #pagination button:disabled {
    background: #94d3b2;
    cursor: not-allowed;
    box-shadow: none;
  }
</style>

<div class="container">
  <div class="top-action-bar">
    <h3>📋 Konfirmasi Pendaftaran Member</h3>
    <p>Berikut adalah data pengunjung yang telah mendaftar sebagai member dan menunggu konfirmasi.</p>
  </div>

  <!-- Search -->
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Cari nama atau username...">
  </div>

  <div class="table-wrapper">
    <table class="table-kepsek">
      <thead>
        <tr>
          <th>No</th>
          <th>Foto</th>
          <th>Nama Lengkap</th>
          <th>Username</th>
          <th>Jenis Kelamin</th>
          <th>Email</th>
          <th>No HP</th>
          <th>Identitas</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tableBody"></tbody>
    </table>
  </div>

  <div id="pagination"></div>
</div>

<script>
  const members = <?php echo json_encode($members); ?>;
  const rowsPerPage = 5;
  let currentPage = 1;

  function displayTable(page = 1, search = '') {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    let filtered = members.filter(m =>
      m.nama_lengkap.toLowerCase().includes(search.toLowerCase()) ||
      m.username.toLowerCase().includes(search.toLowerCase())
    );

    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const paginatedItems = filtered.slice(start, end);

    paginatedItems.forEach((m, i) => {
      const no = start + i + 1;
      const foto = m.foto && m.foto !== '' ? 'upload/' + m.foto : 'img/cover/default.jpg';
      const identitas = m.identitas && m.identitas !== ''
        ? `<a href="upload/${m.identitas}" target="_blank">Lihat</a>`
        : `<span class="text-muted">-</span>`;

      const row = `<tr>
        <td>${no}</td>
        <td><img src="${foto}" class="foto-img"></td>
        <td>${m.nama_lengkap}</td>
        <td>${m.username}</td>
        <td>${m.jenis_kelamin}</td>
        <td>${m.email}</td>
        <td>${m.no_hp}</td>
        <td>${identitas}</td>
        <td>
          <a href="?page_admin=member/konfirmasi_member&setujui_id=${m.id}" class="btn-edit"
            onclick="return confirm('Setujui ${m.nama_lengkap} sebagai member?')">Setujui</a>
          &nbsp;
          <a href="?page_admin=member/konfirmasi_member&tolak_id=${m.id}" class="btn-edit btn-danger"
            onclick="return confirm('Tolak pendaftaran ${m.nama_lengkap}?')">Tolak</a>
        </td>
      </tr>`;
      tableBody.insertAdjacentHTML('beforeend', row);
    });

    // Pagination buttons
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    const totalPages = Math.ceil(filtered.length / rowsPerPage);
    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement('button');
      btn.innerText = i;
      btn.className = (i === page) ? 'active' : '';
      btn.addEventListener('click', () => {
        currentPage = i;
        displayTable(currentPage, document.getElementById('searchInput').value);
      });
      pagination.appendChild(btn);
    }
  }

  document.getElementById('searchInput').addEventListener('input', (e) => {
    displayTable(1, e.target.value);
  });

  displayTable();
</script>
