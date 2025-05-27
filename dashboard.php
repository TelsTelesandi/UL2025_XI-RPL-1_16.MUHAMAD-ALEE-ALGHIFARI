<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics based on user role
if ($_SESSION['role'] === 'Admin') {
    // Get total peminjaman
    $query = "SELECT COUNT(*) as total FROM peminjaman";
    $stmt = $db->query($query);
    $total_peminjaman = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get active peminjaman
    $query = "SELECT COUNT(*) as active FROM peminjaman WHERE status = 'belum selesai'";
    $stmt = $db->query($query);
    $active_peminjaman = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

    // Get total sarana
    $query = "SELECT COUNT(*) as total FROM sarana";
    $stmt = $db->query($query);
    $total_sarana = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total users
    $query = "SELECT COUNT(*) as total FROM users WHERE role != 'Admin'";
    $stmt = $db->query($query);
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} else {
    // Get user's total peminjaman
    $query = "SELECT COUNT(*) as total FROM peminjaman WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $total_peminjaman = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get user's active peminjaman
    $query = "SELECT COUNT(*) as active FROM peminjaman WHERE user_id = :user_id AND status = 'belum selesai'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $active_peminjaman = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>Dashboard</h2>
        <hr>
    </div>
</div>

<?php if ($_SESSION['role'] === 'Admin'): ?>
<!-- Admin Dashboard -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Peminjaman</h5>
                <h2 class="card-text"><?php echo $total_peminjaman; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Peminjaman Aktif</h5>
                <h2 class="card-text"><?php echo $active_peminjaman; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Sarana</h5>
                <h2 class="card-text"><?php echo $total_sarana; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2 class="card-text"><?php echo $total_users; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Recent Peminjaman Table -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Peminjaman Terbaru</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped" id="peminjamanTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Sarana</th>
                            <th>Tanggal Pinjam</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT p.*, u.nama_lengkap, s.nama_sarana 
                                FROM peminjaman p 
                                JOIN users u ON p.user_id = u.user_id 
                                JOIN sarana s ON p.sarana_id = s.sarana_id 
                                ORDER BY p.peminjaman_id DESC LIMIT 10";
                        $stmt = $db->query($query);
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $row['peminjaman_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_sarana']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tanggal_pinjam']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- User Dashboard -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Peminjaman Anda</h5>
                <h2 class="card-text"><?php echo $total_peminjaman; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Peminjaman Aktif Anda</h5>
                <h2 class="card-text"><?php echo $active_peminjaman; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- User's Peminjaman History -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Riwayat Peminjaman Anda</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped" id="userPeminjamanTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sarana</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT p.*, s.nama_sarana 
                                FROM peminjaman p 
                                JOIN sarana s ON p.sarana_id = s.sarana_id 
                                WHERE p.user_id = :user_id 
                                ORDER BY p.peminjaman_id DESC";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $row['peminjaman_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_sarana']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tanggal_pinjam']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tanggal_kembali']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    <?php if ($_SESSION['role'] === 'Admin'): ?>
    $('#peminjamanTable').DataTable();
    <?php else: ?>
    $('#userPeminjamanTable').DataTable();
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?> 