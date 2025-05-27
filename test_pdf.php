<?php
ob_start();
require 'vendor/autoload.php';

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('SMK Telekomunikasi Telesandi');
$pdf->SetTitle('Laporan Peminjaman Barang');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Get the current directory
$currentDir = dirname(__FILE__);
$logoPath = $currentDir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png';

// Debug information
error_log('Logo path: ' . $logoPath);
error_log('File exists: ' . (file_exists($logoPath) ? 'Yes' : 'No'));

try {
    // Try to add the logo
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 10, 25, 25, 'PNG');
    }
} catch (Exception $e) {
    error_log('Error adding logo: ' . $e->getMessage());
}

// Set font for header
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'SMK TELEKOMUNIKASI TELESANDI', 0, 1, 'C');

// Address
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 7, 'Jl. KH. Mochammad - Mekarsari, Tambun Selatan', 0, 1, 'C');
$pdf->Cell(0, 7, 'Bekasi, Jawa Barat', 0, 1, 'C');
$pdf->Cell(0, 7, 'Telp: (021) XXXX-XXXX | Email: info@smktelekomunikasitelesandi.sch.id', 0, 1, 'C');

// Line
$pdf->Line(15, 45, $pdf->getPageWidth()-15, 45);
$pdf->Ln(15);

// Title
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN PEMINJAMAN BARANG', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 10, 'Periode: ' . date('d F Y'), 0, 1, 'C');

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
$data = [
    ['1', 'Loyd Forger', 'HDMI', '2025-05-26', '2025-05-27', '1', 'selesai'],
    ['2', 'Loyd Forger', 'HDMI', '2025-05-26', '2025-05-27', '1', 'ditolak'],
    ['3', 'Loyd Forger', 'Saklar', '2025-05-26', '2025-05-27', '1', 'selesai'],
    ['4', 'Loyd Forger', 'Saklar', '23 May', '24 May', '3', 'selesai'],
    ['5', 'Muhamad Alee Alghifari', 'Pengki', '23 May', '24 May', '1', 'ditolak'],
    ['6', 'Loyd Forger', 'HDMI', '23 May', '24 May', '5', 'selesai'],
    ['7', 'Keenan Kaen', 'Pengki', '9 Mei', '9 Mei', '1', 'selesai'],
    ['8', 'Jea Lipa', 'Saklar', '6 Mei', '10 Mei', '3', 'selesai'],
    ['9', 'Loyd Forger', 'HDMI', '2 Mei', '2 Mei', '1', 'selesai'],
    ['10', 'Looki Ndihome', 'Mic', '27 April', '28 April', '1', 'selesai']
];

foreach ($data as $row) {
    $pdf->Cell(10, 7, $row[0], 1, 0, 'C');
    $pdf->Cell(40, 7, $row[1], 1, 0, 'L');
    $pdf->Cell(40, 7, $row[2], 1, 0, 'L');
    $pdf->Cell(30, 7, $row[3], 1, 0, 'C');
    $pdf->Cell(30, 7, $row[4], 1, 0, 'C');
    $pdf->Cell(20, 7, $row[5], 1, 0, 'C');
    $pdf->Cell(20, 7, $row[6], 1, 1, 'C');
}

$pdf->Ln(20);

// Signature section
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 7, 'Bekasi, ' . date('d F Y'), 0, 1, 'R');
$pdf->Cell(0, 7, 'Mengetahui,', 0, 1, 'R');
$pdf->Ln(15);
$pdf->Cell(0, 7, 'Kepala Sekolah', 0, 1, 'R');
$pdf->Cell(0, 7, 'SMK Telekomunikasi Telesandi Bekasi', 0, 1, 'R');

// Clean output buffer
ob_end_clean();

// Output the PDF
$pdf->Output('Laporan_Peminjaman_'.date('Y-m-d').'.pdf', 'I');
exit; 