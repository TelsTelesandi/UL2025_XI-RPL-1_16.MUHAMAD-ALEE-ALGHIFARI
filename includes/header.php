<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Peminjaman Sarana</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    
    <style>
    .nav-link.active {
        border-bottom: 2px solid white;
        font-weight: bold;
    }
    
    /* Simple table styling */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        background-color: #f8f9fa;
    }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Aplikasi Peminjaman Sarana</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'sarana' ? 'active' : ''; ?>" href="sarana.php">Kelola Sarana</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'approval' ? 'active' : ''; ?>" href="approval.php">Approval</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'report' ? 'active' : ''; ?>" href="report.php">Laporan</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'peminjaman' ? 'active' : ''; ?>" href="peminjaman.php">Peminjaman</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'pengembalian' ? 'active' : ''; ?>" href="pengembalian.php">Pengembalian</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="navbar-nav">
                    <span class="nav-item nav-link text-light">
                        Welcome, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                    </span>
                    <a class="nav-link" href="auth/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4"> 