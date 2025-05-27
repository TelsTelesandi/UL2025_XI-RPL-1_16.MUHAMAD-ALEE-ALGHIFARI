<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Check if user is admin
if ($_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle delete request
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['peminjaman_id'])) {
    try {
        $delete_query = "DELETE FROM peminjaman WHERE peminjaman_id = ?";
        $stmt = $db->prepare($delete_query);
        $stmt->execute([$_POST['peminjaman_id']]);
        header("Location: report.php?success=delete");
        exit();
    } catch (PDOException $e) {
        header("Location: report.php?error=" . urlencode("Gagal menghapus data: " . $e->getMessage()));
        exit();
    }
}

// Get date range filter
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? '';

// Build query based on filters
$where_conditions = [];
$params = [];

if ($start_date) {
    $where_conditions[] = "p.tanggal_pinjam >= :start_date";
    $params[':start_date'] = $start_date;
}

if ($end_date) {
    $where_conditions[] = "p.tanggal_kembali <= :end_date";
    $params[':end_date'] = $end_date;
}

if ($status) {
    $where_conditions[] = "p.status = :status";
    $params[':status'] = $status;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get all peminjaman data
$query = "SELECT p.*, u.nama_lengkap, s.nama_sarana 
          FROM peminjaman p 
          JOIN users u ON p.user_id = u.user_id 
          JOIN sarana s ON p.sarana_id = s.sarana_id 
          $where_clause
          ORDER BY p.peminjaman_id DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$peminjaman = $stmt->fetchAll();

?>

<div class="row">
    <div class="col-md-12">
        <h2>Laporan Peminjaman</h2>
        <hr>
    </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] === 'delete'): ?>
<div class="alert alert-success alert-dismissible fade show">
    Data riwayat berhasil dihapus!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?php echo htmlspecialchars($_GET['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="belum selesai" <?php echo $status === 'belum selesai' ? 'selected' : ''; ?>>Belum Selesai</option>
                    <option value="disetujui" <?php echo $status === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="ditolak" <?php echo $status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    <option value="selesai" <?php echo $status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="report.php" class="btn btn-secondary">Reset</a>
                <a href="export_pdf.php<?php echo $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" 
                   class="btn btn-success ms-2">
                    <i class="bx bx-export"></i> Export PDF
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped" id="reportTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Peminjam</th>
                            <th>Sarana</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Catatan Admin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peminjaman as $row): ?>
                        <tr>
                            <td><?php echo $row['peminjaman_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_sarana']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_pinjam']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_kembali']); ?></td>
                            <td><?php echo $row['jumlah_pinjam']; ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['catatan_admin'] ?? '-'); ?></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="showDeleteModal(<?php echo $row['peminjaman_id']; ?>, '<?php echo htmlspecialchars($row['nama_sarana']); ?>')">
                                    <i class="bx bx-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="peminjaman_id" id="delete_peminjaman_id">
                    <p>Anda yakin ingin menghapus riwayat peminjaman <span id="delete_sarana_name" class="fw-bold"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#reportTable').DataTable();
});

function showDeleteModal(id, nama) {
    document.getElementById('delete_peminjaman_id').value = id;
    document.getElementById('delete_sarana_name').textContent = nama;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php require_once 'includes/footer.php'; ?> 