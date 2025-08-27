<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Proses pengembalian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'], $_POST['id'], $_POST['kondisi_buku_dikembalikan'])) {
    $id = intval($_POST['id']);
    $aksi = $_POST['aksi'];
    $kondisi_buku_dikembalikan = $_POST['kondisi_buku_dikembalikan'];
    $kondisi = $kondisi_buku_dikembalikan; 

    try {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) throw new Exception("Token tidak valid");
        if ($id <= 0 || $aksi !== 'selesaikan') throw new Exception("Aksi tidak valid");
        if (!in_array($kondisi_buku_dikembalikan, ['baik','rusak'])) throw new Exception("Kondisi buku tidak valid");

        $stmt = mysqli_prepare($koneksi, "SELECT tanggal_pinjam, status, kode_buku FROM peminjaman WHERE id_peminjaman = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) throw new Exception("Data tidak ditemukan");

        $data = mysqli_fetch_assoc($result);
        if ($data['status'] !== 'Dipinjam') throw new Exception("Peminjaman belum disetujui atau sudah selesai");

        $tanggal_pinjam = new DateTime($data['tanggal_pinjam']);
        $tanggal_kembali = new DateTime();
        $selisih = $tanggal_kembali->diff($tanggal_pinjam)->days;
        $denda = $selisih > 30 ? ($selisih - 30) * 1000 : 0;

        if (!empty($data['kode_buku'])) {
            $updateStok = mysqli_prepare($koneksi, "UPDATE buku SET jumlah = jumlah + 1 WHERE kode_buku = ?");
            mysqli_stmt_bind_param($updateStok, "s", $data['kode_buku']);
            mysqli_stmt_execute($updateStok);
        }

        $update = mysqli_prepare($koneksi, "
            UPDATE peminjaman 
            SET status = 'Dikembalikan', tanggal_kembali = ?, denda = ?, kondisi_buku_dikembalikan = ?
            WHERE id_peminjaman = ?
        ");
        $tgl = $tanggal_kembali->format('Y-m-d H:i:s');
        mysqli_stmt_bind_param($update, "sisi", $tgl, $denda, $kondisi, $id);
        mysqli_stmt_execute($update);

        $_SESSION['message'] = "Peminjaman berhasil diselesaikan, stok buku dikembalikan, kondisi buku: $kondisi_buku_dikembalikan.";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }

    echo "<script>window.location.href='';</script>";
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ambil semua data peminjaman aktif
$result = mysqli_query($koneksi, "
    SELECT p.id_peminjaman, p.tanggal_pinjam, p.status, p.denda, p.kondisi_buku_dikembalikan, b.judul, pg.nama_lengkap 
    FROM peminjaman p
    LEFT JOIN buku b ON p.kode_buku = b.kode_buku
    LEFT JOIN pengunjung pg ON p.id_pengunjung = pg.id
    WHERE TRIM(LOWER(p.status)) = 'dipinjam'
    ORDER BY p.tanggal_pinjam DESC
");

$data_all = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Pengembalian Buku - Perpustakaan Digital</title>
<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* --- Copy semua style dari versi sebelumnya --- */
* {margin:0;padding:0;box-sizing:border-box}
body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);font-family:'Inter',sans-serif;min-height:100vh;position:relative;overflow-x:hidden}
/* ... sisanya sama persis sampai style dialog ... */

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 117, 140, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(120, 200, 255, 0.2) 0%, transparent 50%);
    pointer-events: none;
    z-index: -1;
}

.main-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    position: relative;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
    color: white;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #fff, #e0e7ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
}

.page-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    font-weight: 400;
}

.dashboard-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.1),
        0 8px 32px rgba(0, 0, 0, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    position: relative;
}

.dashboard-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
}

.card-header-custom {
    padding: 2rem 2.5rem 1.5rem;
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.card-title-icon {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}

.card-title-icon i {
    font-size: 1.8rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.card-description {
    color: #64748b;
    font-weight: 400;
    margin: 0;
}

.card-body-custom {
    padding: 2.5rem;
}

.alert-custom {
    border: none;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 2rem;
    font-weight: 500;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%);
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.modern-table-wrapper {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.table-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.table-header th {
    padding: 1.5rem 1.25rem;
    color: white;
    font-weight: 600;
    text-align: center;
    font-size: 0.95rem;
    letter-spacing: 0.025em;
    border: none;
    position: relative;
}

.table-header th:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 25%;
    bottom: 25%;
    width: 1px;
    background: rgba(255, 255, 255, 0.1);
}

.table-body tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(0, 0, 0, 0.03);
}

.table-body tr:hover {
    background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
    transform: translateY(-1px);
}

.table-body td {
    padding: 1.25rem;
    text-align: center;
    vertical-align: middle;
    color: #374151;
    font-weight: 500;
    font-size: 0.9rem;
    border: none;
}

.row-number {
    font-weight: 700;
    color: #667eea;
    background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
    border-radius: 8px;
    padding: 4px 8px;
    display: inline-block;
    min-width: 32px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    letter-spacing: 0.025em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.status-dipinjam {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
}

.status-dipinjam::before {
    content: '●';
    font-size: 0.7rem;
}

.denda-amount {
    font-weight: 700;
    color: #dc2626;
    background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
    padding: 6px 12px;
    border-radius: 12px;
    display: inline-block;
}

.action-button {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
    text-decoration: none;
}

.action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    color: white;
    text-decoration: none;
}

.action-button i {
    font-size: 0.9rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
}

.empty-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1.5rem;
    display: block;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.5rem;
}

.empty-state p {
    font-size: 1rem;
    margin: 0;
}

/* Dialog Konfirmasi */
.confirmation-dialog {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    transition: all 0.3s ease;
}

.confirmation-dialog.active {
    display: flex;
    opacity: 1;
}

.dialog-content {
    background: white;
    border-radius: 24px;
    padding: 2.5rem;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
    transform: scale(0.9) translateY(20px);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.dialog-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #10b981, #059669, #047857);
}

.confirmation-dialog.active .dialog-content {
    transform: scale(1) translateY(0);
}

.dialog-header {
    text-align: center;
    margin-bottom: 2rem;
}

.dialog-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dcfdf7 0%, #a7f3d0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.dialog-icon i {
    font-size: 2rem;
    color: #059669;
}

.dialog-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.dialog-subtitle {
    color: #6b7280;
    font-weight: 400;
}

.condition-selector {
    margin-bottom: 2rem;
}

.condition-label {
    display: block;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
}

.condition-select {
    width: 100%;
    padding: 1rem 1.25rem;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    font-size: 1rem;
    font-weight: 500;
    color: #374151;
    background: white;
    transition: all 0.3s ease;
}

.condition-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.dialog-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.dialog-button {
    padding: 1rem 2rem;
    border: none;
    border-radius: 16px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 120px;
}

.dialog-button-cancel {
    background: #f3f4f6;
    color: #6b7280;
}

.dialog-button-cancel:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

.dialog-button-confirm {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
}

.dialog-button-confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}

/* Responsif */
@media (max-width: 768px) {
    .main-container {
        padding: 1rem;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .card-header-custom,
    .card-body-custom {
        padding: 1.5rem;
    }
    
    .modern-table-wrapper {
        border-radius: 16px;
    }
    
    .table-header th,
    .table-body td {
        padding: 1rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .dialog-content {
        margin: 1rem;
        padding: 2rem 1.5rem;
    }
    
    .dialog-actions {
        flex-direction: column;
    }
    
    .dialog-button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .table-header th,
    .table-body td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .action-button {
        padding: 8px 12px;
        font-size: 0.8rem;
    }
}
/* --- Search Input --- */
#searchInput {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #ccc;
    width: 100%;
    max-width: 300px;
    font-size: 1rem;
    transition: all 0.3s ease;
}
#searchInput:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 5px rgba(102,126,234,0.5);
}

/* --- Pagination --- */
.pagination {
    display: flex;
    list-style: none;
    gap: 0.5rem;
    justify-content: center;
    padding-left: 0;
    margin-top: 1rem;
}
.pagination li {
    display: inline-block;
}
.pagination li a {
    display: block;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid #ddd;
    color: #333;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}
.pagination li a:hover {
    background-color: #667eea;
    color: #fff;
    border-color: #667eea;
}
.pagination li.active a {
    background-color: #764ba2;
    color: #fff;
    border-color: #764ba2;
    cursor: default;
}

/* --- Empty State Table --- */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #555;
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 0.5rem;
    color: #667eea;
}
.empty-state h3 {
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
}
.empty-state p {
    color: #777;
    font-size: 0.95rem;
}

</style>
</head>
<body>

<div class="main-container">
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-book-open"></i> Pengembalian Buku</h1>
        <p class="page-subtitle">Kelola pengembalian buku perpustakaan dengan mudah dan efisien</p>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <div class="card-title-icon"><i class="fas fa-list-ul"></i><span>Daftar Peminjaman Aktif</span></div>
            <p class="card-description">Kelola dan proses pengembalian buku yang sedang dipinjam</p>
        </div>

        <div class="card-body-custom">
            <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-custom">
                <i class="fas fa-<?= $_SESSION['message_type'] == 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($_SESSION['message']) ?>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            </div>
            <?php endif; ?>

            <!-- Input Search -->
            <div style="margin-bottom:1.5rem;">
                <input type="text" id="searchInput" placeholder="Cari nama pengunjung..." class="form-control" style="max-width:300px;">
            </div>

            <div class="modern-table-wrapper">
                <table class="modern-table" id="peminjamanTable">
                    <thead class="table-header">
                        <tr>
                            <th>No</th>
                            <th>Nama Pengunjung</th>
                            <th>Judul Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-body"></tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav style="margin-top:1.5rem;">
                <ul class="pagination" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Dialog Konfirmasi Pengembalian -->
<div id="confirmationDialog" class="confirmation-dialog">
    <div class="dialog-content">
        <div class="dialog-header">
            <div class="dialog-icon"><i class="fas fa-undo-alt"></i></div>
            <h3 class="dialog-title">Konfirmasi Pengembalian</h3>
            <p class="dialog-subtitle">Pilih kondisi buku yang dikembalikan</p>
        </div>
        <div class="condition-selector">
            <label class="condition-label" for="bookCondition"><i class="fas fa-clipboard-check"></i> Kondisi Buku</label>
            <select id="bookCondition" class="condition-select">
                <option value="">-- Pilih kondisi buku --</option>
                <option value="baik">📗 Kondisi Baik</option>
                <option value="rusak">📕 Kondisi Rusak</option>
            </select>
        </div>
        <div class="dialog-actions">
            <button class="dialog-button dialog-button-cancel" onclick="closeConfirmation()"><i class="fas fa-times"></i> Batal</button>
            <button class="dialog-button dialog-button-confirm" onclick="submitReturn()"><i class="fas fa-check"></i> Konfirmasi</button>
        </div>
    </div>
</div>

<form id="returnForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="aksi" id="formAction">
    <input type="hidden" name="id" id="formId">
    <input type="hidden" name="kondisi_buku_dikembalikan" id="formCondition">
</form>

<script>
// --- Data dari PHP ---
const dataAll = <?= json_encode($data_all) ?>;
let filteredData = [...dataAll];
let currentPage = 1;
const rowsPerPage = 5;

// Render tabel
function renderTable() {
    const tbody = document.querySelector('#peminjamanTable tbody');
    tbody.innerHTML = '';

    const start = (currentPage-1)*rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = filteredData.slice(start, end);

    if(pageData.length === 0){
        tbody.innerHTML = `<tr><td colspan="8" class="empty-state">
            <i class='fas fa-inbox'></i>
            <h3>Tidak Ada Peminjaman</h3>
            <p>Saat ini tidak ada buku yang sedang dipinjam</p>
        </td></tr>`;
        renderPagination();
        return;
    }

    pageData.forEach((row, index)=>{
        const tgl_pinjam = new Date(row.tanggal_pinjam);
        const jatuh_tempo = new Date(tgl_pinjam);
        jatuh_tempo.setDate(jatuh_tempo.getDate()+30);
        const hari_ini = new Date();
        const selisih = hari_ini>jatuh_tempo ? Math.ceil((hari_ini-jatuh_tempo)/(1000*60*60*24)) : 0;
        const denda = selisih>0 ? selisih*1000 : 0;

        tbody.innerHTML += `<tr>
            <td><span class='row-number'>${start+index+1}</span></td>
            <td>${row.nama_lengkap}</td>
            <td>${row.judul}</td>
            <td>${tgl_pinjam.toLocaleDateString('id-ID')}</td>
            <td>${jatuh_tempo.toLocaleDateString('id-ID')}</td>
            <td><span class='status-badge status-dipinjam'>${row.status}</span></td>
            <td>${denda>0 ? '<span class="denda-amount">Rp '+denda.toLocaleString('id-ID')+'</span>' : '<span style="color:#059669;font-weight:600">-</span>'}</td>
            <td><button class='action-button' onclick="showConfirmation('selesaikan', ${row.id_peminjaman})"><i class='fas fa-check-circle'></i> Selesaikan peminjaman</button></td>
        </tr>`;
    });

    renderPagination();
}

// Render pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredData.length/rowsPerPage);
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if(totalPages<=1) return;

    for(let i=1;i<=totalPages;i++){
        const li = document.createElement('li');
        li.classList.add('page-item');
        if(i===currentPage) li.classList.add('active');
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.addEventListener('click', e=>{
            e.preventDefault();
            currentPage=i;
            renderTable();
        });
        pagination.appendChild(li);
    }
}

// Search
document.getElementById('searchInput').addEventListener('input', function(){
    const val = this.value.toLowerCase();
    filteredData = dataAll.filter(d=>d.nama_lengkap.toLowerCase().includes(val));
    currentPage=1;
    renderTable();
});

// --- Dialog Konfirmasi ---
let currentAction = '';
let currentId = '';
function showConfirmation(action, id){
    currentAction=action;
    currentId=id;
    document.getElementById('bookCondition').value='';
    document.getElementById('confirmationDialog').classList.add('active');
    setTimeout(()=>document.getElementById('bookCondition').focus(),300);
}
function closeConfirmation(){document.getElementById('confirmationDialog').classList.remove('active');}
function submitReturn(){
    const condition=document.getElementById('bookCondition').value;
    if(!condition){alert('Silakan pilih kondisi buku terlebih dahulu!');document.getElementById('bookCondition').focus();return;}
    document.getElementById('formAction').value=currentAction;
    document.getElementById('formId').value=currentId;
    document.getElementById('formCondition').value=condition;
    document.getElementById('returnForm').submit();
}

// Keyboard events
document.addEventListener('keydown', function(e){
    const dialog=document.getElementById('confirmationDialog');
    if(dialog.classList.contains('active')){
        if(e.key==='Escape') closeConfirmation();
        if(e.key==='Enter'){const condition=document.getElementById('bookCondition').value;if(condition) submitReturn();}
    }
});
document.getElementById('confirmationDialog').addEventListener('click', function(e){if(e.target===this) closeConfirmation();});
document.getElementById('returnForm').addEventListener('submit', function(){const confirmBtn=document.querySelector('.dialog-button-confirm');confirmBtn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Memproses...';confirmBtn.disabled=true;});

// Initial render
renderTable();
</script>

</body>
</html>