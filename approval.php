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

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $peminjaman_id = $_POST['peminjaman_id'];
    $status = $_POST['status'];
    $catatan = $_POST['catatan'] ?? '';

    try {
        // Get peminjaman details first
        $get_peminjaman = "SELECT sarana_id, jumlah_pinjam, status FROM peminjaman WHERE peminjaman_id = ?";
        $stmt = $db->prepare($get_peminjaman);
        $stmt->execute([$peminjaman_id]);
        $peminjaman = $stmt->fetch();

        if ($status === 'disetujui') {
            // Kurangi stok saat disetujui
            $update_stock = "UPDATE sarana SET jumlah_tersedia = jumlah_tersedia - ? WHERE sarana_id = ?";
            $stmt = $db->prepare($update_stock);
            $stmt->execute([$peminjaman['jumlah_pinjam'], $peminjaman['sarana_id']]);
        } else if ($status === 'selesai' && $peminjaman['status'] === 'pengajuan pengembalian') {
            // Kembalikan stok saat pengembalian disetujui
            $update_stock = "UPDATE sarana SET jumlah_tersedia = jumlah_tersedia + ? WHERE sarana_id = ?";
            $stmt = $db->prepare($update_stock);
            $stmt->execute([$peminjaman['jumlah_pinjam'], $peminjaman['sarana_id']]);
        }

        // Update status peminjaman
        $query = "UPDATE peminjaman SET status = ?, catatan_admin = ? WHERE peminjaman_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$status, $catatan, $peminjaman_id]);

        header("Location: approval.php?success=1");
    } catch (PDOException $e) {
        header("Location: approval.php?error=" . urlencode("Terjadi kesalahan: " . $e->getMessage()));
    }
    exit();
}

// Get pending requests
$query = "SELECT p.*, u.nama_lengkap, s.nama_sarana, s.lokasi, s.jumlah_tersedia 
          FROM peminjaman p 
          JOIN users u ON p.user_id = u.user_id 
          JOIN sarana s ON p.sarana_id = s.sarana_id 
          WHERE p.status = 'belum selesai' OR p.status = 'pengajuan pengembalian'
          ORDER BY p.peminjaman_id DESC";
$peminjaman = $db->query($query)->fetchAll();
?>

<div class="container mt-4">
    <h2>Approval Peminjaman & Pengembalian</h2>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Permintaan berhasil diproses!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5>Daftar Permintaan</h5>
        </div>
        <div class="card-body">
            <?php if (empty($peminjaman)): ?>
            <div class="alert alert-info">
                Tidak ada permintaan yang perlu diproses.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Sarana</th>
                            <th>Lokasi</th>
                            <th>Jumlah</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peminjaman as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($p['nama_sarana']) ?></td>
                            <td><?= htmlspecialchars($p['lokasi']) ?></td>
                            <td><?= $p['jumlah_pinjam'] ?></td>
                            <td><?= $p['tanggal_pinjam'] ?></td>
                            <td><?= $p['tanggal_kembali'] ?></td>
                            <td>
                                <?php if ($p['status'] === 'belum selesai'): ?>
                                    <span class="badge bg-warning">Menunggu Persetujuan Peminjaman</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Menunggu Persetujuan Pengembalian</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status'] === 'belum selesai'): ?>
                                    <button type="button" class="btn btn-success btn-sm" 
                                            onclick="showApprovalModal(<?= $p['peminjaman_id'] ?>, 'disetujui')">
                                        Setuju
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="showApprovalModal(<?= $p['peminjaman_id'] ?>, 'ditolak')">
                                        Tolak
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-success btn-sm" 
                                            onclick="showReturnApprovalModal(<?= $p['peminjaman_id'] ?>, 'selesai')">
                                        Terima Pengembalian
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="peminjaman_id" id="peminjaman_id">
                    <input type="hidden" name="status" id="status">
                    
                    <p id="modalMessage"></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan Admin</label>
                        <textarea class="form-control" name="catatan" rows="3" 
                                placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Approval Modal -->
<div class="modal fade" id="returnApprovalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pengembalian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="peminjaman_id" id="return_peminjaman_id">
                    <input type="hidden" name="status" id="return_status">
                    
                    <p>Anda akan menyetujui pengembalian sarana ini.</p>
                    <p>Pastikan sarana telah dikembalikan dalam kondisi baik.</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan Admin</label>
                        <textarea class="form-control" name="catatan" rows="3" 
                                placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>
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
function showApprovalModal(id, status) {
    document.getElementById('peminjaman_id').value = id;
    document.getElementById('status').value = status;
    
    const message = status === 'disetujui' 
        ? 'Anda akan menyetujui permintaan peminjaman ini.'
        : 'Anda akan menolak permintaan peminjaman ini.';
    document.getElementById('modalMessage').textContent = message;
    
    const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
    modal.show();
}

function showReturnApprovalModal(id, status) {
    document.getElementById('return_peminjaman_id').value = id;
    document.getElementById('return_status').value = status;
    
    const modal = new bootstrap.Modal(document.getElementById('returnApprovalModal'));
    modal.show();
}
</script>

<?php require_once 'includes/footer.php'; ?> 