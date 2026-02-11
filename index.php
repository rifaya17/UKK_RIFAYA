<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: admin-dashboard.php");
    exit();
}

// ================== KONEKSI DATABASE ==================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "dbukk_rifaya";

$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) {
  die("Koneksi database gagal");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Pengaduan Sekolah - SMKN 12 Malang</title>

<!-- ICON (Font Awesome CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ================== RESET ================== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif; 
}

/* ================== BODY ================== */
body {
  min-height: 100vh;
  background: #ffffff;
  background-attachment: fixed;
}

/* ================== HEADER ================== */
.header {
  background: white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 15px 0;
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 15px;
}

.logo-section img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  object-fit: cover;
}

.logo-text {
  font-size: 20px;
  font-weight: 700;
  color: #0082fb;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 15px;
}

.nav-link {
  text-decoration: none;
  color: #0064e0;
  font-weight: 500;
  font-size: 15px;
  padding: 8px 16px;
  border-radius: 8px;
  transition: all 0.3s;
}

.nav-link:hover {
  background: #f1f5f9;
  color: #1e3a8a;
}

.btn-login-header {
  background: #0082fb;
  color: white;
  text-decoration: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-login-header:hover {
  background: #0064e0;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

/* ================== HERO SECTION ================== */
.hero-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 80px 30px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}

.hero-content {
  color: white;
}

.hero-content h1 {
  font-size: 48px;
  font-weight: 800;
  color: #0082fb;
  line-height: 1.2;
  margin-bottom: 20px;
  text-align: left;
}

.hero-content p {
  font-size: 18px;
  color: #5c6b73;
  line-height: 1.6;
  margin-bottom: 35px;
  opacity: 0.95;
  text-align: left;
}

.btn-pengaduan-hero {
  background: #0082fb;
  color: white;
  text-decoration: none;
  padding: 16px 32px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 16px;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
}

.btn-pengaduan-hero:hover {
  background: #0064e0;
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
}

.hero-image {
  display: flex;
  justify-content: center;
  align-items: center;
}

.hero-image img {
  width: 100%;
  max-width: 500px;
  height: auto;
  filter: drop-shadow(0 10px 30px rgba(0,0,0,0.2));
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}

/* ================== MOBILE MENU TOGGLE ================== */
.menu-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 24px;
  color: #1e3a8a;
  cursor: pointer;
}

/* ================== RESPONSIVE ================== */
@media (max-width: 768px) {
  .header-container {
    padding: 0 20px;
  }

  .logo-text {
    font-size: 16px;
  }

  .logo-section img {
    width: 40px;
    height: 40px;
  }

  .menu-toggle {
    display: block;
  }

  .nav-menu {
    position: fixed;
    top: 80px;
    right: -100%;
    background: white;
    flex-direction: column;
    width: 250px;
    padding: 20px;
    box-shadow: -2px 0 10px rgba(0,0,0,0.1);
    transition: right 0.3s;
    height: calc(100vh - 80px);
    align-items: stretch;
  }

  .nav-menu.active {
    right: 0;
  }

  .nav-link {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
  }

  .btn-login-header {
    width: 100%;
    justify-content: center;
  }

  .hero-section {
    grid-template-columns: 1fr;
    gap: 40px;
    padding: 50px 20px;
  }

  .hero-content h1 {
    font-size: 32px;
  }

  .hero-content p {
    font-size: 16px;
  }

  .hero-image {
    order: -1;
  }

  .hero-image img {
    max-width: 100%;
  }
}

@media (max-width: 480px) {
  .logo-text {
    font-size: 14px;
  }

  .hero-content h1 {
    font-size: 28px;
  }

  .hero-content p {
    font-size: 15px;
  }

  .btn-pengaduan-hero {
    width: 100%;
    justify-content: center;
    padding: 14px 24px;
    font-size: 15px;
  }
}
</style>
</head>

<body>

<!-- ================== HEADER ================== -->
<header class="header">
  <div class="header-container">
    <div class="logo-section">
      <img src="assets/smk.jpg" alt="Logo SMKN 12 Malang">
      <div class="logo-text">SMKN 12 Malang</div>
    </div>

    <button class="menu-toggle" onclick="toggleMenu()">
      <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-menu" id="navMenu">
      <a href="histori-aspirasi.php" class="nav-link"> Histori Aspirasi
      </a>
      <a href="input-aspirasi.php" class="nav-link">
         Ajukan Pengaduan
      </a>
      <a href="admin-login.php" class="btn-login-header">
        <i class="fas fa-user-shield"></i> Login Admin
      </a>
    </nav>
  </div>
</header>

<!-- ================== HERO SECTION ================== -->
<section class="hero-section">
  <div class="hero-content">
    <h1>Sistem Aspirasi & Pengaduan Sekolah</h1>
    <p>Wadah resmi untuk menyampaikan aspirasi, saran, dan pengaduan demi menciptakan lingkungan sekolah yang aman, nyaman, dan berkualitas.</p>
    <a href="input-aspirasi.php" class="btn-pengaduan-hero">
      <i class="fas fa-pen-to-square"></i>
      Ajukan Pengaduan
    </a>
  </div>

  <div class="hero-image">
    <img src="assets/web.svg" alt="Illustration">
  </div>
</section>

<script>
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
  const navMenu = document.getElementById('navMenu');
  const menuToggle = document.querySelector('.menu-toggle');
  
  if (!navMenu.contains(event.target) && !menuToggle.contains(event.target)) {
    navMenu.classList.remove('active');
  }
});
</script>

</body>
</html>