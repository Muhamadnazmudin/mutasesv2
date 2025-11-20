<!-- ================= SIDEBAR.PHP ================= -->
<?php $role = $this->session->userdata('role_name'); ?>
<?php if (!isset($active)) $active = ''; ?>

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
<?php
$group_data   = in_array($active, ['guru','kelas','siswa']);
$group_mutasi = in_array($active, ['mutasi','kenaikan','siswa_keluar','siswa_lulus','laporan']);
$group_absen  = in_array($active, ['absensi','laporan_absensi']);
$group_izin   = in_array($active, ['izin','laporan_izin']);
$group_setting = in_array($active, ['hari_libur','tahun','jadwal_absensi']);
$group_absenqr = in_array($active, ['absensiqr_siswa','laporan_absensiqr']);
?>

  <hr class="sidebar-divider">

  <?php if ($role == 'admin'): ?>

<div class="sidebar-heading">Manajemen Data</div>

<!-- Manajemen Data -->
<li class="nav-item <?= $group_data ? 'active' : '' ?>">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#mData">
        <i class="fas fa-database"></i>
        <span>Manajemen Data</span>
    </a>
    <div id="mData" class="collapse <?= $group_data ? 'show' : '' ?>">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item <?= $active=='guru'?'active':'' ?>" href="<?= site_url('guru') ?>">Data Guru</a>
            <a class="collapse-item <?= $active=='kelas'?'active':'' ?>" href="<?= site_url('kelas') ?>">Data Kelas</a>
            <a class="collapse-item <?= $active=='siswa'?'active':'' ?>" href="<?= site_url('siswa') ?>">Data Siswa</a>
        </div>
    </div>
</li>

<!-- Mutasi -->
<li class="nav-item <?= $group_mutasi ? 'active' : '' ?>">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#mMutasi">
        <i class="fas fa-random"></i>
        <span>Mutasi</span>
    </a>
    <div id="mMutasi" class="collapse <?= $group_mutasi ? 'show' : '' ?>">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item <?= $active=='mutasi'?'active':'' ?>" href="<?= site_url('mutasi') ?>">Mutasi Siswa</a>
            <a class="collapse-item <?= $active=='kenaikan'?'active':'' ?>" href="<?= site_url('kenaikan') ?>">Kenaikan Kelas</a>
            <a class="collapse-item <?= $active=='siswa_keluar'?'active':'' ?>" href="<?= site_url('siswa_keluar') ?>">Siswa Keluar</a>
            <a class="collapse-item <?= $active=='siswa_lulus'?'active':'' ?>" href="<?= site_url('siswa_lulus') ?>">Siswa Lulus</a>
            <a class="collapse-item <?= $active=='laporan'?'active':'' ?>" href="<?= site_url('laporan') ?>">Laporan Mutasi</a>
        </div>
    </div>
</li>

<!-- Absensi -->
<li class="nav-item <?= $group_absen ? 'active' : '' ?>">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#mAbsensi">
        <i class="fas fa-user-check"></i>
        <span>Absensi</span>
    </a>
    <div id="mAbsensi" class="collapse <?= $group_absen ? 'show' : '' ?>">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item <?= $active=='absensi'?'active':'' ?>" 
                href="<?= base_url('index.php/Absensi/Absensi') ?>">Absensi Siswa</a>

            <a class="collapse-item <?= $active=='laporan_absensi'?'active':'' ?>" 
                href="<?= site_url('Absensi/Laporan') ?>">Laporan Absensi</a>
        </div>
    </div>
</li>

<!-- Absensi QR -->
<li class="nav-item <?= $group_absenqr ? 'active' : '' ?>">

    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#mAbsensiQR">
        <i class="fas fa-qrcode"></i>
        <span>Absensi QR</span>
    </a>

    <div id="mAbsensiQR" class="collapse <?= $group_absenqr ? 'show' : '' ?>">
        <div class="bg-white py-2 collapse-inner rounded">

            <a class="collapse-item <?= $active=='absensi_qr'?'active':'' ?>" 
               href="<?= base_url('index.php/AbsensiQRAdmin') ?>">
                Absensi QR Siswa
            </a>

            <a class="collapse-item <?= $active=='laporan_absensi_qr'?'active':'' ?>" 
               href="<?= base_url('index.php/AbsensiQRAdmin/laporan') ?>">
                Laporan Absensi QR
            </a>

        </div>
    </div>

</li>


<!-- Izin -->
<li class="nav-item <?= $group_izin ? 'active' : '' ?>">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#mIzin">
        <i class="fas fa-door-open"></i>
        <span>Izin Siswa</span>
    </a>
    <div id="mIzin" class="collapse <?= $group_izin ? 'show' : '' ?>">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item <?= $active=='izin'?'active':'' ?>" 
                href="<?= site_url('izin') ?>">Izin Keluar Siswa</a>

            <a class="collapse-item <?= $active=='laporan_izin'?'active':'' ?>" 
                href="<?= site_url('izin/laporan') ?>">Laporan Izin Keluar</a>
        </div>
    </div>
</li>

<!-- Pengaturan -->
<li class="nav-item <?= $group_setting ? 'active' : '' ?>">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#mSetting">
        <i class="fas fa-cog"></i>
        <span>Pengaturan</span>
    </a>
    <div id="mSetting" class="collapse <?= $group_setting ? 'show' : '' ?>">
        <div class="bg-white py-2 collapse-inner rounded">
            
            <a class="collapse-item <?= $active=='hari_libur' ? 'active' : '' ?>" 
                href="<?= base_url('index.php/HariLibur') ?>">Hari Libur</a>

            <a class="collapse-item <?= $active=='tahun' ? 'active' : '' ?>" 
                href="<?= site_url('tahun') ?>">Tahun Ajaran</a>

            <a class="collapse-item <?= $active=='jadwal_absensi' ? 'active' : '' ?>" 
                href="<?= base_url('index.php/jadwalabsensi') ?>">
                Setting Jam Absen
            </a>

            <!-- 🌟 Tambahan Baru: Backup DB -->
            <a class="collapse-item <?= $active=='backup_db' ? 'active' : '' ?>" 
                href="<?= base_url('index.php/backup') ?>">
                Backup Database
            </a>

            <!-- 🌟 Tambahan Baru: Restore DB -->
            <a class="collapse-item <?= $active=='restore_db' ? 'active' : '' ?>" 
                href="<?= base_url('index.php/backup/restore') ?>">
                Restore Database
            </a>

        </div>
    </div>
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
