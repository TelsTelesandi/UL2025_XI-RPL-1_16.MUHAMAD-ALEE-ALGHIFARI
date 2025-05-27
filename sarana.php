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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $query = "INSERT INTO sarana (nama_sarana, jumlah_tersedia, lokasi, keterangan) 
                     VALUES (:nama, :jumlah, :lokasi, :keterangan)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama', $_POST['nama_sarana']);
            $stmt->bindParam(':jumlah', $_POST['jumlah_tersedia']);
            $stmt->bindParam(':lokasi', $_POST['lokasi']);
            $stmt->bindParam(':keterangan', $_POST['keterangan']);
            $stmt->execute();
            header("Location: sarana.php?success=add");
            exit();
        } elseif ($_POST['action'] === 'edit') {
            $query = "UPDATE sarana 
                     SET nama_sarana = :nama, 
                         jumlah_tersedia = :jumlah, 
                         lokasi = :lokasi, 
                         keterangan = :keterangan 
                     WHERE sarana_id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_POST['sarana_id']);
            $stmt->bindParam(':nama', $_POST['nama_sarana']);
            $stmt->bindParam(':jumlah', $_POST['jumlah_tersedia']);
            $stmt->bindParam(':lokasi', $_POST['lokasi']);
            $stmt->bindParam(':keterangan', $_POST['keterangan']);
            $stmt->execute();
            header("Location: sarana.php?success=edit");
            exit();
        } elseif ($_POST['action'] === 'delete' && isset($_POST['sarana_id'])) {
            try {
                $sarana_id = $_POST['sarana_id'];
                
                // Hapus dulu data di tabel peminjaman yang terkait
                $delete_peminjaman = "DELETE FROM peminjaman WHERE sarana_id = :id";
                $stmt_peminjaman = $db->prepare($delete_peminjaman);
                $stmt_peminjaman->bindParam(':id', $sarana_id);
                $stmt_peminjaman->execute();
                
                // Baru hapus sarananya
                $delete_sarana = "DELETE FROM sarana WHERE sarana_id = :id";
                $stmt_sarana = $db->prepare($delete_sarana);
                $stmt_sarana->bindParam(':id', $sarana_id);
                $stmt_sarana->execute();
                
                header("Location: sarana.php?success=delete");
            } catch (PDOException $e) {
                header("Location: sarana.php?error=Gagal menghapus: " . $e->getMessage());
            }
            exit();
        }
    }
}

// Show success/error messages
if (isset($_GET['success'])) {
    $message = '';
    switch($_GET['success']) {
        case 'add':
            $message = 'Sarana berhasil ditambahkan!';
            break;
        case 'edit':
            $message = 'Sarana berhasil diubah!';
            break;
        case 'delete':
            $message = 'Sarana berhasil dihapus!';
            break;
    }
    if ($message) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_GET['error']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>Kelola Sarana</h2>
        <hr>
    </div>
</div>

<!-- Add Sarana Button -->
<div class="row mb-4">
    <div class="col-md-12">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaranaModal">
            Tambah Sarana
        </button>
    </div>
</div>

<!-- Sarana Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped" id="saranaTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Sarana</th>
                            <th>Jumlah Tersedia</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM sarana ORDER BY sarana_id DESC";
                        $stmt = $db->query($query);
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $row['sarana_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_sarana']) . "</td>";
                            echo "<td>" . $row['jumlah_tersedia'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['lokasi']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                            echo "<td>
                                    <button type='button' class='btn btn-sm btn-warning edit-btn' 
                                            data-id='" . $row['sarana_id'] . "'
                                            data-nama='" . htmlspecialchars($row['nama_sarana']) . "'
                                            data-jumlah='" . $row['jumlah_tersedia'] . "'
                                            data-lokasi='" . htmlspecialchars($row['lokasi']) . "'
                                            data-keterangan='" . htmlspecialchars($row['keterangan']) . "'>
                                        <i class='bx bx-edit'></i> Edit
                                    </button>
                                    <button type='button' class='btn btn-sm btn-danger delete-btn'
                                            data-id='" . $row['sarana_id'] . "'
                                            data-nama='" . htmlspecialchars($row['nama_sarana']) . "'>
                                        <i class='bx bx-trash'></i> Hapus
                                    </button>
                                </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Sarana Modal -->
<div class="modal fade" id="addSaranaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Sarana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="sarana.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="nama_sarana" class="form-label">Nama Sarana</label>
                        <input type="text" class="form-control" id="nama_sarana" name="nama_sarana" required>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah_tersedia" class="form-label">Jumlah Tersedia</label>
                        <input type="number" class="form-control" id="jumlah_tersedia" name="jumlah_tersedia" required>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Sarana Modal -->
<div class="modal fade" id="editSaranaModal" tabindex="-1" aria-labelledby="editSaranaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSaranaModalLabel">Edit Sarana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="sarana.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="sarana_id" id="edit_sarana_id">
                    <div class="mb-3">
                        <label for="edit_nama_sarana" class="form-label">Nama Sarana</label>
                        <input type="text" class="form-control" id="edit_nama_sarana" name="nama_sarana" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jumlah_tersedia" class="form-label">Jumlah Tersedia</label>
                        <input type="number" class="form-control" id="edit_jumlah_tersedia" name="jumlah_tersedia" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="edit_lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="edit_lokasi" name="lokasi" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Sarana Modal -->
<div class="modal fade" id="deleteSaranaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Sarana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="sarana.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="sarana_id" id="delete_sarana_id">
                    <p>Apakah Anda yakin ingin menghapus sarana "<span id="delete_sarana_name"></span>"?</p>
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with minimal configuration
    $('#saranaTable').DataTable({
        "paging": false,      // Disable pagination
        "searching": false,   // Disable search
        "info": false,       // Disable showing info (Showing 1 to n of n entries)
        "ordering": true,    // Keep sorting functionality
        "language": {
            "emptyTable": "Tidak ada data yang tersedia"
        }
    });

    // Handle Edit Button Click
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const jumlah = this.getAttribute('data-jumlah');
            const lokasi = this.getAttribute('data-lokasi');
            const keterangan = this.getAttribute('data-keterangan');
            
            document.getElementById('edit_sarana_id').value = id;
            document.getElementById('edit_nama_sarana').value = nama;
            document.getElementById('edit_jumlah_tersedia').value = jumlah;
            document.getElementById('edit_lokasi').value = lokasi;
            document.getElementById('edit_keterangan').value = keterangan;
            
            new bootstrap.Modal(document.getElementById('editSaranaModal')).show();
        });
    });

    // Handle Delete Button Click
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            
            document.getElementById('delete_sarana_id').value = id;
            document.getElementById('delete_sarana_name').textContent = nama;
            
            new bootstrap.Modal(document.getElementById('deleteSaranaModal')).show();
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 