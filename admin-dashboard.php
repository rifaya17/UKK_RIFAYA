<?php
session_start();

// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Cek login admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin-login.php");
    exit();
}

// Mencegah caching halaman (agar tidak bisa back setelah logout)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Koneksi Database
$conn = mysqli_connect("localhost", "root", "", "dbukk_rifaya");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Proses update status - simpan ke tabel aspirasi DAN histori
if (isset($_POST['update_status'])) {
    $id_pelaporan = mysqli_real_escape_string($conn, $_POST['id_pelaporan']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Ambil data dari input_aspirasi
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM input_aspirasi WHERE id_pelaporan='$id_pelaporan'"));
    
    if ($data) {
        // Cek apakah sudah ada di tabel aspirasi
        $cek = mysqli_query($conn, "SELECT * FROM aspirasi WHERE id_pelaporan='$id_pelaporan'");
        
        if (mysqli_num_rows($cek) > 0) {
            // Ambil status lama
            $old_data = mysqli_fetch_assoc($cek);
            $old_status = $old_data['status'];
            
            // Update status di tabel aspirasi
            $update = mysqli_query($conn, "UPDATE aspirasi SET status='$status' WHERE id_pelaporan='$id_pelaporan'");
            if ($update) {
                // Simpan ke histori jika status berubah
                if ($old_status != $status) {
                    mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_lama, nilai_baru, waktu_perubahan) 
                                       VALUES ('$id_pelaporan', 'status', '$old_status', '$status', NOW())");
                }
                $success = "Status berhasil diupdate!";
            } else {
                $error = "Gagal update status: " . mysqli_error($conn);
            }
        } else {
            // Insert ke tabel aspirasi
            $insert = mysqli_query($conn, "INSERT INTO aspirasi (id_pelaporan, id_kategori, status) 
                                          VALUES ('$id_pelaporan', '{$data['id_kategori']}', '$status')");
            if ($insert) {
                // Simpan ke histori - aspirasi masuk
                mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_baru, waktu_perubahan) 
                                   VALUES ('$id_pelaporan', 'masuk', 'Aspirasi masuk ke sistem', '{$data['tanggal']}')");
                // Simpan ke histori - status awal
                mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_baru, waktu_perubahan) 
                                   VALUES ('$id_pelaporan', 'status', '$status', NOW())");
                $success = "Status berhasil diupdate!";
            } else {
                $error = "Gagal insert ke aspirasi: " . mysqli_error($conn);
            }
        }
    }
}

// Proses update feedback DAN status - simpan ke tabel aspirasi bersamaan DAN histori
if (isset($_POST['update_feedback'])) {
    $id_pelaporan = mysqli_real_escape_string($conn, $_POST['id_pelaporan']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $status = mysqli_real_escape_string($conn, $_POST['feedback_status']);
    
    // Ambil data dari input_aspirasi
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM input_aspirasi WHERE id_pelaporan='$id_pelaporan'"));
    
    if ($data) {
        // Cek apakah sudah ada di tabel aspirasi
        $cek = mysqli_query($conn, "SELECT * FROM aspirasi WHERE id_pelaporan='$id_pelaporan'");
        
        if (mysqli_num_rows($cek) > 0) {
            // Ambil data lama
            $old_data = mysqli_fetch_assoc($cek);
            $old_status = $old_data['status'];
            $old_feedback = $old_data['feedback'];
            
            // Update feedback DAN status di tabel aspirasi
            $update = mysqli_query($conn, "UPDATE aspirasi SET feedback='$feedback', status='$status' WHERE id_pelaporan='$id_pelaporan'");
            if ($update) {
                // Simpan ke histori jika status berubah
                if ($old_status != $status) {
                    mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_lama, nilai_baru, waktu_perubahan) 
                                       VALUES ('$id_pelaporan', 'status', '$old_status', '$status', NOW())");
                }
                // Simpan ke histori jika feedback berubah
                if ($old_feedback != $feedback) {
                    $nilai_lama = $old_feedback ? "'$old_feedback'" : "NULL";
                    $nilai_baru = $feedback ? "'$feedback'" : "NULL";
                    mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_lama, nilai_baru, waktu_perubahan) 
                                       VALUES ('$id_pelaporan', 'feedback', $nilai_lama, $nilai_baru, NOW())");
                }
                $success = "Status dan feedback berhasil disimpan!";
            } else {
                $error = "Gagal simpan: " . mysqli_error($conn);
            }
        } else {
            // Insert ke tabel aspirasi dengan feedback DAN status
            $insert = mysqli_query($conn, "INSERT INTO aspirasi (id_pelaporan, id_kategori, status, feedback) 
                                          VALUES ('$id_pelaporan', '{$data['id_kategori']}', '$status', '$feedback')");
            if ($insert) {
                // Simpan ke histori - aspirasi masuk
                mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_baru, waktu_perubahan) 
                                   VALUES ('$id_pelaporan', 'masuk', 'Aspirasi masuk ke sistem', '{$data['tanggal']}')");
                // Simpan ke histori - status
                mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_baru, waktu_perubahan) 
                                   VALUES ('$id_pelaporan', 'status', '$status', NOW())");
                // Simpan ke histori - feedback (jika ada)
                if (!empty($feedback)) {
                    mysqli_query($conn, "INSERT INTO histori_aspirasi (id_pelaporan, jenis_perubahan, nilai_baru, waktu_perubahan) 
                                       VALUES ('$id_pelaporan', 'feedback', '$feedback', NOW())");
                }
                $success = "Status dan feedback berhasil disimpan!";
            } else {
                $error = "Gagal insert: " . mysqli_error($conn);
            }
        }
    }
}

// Filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_tanggal_range = isset($_GET['tanggal_range']) ? $_GET['tanggal_range'] : '';
$filter_nis = isset($_GET['nis']) ? $_GET['nis'] : '';
$filter_urutan = isset($_GET['urutan']) ? $_GET['urutan'] : 'terbaru';

// Hitung total berdasarkan status dengan filter yang aktif
$where_count = [];
if ($filter_nis) {
    $filter_nis_escaped = mysqli_real_escape_string($conn, $filter_nis);
    $where_count[] = "i.nis LIKE '%$filter_nis_escaped%'";
}
if ($filter_kategori) {
    $where_count[] = "i.id_kategori = '$filter_kategori'";
}
if ($filter_tanggal_range) {
    $dates = explode(' to ', $filter_tanggal_range);
    if (count($dates) == 2) {
        $start_date = date('Y-m-d 00:00:00', strtotime($dates[0]));
        $end_date = date('Y-m-d 23:59:59', strtotime($dates[1]));
        $where_count[] = "i.tanggal BETWEEN '$start_date' AND '$end_date'";
    } elseif (count($dates) == 1) {
        $start_date = date('Y-m-d 00:00:00', strtotime($dates[0]));
        $end_date = date('Y-m-d 23:59:59', strtotime($dates[0]));
        $where_count[] = "i.tanggal BETWEEN '$start_date' AND '$end_date'";
    }
}

$where_count_sql = count($where_count) > 0 ? "WHERE " . implode(" AND ", $where_count) : "";

// Hitung total berdasarkan filter status
if ($filter_status) {
    if ($filter_status == 'Menunggu') {
        $total_menunggu_input = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM input_aspirasi i 
            WHERE NOT EXISTS (SELECT 1 FROM aspirasi a WHERE a.id_pelaporan = i.id_pelaporan)
            " . ($where_count_sql ? str_replace("WHERE", "AND", $where_count_sql) : "") . "
        "))['total'];

        $total_menunggu_aspirasi = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM input_aspirasi i
            LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan
            WHERE asp.status = 'Menunggu'
            " . ($where_count_sql ? str_replace("WHERE", "AND", $where_count_sql) : "") . "
        "))['total'];

        $total_menunggu = $total_menunggu_input + $total_menunggu_aspirasi;
        $total_proses = 0;
        $total_selesai = 0;
    } elseif ($filter_status == 'Proses') {
        $total_menunggu = 0;
        $total_proses = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM input_aspirasi i
            LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan
            WHERE asp.status = 'Proses'
            " . ($where_count_sql ? str_replace("WHERE", "AND", $where_count_sql) : "") . "
        "))['total'];
        $total_selesai = 0;
    } elseif ($filter_status == 'Selesai') {
        $total_menunggu = 0;
        $total_proses = 0;
        $total_selesai = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM input_aspirasi i
            LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan
            WHERE asp.status = 'Selesai'
            " . ($where_count_sql ? str_replace("WHERE", "AND", $where_count_sql) : "") . "
        "))['total'];
    }
} else {
    $total_menunggu_input = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM input_aspirasi i 
        WHERE NOT EXISTS (SELECT 1 FROM aspirasi a WHERE a.id_pelaporan = i.id_pelaporan)
        " . ($where_count_sql ? str_replace("WHERE", "AND", $where_count_sql) : "") . "
    "))['total'];

    $total_menunggu_aspirasi = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM input_aspirasi i
        LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan
        WHERE asp.status = 'Menunggu'
        " . ($where_count_sql ? str_replace("WHERE", "AND", $where_count_sql) : "") . "
    "))['total'];

    $total_menunggu = $total_menunggu_input + $total_menunggu_aspirasi;

    $total_proses = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM input_aspirasi i
        LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan
        WHERE asp.status = 'Proses'
        " . ($where_count_sql ? str_replace("WHERE", "AND", $where_count_sql) : "") . "
    "))['total'];

    $total_selesai = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM input_aspirasi i
        LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan
        WHERE asp.status = 'Selesai'
        " . ($where_count_sql ? str_replace("WHERE", "AND", $where_count_sql) : "") . "
    "))['total'];
}

$total_aspirasi = $total_menunggu + $total_proses + $total_selesai;

// Ambil data kategori untuk dropdown
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori ORDER BY ket_kategori ASC");

// Query dengan filter
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

// Tentukan urutan
$order_by = $filter_urutan == 'terlama' ? "ORDER BY i.tanggal ASC" : "ORDER BY i.tanggal DESC";

// PAGINATION
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Hitung total data
$count_query = "SELECT COUNT(*) as total
          FROM input_aspirasi i
          LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan
          LEFT JOIN kategori k ON i.id_kategori = k.id_kategori
          $where_sql";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Query data
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
          $order_by
          LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error query: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Poppins', sans-serif;
}

body {
  background: #f1f5f9;
  min-height: 100vh;
  padding-top: 80px;
}

/* Fixed Header */
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: linear-gradient(135deg, #1e3a8a, #2563eb);
  color: white;
  padding: 20px 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 999;
}

.header h1 {
  font-size: 24px;
}

.logout-btn {
  background: rgba(255,255,255,0.2);
  color: white;
  padding: 10px 20px;
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.3s;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 500;
}

.logout-btn:hover {
  background: rgba(255,255,255,0.3);
}

.scroll-top {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #2563eb, #1e40af);
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
  transition: all 0.3s ease-in-out;
  z-index: 998;
  opacity: 0;
  visibility: hidden;
  transform: translateY(100px);
}

.scroll-top:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
}

.scroll-top.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.container {
  max-width: 1400px;
  margin: 30px auto;
  padding: 0 20px;
}

.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  display: flex;
  align-items: center;
  gap: 20px;
  transition: transform 0.3s;
}

.card:hover {
  transform: translateY(-5px);
}

.card-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.card-icon.total {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
}

.card-icon.menunggu {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.card-icon.proses {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.card-icon.selesai {
  background: linear-gradient(135deg, #10b981, #059669);
}

.card-content h3 {
  font-size: 14px;
  color: #64748b;
  margin-bottom: 5px;
  font-weight: 500;
}

.card-content p {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
}

.filter-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  padding: 20px 25px;
  margin-bottom: 20px;
}

.filter-section h3 {
  margin-bottom: 15px;
  color: #1e3a8a;
  font-size: 16px;
}

.filter-form {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  align-items: end;
}

.filter-group {
  flex: 1;
  min-width: 200px;
}

.filter-group label {
  display: block;
  margin-bottom: 6px;
  color: #475569;
  font-size: 14px;
  font-weight: 500;
}

.filter-group select,
.filter-group input[type="text"] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  background: white;
}

.filter-group select {
  cursor: pointer;
}

.filter-group input[type="text"]:focus {
  outline: none;
  border-color: #2563eb;
}

.filter-btn {
  padding: 10px 24px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
  height: 42px;
}

.filter-btn:hover {
  background: #1e40af;
}

.reset-btn {
  padding: 10px 20px;
  background: #64748b;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
  text-decoration: none;
  display: inline-block;
  height: 42px;
  line-height: 22px;
}

.reset-btn:hover {
  background: #475569;
}

.export-btn {
  padding: 10px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  text-decoration: none;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 2px solid;
  font-weight: 500;
  min-width: 45px;
}

.excel-btn {
  background: #107C41;
  color: white;
  border-color: #107C41;
}

.excel-btn:hover {
  background: #0D5F31;
  border-color: #0D5F31;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 124, 65, 0.3);
}

.pdf-btn {
  background: #DC2626;
  color: white;
  border-color: #DC2626;
}

.pdf-btn:hover {
  background: #B91C1C;
  border-color: #B91C1C;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.sort-btn-icon {
  padding: 10px 14px;
  background: #2563eb;
  color: white;
  border: 2px solid #2563eb;
  border-radius: 8px;
  cursor: pointer;
  font-size: 18px;
  text-decoration: none;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 45px;
}

.sort-btn-icon:hover {
  background: #1e40af;
  border-color: #1e40af;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.table-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  padding: 25px;
}

.table-section h2 {
  margin-bottom: 20px;
  color: #1e3a8a;
  font-size: 20px;
}

.table-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.table-wrapper::-webkit-scrollbar {
  height: 8px;
}

.table-wrapper::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

table {
  width: 100%;
  border-collapse: collapse;
}

table thead {
  background: #f8fafc;
}

table th {
  padding: 15px;
  text-align: left;
  font-weight: 600;
  color: #475569;
  font-size: 14px;
  border-bottom: 2px solid #e2e8f0;
}

table td {
  padding: 15px;
  color: #334155;
  font-size: 14px;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: top;
}

table tbody tr:hover {
  background: #f8fafc;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.menunggu {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.proses {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.selesai {
  background: #d1fae5;
  color: #065f46;
}

.histori-btn {
  padding: 8px 16px;
  background: #8b5cf6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  transition: background 0.3s;
  white-space: nowrap;
}

.histori-btn:hover {
  background: #7c3aed;
}

.feedback-btn {
  padding: 8px 16px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  transition: background 0.3s;
  white-space: nowrap;
}

.feedback-btn:hover {
  background: #059669;
}

.delete-btn {
  padding: 8px 16px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  transition: background 0.3s;
  white-space: nowrap;
}

.delete-btn:hover {
  background: #dc2626;
}

.action-buttons {
  display: flex;
  flex-direction: row;
  gap: 5px;
  justify-content: center;
}

.alert {
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert.success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #86efac;
}

.alert.error {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}

.ket-text {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  white-space: normal;
}

.feedback-text {
  max-width: 250px;
  font-size: 13px;
  color: #059669;
  font-style: italic;
  margin-top: 5px;
}

.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  align-items: center;
  justify-content: center;
}

.modal.show {
  display: flex;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 12px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-content h3 {
  margin-bottom: 15px;
  color: #1e3a8a;
}

.modal-content .form-group {
  margin-bottom: 15px;
}

.modal-content label {
  display: block;
  margin-bottom: 6px;
  color: #475569;
  font-size: 14px;
  font-weight: 500;
}

.modal-content select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  margin-bottom: 10px;
}

.modal-content textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  resize: vertical;
  min-height: 100px;
  margin-bottom: 15px;
  font-family: 'Poppins', sans-serif;
}

.modal-buttons {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.close-btn {
  padding: 10px 20px;
  background: #64748b;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
}

.close-btn:hover {
  background: #475569;
}

.clear-feedback-btn {
  padding: 10px 20px;
  background: #f59e0b;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
}

.clear-feedback-btn:hover {
  background: #d97706;
}

.update-btn {
  padding: 10px 20px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
}

.update-btn:hover {
  background: #1e40af;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 25px;
}

.pagination a, .pagination span {
  padding: 10px 16px;
  border-radius: 8px;
  background: white;
  color: #1e3a8a;
  text-decoration: none;
  border: 1px solid #e2e8f0;
  transition: all 0.3s;
  font-weight: 500;
}

.pagination a:hover {
  background: #f1f5f9;
  color: #1e3a8a;
}

.pagination .active {
  background: #2563eb;
  color: white;
  border-color: #2563eb;
}

.pagination .disabled {
  color: #cbd5e1;
  cursor: not-allowed;
}

.alert-popup {
  position: fixed;
  top: 100px;
  right: 30px;
  padding: 15px 25px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 10px;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s, visibility 0.3s;
}

.alert-popup.show {
  opacity: 1;
  visibility: visible;
}

.alert-popup.success {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.alert-popup.error {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

/* Modal Histori */
.histori-modal {
  display: none;
  position: fixed;
  z-index: 1001;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  align-items: center;
  justify-content: center;
}

.histori-modal.show {
  display: flex;
}

.histori-modal-content {
  background: white;
  padding: 30px;
  border-radius: 12px;
  max-width: 700px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.histori-modal-content h3 {
  margin-bottom: 20px;
  color: #1e3a8a;
  display: flex;
  align-items: center;
  gap: 10px;
}

.histori-timeline {
  position: relative;
  padding-left: 0;
  margin-top: 20px;
}

.histori-item {
  position: relative;
  margin-bottom: 25px;
  padding: 15px;
  background: #f8fafc;
  border-radius: 8px;
  border-left: 3px solid #8b5cf6;
}

.histori-item.masuk {
  border-left-color: #10b981;
}

.histori-item.status {
  border-left-color: #3b82f6;
}

.histori-item.feedback {
  border-left-color: #f59e0b;
}

.histori-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.histori-type {
  font-weight: 600;
  color: #1e3a8a;
  display: flex;
  align-items: center;
  gap: 8px;
}

.histori-type i {
  font-size: 16px;
}

.histori-time {
  font-size: 12px;
  color: #64748b;
}

.histori-detail {
  font-size: 13px;
  color: #475569;
  line-height: 1.6;
}

.histori-detail .old-value {
  text-decoration: line-through;
  color: #ef4444;
  display: block;
  margin-bottom: 5px;
}

.histori-detail .new-value {
  color: #10b981;
  font-weight: 500;
}

.histori-close-btn {
  padding: 10px 20px;
  background: #64748b;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
  margin-top: 20px;
  width: 100%;
}

.histori-close-btn:hover {
  background: #475569;
}

.no-histori {
  text-align: center;
  padding: 40px;
  color: #94a3b8;
}

.no-histori i {
  font-size: 48px;
  margin-bottom: 15px;
  display: block;
}

/* Responsive */
@media (max-width: 768px) {
  .header {
    padding: 12px 15px;
  }
  
  .header h1 {
    font-size: 18px;
  }
  
  .logout-btn {
    padding: 8px 16px;
    font-size: 13px;
  }
  
  body {
    padding-top: 70px;
  }
  
  .container {
    padding: 15px;
  }
  
  .cards {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .card {
    padding: 20px;
  }
  
  .card-content h3 {
    font-size: 14px;
  }
  
  .card-content p {
    font-size: 28px;
  }
  
  .filter-section {
    padding: 15px;
  }
  
  .filter-section h3 {
    font-size: 15px;
  }
  
  .filter-form {
    flex-direction: column;
    gap: 12px;
  }
  
  .filter-group {
    width: 100%;
    min-width: 100%;
  }
  
  .filter-btn, .reset-btn {
    width: 100%;
    height: auto;
    padding: 12px 20px;
  }
  
  .table-section {
    padding: 15px;
  }
  
  .table-section h2 {
    font-size: 16px;
  }
  
  .table-section > div:first-child {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 12px;
  }
  
  .table-section h2 {
    margin-bottom: 0 !important;
  }
  
  .table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  table {
    min-width: 1000px;
  }
  
  table th,
  table td {
    font-size: 13px;
    padding: 12px 10px;
  }
  
  .ket-text {
    max-width: 150px;
  }
  
  .feedback-text {
    max-width: 150px;
    font-size: 12px;
  }
  
  .feedback-btn,
  .histori-btn {
    font-size: 12px;
    padding: 6px 12px;
  }
  
  .status-badge {
    font-size: 11px;
    padding: 4px 8px;
  }
  
  .table-section::after {
    content: '← Geser untuk melihat lebih banyak →';
    display: block;
    text-align: center;
    padding: 10px;
    color: #64748b;
    font-size: 12px;
    font-style: italic;
    background: #f8fafc;
    border-radius: 0 0 12px 12px;
  }
  
  .alert {
    font-size: 13px;
    padding: 12px;
  }
  
  .modal-content {
    padding: 20px;
    width: 95%;
  }
  
  .modal-content h3 {
    font-size: 16px;
  }
  
  .modal-buttons {
    flex-direction: column;
  }
  
  .modal-buttons button {
    width: 100%;
  }

  .scroll-top {
    bottom: 20px;
    right: 20px;
    width: 45px;
    height: 45px;
    font-size: 18px;
  }
  
  .pagination {
    gap: 5px;
    margin-top: 20px;
    flex-wrap: wrap;
  }
  
  .pagination a, .pagination span {
    padding: 8px 12px;
    font-size: 14px;
    min-width: 40px;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .header {
    padding: 10px 12px;
  }
  
  .header h1 {
    font-size: 13px;
  }
  
  .logout-btn {
    padding: 7px 12px;
    font-size: 11px;
  }
  
  body {
    padding-top: 55px;
  }
  
  .card-icon {
    width: 50px;
    height: 50px;
    font-size: 22px;
  }
  
  .card-content h3 {
    font-size: 13px;
  }
  
  .card-content p {
    font-size: 24px;
  }
  
  .filter-section h3 {
    font-size: 14px;
  }
  
  .filter-group label {
    font-size: 13px;
  }
  
  .filter-group select,
  .filter-group input[type="text"] {
    font-size: 13px;
    padding: 9px 10px;
  }
  
  .filter-btn, .reset-btn {
    font-size: 13px;
  }
  
  .table-section h2 {
    font-size: 15px;
  }
  
  .alert-popup {
    right: 15px;
    left: 15px;
    padding: 12px 15px;
    font-size: 13px;
  }
  
  .modal-content {
    padding: 18px;
  }
  
  .modal-content h3 {
    font-size: 15px;
  }
  
  .modal-content label {
    font-size: 13px;
  }
  
  .modal-content select,
  .modal-content textarea {
    font-size: 13px;
  }
  
  .close-btn, .update-btn {
    font-size: 13px;
    padding: 10px 16px;
  }

  .pagination {
    gap: 4px;
  }
  
  .pagination a, .pagination span {
    padding: 6px 10px;
    font-size: 13px;
    min-width: 36px;
  }
}
</style>
</head>
<body>

<div class="header">
  <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
  <a href="#" onclick="confirmLogout(event)" class="logout-btn">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</div>

<button class="scroll-top" id="scrollTopBtn" onclick="scrollToTop()">
  <i class="fas fa-arrow-up"></i>
</button>

<div id="alertPopup" class="alert-popup">
  <i class="fas fa-check-circle"></i>
  <span id="alertMessage"></span>
</div>

<div class="container">
  
  <?php if (isset($success)) { ?>
    <div class="alert success">
      <i class="fas fa-check-circle"></i> <?= $success ?>
    </div>
  <?php } ?>
  
  <?php if (isset($error)) { ?>
    <div class="alert error">
      <i class="fas fa-exclamation-circle"></i> <?= $error ?>
    </div>
  <?php } ?>
  
  <?php 
  $filter_aktif = $filter_nis || $filter_kategori || $filter_tanggal_range;
  ?>
  
  <?php if ($filter_aktif) { ?>
    <div class="alert" style="background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd;">
      <i class="fas fa-info-circle"></i> 
      Filter aktif: 
      <?php if ($filter_nis) echo "NIS: <strong>$filter_nis</strong> "; ?>
      <?php if ($filter_kategori) {
          $kat_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ket_kategori FROM kategori WHERE id_kategori='$filter_kategori'"));
          echo "Kategori: <strong>{$kat_name['ket_kategori']}</strong> ";
      } ?>
      <?php if ($filter_tanggal_range) {
          echo "Tanggal: <strong>$filter_tanggal_range</strong> ";
      } ?>
      - Menampilkan data sesuai filter
    </div>
  <?php } ?>
  
  <div class="cards">
    <div class="card">
      <div class="card-icon total">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <div class="card-content">
        <h3>Total Aspirasi</h3>
        <p><?= $total_aspirasi ?></p>
        <?php if ($filter_aktif) { ?>
          <small style="color: #64748b; font-size: 11px;">dari filter</small>
        <?php } ?>
      </div>
    </div>
    
    <div class="card">
      <div class="card-icon menunggu">
        <i class="fas fa-clock"></i>
      </div>
      <div class="card-content">
        <h3>Menunggu</h3>
        <p><?= $total_menunggu ?></p>
        <?php if ($filter_aktif) { ?>
          <small style="color: #64748b; font-size: 11px;">dari filter</small>
        <?php } ?>
      </div>
    </div>
    
    <div class="card">
      <div class="card-icon proses">
        <i class="fas fa-spinner"></i>
      </div>
      <div class="card-content">
        <h3>Proses Perbaikan</h3>
        <p><?= $total_proses ?></p>
        <?php if ($filter_aktif) { ?>
          <small style="color: #64748b; font-size: 11px;">dari filter</small>
        <?php } ?>
      </div>
    </div>
    
    <div class="card">
      <div class="card-icon selesai">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="card-content">
        <h3>Selesai</h3>
        <p><?= $total_selesai ?></p>
        <?php if ($filter_aktif) { ?>
          <small style="color: #64748b; font-size: 11px;">dari filter</small>
        <?php } ?>
      </div>
    </div>
  </div>
  
  <div class="filter-section">
    <h3><i class="fas fa-filter"></i> Filter Aspirasi</h3>
    <form method="GET" class="filter-form">
      <div class="filter-group">
        <label for="status">Status</label>
        <select name="status" id="status">
          <option value="">Semua Status</option>
          <option value="Menunggu" <?= $filter_status == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
          <option value="Proses" <?= $filter_status == 'Proses' ? 'selected' : '' ?>>Proses</option>
          <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
        </select>
      </div>
      
      <div class="filter-group">
        <label for="kategori">Kategori</label>
        <select name="kategori" id="kategori">
          <option value="">Semua Kategori</option>
          <?php 
          mysqli_data_seek($kategori_list, 0);
          while ($kat = mysqli_fetch_assoc($kategori_list)) { 
          ?>
            <option value="<?= $kat['id_kategori'] ?>" <?= $filter_kategori == $kat['id_kategori'] ? 'selected' : '' ?>>
              <?= $kat['ket_kategori'] ?>
            </option>
          <?php } ?>
        </select>
      </div>
      
      <div class="filter-group">
        <label for="tanggal_range">Rentang Tanggal</label>
        <input type="text" name="tanggal_range" id="tanggal_range" 
               placeholder="Pilih rentang tanggal..." 
               value="<?= htmlspecialchars($filter_tanggal_range) ?>" readonly>
      </div>
      
      <div class="filter-group">
        <label for="nis">Cari NIS</label>
        <input type="text" name="nis" id="nis" placeholder="Masukkan NIS..." value="<?= htmlspecialchars($filter_nis) ?>">
      </div>
      
      <button type="submit" class="filter-btn">
        <i class="fas fa-search"></i> Filter
      </button>
      <a href="admin-dashboard.php" class="reset-btn">
        <i class="fas fa-redo"></i> Reset
      </a>
    </form>
  </div>
  
  <div class="table-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 style="margin: 0;">
        <i class="fas fa-list"></i> Daftar Aspirasi Masuk
      </h2>
      
      <div style="display: flex; gap: 10px; align-items: center;">
        <?php 
        $export_params = [
            'urutan' => $filter_urutan,
            'status' => $filter_status,
            'kategori' => $filter_kategori,
            'tanggal_range' => $filter_tanggal_range,
            'nis' => $filter_nis
        ];
        $export_params = array_filter($export_params);
        ?>
        <a href="export-excel.php?<?= http_build_query($export_params) ?>" class="export-btn excel-btn" title="Export ke Excel">
          <i class="fas fa-file-excel"></i>
        </a>
        <a href="export-pdf.php?<?= http_build_query($export_params) ?>" class="export-btn pdf-btn" title="Export ke PDF" target="_blank">
          <i class="fas fa-file-pdf"></i>
        </a>
        
        <?php 
        $next_urutan = $filter_urutan == 'terbaru' ? 'terlama' : 'terbaru';
        $params_sort = $_GET;
        $params_sort['urutan'] = $next_urutan;
        ?>
        
        <a href="?<?= http_build_query($params_sort) ?>" 
           class="sort-btn-icon" 
           title="Saat ini: <?= $filter_urutan == 'terbaru' ? 'Terbaru' : 'Terlama' ?> - Klik untuk ubah">
          <i class="fas fa-sort-amount-<?= $filter_urutan == 'terbaru' ? 'down-alt' : 'up-alt' ?>"></i>
        </a>
      </div>
    </div>
    
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>NIS</th>
            <th>Kategori</th>
            <th>Lokasi</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Feedback</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $no = $start + 1;
          while ($row = mysqli_fetch_assoc($result)) { 
            $status_class = strtolower($row['status']);
          ?>
          <tr id="row-<?= $row['id_pelaporan'] ?>">
            <td><?= $no++ ?></td>
            <td><?= isset($row['tanggal']) ? date('d/m/Y H:i', strtotime($row['tanggal'])) : '-' ?></td>
            <td><?= $row['nis'] ?></td>
            <td><?= $row['ket_kategori'] ?></td>
            <td><?= $row['lokasi'] ?? '-' ?></td>
            <td>
              <div class="ket-text" title="<?= htmlspecialchars($row['ket']) ?>">
                <?= htmlspecialchars($row['ket']) ?>
              </div>
            </td>
            <td>
              <span class="status-badge <?= $status_class ?>" id="status-badge-<?= $row['id_pelaporan'] ?>">
                <?= $row['status'] ?>
              </span>
            </td>
            <td>
              <div id="feedback-cell-<?= $row['id_pelaporan'] ?>">
              <?php if (!empty($row['feedback'])) { ?>
                <div class="feedback-text" data-feedback="<?= htmlspecialchars($row['feedback']) ?>">
                  <i class="fas fa-comment-dots"></i> <?= htmlspecialchars($row['feedback']) ?>
                </div>
              <?php } else { ?>
                <span style="color: #94a3b8; font-size: 13px;" data-feedback="">Belum ada feedback</span>
              <?php } ?>
              </div>
            </td>
            <td>
              <div class="action-buttons">
                <button type="button" class="histori-btn" onclick="openHistoriModal('<?= $row['id_pelaporan'] ?>')" title="Lihat Histori">
                  <i class="fas fa-history"></i>
                </button>
                <button type="button" class="feedback-btn" onclick="openModal('<?= $row['id_pelaporan'] ?>')" title="Edit Status & Feedback">
                  <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="delete-btn" onclick="confirmDelete('<?= $row['id_pelaporan'] ?>')" title="Hapus">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php } ?>
          
          <?php if (mysqli_num_rows($result) == 0) { ?>
          <tr>
            <td colspan="9" style="text-align: center; padding: 30px; color: #94a3b8;">
              <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
              Tidak ada aspirasi yang sesuai dengan filter
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_pages > 1) { ?>
    <div class="pagination">
      
      <?php if ($page > 1) { 
        $params_prev = $_GET;
        $params_prev['page'] = $page - 1;
      ?>
        <a href="?<?= http_build_query($params_prev) ?>">
          <i class="fas fa-chevron-left"></i>
        </a>
      <?php } else { ?>
        <span class="disabled">
          <i class="fas fa-chevron-left"></i>
        </span>
      <?php } ?>
      
      <?php 
      $start_page = max(1, $page - 2);
      $end_page = min($total_pages, $page + 2);
      
      for ($i = $start_page; $i <= $end_page; $i++) { 
        $params_page = $_GET;
        $params_page['page'] = $i;
      ?>
        <?php if ($i == $page) { ?>
          <span class="active"><?= $i ?></span>
        <?php } else { ?>
          <a href="?<?= http_build_query($params_page) ?>"><?= $i ?></a>
        <?php } ?>
      <?php } ?>
      
      <?php if ($page < $total_pages) { 
        $params_next = $_GET;
        $params_next['page'] = $page + 1;
      ?>
        <a href="?<?= http_build_query($params_next) ?>">
          <i class="fas fa-chevron-right"></i>
        </a>
      <?php } else { ?>
        <span class="disabled">
          <i class="fas fa-chevron-right"></i>
        </span>
      <?php } ?>
      
    </div>
    
    <div style="text-align: center; margin-top: 15px; color: #64748b; font-size: 13px;">
      Menampilkan <?= $start + 1 ?> - <?= min($start + $limit, $total_records) ?> dari <?= $total_records ?> data
    </div>
    <?php } ?>

  </div>
  
</div>

<!-- Modal Feedback -->
<div id="feedbackModal" class="modal">
  <div class="modal-content">
    <h3><i class="fas fa-comment-dots"></i> Update Status & Feedback</h3>
    <form id="feedbackForm" onsubmit="submitFeedback(event); return false;">
      <input type="hidden" name="id_pelaporan" id="modal_id_pelaporan">
      
      <div class="form-group">
        <label for="feedback_status">Status Aspirasi</label>
        <select name="feedback_status" id="feedback_status" required>
          <option value="Menunggu">Menunggu</option>
          <option value="Proses">Proses Perbaikan</option>
          <option value="Selesai">Selesai</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="modal_feedback">Feedback untuk Siswa (Opsional)</label>
        <textarea name="feedback" id="modal_feedback" placeholder="Tulis feedback untuk siswa..."></textarea>
      </div>
      
      <div class="modal-buttons">
        <button type="button" class="close-btn" onclick="closeModal()">
          <i class="fas fa-times"></i> Batal
        </button>
        <button type="button" class="clear-feedback-btn" onclick="clearFeedback()" id="clearFeedbackBtn" style="display: none;">
          <i class="fas fa-eraser"></i> Hapus Feedback
        </button>
        <button type="submit" name="update_feedback" class="update-btn">
          <i class="fas fa-save"></i> Simpan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Histori -->
<div id="historiModal" class="histori-modal">
  <div class="histori-modal-content">
    <h3>
      <i class="fas fa-history"></i>
      Histori Perubahan Aspirasi
    </h3>
    
    <div class="histori-timeline" id="historiTimeline">
      <!-- Timeline items akan dimuat di sini via JavaScript -->
    </div>
    
    <button type="button" class="histori-close-btn" onclick="closeHistoriModal()">
      <i class="fas fa-times"></i> Tutup
    </button>
  </div>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// Logout confirmation
function confirmLogout(event) {
  event.preventDefault();
  
  Swal.fire({
    title: 'Yakin ingin keluar?',
    text: "Anda akan keluar dari dashboard admin",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Ya, Keluar',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'logout.php';
    }
  });
}

// Scroll to top
window.onscroll = function() {
  const scrollBtn = document.getElementById('scrollTopBtn');
  if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
    scrollBtn.classList.add('show');
  } else {
    scrollBtn.classList.remove('show');
  }
};

function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

// Modal functions
function openModal(id) {
  let feedback = '';
  let currentStatus = 'Menunggu';
  
  const feedbackCell = document.getElementById('feedback-cell-' + id);
  if (feedbackCell) {
    const feedbackElement = feedbackCell.querySelector('[data-feedback]');
    if (feedbackElement) {
      feedback = feedbackElement.getAttribute('data-feedback');
    }
  }
  
  const statusBadge = document.getElementById('status-badge-' + id);
  if (statusBadge) {
    currentStatus = statusBadge.textContent.trim();
  }
  
  document.getElementById('modal_id_pelaporan').value = id;
  document.getElementById('modal_feedback').value = feedback;
  document.getElementById('feedback_status').value = currentStatus;
  
  const clearBtn = document.getElementById('clearFeedbackBtn');
  if (feedback && feedback.trim() !== '') {
    clearBtn.style.display = 'inline-block';
  } else {
    clearBtn.style.display = 'none';
  }
  
  document.getElementById('feedbackModal').classList.add('show');
}

function closeModal() {
  document.getElementById('feedbackModal').classList.remove('show');
}

window.onclick = function(event) {
  const modal = document.getElementById('feedbackModal');
  if (event.target == modal) {
    closeModal();
  }
  
  const historiModal = document.getElementById('historiModal');
  if (event.target == historiModal) {
    closeHistoriModal();
  }
}

// AJAX Submit
function submitFeedback(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  
  const id = formData.get('id_pelaporan');
  const status = formData.get('feedback_status');
  const feedback = formData.get('feedback');
  
  fetch('ajax-update.php', { 
    method: 'POST', 
    body: formData 
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const badge = document.getElementById('status-badge-' + id);
      if (badge) {
        badge.className = 'status-badge ' + status.toLowerCase();
        badge.textContent = status;
      }
      
      const feedbackCell = document.getElementById('feedback-cell-' + id);
      if (feedbackCell) {
        if (feedback && feedback.trim() !== '') {
          feedbackCell.innerHTML = 
            '<div class="feedback-text" data-feedback="' + feedback + '"><i class="fas fa-comment-dots"></i> ' + feedback + '</div>';
        } else {
          feedbackCell.innerHTML = 
            '<span style="color: #94a3b8; font-size: 13px;" data-feedback="">Belum ada feedback</span>';
        }
      }
      
      closeModal();
      showAlert(data.message, 'success');
      
      setTimeout(function() {
        const row = document.getElementById('row-' + id);
        if (row) {
          row.scrollIntoView({ behavior: 'smooth', block: 'center' });
          row.style.backgroundColor = '#dbeafe';
          setTimeout(function() {
            row.style.transition = 'background-color 1s';
            row.style.backgroundColor = '';
          }, 1000);
        }
      }, 300);
    } else {
      showAlert(data.message, 'error');
    }
  })
  .catch(function(err) {
    console.error('Error:', err);
    showAlert('Terjadi kesalahan!', 'error');
  });
}

// Delete confirmation
function confirmDelete(id) {
  Swal.fire({
    title: 'Hapus Aspirasi?',
    text: "Data yang dihapus tidak dapat dikembalikan!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      deleteAspirasi(id);
    }
  });
}

function deleteAspirasi(id) {
  const formData = new FormData();
  formData.append('id_pelaporan', id);
  
  fetch('ajax-delete.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const row = document.getElementById('row-' + id);
      if (row) {
        row.remove();
      }
      
      showAlert(data.message, 'success');
      setTimeout(function() {
        location.reload();
      }, 1500);
    } else {
      showAlert(data.message, 'error');
    }
  })
  .catch(function(err) {
    console.error('Error:', err);
    showAlert('Terjadi kesalahan saat menghapus!', 'error');
  });
}

// Clear feedback
function clearFeedback() {
  const id = document.getElementById('modal_id_pelaporan').value;
  
  Swal.fire({
    title: 'Hapus Feedback?',
    text: "Feedback akan dihapus dan kembali menjadi 'Belum ada feedback'",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#f59e0b',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append('id_pelaporan', id);
      
      fetch('ajax-clear-feedback.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const feedbackCell = document.getElementById('feedback-cell-' + id);
          if (feedbackCell) {
            feedbackCell.innerHTML = 
              '<span style="color: #94a3b8; font-size: 13px;" data-feedback="">Belum ada feedback</span>';
          }
          
          document.getElementById('modal_feedback').value = '';
          document.getElementById('clearFeedbackBtn').style.display = 'none';
          
          closeModal();
          showAlert(data.message, 'success');
          
          setTimeout(function() {
            const row = document.getElementById('row-' + id);
            if (row) {
              row.scrollIntoView({ behavior: 'smooth', block: 'center' });
              row.style.backgroundColor = '#fef3c7';
              setTimeout(function() {
                row.style.transition = 'background-color 1s';
                row.style.backgroundColor = '';
              }, 1000);
            }
          }, 300);
        } else {
          showAlert(data.message, 'error');
        }
      })
      .catch(function(err) {
        console.error('Error:', err);
        showAlert('Terjadi kesalahan saat menghapus feedback!', 'error');
      });
    }
  });
}

// Show alert
function showAlert(msg, type) {
  const alertPopup = document.getElementById('alertPopup');
  const alertMessage = document.getElementById('alertMessage');
  const icon = alertPopup.querySelector('i');
  
  alertMessage.textContent = msg;
  alertPopup.className = 'alert-popup ' + type;
  icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
  
  alertPopup.classList.add('show');
  setTimeout(function() {
    alertPopup.classList.remove('show');
  }, 3000);
}

// Flatpickr
flatpickr("#tanggal_range", {
  mode: "range",
  dateFormat: "Y-m-d",
  locale: {
    firstDayOfWeek: 1,
    weekdays: {
      shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
      longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
    },
    months: {
      shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
      longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    },
  }
});

// ============= HISTORI FUNCTIONS =============

function openHistoriModal(id_pelaporan) {
  console.log('Opening histori modal for ID:', id_pelaporan);
  
  const modal = document.getElementById('historiModal');
  const timelineContainer = document.getElementById('historiTimeline');
  
  if (!modal || !timelineContainer) {
    console.error('Modal atau timeline container tidak ditemukan');
    alert('Error: Element modal tidak ditemukan');
    return;
  }
  
  timelineContainer.innerHTML = `
    <div class="no-histori">
      <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #8b5cf6;"></i>
      <p style="margin-top: 15px;">Memuat histori...</p>
    </div>
  `;
  modal.classList.add('show');
  
  const formData = new FormData();
  formData.append('id_pelaporan', id_pelaporan);
  
  fetch('ajax-get-histori.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    console.log('Response status:', response.status);
    console.log('Response headers:', response.headers);
    
    if (!response.ok) {
      throw new Error('HTTP error! status: ' + response.status);
    }
    
    const contentType = response.headers.get('content-type');
    console.log('Content-Type:', contentType);
    
    if (!contentType || !contentType.includes('application/json')) {
      return response.text().then(text => {
        console.error('Response bukan JSON:', text);
        throw new Error('Response bukan JSON. Kemungkinan ada error PHP.');
      });
    }
    
    return response.json();
  })
  .then(data => {
    console.log('Data histori diterima:', data);
    
    if (data.success && data.histori && data.histori.length > 0) {
      let html = '';
      data.histori.forEach(item => {
        const waktu = formatWaktu(item.waktu_perubahan);
        const iconClass = getIconClass(item.jenis_perubahan);
        const itemClass = item.jenis_perubahan;
        
        html += `
          <div class="histori-item ${itemClass}">
            <div class="histori-header">
              <div class="histori-type">
                <i class="${iconClass}"></i>
                ${getJenisLabel(item.jenis_perubahan)}
              </div>
              <div class="histori-time">
                <i class="far fa-clock"></i> ${waktu}
              </div>
            </div>
            <div class="histori-detail">
              ${getDetailHTML(item)}
            </div>
          </div>
        `;
      });
      timelineContainer.innerHTML = html;
    } else {
      timelineContainer.innerHTML = `
        <div class="no-histori">
          <i class="fas fa-history" style="font-size: 48px; color: #cbd5e1;"></i>
          <p style="margin-top: 15px; color: #64748b;">Belum ada histori perubahan</p>
        </div>
      `;
    }
  })
  .catch(error => {
    console.error('Error fetch histori:', error);
    timelineContainer.innerHTML = `
      <div class="no-histori">
        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ef4444;"></i>
        <p style="margin-top: 15px; color: #ef4444; font-weight: 600;">Gagal memuat histori</p>
        <p style="color: #94a3b8; font-size: 13px; margin-top: 8px;">Error: ${error.message}</p>
        <p style="color: #94a3b8; font-size: 12px; margin-top: 5px;">Periksa console browser untuk detail</p>
      </div>
    `;
  });
}

function closeHistoriModal() {
  document.getElementById('historiModal').classList.remove('show');
}

function formatWaktu(waktu) {
  const date = new Date(waktu);
  const options = { 
    day: '2-digit', 
    month: 'long', 
    year: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  };
  return date.toLocaleDateString('id-ID', options);
}

function getIconClass(jenis) {
  return 'fas fa-history';
}

function getJenisLabel(jenis) {
  switch(jenis) {
    case 'masuk':
      return 'Aspirasi Masuk';
    case 'status':
      return 'Perubahan Status';
    case 'feedback':
      return 'Perubahan Feedback';
    default:
      return jenis;
  }
}

function getDetailHTML(item) {
  switch(item.jenis_perubahan) {
    case 'masuk':
      return '<strong>Aspirasi telah masuk ke sistem</strong>';
    case 'status':
      if (item.nilai_lama) {
        return `
          <span class="old-value">Status: ${item.nilai_lama}</span>
          <span class="new-value">Status: ${item.nilai_baru}</span>
        `;
      } else {
        return `<span class="new-value">Status: ${item.nilai_baru}</span>`;
      }
    case 'feedback':
      if (item.nilai_lama && item.nilai_lama !== '') {
        return `
          <span class="old-value">Feedback: ${item.nilai_lama}</span>
          <span class="new-value">Feedback: ${item.nilai_baru || 'Dihapus'}</span>
        `;
      } else {
        return `<span class="new-value">Feedback: ${item.nilai_baru}</span>`;
      }
    default:
      return item.nilai_baru;
  }
}
</script>

</body>
</html>