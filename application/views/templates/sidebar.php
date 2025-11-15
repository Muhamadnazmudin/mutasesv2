<!-- ================= SIDEBAR.PHP ================= -->
<?php $role = $this->session->userdata('role_name'); ?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

  <!-- Brand -->
  <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('dashboard') ?>">
    <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-exchange-alt"></i></div>
    <div class="sidebar-brand-text mx-3">MUTASES</div>
  </a>

  <hr class="sidebar-divider my-0">

  <li class="nav-item <?= $active=='dashboard'?'active':'' ?>">
    <a class="nav-link" href="<?= site_url('dashboard') ?>">
      <i class="fas fa-fw fa-tachometer-alt"></i>
      <span>Dashboard</span>
    </a>
  </li>

  <hr class="sidebar-divider">

  <?php if ($role == 'admin'): ?>
    <div class="sidebar-heading">Manajemen Data</div>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('guru') ?>"><i class="fas fa-user-tie"></i> Data Guru</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('kelas') ?>"><i class="fas fa-school"></i> Data Kelas</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('siswa') ?>"><i class="fas fa-user-graduate"></i> Data Siswa</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('mutasi') ?>"><i class="fas fa-random"></i> Mutasi Siswa</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('kenaikan') ?>"><i class="fas fa-level-up-alt"></i> Kenaikan Kelas</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('siswa_keluar') ?>"><i class="fas fa-door-open"></i> Siswa Keluar</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= site_url('siswa_lulus') ?>"><i class="fas fa-graduation-cap"></i> Siswa Lulus</a></li>
    
    <li class="nav-item"><a class="nav-link" href="<?= site_url('laporan') ?>"><i class="fas fa-file-alt"></i> Laporan</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('tahun') ?>"><i class="fas fa-calendar"></i> Tahun Ajaran</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('users') ?>"><i class="fas fa-users-cog"></i> Manajemen User</a></li>
 <li class="nav-item <?= ($active == 'absensi') ? 'active' : '' ?>">
    <a class="nav-link" href="<?= base_url('index.php/Absensi/Absensi') ?>">
        <i class="fas fa-user-check"></i>
        <span>Absensi Siswa</span>
    </a>
</li>
<li class="nav-item <?= ($active == 'laporan_absensi') ? 'active' : '' ?>">
    <a class="nav-link" href="<?= site_url('Absensi/Laporan') ?>">
        <i class="fas fa-file-alt"></i>
        <span>Laporan Absensi</span>
    </a>
</li>
<li class="nav-item <?= ($active == 'hari_libur') ? 'active' : '' ?>">
    <a class="nav-link" href="<?= base_url('index.php/HariLibur') ?>">
        <i class="fas fa-calendar-times"></i>
        <span>Hari Libur</span>
    </a>
</li>


    <?php endif; ?>

  <hr class="sidebar-divider d-none d-md-block">

  <div class="text-center mb-2">
    <a href="<?= site_url('auth/logout') ?>" class="btn btn-danger btn-sm w-75 shadow-sm">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>

  <div class="text-center d-none d-md-inline mb-3">
    <button class="rounded-circle border-0 bg-white shadow-sm" id="sidebarToggle">
      <i class="fas fa-angle-double-left text-primary"></i>
    </button>
  </div>
</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

  <!-- Main Content -->
  <div id="content">

    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
  <ul class="navbar-nav ml-auto align-items-center">
    <!-- Tombol Toggle Mode -->
    <li class="nav-item">
      <button id="toggleMode" class="btn btn-sm btn-outline-secondary mr-3">
        <i class="fas fa-moon"></i>
      </button>
    </li>
    
    <!-- User info -->
    <li class="nav-item dropdown no-arrow">
      <span class="nav-link text-gray-800">
        <?= $this->session->userdata('nama'); ?> |
        <strong><?= ucfirst($this->session->userdata('role_name')); ?></strong>
      </span>
    </li>
  </ul>
</nav>


    <!-- Page Content -->
    <div class="container-fluid">
