<?php
// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Koneksi Database
$conn = mysqli_connect("localhost", "root", "", "dbukk_rifaya");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Pengaturan Pagination
$limit = 10; // Maksimal 10 data per halaman
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$offset = ($page > 1) ? ($page * $limit) - $limit : 0;

// Ambil NIS dari form pencarian
$nis_search = isset($_GET['nis']) ? $_GET['nis'] : '';

// Validasi NIS harus angka
$error_nis = '';
if ($nis_search && !ctype_digit($nis_search)) {
    $error_nis = "NIS harus berupa angka!";
    $nis_search = '';
}

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_waktu = isset($_GET['waktu']) ? $_GET['waktu'] : '';

// Ambil data kategori untuk dropdown
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori ORDER BY ket_kategori ASC");

$result = null;
$total_data_filter = 0;
$total_pages = 0;

// Inisialisasi statistik
$total_aspirasi = 0; $total_menunggu = 0; $total_proses = 0; $total_selesai = 0;

if ($nis_search) {
    // 1. Hitung Statistik (Tanpa Filter Status/Waktu untuk Card)
    $count_all = mysqli_query($conn, "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN asp.status = 'Menunggu' OR asp.status IS NULL THEN 1 ELSE 0 END) as menunggu,
        SUM(CASE WHEN asp.status = 'Proses' THEN 1 ELSE 0 END) as proses,
        SUM(CASE WHEN asp.status = 'Selesai' THEN 1 ELSE 0 END) as selesai
        FROM input_aspirasi i 
        LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan 
        WHERE i.nis = '$nis_search'");
    $stats = mysqli_fetch_assoc($count_all);
    $total_aspirasi = $stats['total'];
    $total_menunggu = $stats['menunggu'];
    $total_proses = $stats['proses'];
    $total_selesai = $stats['selesai'];

    // 2. Membangun Filter untuk Tabel
    $where = ["i.nis = '$nis_search'"];
    if ($filter_status) {
        $where[] = ($filter_status == 'Menunggu') ? "(asp.status = 'Menunggu' OR asp.status IS NULL)" : "asp.status = '$filter_status'";
    }
    if ($filter_kategori) $where[] = "i.id_kategori = '$filter_kategori'";
    if ($filter_waktu) {
        $days = (int)$filter_waktu;
        $tanggal_filter = date('Y-m-d H:i:s', strtotime("-$days days"));
        $where[] = "i.tanggal >= '$tanggal_filter'";
    }
    $where_sql = "WHERE " . implode(" AND ", $where);

    // 3. Hitung Total Data Terfilter untuk Pagination
    $query_count = "SELECT COUNT(*) as jml FROM input_aspirasi i LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan $where_sql";
    $res_count = mysqli_query($conn, $query_count);
    $total_data_filter = mysqli_fetch_assoc($res_count)['jml'];
    $total_pages = ceil($total_data_filter / $limit);

    // 4. Query Utama dengan LIMIT
    $query = "SELECT i.*, IFNULL(asp.status, 'Menunggu') as status, asp.feedback, k.ket_kategori 
              FROM input_aspirasi i 
              LEFT JOIN aspirasi asp ON i.id_pelaporan = asp.id_pelaporan 
              LEFT JOIN kategori k ON i.id_kategori = k.id_kategori 
              $where_sql 
              ORDER BY i.tanggal DESC 
              LIMIT $offset, $limit";
    $result = mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Histori Aspirasi Siswa</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Font Poppins -->
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
}

.header {
  background: linear-gradient(135deg, #1e3a8a, #2563eb);
  color: white;
  padding: 20px 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: fixed;
  top: 0;           /* TAMBAHKAN */
  left: 0;          /* TAMBAHKAN */
  right: 0;         /* TAMBAHKAN */
  z-index: 1000;    /* TAMBAHKAN */
}

.header h1 {
  font-size: 24px;
}

.back-btn {
  background: rgba(255,255,255,0.2);
  color: white;
  padding: 10px 20px;
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.3s;
}

.back-btn:hover {
  background: rgba(255,255,255,0.3);
}

.container {
  max-width: 1400px;
  margin: 30px auto;
  padding: 0 20px;
  margin-top: 100px;
}

.search-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  padding: 30px;
  margin-bottom: 30px;
}

.search-section h2 {
  margin-bottom: 20px;
  color: #1e3a8a;
  font-size: 20px;
}

.search-form {
  display: flex;
  gap: 15px;
  align-items: end;
  flex-wrap: wrap;
}

.search-group {
  flex: 1;
  min-width: 250px;
}

.search-group label {
  display: block;
  margin-bottom: 8px;
  color: #475569;
  font-size: 14px;
  font-weight: 500;
}

.search-group input {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 15px;
}

.search-btn {
  padding: 12px 30px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
  transition: background 0.3s;
  height: 48px;
  font-weight: 600;
}

.search-btn:hover {
  background: #1e40af;
}

.error-message {
  margin-top: 15px;
  padding: 12px;
  background: #fee2e2;
  color: #b91c1c;
  border-radius: 8px;
  border: 1px solid #fca5a5;
  font-size: 14px;
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

.filter-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
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

.filter-buttons {
  display: flex;
  gap: 15px;
  align-items: center;
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
}

.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}

.no-data i {
  font-size: 64px;
  margin-bottom: 20px;
  display: block;
  color: #cbd5e1;
}

.no-data h3 {
  font-size: 20px;
  margin-bottom: 10px;
  color: #64748b;
}

.no-data p {
  font-size: 14px;
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

@media (max-width: 768px) {
  .header {
    padding: 15px 20px;
  }
  
  .header h1 {
    font-size: 18px;
  }
  
  .back-btn {
    font-size: 13px;
    padding: 8px 16px;
  }
  
  .cards {
    grid-template-columns: 1fr;
  }
  
  .search-form {
    flex-direction: column;
  }
  
  .search-group {
    width: 100%;
  }
  
  .search-group > div {
    display: flex !important;
    flex-direction: row !important;
    gap: 10px;
  }
  
  .search-group input {
    flex: 1;
  }
  
  .search-btn {
    flex-shrink: 0;
    white-space: nowrap;
  }
  
  .filter-form {
    flex-direction: column;
  }
  
  .filter-group {
    width: 100%;
  }
  
  .filter-buttons {
    width: 100%;
    flex-direction: column;
    gap: 10px;
  }
  
  .filter-btn, .reset-btn {
    width: 100%;
    text-align: center;
  }
  
  .table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  table {
    min-width: 1000px;
  }
}

@media (max-width: 480px) {
  .header {
    padding: 12px 15px;
  }
  
  .header h1 {
    font-size: 15px;
  }
  
  .header h1 i {
    font-size: 14px;
  }
  
  .back-btn {
    font-size: 11px;
    padding: 7px 12px;
  }
  
  .container {
    padding: 0 15px;
    margin: 20px auto;
  }
  
  .search-section, .filter-section {
    padding: 20px 15px;
  }
  
  .search-section h2, .filter-section h3 {
    font-size: 16px;
  }
  
  .search-group input {
    font-size: 14px;
  }
  
  .search-btn {
    font-size: 13px;
    padding: 10px 16px;
  }
  
  .card {
    padding: 18px;
  }
  
  .card-icon {
    width: 50px;
    height: 50px;
    font-size: 20px;
  }
  
  .card-content h3 {
    font-size: 13px;
  }
  
  .card-content p {
    font-size: 26px;
  }
  
  .filter-group label, .filter-group select {
    font-size: 13px;
  }
  
  .filter-btn, .reset-btn {
    font-size: 13px;
    padding: 12px 20px;
  }
}

/* CSS Tambahan untuk Histori */
.feedback-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.histori-icon {
  padding: 8px 12px;
  background: #8b5cf6;
  color: white;
  cursor: pointer;
  font-size: 14px;
  border-radius: 8px;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 36px;
  min-height: 36px;
}

.histori-icon:hover {
  background: #7c3aed;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
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
</style>
</head>
<body>

<div class="header">
  <h1><i class="fas fa-history"></i> Histori Aspirasi Siswa</h1>
  <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="container">
  
  <!-- Search Section -->
  <div class="search-section">
    <h2><i class="fas fa-search"></i> Cari Aspirasi Berdasarkan NIS</h2>
    <form method="GET" class="search-form">
      <div class="search-group">
        <label for="nis">Nomor Induk Siswa (NIS)</label>
        <div style="display: flex; gap: 10px; align-items: flex-end;">
          <input 
            type="number" 
            name="nis" 
            id="nis" 
            placeholder="Masukkan NIS Anda" 
            value="<?= htmlspecialchars($nis_search) ?>" 
            required 
            min="0" 
            pattern="[0-9]*" 
            inputmode="numeric"
            style="flex: 1;">
          <button type="submit" class="search-btn">
            <i class="fas fa-search"></i> Cari Aspirasi
          </button>
        </div>
      </div>
    </form>
    
    <?php if ($error_nis) { ?>
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i> <?= $error_nis ?>
      </div>
    <?php } ?>
  </div>
  
  <?php if ($nis_search) { ?>
  
  <!-- Cards Status -->
  <div class="cards">
    <div class="card">
      <div class="card-icon total">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <div class="card-content">
        <h3>Total Aspirasi</h3>
        <p><?= $total_aspirasi ?></p>
      </div>
    </div>
    
    <div class="card">
      <div class="card-icon menunggu">
        <i class="fas fa-clock"></i>
      </div>
      <div class="card-content">
        <h3>Menunggu</h3>
        <p><?= $total_menunggu ?></p>
      </div>
    </div>
    
    <div class="card">
      <div class="card-icon proses">
        <i class="fas fa-spinner"></i>
      </div>
      <div class="card-content">
        <h3>Proses Perbaikan</h3>
        <p><?= $total_proses ?></p>
      </div>
    </div>
    
    <div class="card">
      <div class="card-icon selesai">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="card-content">
        <h3>Selesai</h3>
        <p><?= $total_selesai ?></p>
      </div>
    </div>
  </div>
  
  <!-- Filter Section -->
  <div class="filter-section">
    <h3><i class="fas fa-filter"></i> Filter Aspirasi</h3>
    <form method="GET" class="filter-form">
      <input type="hidden" name="nis" value="<?= htmlspecialchars($nis_search) ?>">
      
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
        <label for="waktu">Waktu</label>
        <select name="waktu" id="waktu">
          <option value="">Semua Waktu</option>
          <option value="7" <?= $filter_waktu == '7' ? 'selected' : '' ?>>7 Hari Terakhir</option>
          <option value="30" <?= $filter_waktu == '30' ? 'selected' : '' ?>>30 Hari Terakhir</option>
          <option value="365" <?= $filter_waktu == '365' ? 'selected' : '' ?>>1 Tahun Terakhir</option>
        </select>
      </div>
      
      <div class="filter-buttons">
        <button type="submit" class="filter-btn">
          <i class="fas fa-search"></i> Filter
        </button>
        <a href="histori-aspirasi.php?nis=<?= htmlspecialchars($nis_search) ?>" class="reset-btn">
          <i class="fas fa-redo"></i> Reset
        </a>
      </div>
    </form>
  </div>
  
  <!-- Table Section -->
  <div class="table-section">
    <h2><i class="fas fa-list"></i> Riwayat Aspirasi Anda</h2>
    
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
            <th>Feedback Admin</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (mysqli_num_rows($result) > 0) {
            $no = $offset + 1;
            while ($row = mysqli_fetch_assoc($result)) { 
              $status_class = strtolower($row['status']);
          ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
            <td><?= $row['nis'] ?></td>
            <td><?= $row['ket_kategori'] ?></td>
            <td><?= $row['lokasi'] ?></td>
            <td>
              <div class="ket-text" title="<?= htmlspecialchars($row['ket']) ?>">
                <?= htmlspecialchars($row['ket']) ?>
              </div>
            </td>
            <td>
              <span class="status-badge <?= $status_class ?>">
                <?= $row['status'] ?>
              </span>
            </td>
            <td>
              <div class="feedback-container">
                <div style="flex: 1;">
                  <?php if (!empty($row['feedback'])) { ?>
                    <div class="feedback-text">
                      <i class="fas fa-comment-dots"></i> <?= htmlspecialchars($row['feedback']) ?>
                    </div>
                  <?php } else { ?>
                    <span style="color: #94a3b8; font-size: 13px;">Belum ada feedback</span>
                  <?php } ?>
                </div>
                <i class="fas fa-history histori-icon" onclick="openHistoriModal('<?= $row['id_pelaporan'] ?>')" title="Lihat Histori"></i>
              </div>
            </td>
          </tr>
          <?php 
            }
          } else { ?>
          <tr>
            <td colspan="8" style="text-align: center; padding: 30px; color: #94a3b8;">
              <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
              Tidak ada aspirasi yang sesuai dengan filter
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    
    <!-- PAGINATION - DIPINDAHKAN KE SINI -->
    <?php if ($total_pages > 1) { ?>
    <div class="pagination">
        <?php if ($page > 1) { ?>
            <a href="?nis=<?= $nis_search ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&waktu=<?= $filter_waktu ?>&halaman=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
        <?php } else { ?>
            <span class="disabled"><i class="fas fa-chevron-left"></i></span>
        <?php } ?>

        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
            <a href="?nis=<?= $nis_search ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&waktu=<?= $filter_waktu ?>&halaman=<?= $i ?>" 
               class="<?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php } ?>

        <?php if ($page < $total_pages) { ?>
            <a href="?nis=<?= $nis_search ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&waktu=<?= $filter_waktu ?>&halaman=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
        <?php } else { ?>
            <span class="disabled"><i class="fas fa-chevron-right"></i></span>
        <?php } ?>
    </div>
    <?php } ?>
    
  </div>
  
  <?php } else { ?>
  
  <!-- No Data Message -->
  <div class="table-section">
    <div class="no-data">
      <i class="fas fa-search"></i>
      <h3>Cari Histori Aspirasi Anda</h3>
      <p>Masukkan NIS Anda di form pencarian di atas untuk melihat histori aspirasi</p>
    </div>
  </div>
  
  <?php } ?>
  
</div>

<script>
// Validasi input hanya angka - block semua karakter non-angka
document.getElementById('nis').addEventListener('input', function(e) {
  // Hapus semua karakter non-angka
  this.value = this.value.replace(/[^0-9]/g, '');
});

// Validasi saat form submit
document.querySelector('.search-form').addEventListener('submit', function(e) {
  const nisInput = document.getElementById('nis');
  const nisValue = nisInput.value.trim();
  
  // Cek apakah input kosong
  if (nisValue === '') {
    e.preventDefault();
    alert('NIS tidak boleh kosong!');
    nisInput.focus();
    return false;
  }
  
  // Cek apakah input hanya berisi angka
  if (!/^\d+$/.test(nisValue)) {
    e.preventDefault();
    alert('NIS harus berupa angka!');
    nisInput.focus();
    return false;
  }
});

// Prevent paste non-numeric content
document.getElementById('nis').addEventListener('paste', function(e) {
  e.preventDefault();
  const pastedText = (e.clipboardData || window.clipboardData).getData('text');
  const numericOnly = pastedText.replace(/[^0-9]/g, '');
  this.value = numericOnly;
});

// Prevent keyboard shortcuts that might input non-numeric
document.getElementById('nis').addEventListener('keypress', function(e) {
  // Allow: backspace, delete, tab, escape, enter
  if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
      // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
      (e.keyCode === 65 && e.ctrlKey === true) ||
      (e.keyCode === 67 && e.ctrlKey === true) ||
      (e.keyCode === 86 && e.ctrlKey === true) ||
      (e.keyCode === 88 && e.ctrlKey === true)) {
    return;
  }
  // Ensure that it is a number and stop the keypress
  if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
    e.preventDefault();
  }
});

// ============= HISTORI FUNCTIONS =============

// Fungsi untuk membuka modal histori
function openHistoriModal(id_pelaporan) {
  const modal = document.getElementById('historiModal');
  const timelineContainer = document.getElementById('historiTimeline');
  
  // Tampilkan loading
  timelineContainer.innerHTML = '<div class="no-histori"><i class="fas fa-spinner fa-spin"></i><p>Memuat histori...</p></div>';
  modal.classList.add('show');
  
  // Fetch histori data
  const formData = new FormData();
  formData.append('id_pelaporan', id_pelaporan);
  
  fetch('ajax-get-histori.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && data.histori.length > 0) {
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
      timelineContainer.innerHTML = '<div class="no-histori"><i class="fas fa-history"></i><p>Belum ada histori perubahan</p></div>';
    }
  })
  .catch(error => {
    console.error('Error:', error);
    timelineContainer.innerHTML = '<div class="no-histori"><i class="fas fa-exclamation-triangle"></i><p>Gagal memuat histori</p></div>';
  });
}

// Fungsi untuk menutup modal histori
function closeHistoriModal() {
  document.getElementById('historiModal').classList.remove('show');
}

// Fungsi untuk format waktu
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

// Fungsi untuk mendapatkan icon class
function getIconClass(jenis) {
  return 'fas fa-history';
}

// Fungsi untuk mendapatkan label jenis perubahan
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

// Fungsi untuk mendapatkan detail HTML
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

</body>
</html>