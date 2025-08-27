<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
html, body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  margin: 0;
  padding: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
.container {
  max-width: 1200px;
  margin: auto;
  flex: 1;
  padding: 20px 0;
}
.table-container {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  min-height: 450px;
}
.scroll-horizontal {
  overflow-x: auto;
}
.table-buku {
  width: 100%;
  border-collapse: collapse;
  min-width: 1000px;
  border-radius: 12px;
  overflow: hidden;
}
.table-buku thead th {
  background: linear-gradient(to right, #000b3a, #00365c);
  color: white;
  font-weight: 600;
  text-align: left;
  padding: 14px 16px;
  white-space: nowrap;
}
.table-buku th,
.table-buku td {
  padding: 12px 16px;
  vertical-align: middle;
  border-bottom: 1px solid #f0f0f0;
  font-size: 0.95rem;
  color: #333;
}
.table-buku tbody tr:hover {
  background: #f7f9fc;
  transition: 0.2s;
}
.table-buku tbody tr:nth-child(even) {
  background: #fafafa;
}
.table-buku img.cover-img {
  width: 55px;
  height: 75px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #ddd;
}
.aksi-btn {
  display: flex;
  gap: 6px;
}
.btn-edit,
.btn-hapus {
  padding: 6px 10px;
  font-size: 0.85rem;
  font-weight: 500;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-edit {
  background: #4CAF50;
  color: white;
}
.btn-edit:hover {
  background: #43a047;
}
.btn-hapus {
  background: #e53935;
  color: white;
}
.btn-hapus:hover {
  background: #d32f2f;
}
.btn-tambah-buku {
  background: linear-gradient(to right, #021f9f, #00365c);
  border: none;
  color: white;
  font-weight: 600;
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
  text-decoration: none;
  transition: 0.3s;
  font-size: 0.9rem;
}
.btn-tambah-buku:hover {
  background: linear-gradient(to right, #2535d8, #2c2dc1);
}
.top-action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}
.top-action-bar h3 {
  margin: 0;
  font-size: 1.4rem;
  color: white;
}
.top-action-bar p {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.9rem;
  margin: 0;
}
#searchInput {
  margin-bottom: 15px;
  padding: 6px 12px;
  width: 300px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 0.95rem;
}
#pagination {
  margin-top: 15px;
  text-align: center;
}
#pagination button {
  margin-right: 5px;
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  color: white;
  background: #00365c;
}
#pagination button.active {
  background: #764ba2;
}
footer {
  background: #00365c;
  color: white;
  text-align: center;
  padding: 15px 0;
}
</style>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3>📖 Data Buku Digital</h3>
      <p>Lihat dan kelola semua buku digital di sistem.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="index_admin.php?page_admin=buku_digital/tambah_buku_digital" class="btn-tambah-buku">
        <i class="fas fa-plus-circle"></i> Tambah Buku Digital
      </a>
      <a href="page_admin/buku_digital/cetak_buku_digital.php" target="_blank" class="btn-tambah-buku" style="background: linear-gradient(to right, #000b3aff, #337ccfff);">
        <i class="fas fa-print"></i> Cetak PDF
      </a>
    </div>
  </div>

  <!-- Search input -->
  <input type="text" id="searchInput" placeholder="Cari buku berdasarkan judul..." />

  <div class="table-container">
    <div class="scroll-horizontal">
      <table class="table-buku" id="bukuTable">
        <thead>
          <tr>
            <th>No</th>
            <th>Cover</th>
            <th>Kode Buku</th>
            <th>Judul</th>
            <th>Pengarang</th>
            <th>Penerbit</th>
            <th>Tahun Terbit</th>
            <th>Kategori</th>
            <th>Deskripsi</th>
            <th>File Ebook</th>
            <th>Akses</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          include "config/koneksi.php";
          $no = 1;
          $query = "SELECT * FROM buku_digital ORDER BY judul ASC";
          $sql = mysqli_query($koneksi, $query);

          if ($sql && mysqli_num_rows($sql) > 0) {
            while ($buku = mysqli_fetch_assoc($sql)) {
              $coverPath = (!empty($buku['cover']) && file_exists("upload/" . $buku['cover'])) 
                ? "upload/" . $buku['cover'] 
                : "img/cover/default.jpg";

              $filePath = (!empty($buku['file_ebook']) && file_exists("upload/" . $buku['file_ebook'])) 
                ? "<a href='upload/{$buku['file_ebook']}' target='_blank'>Lihat File</a>" 
                : "<span style='color:#999;'>Tidak ada file</span>";

              echo "<tr>
                <td>{$no}</td>
                <td><img src='{$coverPath}' alt='{$buku['judul']}' class='cover-img'></td>
                <td>{$buku['kode_buku']}</td>
                <td>{$buku['judul']}</td>
                <td>{$buku['pengarang']}</td>
                <td>{$buku['penerbit']}</td>
                <td>{$buku['tahun_terbit']}</td>
                <td>{$buku['kategori']}</td>
                <td>{$buku['deskripsi']}</td>
                <td>{$filePath}</td>
                <td>{$buku['akses']}</td>
                <td class='aksi-btn'>
                  <a href='index_admin.php?page_admin=buku_digital/edit_buku_digital&id={$buku['id_buku']}' class='btn-edit'>Edit</a>
                  <a href='index_admin.php?page_admin=buku_digital/hapus_buku_digital&id={$buku['id_buku']}' class='btn-hapus' onclick='return confirm(\"Yakin ingin menghapus buku ini?\")'>Hapus</a>
                </td>
              </tr>";
              $no++;
            }
          } else {
            echo "<tr><td colspan='12' style='text-align:center; color:#666;'>Tidak ada data buku.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div id="pagination"></div>
  </div>
</div>


<script>
const rowsPerPage = 5;
const table = document.getElementById("bukuTable");
const tbody = table.querySelector("tbody");
let rows = Array.from(tbody.querySelectorAll("tr"));
const paginationDiv = document.getElementById("pagination");
let currentPage = 1;

// Fungsi tampilkan halaman
function displayPage(page) {
  currentPage = page;
  const start = (page - 1) * rowsPerPage;
  const end = start + rowsPerPage;
  rows.forEach((row, index) => row.style.display = (index >= start && index < end) ? "" : "none");
  renderPagination();
}

// Fungsi render pagination
function renderPagination() {
  const totalPages = Math.ceil(rows.length / rowsPerPage);
  paginationDiv.innerHTML = "";
  if (currentPage > 1) {
    const prevBtn = document.createElement("button");
    prevBtn.innerText = "Previous";
    prevBtn.onclick = () => displayPage(currentPage - 1);
    paginationDiv.appendChild(prevBtn);
  }
  for (let i = 1; i <= totalPages; i++) {
    const pageBtn = document.createElement("button");
    pageBtn.innerText = i;
    pageBtn.classList.toggle("active", i === currentPage);
    pageBtn.onclick = () => displayPage(i);
    paginationDiv.appendChild(pageBtn);
  }
  if (currentPage < totalPages) {
    const nextBtn = document.createElement("button");
    nextBtn.innerText = "Next";
    nextBtn.onclick = () => displayPage(currentPage + 1);
    paginationDiv.appendChild(nextBtn);
  }
}

// Fungsi search
const searchInput = document.getElementById("searchInput");
searchInput.addEventListener("keyup", () => {
  const filter = searchInput.value.toLowerCase();
  rows.forEach(row => {
    const judul = row.cells[3].textContent.toLowerCase();
    row.style.display = judul.includes(filter) ? "" : "none";
  });
  // Update rows sesuai hasil filter
  rows = Array.from(tbody.querySelectorAll("tr")).filter(row => row.style.display !== "none");
  displayPage(1);
});

// Tampilkan halaman pertama saat load
displayPage(1);
</script>
