<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Check if user is not admin
if ($_SESSION['role'] === 'Admin') {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle form submission for return
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'return') {
        try {
            // Get peminjaman details first
            $get_peminjaman = "SELECT sarana_id, jumlah_pinjam FROM peminjaman WHERE peminjaman_id = ? AND user_id = ?";
            $stmt = $db->prepare($get_peminjaman);
            $stmt->execute([$_POST['peminjaman_id'], $_SESSION['user_id']]);
            $peminjaman = $stmt->fetch();

            if ($peminjaman) {
                // Update status peminjaman menjadi pengajuan pengembalian
                $update_peminjaman = "UPDATE peminjaman SET status = 'pengajuan pengembalian' WHERE peminjaman_id = ? AND user_id = ?";
                $stmt = $db->prepare($update_peminjaman);
                $stmt->execute([$_POST['peminjaman_id'], $_SESSION['user_id']]);

                header("Location: pengembalian.php?success=1");
            } else {
                header("Location: pengembalian.php?error=Data peminjaman tidak ditemukan");
            }
        } catch (PDOException $e) {
            header("Location: pengembalian.php?error=Gagal mengembalikan sarana: " . $e->getMessage());
        }
        exit();
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>Status Peminjaman & Pengembalian Sarana</h2>
        <hr>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    Pengajuan pengembalian berhasil! Silahkan menunggu persetujuan admin.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?php echo htmlspecialchars($_GET['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Active Borrowings Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Daftar Peminjaman Aktif</h5>
                <small class="text-muted">Menampilkan semua peminjaman yang belum selesai</small>
            </div>
            <div class="card-body">
                <table class="table table-striped" id="pengembalianTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Sarana</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get active borrowings for current user
                        $query = "SELECT p.*, s.nama_sarana 
                                FROM peminjaman p 
                                JOIN sarana s ON p.sarana_id = s.sarana_id 
                                WHERE p.user_id = ? 
                                AND p.status IN ('belum selesai', 'disetujui', 'pengajuan pengembalian')
                                ORDER BY p.peminjaman_id DESC";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$_SESSION['user_id']]);

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $statusClass = '';
                            $statusText = '';
                            
                            switch($row['status']) {
                                case 'belum selesai':
                                    $statusClass = 'warning';
                                    $statusText = 'Menunggu Persetujuan Peminjaman';
                                    break;
                                case 'disetujui':
                                    $statusClass = 'success';
                                    $statusText = 'Disetujui - Sedang Dipinjam';
                                    break;
                                case 'pengajuan pengembalian':
                                    $statusClass = 'info';
                                    $statusText = 'Menunggu Persetujuan Pengembalian';
                                    break;
                            }

                            echo "<tr>";
                            echo "<td>" . $row['peminjaman_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_sarana']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tanggal_pinjam']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tanggal_kembali']) . "</td>";
                            echo "<td>" . $row['jumlah_pinjam'] . "</td>";
                            echo "<td><span class='badge bg-" . $statusClass . "'>" . $statusText . "</span></td>";
                            echo "<td>";
                            if ($row['status'] === 'disetujui') {
                                echo "<button type='button' class='btn btn-sm btn-success return-btn' 
                                        onclick='showReturnModal(" . $row['peminjaman_id'] . ", \"" . htmlspecialchars($row['nama_sarana'], ENT_QUOTES) . "\")'>
                                        Kembalikan
                                    </button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Return Confirmation Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pengembalian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="pengembalian.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="return">
                    <input type="hidden" name="peminjaman_id" id="return_peminjaman_id">
                    <p>Anda akan mengembalikan <span id="return_sarana_name" class="fw-bold"></span>.</p>
                    <p>Pastikan barang dalam kondisi baik sebelum dikembalikan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Konfirmasi Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#pengembalianTable').DataTable({
        "language": {
            "emptyTable": "Tidak ada peminjaman yang dapat dikembalikan"
        },
        "paging": false,
        "searching": false,
        "info": false
    });
});

// Function to show return modal
function showReturnModal(id, nama) {
    document.getElementById('return_peminjaman_id').value = id;
    document.getElementById('return_sarana_name').textContent = nama;
    
    const returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
    returnModal.show();
}
</script>

<?php require_once 'includes/footer.php'; ?> 