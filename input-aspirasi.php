<?php
// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Koneksi Database
$conn = mysqli_connect("localhost", "root", "", "dbukk_rifaya");

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil data kategori untuk dropdown
$kategori_query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY ket_kategori ASC");

// Proses submit form
$success = "";
$error = "";

if (isset($_POST['submit'])) {
    $nis = trim($_POST['nis']);
    $id_kategori = trim($_POST['id_kategori']);
    $lokasi = trim($_POST['lokasi']);
    $ket = trim($_POST['ket']);
    $tanggal = date('Y-m-d H:i:s'); // Timestamp otomatis
    
    // VALIDASI NIS - HARUS ANGKA
    $nis_clean = preg_replace('/[^0-9]/', '', $nis);
    
    if ($nis_clean !== $nis || !ctype_digit($nis)) {
        $error = "NIS harus berupa angka! Tidak boleh mengandung huruf atau simbol.";
    } elseif (empty($nis)) {
        $error = "NIS tidak boleh kosong!";
    } elseif (strlen($nis) > 20) {
        $error = "NIS terlalu panjang! Maksimal 20 digit.";
    } elseif (empty($id_kategori)) {
        $error = "Kategori harus dipilih!";
    } elseif (empty($lokasi)) {
        $error = "Lokasi harus diisi!";
    } elseif (empty($ket)) {
        $error = "Keterangan aspirasi harus diisi!";
    } else {
        // Escape untuk keamanan SQL
        $nis_escaped = mysqli_real_escape_string($conn, $nis);
        $id_kategori_escaped = mysqli_real_escape_string($conn, $id_kategori);
        $lokasi_escaped = mysqli_real_escape_string($conn, $lokasi);
        $ket_escaped = mysqli_real_escape_string($conn, $ket);
        
        // Insert ke database
        $query = "INSERT INTO input_aspirasi (nis, id_kategori, lokasi, ket, tanggal) 
                  VALUES ('$nis_escaped', '$id_kategori_escaped', '$lokasi_escaped', '$ket_escaped', '$tanggal')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Aspirasi berhasil dikirim! Terima kasih atas partisipasi Anda.";
            // Reset form
            $_POST = array();
        } else {
            $error = "Gagal mengirim aspirasi: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Aspirasi</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Font Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* {
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0;
}

body {
  min-height: 100vh;
  margin: 0;
  padding: 0;
  
  /* Background gambar dengan overlay hitam 30% */
  background: #0064e0;
  background-position: center center;
  background-repeat: no-repeat;
  background-attachment: fixed;
  background-size: cover;
  
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.aspirasi-box {
  width: 100%;
  max-width: 500px;
  min-height: 600px;  /* TINGGI MINIMAL SAMA dengan index */
  background: white;
  padding: 35px;
  border-radius: 12px;
  box-shadow: 0 20px 35px rgba(0,0,0,0.2);
  
  /* Flexbox untuk centering konten di dalam container */
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.aspirasi-box h2 {
  text-align: center;
  margin-bottom: 10px;
  color: #1e3a8a;
  font-size: 28px;
  font-weight: 600;
}

.aspirasi-box p {
  text-align: center;
  color: #64748b;
  margin-bottom: 25px;
  font-size: 14px;
  font-weight: 400;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #1e3a8a;
  font-weight: 600;
  font-size: 14px;
}

.field {
  display: flex;
  align-items: center;
  background: #f1f5ff;
  border: 1px solid #c7d2fe;
  border-radius: 10px;
  padding: 12px 14px;
}

.field i {
  color: #1e3a8a;
  font-size: 18px;
  min-width: 18px;
}

.field input,
.field select {
  border: none;
  outline: none;
  background: transparent;
  flex: 1;
  padding: 0 12px;
  font-size: 15px;
  color: #1e293b;
  font-family: 'Poppins', sans-serif;
}

.field select {
  cursor: pointer;
}

.field-textarea {
  background: #f1f5ff;
  border: 1px solid #c7d2fe;
  border-radius: 10px;
  padding: 12px 14px;
}

.field-textarea textarea {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 15px;
  color: #1e293b;
  resize: vertical;
  min-height: 120px;
  font-family: 'Poppins', sans-serif;
}

button {
  width: 100%;
  padding: 14px;
  background: #2563eb;
  border: none;
  border-radius: 10px;
  color: white;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 10px;
  transition: background 0.3s;
  font-family: 'Poppins', sans-serif;
}

button:hover {
  background: #1e40af;
}

button:active {
  transform: scale(0.98);
}

.success {
  background: #dcfce7;
  color: #15803d;
  padding: 12px;
  border-radius: 10px;
  margin-bottom: 20px;
  text-align: center;
  border: 1px solid #86efac;
  font-weight: 500;
}

.error {
  background: #fee2e2;
  color: #b91c1c;
  padding: 12px;
  border-radius: 10px;
  margin-bottom: 20px;
  text-align: center;
  border: 1px solid #fca5a5;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 576px) {
  .aspirasi-box {
    min-height: 550px;  /* Sedikit lebih pendek di mobile */
    padding: 25px 20px;
  }
  
  .aspirasi-box h2 {
    font-size: 24px;
  }
  
  .field input,
  .field select,
  .field-textarea textarea {
    font-size: 14px;
  }
}
</style>
</head>
<body>
<div class="aspirasi-box">
  <h2>Input Aspirasi</h2>
  <p>Sampaikan aspirasi Anda untuk perbaikan sekolah</p>
  
  <?php if ($success) { ?>
    <div class="success">
      <i class="fas fa-check-circle"></i> <?= $success ?>
    </div>
  <?php } ?>
  
  <?php if ($error) { ?>
    <div class="error">
      <i class="fas fa-exclamation-circle"></i> <?= $error ?>
    </div>
  <?php } ?>
  
  <form method="post">
    <div class="form-group">
      <label for="nis">NIS (Nomor Induk Siswa)</label>
      <div class="field">
        <i class="fas fa-id-card"></i>
        <input type="number" name="nis" id="nis" placeholder="Masukkan NIS Anda" required min="0" pattern="[0-9]*" inputmode="numeric">
      </div>
    </div>
    
    <div class="form-group">
      <label for="id_kategori">Kategori Aspirasi</label>
      <div class="field">
        <i class="fas fa-list"></i>
        <select name="id_kategori" id="id_kategori" required>
          <option value="">-- Pilih Kategori --</option>
          <?php while ($row = mysqli_fetch_assoc($kategori_query)) { ?>
            <option value="<?= $row['id_kategori'] ?>"><?= $row['ket_kategori'] ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
    
    <div class="form-group">
      <label for="lokasi">Lokasi</label>
      <div class="field">
        <i class="fas fa-map-marker-alt"></i>
        <input type="text" name="lokasi" id="lokasi" placeholder="Contoh: Kelas 10A, Kantin, Toilet Lantai 2" required>
      </div>
    </div>
    
    <div class="form-group">
      <label for="ket">Keterangan Aspirasi</label>
      <div class="field-textarea">
        <textarea name="ket" id="ket" placeholder="Jelaskan aspirasi Anda secara detail..." required></textarea>
      </div>
    </div>
    
    <button type="submit" name="submit">
      <i class="fas fa-paper-plane"></i> Kirim Aspirasi
    </button>
  </form>
</div>

<script>
// Validasi input NIS - hanya angka
document.getElementById('nis').addEventListener('input', function(e) {
  // Hapus semua karakter non-angka
  this.value = this.value.replace(/[^0-9]/g, '');
});

// Validasi saat form submit
document.querySelector('form').addEventListener('submit', function(e) {
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
    alert('NIS harus berupa angka! Tidak boleh mengandung huruf atau simbol.');
    nisInput.focus();
    return false;
  }
  
  // Cek panjang maksimal
  if (nisValue.length > 20) {
    e.preventDefault();
    alert('NIS terlalu panjang! Maksimal 20 digit.');
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
</script>

</body>
</html>