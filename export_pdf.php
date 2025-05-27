<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require 'vendor/autoload.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

// Clear any existing output
ob_clean();

$database = new Database();
$db = $database->getConnection();

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

// Get peminjaman data
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

// Create new PDF document
class MYPDF extends TCPDF {
    public function Header() {
        // Set font
        $this->SetFont('helvetica', 'B', 16);
        
        // Title
        $this->Cell(0, 15, 'SMK TELEKOMUNIKASI TELESANDI BEKASI', 0, false, 'C', 0);
        $this->Ln(7);
        
        // Address
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 15, 'Jl. KH. Mochammad - Mekarsari, Tambun Selatan', 0, false, 'C', 0);
        $this->Ln(5);
        $this->Cell(0, 15, 'Bekasi, Jawa Barat', 0, false, 'C', 0);
        $this->Ln(5);
        $this->Cell(0, 15, 'Telp: (021) XXXX-XXXX | Email: info@smktelekomunikasitelesandi.sch.id', 0, false, 'C', 0);
        $this->Ln(5);
        
        // Line
        $this->Line(10, 50, $this->getPageWidth()-10, 50);
        $this->Ln(20);
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('SMK Telekomunikasi Telesandi');
$pdf->SetTitle('Laporan Peminjaman Barang');

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 30, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', 'B', 14);

// Title
$pdf->Cell(0, 15, 'LAPORAN PEMINJAMAN BARANG', 0, false, 'C', 0);
$pdf->Ln(10);

// Period
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 10, 'Periode: ' . date('d F Y'), 0, false, 'C', 0);
$pdf->Ln(15);

// Table header
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(10, 7, 'No', 1, 0, 'C', 1);
$pdf->Cell(40, 7, 'Peminjam', 1, 0, 'C', 1);
$pdf->Cell(40, 7, 'Sarana', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Tgl Pinjam', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Tgl Kembali', 1, 0, 'C', 1);
$pdf->Cell(20, 7, 'Jumlah', 1, 0, 'C', 1);
$pdf->Cell(20, 7, 'Status', 1, 1, 'C', 1);

// Table data
$pdf->SetFont('helvetica', '', 10);
$no = 1;
foreach ($peminjaman as $row) {
    $pdf->Cell(10, 7, $no, 1, 0, 'C');
    $pdf->Cell(40, 7, $row['nama_lengkap'], 1, 0, 'L');
    $pdf->Cell(40, 7, $row['nama_sarana'], 1, 0, 'L');
    $pdf->Cell(30, 7, $row['tanggal_pinjam'], 1, 0, 'C');
    $pdf->Cell(30, 7, $row['tanggal_kembali'], 1, 0, 'C');
    $pdf->Cell(20, 7, $row['jumlah_pinjam'], 1, 0, 'C');
    $pdf->Cell(20, 7, $row['status'], 1, 1, 'C');
    $no++;
}

$pdf->Ln(20);

// Signature section
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 7, 'Bekasi, ' . date('d F Y'), 0, 1, 'R');
$pdf->Cell(0, 7, 'Mengetahui,', 0, 1, 'R');
$pdf->Ln(15);
$pdf->Cell(0, 7, 'Kepala Sekolah', 0, 1, 'R');
$pdf->Cell(0, 7, 'SMK Telekomunikasi Telesandi Bekasi', 0, 1, 'R');

// Clean any output that might have been generated
ob_end_clean();

// Output the PDF
$pdf->Output('Laporan_Peminjaman_'.date('Y-m-d').'.pdf', 'I');
exit;
?> 