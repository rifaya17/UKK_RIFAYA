<?php
session_start();
include "koneksi.php";

// Cek apakah sudah login, redirect ke dashboard
if (isset($_SESSION['admin'])) {
  header("Location: admin-dashboard.php");
  exit();
}

if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = ($_POST['password']);
  $q = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
  if (mysqli_num_rows($q) > 0) {
    $_SESSION['admin'] = $username;
    
    // Hapus cookie just_logged_out jika ada
    if (isset($_COOKIE['just_logged_out'])) {
      setcookie('just_logged_out', '', time()-3600, '/');
    }
    
    header("Location: admin-dashboard.php");
  } else {
    $error = "Username atau password salah!";
  }
}

// Cek apakah user baru logout (dari cookie)
$show_logout_message = isset($_COOKIE['just_logged_out']) && $_COOKIE['just_logged_out'] == '1';

// Hapus cookie setelah ditampilkan (agar tidak muncul terus)
if ($show_logout_message) {
  setcookie('just_logged_out', '', time()-3600, '/');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login Admin</title>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif; 
}
body {
  min-height: 100vh;
  margin: 0;
  padding: 0;
  
  /* Background Settings */
  background: #0064e0;
  background-position: center center;  /* Posisi tengah */
  background-repeat: no-repeat;        /* Tidak diulang */
  background-attachment: fixed;        /* Tetap saat scroll */
  background-size: cover;              /* Menutupi seluruh area */
  
  /* Flexbox untuk centering */
  display: flex;
  justify-content: center;
  align-items: center;
}
.login-box {
  width: 360px;
  background: white;
  padding: 35px;
  border-radius: 12px;
  box-shadow: 0 20px 35px rgba(0,0,0,0.2);
}
.login-box h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #1e3a8a;
}

/* Pesan Logout Success */
.logout-message {
  background: #fef3c7;
  color: #92400e;
  padding: 15px 18px;
  border-radius: 10px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.logout-message i {
  font-size: 18px;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.field {
  display: flex;
  align-items: center;
  background: #f1f5ff;
  border: 1px solid #c7d2fe;
  border-radius: 10px;
  padding: 12px 14px;
  margin-bottom: 18px;
}
.field i {
  color: #1e3a8a;
  font-size: 18px;
}
.field input {
  border: none;
  outline: none;
  background: transparent;
  flex: 1;
  padding: 0 12px;
  font-size: 15px;
}
.field .toggle {
  cursor: pointer;
  width: 18px;
  min-width: 18px;
  text-align: center;
  transition: none;
}
.input-group input:focus {
  outline: none;
  border-color: #2563eb;
}
.toggle-password {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  cursor: pointer;
  color: #64748b;
}
button {
  width: 100%;
  padding: 12px;
  background: #2563eb;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 15px;
  cursor: pointer;
  margin-top: 10px;
}
button:hover {
  background: #1e40af;
}
.error {
  background: #fee2e2;
  color: #b91c1c;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 15px;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
}

.error i {
  font-size: 16px;
}
</style>
</head>
<body>
<div class="login-box">
  <h2>Login Admin</h2>
  
  <!-- Pesan Logout Success -->
  <?php if ($show_logout_message) { ?>
    <div class="logout-message">
      <i class="fas fa-warning"></i>
      <span>Silahkan login kembali untuk kembali ke dashboard admin</span>
    </div>
  <?php } ?>
  
  <!-- Pesan Error -->
  <?php if (isset($error)) { ?>
    <div class="error">
      <i class="fas fa-exclamation-circle"></i>
      <?= $error ?>
    </div>
  <?php } ?>
  
  <form method="post">
    <div class="field">
      <i class="fas fa-user"></i>
      <input type="text" name="username" placeholder="Username" required>
    </div>
    <div class="field">
      <i class="fas fa-lock"></i>
      <input type="password" name="password" id="password" placeholder="Password" required>
      <i class="fas fa-eye toggle" id="togglePassword"></i>
    </div>
    <button type="submit" name="login">Login</button>
  </form>
</div>
<script>
const toggle = document.getElementById('togglePassword');
const password = document.getElementById('password');
toggle.addEventListener('click', function () {
  const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
  password.setAttribute('type', type);
  
  // Ganti icon mata
  if (type === 'text') {
    // Jika password terlihat, tampilkan icon mata dengan garis
    this.classList.remove('fa-eye');
    this.classList.add('fa-eye-slash');
  } else {
    // Jika password tersembunyi, tampilkan icon mata biasa
    this.classList.remove('fa-eye-slash');
    this.classList.add('fa-eye');
  }
});
</script>
</body>
</html>