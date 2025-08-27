<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Aksi setujui/tolak
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'], $_POST['id'])) {
    $id = intval($_POST['id']);
    $aksi = $_POST['aksi'];

    try {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) throw new Exception("Token tidak valid");
        if ($id <= 0 || !in_array($aksi, ['setujui', 'tolak'])) throw new Exception("Input tidak valid");

        $cek = mysqli_prepare($koneksi, "SELECT status, kode_buku FROM peminjaman WHERE id_peminjaman = ?");
        mysqli_stmt_bind_param($cek, "i", $id);
        mysqli_stmt_execute($cek);
        $res = mysqli_stmt_get_result($cek);
        if (mysqli_num_rows($res) == 0) throw new Exception("Data tidak ditemukan");
        $data = mysqli_fetch_assoc($res);

        if (trim($data['status']) !== 'Diajukan') throw new Exception("Status bukan Diajukan");

        $status_baru = ($aksi === 'setujui') ? 'Dipinjam' : 'Ditolak';

        if ($aksi === 'setujui') {
            $kode_buku = $data['kode_buku'];
            $stok = mysqli_prepare($koneksi, "SELECT jumlah FROM buku WHERE kode_buku = ?");
            mysqli_stmt_bind_param($stok, "s", $kode_buku);
            mysqli_stmt_execute($stok);
            $stokRes = mysqli_stmt_get_result($stok);
            $stokData = mysqli_fetch_assoc($stokRes);

            if (!$stokData || $stokData['jumlah'] <= 0) throw new Exception("Stok buku habis");

            $kurangi = mysqli_prepare($koneksi, "UPDATE buku SET jumlah = jumlah - 1 WHERE kode_buku = ?");
            mysqli_stmt_bind_param($kurangi, "s", $kode_buku);
            mysqli_stmt_execute($kurangi);

            $kondisi_buku_dipinjam = $_POST['kondisi_buku_dipinjam'] ?? 'baik';
            $updateKondisi = mysqli_prepare($koneksi, "UPDATE peminjaman SET kondisi_buku_dipinjam = ? WHERE id_peminjaman = ?");
            mysqli_stmt_bind_param($updateKondisi, "si", $kondisi_buku_dipinjam, $id);
            mysqli_stmt_execute($updateKondisi);
        }

        $up = mysqli_prepare($koneksi, "UPDATE peminjaman SET status = ? WHERE id_peminjaman = ?");
        mysqli_stmt_bind_param($up, "si", $status_baru, $id);
        mysqli_stmt_execute($up);

        $_SESSION['message'] = "Berhasil " . ($aksi === 'setujui' ? "menyetujui" : "menolak") . " peminjaman.";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
}

// Ambil data peminjaman yang diajukan
$all_data = [];
$res = mysqli_query($koneksi, "
    SELECT p.id_peminjaman, p.tanggal_pinjam, p.status, b.judul, pg.nama_lengkap 
    FROM peminjaman p
    LEFT JOIN buku b ON p.kode_buku = b.kode_buku
    LEFT JOIN pengunjung pg ON p.id_pengunjung = pg.id
    WHERE TRIM(LOWER(p.status)) = 'diajukan'
    ORDER BY p.tanggal_pinjam DESC
");
while($r = mysqli_fetch_assoc($res)){
    $all_data[] = [
        'id_peminjaman' => $r['id_peminjaman'],
        'tanggal_pinjam' => $r['tanggal_pinjam'],
        'status' => $r['status'],
        'judul' => $r['judul'] ?: 'Belum ada judul',
        'nama_lengkap' => $r['nama_lengkap']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Permintaan Peminjaman Buku</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* --- Reset & Body --- */
*{margin:0;padding:0;box-sizing:border-box;}
body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);font-family:'Inter',sans-serif;min-height:100vh;position:relative;overflow-x:hidden;color:#1f2937;}
body::before{content:'';position:fixed;top:0;left:0;width:100%;height:100%;background:radial-gradient(circle at 20% 80%, rgba(120,119,198,0.3) 0%, transparent 50%),radial-gradient(circle at 80% 20%, rgba(255,117,140,0.3) 0%, transparent 50%),radial-gradient(circle at 40% 40%, rgba(120,200,255,0.2) 0%, transparent 50%);pointer-events:none;z-index:-1;}

/* --- Container & Header --- */
.main-container{max-width:1400px;margin:0 auto;padding:2rem;position:relative;}
.page-header{text-align:center;margin-bottom:2rem;color:white;}
.page-title{font-size:2.5rem;font-weight:700;margin-bottom:0.5rem;background:linear-gradient(45deg,#fff,#e0e7ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;display:inline-block;}
.page-subtitle{font-size:1.1rem;opacity:0.9;font-weight:400;margin-bottom:1.5rem;}

/* --- Search --- */
/* --- Search --- */
.search-container{
    display:flex;
    justify-content:space-between; /* kiri-kanan */
    margin-bottom:1.5rem;
}
#searchInput{
    padding:0.6rem 1rem;
    border-radius:0.5rem;
    border:1px solid #ccc;
    width:100%;
    max-width:300px;
    font-size:1rem;
    transition:all 0.3s ease;
}
#searchInput:focus{
    outline:none;
    border-color:#667eea;
    box-shadow:0 0 5px rgba(102,126,234,0.5);
}


/* --- Table --- */
.modern-table-wrapper{background:white;border-radius:16px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.05);}
.modern-table{width:100%;border-collapse:collapse;margin:0;}
.table-header{background:linear-gradient(135deg,#1e293b 0%,#334155 100%);}
.table-header th{padding:1rem 1rem;color:white;font-weight:600;text-align:center;font-size:0.95rem;letter-spacing:0.025em;border:none;position:relative;}
.table-header th:not(:last-child)::after{content:'';position:absolute;right:0;top:25%;bottom:25%;width:1px;background:rgba(255,255,255,0.1);}
.table-body tr{transition:all 0.3s ease;border-bottom:1px solid rgba(0,0,0,0.03);}
.table-body tr:hover{background:linear-gradient(135deg,#f8faff 0%,#f1f5f9 100%);transform:translateY(-1px);}
.table-body td{padding:1rem;text-align:center;vertical-align:middle;color:#374151;font-weight:500;font-size:0.9rem;border:none;}
.row-number{font-weight:700;color:#667eea;background:linear-gradient(135deg,#e0e7ff 0%,#f0f4ff 100%);border-radius:8px;padding:4px 8px;display:inline-block;min-width:32px;}
.status-badge{padding:6px 12px;border-radius:20px;font-weight:600;font-size:0.85rem;letter-spacing:0.025em;display:inline-flex;align-items:center;gap:6px;}
.status-dipinjam{background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);color:#92400e;box-shadow:0 2px 8px rgba(245,158,11,0.2);}
.status-dipinjam::before{content:'●';font-size:0.7rem;}

/* --- Buttons --- */
.action-button{background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:white;border:none;border-radius:12px;padding:8px 16px;font-weight:600;font-size:0.9rem;cursor:pointer;transition:all 0.3s ease;display:inline-flex;align-items:center;gap:6px;}
.action-button:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(16,185,129,0.4);}
.action-button i{font-size:0.9rem;}
.action-button.danger{background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);}

/* --- Pagination --- */
.pagination{display:flex;list-style:none;gap:0.5rem;justify-content:center;padding-left:0;margin-top:1.5rem;}
.pagination li a{display:block;padding:0.5rem 0.75rem;border-radius:0.375rem;border:1px solid #ddd;color:#333;text-decoration:none;font-weight:500;transition:all 0.2s ease;}
.pagination li a:hover{background-color:#667eea;color:#fff;border-color:#667eea;}
.pagination li.active a{background-color:#764ba2;color:#fff;border-color:#764ba2;cursor:default;}

/* --- Empty State --- */
.empty-state{text-align:center;padding:2rem;color:#555;}
.empty-state i{font-size:3rem;margin-bottom:0.5rem;color:#667eea;}
.empty-state h3{margin-bottom:0.5rem;font-size:1.25rem;font-weight:600;}
.empty-state p{color:#777;font-size:0.95rem;}

/* --- Modal Approve --- */
#approveModal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(8px);z-index:1000;align-items:center;justify-content:center;padding:1rem;}
#approveModal .modal-content{background:white;border-radius:16px;padding:2rem;width:90%;max-width:400px;text-align:center;box-shadow:0 25px 80px rgba(0,0,0,0.2);}
#approveModal .modal-header{margin-bottom:1.5rem;font-size:1.25rem;font-weight:700;}
#approveModal select{width:100%;padding:0.75rem;border-radius:8px;border:1px solid #ccc;margin-top:0.5rem;}
#approveModal button{padding:0.75rem 1.25rem;border:none;border-radius:8px;font-weight:600;margin:0.25rem;cursor:pointer;}
#approveModal .btn-cancel{background:#f3f4f6;color:#6b7280;}
#approveModal .btn-cancel:hover{background:#e5e7eb;}
#approveModal .btn-confirm{background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:white;}
#approveModal .btn-confirm:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(16,185,129,0.4);}

/* --- Responsif --- */
@media(max-width:768px){.search-container{justify-content:center;margin-bottom:1rem;}.table-header th,.table-body td{padding:0.75rem 0.5rem;font-size:0.85rem;}.action-button{padding:6px 12px;font-size:0.8rem;}}
</style>
</head>
<body>
<div class="main-container">
    <div class="page-header">
        <h1 class="page-title">📄 Permintaan Peminjaman Buku</h1>
        <p class="page-subtitle">Daftar permintaan peminjaman yang perlu persetujuan</p>
    </div>

    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Cari nama pengunjung...">
    </div>

    <div class="modern-table-wrapper">
        <table class="modern-table">
            <thead class="table-header">
                <tr>
                    <th>No</th>
                    <th>Nama Pengunjung</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-body" id="tableBody"></tbody>
        </table>
    </div>

    <ul class="pagination" id="pagination"></ul>
</div>

<!-- Modal Approve -->
<div id="approveModal">
    <div class="modal-content">
        <div class="modal-header">Setujui Peminjaman</div>
        <p id="approveMessage">Apakah Anda yakin?</p>
        <select id="kondisiBuku">
            <option value="baik">Baik</option>
            <option value="rusak">Rusak</option>
        </select>
        <div style="margin-top:1rem;">
            <button class="btn-cancel" onclick="closeApproveModal()">Batal</button>
            <button class="btn-confirm" onclick="submitApprove()">Setujui</button>
        </div>
    </div>
</div>

<form id="actionForm" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="aksi" id="formAksi">
    <input type="hidden" name="id" id="formId">
    <input type="hidden" name="kondisi_buku_dipinjam" id="formKondisi">
</form>

<script>
    // Tampilkan pesan sukses/gagal dari PHP
<?php if(isset($_SESSION['message'])): ?>
alert("<?= addslashes($_SESSION['message']) ?>");
<?php unset($_SESSION['message'], $_SESSION['message_type']); endif; ?>
// Data dari PHP
const data = <?= json_encode($all_data); ?>;

let currentPage = 1;
const rowsPerPage = 5;
let filteredData = [...data];
let currentId = 0;

function renderTable(){
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';
    const start = (currentPage-1)*rowsPerPage;
    const pageData = filteredData.slice(start,start+rowsPerPage);
    if(pageData.length===0){
        tableBody.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fas fa-folder-open"></i><h3>Tidak ada permintaan</h3><p>Belum ada peminjaman yang diajukan</p></td></tr>`;
    }else{
        pageData.forEach((row,i)=>{
            tableBody.innerHTML += `<tr>
                <td><span class='row-number'>${start+i+1}</span></td>
                <td>${row.nama_lengkap}</td>
                <td>${row.judul}</td>
                <td>${new Date(row.tanggal_pinjam).toLocaleDateString('id-ID')}</td>
                <td><span class='status-badge status-dipinjam'>${row.status}</span></td>
                <td>
                    <button class='action-button' onclick="showApproveModal(${row.id_peminjaman},'${row.judul.replace(/'/g,"\\'")}')"><i class='fas fa-check'></i> Setujui</button>
                    <button class='action-button danger' onclick="submitAction('tolak',${row.id_peminjaman})"><i class='fas fa-times'></i> Tolak</button>
                </td>
            </tr>`;
        });
    }
    renderPagination();
}

function renderPagination(){
    const totalPages = Math.ceil(filteredData.length/rowsPerPage);
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    for(let i=1;i<=totalPages;i++){
        pagination.innerHTML += `<li class="${i===currentPage?'active':''}"><a href="#" onclick="changePage(${i});return false;">${i}</a></li>`;
    }
}

function changePage(page){currentPage=page;renderTable();}

// Search
document.getElementById('searchInput').addEventListener('input',function(e){
    const val = e.target.value.toLowerCase();
    filteredData = data.filter(d=>d.nama_lengkap.toLowerCase().includes(val));
    currentPage=1;
    renderTable();
});

// Modal
function showApproveModal(id, judul){
    currentId = id;
    document.getElementById('approveMessage').innerHTML = `Apakah Anda yakin akan menyetujui peminjaman <b>${judul}</b>?`;
    document.getElementById('approveModal').style.display = 'flex';
}
function closeApproveModal(){document.getElementById('approveModal').style.display='none';}
function submitApprove(){
    const kondisi = document.getElementById('kondisiBuku').value;
    document.getElementById('formAksi').value = 'setujui';
    document.getElementById('formId').value = currentId;
    document.getElementById('formKondisi').value = kondisi;
    document.getElementById('actionForm').submit();
}
function submitAction(aksi,id){
    document.getElementById('formAksi').value = aksi;
    document.getElementById('formId').value = id;
    document.getElementById('formKondisi').value = '';
    document.getElementById('actionForm').submit();
}

// Render awal
renderTable();
</script>
</body>
</html>
