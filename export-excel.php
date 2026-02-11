<?php
session_start();

// Cek login admin
if (!isset($_SESSION['admin'])) {
    header("Location: login-admin.php");
    exit();
}

// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Koneksi Database
$conn = mysqli_connect("localhost", "root", "", "dbukk_rifaya");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil semua filter dari URL
$filter_urutan = isset($_GET['urutan']) ? $_GET['urutan'] : 'terbaru';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_tanggal_range = isset($_GET['tanggal_range']) ? $_GET['tanggal_range'] : '';
$filter_nis = isset($_GET['nis']) ? $_GET['nis'] : '';

// Buat WHERE clause
$where = [];
if ($filter_status) {
    if ($filter_status == 'Menunggu') {
        $where[] = "(asp.status = 'Menunggu' OR asp.status IS NULL)";
    } else {
        $where[] = "asp.status = '$filter_status'";
    }
}
if ($filter_kategori) {
    $where[] = "i.id_kategori = '$filter_kategori'";
}
if ($filter_tanggal_range) {
    $dates = explode(' to ', $filter_tanggal_range);
    if (count($dates) == 2) {
        $start_date = date('Y-m-d 00:00:00', strtotime($dates[0]));
        $end_date = date('Y-m-d 23:59:59', strtotime($dates[1]));
        $where[] = "i.tanggal BETWEEN '$start_date' AND '$end_date'";
    } elseif (count($dates) == 1) {
        $start_date = date('Y-m-d 00:00:00', strtotime($dates[0]));
        $end_date = date('Y-m-d 23:59:59', strtotime($dates[0]));
        $where[] = "i.tanggal BETWEEN '$start_date' AND '$end_date'";
    }
}
if ($filter_nis) {
    $filter_nis_escaped = mysqli_real_escape_string($conn, $filter_nis);
    $where[] = "i.nis LIKE '%$filter_nis_escaped%'";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Tentukan urutan berdasarkan parameter
$order_by = "ORDER BY i.tanggal DESC"; // Default: terbaru
if ($filter_urutan == 'terlama') {
    $order_by = "ORDER BY i.tanggal ASC";
}

// Query dengan filter
$query = "SELECT 
            i.id_pelaporan,
            i.nis,
            i.id_kategori,
            i.lokasi,
            i.ket,
            i.tanggal,
            IFNULL(asp.status, 'Menunggu') as status,
            asp.feedback,
            IFNULL(k.ket_kategori, 'Tidak ada kategori') as ket_kategori
          FROM input_aspirasi i
          LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan
          LEFT JOIN kategori k ON i.id_kategori = k.id_kategori
          $where_sql
          $order_by";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error query: " . mysqli_error($conn));
}

// Tanggal cetak
$tanggal_cetak = date('d/m/Y H:i:s');
$filename = 'Laporan_Aspirasi_' . date('Ymd_His') . '.xls';

// Header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Mulai output Excel (HTML Table format yang bisa dibaca Excel)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
        }
        th {
            background-color: #3b82f6;
            color: white;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
            padding: 8px;
        }
        td {
            border: 1px solid #000;
            padding: 5px;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 16pt;
        }
        .subheader {
            text-align: center;
            font-size: 11pt;
        }
        .status-menunggu {
            background-color: #fef3c7;
        }
        .status-proses {
            background-color: #dbeafe;
        }
        .status-selesai {
            background-color: #d1fae5;
        }
    </style>
</head>
<body>
    <table border="1">
        <tr>
            <td colspan="8" class="header">LAPORAN HASIL ASPIRASI SISWA SMKN 12 MALANG</td>
        </tr>
        <tr>
            <td colspan="8" class="subheader">Tanggal Cetak: <?= $tanggal_cetak ?> WIB</td>
        </tr>
        <tr>
            <td colspan="8"></td>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>NIS</th>
            <th>Kategori</th>
            <th>Lokasi</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Feedback</th>
        </tr>
        <?php 
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) { 
            $tanggal = isset($row['tanggal']) ? date('d/m/Y H:i', strtotime($row['tanggal'])) : '-';
            $lokasi = !empty($row['lokasi']) ? htmlspecialchars($row['lokasi']) : '-';
            $keterangan = htmlspecialchars($row['ket']);
            $feedback = !empty($row['feedback']) ? htmlspecialchars($row['feedback']) : 'Belum ada feedback';
            
            $status_class = 'status-' . strtolower($row['status']);
        ?>
        <tr>
            <td align="center"><?= $no++ ?></td>
            <td><?= $tanggal ?></td>
            <td align="center"><?= $row['nis'] ?></td>
            <td><?= $row['ket_kategori'] ?></td>
            <td><?= $lokasi ?></td>
            <td><?= $keterangan ?></td>
            <td align="center" class="<?= $status_class ?>"><?= $row['status'] ?></td>
            <td><?= $feedback ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
<?php
mysqli_close($conn);
?>