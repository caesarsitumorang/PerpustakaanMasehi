<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  html, body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Poppins', sans-serif;
  }

  .container {
    padding: 25px;
  }

  /* Top bar */
  .top-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
  }

  .top-action-bar h3 {
    margin: 0;
    font-size: 1.6rem;
    font-weight: 600;
    color: #fff;
  }

  .top-action-bar p {
    margin-top: 4px;
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.9rem;
  }

  /* Buttons */
  .btn-tambah-member {
    background: linear-gradient(to right, #021f9f, #00365c);
    border: none;
    color: white;
    font-weight: 500;
    padding: 0.55rem 1.2rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .btn-tambah-member:hover {
    transform: translateY(-1px);
    background: linear-gradient(to right, #5a67d8, #6b46c1);
  }

  /* Table */
  .table-wrapper {
    width: 100%;
    overflow-x: auto;
    margin-top: 1rem;
  }

  .table-member {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    min-width: 1000px;
  }

  .table-member thead th {
    background: linear-gradient(to right, #000b3a, #00365c);
    color: white;
    font-weight: 500;
    padding: 14px 16px;
    text-align: left;
  }

  .table-member th, .table-member td {
    padding: 12px 16px;
    text-align: left;
    vertical-align: middle;
    font-size: 0.92rem;
    color: #444;
    border-bottom: 1px solid #f0f0f0;
  }

  .table-member tbody tr:nth-child(even) {
    background: #fafafa;
  }

  .table-member tbody tr:hover {
    background: #f2f6ff;
    transition: background 0.2s ease;
  }

  /* Foto */
  .table-member img.foto-img {
    width: 50px;
    height: 70px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ddd;
  }

  /* Search Bar */
  .search-bar {
    margin-bottom: 1rem;
    display: flex;
    justify-content: flex-start;
  }

  .search-bar input {
    padding: 0.5rem 1rem;
    border-radius: 12px;
    border: 1px solid #ccc;
    width: 250px;
    font-size: 0.9rem;
  }

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

  /* Responsive */
  @media (max-width: 768px) {
    .top-action-bar {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }

    .btn-tambah-member {
      width: 100%;
      justify-content: center;
    }

    .search-bar input {
      width: 100%;
    }
  }
</style>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3>📚 Data Member</h3>
      <p>Lihat dan kelola data member perpustakaan digital.</p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="index_admin.php?page_admin=member/konfirmasi_member" class="btn-tambah-member">
        <i class="fas fa-users"></i> Lihat Pendaftar Member
      </a>
      <a href="page_admin/member/cetak_member.php" target="_blank" class="btn-tambah-member" style="background: linear-gradient(to right, #000b3aff, #337ccfff);">
        <i class="fas fa-print"></i> Cetak PDF
      </a>
    </div>
  </div>

  <!-- Search -->
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Cari username atau nama lengkap...">
  </div>

  <div class="table-wrapper">
    <table class="table-member" id="memberTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Foto</th>
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>Jenis Kelamin</th>
          <th>No HP</th>
          <th>Email</th>
          <th>Tanggal Bergabung</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php
        include "config/koneksi.php";
        $no = 1;
        $query = "SELECT member.*, pengunjung.* 
                  FROM member 
                  JOIN pengunjung ON member.id_pengunjung = pengunjung.id 
                  ORDER BY member.id_member ASC";
        $sql = mysqli_query($koneksi, $query);

        $members = [];
        if ($sql && mysqli_num_rows($sql) > 0) {
          while ($row = mysqli_fetch_assoc($sql)) {
            $members[] = $row; // simpan ke array untuk JS
          }
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
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
      m.username.toLowerCase().includes(search.toLowerCase()) || 
      m.nama_lengkap.toLowerCase().includes(search.toLowerCase())
    );

    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const paginatedItems = filtered.slice(start, end);

    paginatedItems.forEach((m, i) => {
      const no = start + i + 1;
      const foto = m.foto && m.foto !== '' ? 'upload/' + m.foto : 'img/cover/default.jpg';
      const username = m.username || '-';
      const nama = m.nama_lengkap || '-';
      const jk = m.jenis_kelamin || '-';
      const hp = m.no_hp || '-';
      const email = m.email || '-';
      const tgl = m.tanggal_bergabung || '-';
      const status = m.status || '-';

      const row = `<tr>
        <td>${no}</td>
        <td><img src="${foto}" class="foto-img"></td>
        <td>${username}</td>
        <td>${nama}</td>
        <td>${jk}</td>
        <td>${hp}</td>
        <td>${email}</td>
        <td>${tgl}</td>
        <td>${status}</td>
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
