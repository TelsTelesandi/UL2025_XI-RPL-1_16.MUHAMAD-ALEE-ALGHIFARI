<?php
require_once 'includes/header.php';
require_once 'config/database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;

// Check if user is admin
if ($_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

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

// Create new Word Document
$phpWord = new PhpWord();

// Add styles
$phpWord->addTitleStyle(1, ['size' => 16, 'bold' => true], ['alignment' => Jc::CENTER]);
$phpWord->addTitleStyle(2, ['size' => 14, 'bold' => true], ['alignment' => Jc::CENTER]);

// New section
$section = $phpWord->addSection();

// Add header with logo and school name
$header = $section->addHeader();

// Logo path
$logoPath = __DIR__ . '/assets/images/logo.png';

// Check if logo exists, if not, skip the logo
if (file_exists($logoPath)) {
    try {
        $header->addImage(
            $logoPath,
            [
                'width' => 60,
                'height' => 60,
                'alignment' => Jc::CENTER
            ]
        );
    } catch (Exception $e) {
        // If there's an error with the image, continue without it
        error_log("Error adding logo: " . $e->getMessage());
    }
}

// School name and address (centered)
$header->addText('SMK TELEKOMUNIKASI TELESANDI BEKASI', ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);
$header->addText('Jl. KH. Mochammad - Mekarsari, Tambun Selatan', ['size' => 11], ['alignment' => Jc::CENTER]);
$header->addText('Bekasi, Jawa Barat', ['size' => 11], ['alignment' => Jc::CENTER]);
$header->addText('Telp: (021) XXXX-XXXX | Email: info@smktelekomunikasitelesandi.sch.id', ['size' => 11], ['alignment' => Jc::CENTER]);

// Add horizontal line
$header->addText('_____________________________________________________________________________', ['bold' => true], ['alignment' => Jc::CENTER]);

// Add title
$section->addTitle('LAPORAN PEMINJAMAN BARANG', 1);
$section->addText('Periode: ' . date('d F Y'), ['size' => 11], ['alignment' => Jc::CENTER]);
$section->addTextBreak(2);

// Create the table
$table = $section->addTable([
    'borderSize' => 1,
    'borderColor' => '000000',
    'alignment' => Jc::CENTER,
    'unit' => 'pct',
    'width' => 100
]);

// Add header row
$table->addRow();
$table->addCell(1000)->addText('No', ['bold' => true], ['alignment' => Jc::CENTER]);
$table->addCell(3000)->addText('Peminjam', ['bold' => true], ['alignment' => Jc::CENTER]);
$table->addCell(3000)->addText('Sarana', ['bold' => true], ['alignment' => Jc::CENTER]);
$table->addCell(2000)->addText('Tanggal Pinjam', ['bold' => true], ['alignment' => Jc::CENTER]);
$table->addCell(2000)->addText('Tanggal Kembali', ['bold' => true], ['alignment' => Jc::CENTER]);
$table->addCell(1500)->addText('Jumlah', ['bold' => true], ['alignment' => Jc::CENTER]);
$table->addCell(2000)->addText('Status', ['bold' => true], ['alignment' => Jc::CENTER]);

// Add data rows
$no = 1;
foreach ($peminjaman as $row) {
    $table->addRow();
    $table->addCell(1000)->addText($no, [], ['alignment' => Jc::CENTER]);
    $table->addCell(3000)->addText($row['nama_lengkap']);
    $table->addCell(3000)->addText($row['nama_sarana']);
    $table->addCell(2000)->addText($row['tanggal_pinjam']);
    $table->addCell(2000)->addText($row['tanggal_kembali']);
    $table->addCell(1500)->addText($row['jumlah_pinjam'], [], ['alignment' => Jc::CENTER]);
    $table->addCell(2000)->addText($row['status']);
    $no++;
}

// Add signature section
$section->addTextBreak(2);
$section->addText('Bekasi, ' . date('d F Y'), null, ['alignment' => Jc::END]);
$section->addText('Mengetahui,', null, ['alignment' => Jc::END]);
$section->addTextBreak(3);
$section->addText('Kepala Sekolah', null, ['alignment' => Jc::END]);
$section->addText('SMK Telekomunikasi Telesandi Bekasi', null, ['alignment' => Jc::END]);

// Save file
$filename = 'Laporan_Peminjaman_'.date('Y-m-d').'.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');
?> 