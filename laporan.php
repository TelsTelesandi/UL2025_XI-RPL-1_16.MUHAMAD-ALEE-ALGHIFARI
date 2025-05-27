<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .logo {
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .school-address {
            font-size: 14px;
            margin: 5px 0;
        }
        .content {
            margin-top: 30px;
        }
        .signature {
            margin-top: 100px;
            float: right;
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }
        .date {
            text-align: right;
            margin: 20px 0;
        }
        @media print {
            body {
                padding: 0;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <img src="https://smktelekomunikasitelesandi.sch.id/wp-content/uploads/2020/05/logo-300x300.png" alt="Logo SMK" class="logo">
        <div class="school-name">SMK TELEKOMUNIKASI TELESANDI BEKASI</div>
        <div class="school-address">Jl. KH. Mochammad - Mekarsari, Tambun Selatan</div>
        <div class="school-address">Bekasi, Jawa Barat</div>
        <div class="school-address">Telp: (021) XXXX-XXXX | Email: info@smktelekomunikasitelesandi.sch.id</div>
    </div>

    <div class="date">
        Bekasi, <?php echo date('d F Y'); ?>
    </div>

    <div class="content">
        <!-- Isi laporan akan ditampilkan di sini -->
        <?php
        if(isset($_GET['content'])) {
            echo htmlspecialchars($_GET['content']);
        } else {
            echo "<p>Isi laporan akan ditampilkan di sini...</p>";
        }
        ?>
    </div>

    <div class="signature">
        <div>Mengetahui,</div>
        <div class="signature-line"></div>
        <div>Kepala Sekolah</div>
        <div>SMK Telekomunikasi Telesandi Bekasi</div>
    </div>
</body>
</html> 