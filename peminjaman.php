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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sarana_id = $_POST['sarana_id'];
    $jumlah_pinjam = $_POST['jumlah_pinjam'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Check stock availability
        $check_stock = $db->prepare("SELECT jumlah_tersedia FROM sarana WHERE sarana_id = ?");
        $check_stock->execute([$sarana_id]);
        $sarana = $check_stock->fetch();
        
        if ($sarana && $sarana['jumlah_tersedia'] >= $jumlah_pinjam) {
            // Insert peminjaman with status 'belum selesai'
            $insert = $db->prepare("INSERT INTO peminjaman (user_id, sarana_id, tanggal_pinjam, tanggal_kembali, jumlah_pinjam, status) VALUES (?, ?, ?, ?, ?, 'belum selesai')");
            
            if ($insert->execute([$user_id, $sarana_id, $tanggal_pinjam, $tanggal_kembali, $jumlah_pinjam])) {
                header("Location: peminjaman.php?success=1");
            } else {
                header("Location: peminjaman.php?error=Gagal mengajukan peminjaman");
            }
        } else {
            header("Location: peminjaman.php?error=Stok tidak mencukupi");
        }
    } catch (PDOException $e) {
        header("Location: peminjaman.php?error=Terjadi kesalahan: " . $e->getMessage());
    }
    exit();
}

// Get available sarana
$query = "SELECT * FROM sarana WHERE jumlah_tersedia > 0";
$sarana = $db->query($query)->fetchAll();
?>

<div class="container mt-4">
    <h2>Peminjaman Sarana</h2>
    
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Permintaan peminjaman berhasil terkirim! Silahkan menunggu persetujuan admin.
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        Maaf, stok tidak mencukupi atau terjadi kesalahan.
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5>Daftar Sarana Tersedia</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama Sarana</th>
                        <th>Jumlah Tersedia</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sarana as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_sarana']) ?></td>
                        <td><?= $item['jumlah_tersedia'] ?></td>
                        <td><?= htmlspecialchars($item['lokasi']) ?></td>
                        <td>
                            <button type="button" 
                                    class="btn btn-primary btn-sm" 
                                    onclick="pinjam(<?= $item['sarana_id'] ?>, '<?= htmlspecialchars($item['nama_sarana']) ?>', <?= $item['jumlah_tersedia'] ?>)">
                                Pinjam
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Peminjaman -->
<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="sarana_id" id="sarana_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Sarana</label>
                        <input type="text" class="form-control" id="nama_sarana" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jumlah Pinjam</label>
                        <input type="number" class="form-control" name="jumlah_pinjam" required min="1">
                        <small class="text-muted">Maksimal: <span id="max_qty"></span></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pinjam</label>
                        <input type="date" class="form-control" name="tanggal_pinjam" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Kembali</label>
                        <input type="date" class="form-control" name="tanggal_kembali" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ajukan Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function pinjam(id, nama, max) {
    document.getElementById('sarana_id').value = id;
    document.getElementById('nama_sarana').value = nama;
    document.getElementById('max_qty').textContent = max;
    
    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="tanggal_pinjam"]').min = today;
    document.querySelector('input[name="tanggal_kembali"]').min = today;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('modalPinjam'));
    modal.show();
}

// Validate form before submit
document.querySelector('form').onsubmit = function(e) {
    const jumlah = parseInt(this.jumlah_pinjam.value);
    const max = parseInt(document.getElementById('max_qty').textContent);
    const tglPinjam = this.tanggal_pinjam.value;
    const tglKembali = this.tanggal_kembali.value;
    
    if (jumlah > max) {
        e.preventDefault();
        alert('Jumlah pinjam tidak boleh melebihi stok tersedia!');
        return false;
    }
    
    if (tglKembali < tglPinjam) {
        e.preventDefault();
        alert('Tanggal kembali harus setelah tanggal pinjam!');
        return false;
    }
    
    return true;
};
</script>

<?php require_once 'includes/footer.php'; ?> 