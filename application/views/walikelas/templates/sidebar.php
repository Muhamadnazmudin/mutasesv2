<!-- ================= SIDEBAR WALIKELAS ================= -->
<style> 
html, body {
    height: 100%;
    margin: 0;
}

/* ================= SIDEBAR ================= */
#accordionSidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 224px;
    overflow-y: auto;
    z-index: 1032;
    transition: all .3s ease;
}

/* ================= CONTENT ================= */
#content-wrapper {
    margin-left: 224px;
    min-height: 100vh;
    padding-top: 70px;
    background-color: #e7f9e7;
    transition: all .3s ease;
}

/* ================= TOPBAR ================= */
.topbar {
    position: fixed;
    top: 0;
    left: 224px;
    right: 0;
    z-index: 1031;
    background-color: #28a745 !important;
    transition: all .3s ease;
}

/* ===== DESKTOP TOGGLE ===== */
.sidebar-toggled #accordionSidebar {
    width: 80px;
}

.sidebar-toggled #content-wrapper {
    margin-left: 80px;
}

.sidebar-toggled .topbar {
    left: 80px;
}

/* ================= MOBILE ================= */
@media (max-width: 768px) {

    #accordionSidebar {
        left: -224px;
    }

    #accordionSidebar.show {
        left: 0;
    }

    #content-wrapper {
        margin-left: 0 !important;
        padding-top: 60px;
    }

    .topbar {
        left: 0 !important;
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 1030;
    }

    .sidebar-overlay.show {
        display: block;
    }
}

</style>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

  <!-- Brand -->
  <a class="sidebar-brand d-flex align-items-center justify-content-center"
     href="<?= site_url('walikelas') ?>">
    <div class="sidebar-brand-icon rotate-n-15">
      <i class="fas fa-exchange-alt"></i>
    </div>
    <div class="sidebar-brand-text mx-3">WALI KELAS</div>
  </a>

  <hr class="sidebar-divider my-0">

  <!-- Dashboard -->
  <li class="nav-item <?= ($active=='dashboard')?'active':'' ?>">
    <a class="nav-link" href="<?= site_url('walikelas') ?>">
      <i class="fas fa-fw fa-tachometer-alt"></i>
      <span>Dashboard</span>
    </a>
  </li>

  <hr class="sidebar-divider">

  <div class="sidebar-heading">
    Menu Wali Kelas
  </div>

  <!-- Data Siswa -->
  <li class="nav-item <?= ($active=='wk_siswa')?'active':'' ?>">
    <a class="nav-link" href="<?= site_url('walikelas/siswa') ?>">
      <i class="fas fa-users"></i>
      <span>Data Siswa</span>
    </a>
  </li>

  <!-- Rekap Absensi -->
  <li class="nav-item <?= ($active=='wk_absensi')?'active':'' ?>">
    <a class="nav-link" href="<?= site_url('walikelas/absensi') ?>">
      <i class="fas fa-clipboard-check"></i>
      <span>Rekap Absensi</span>
    </a>
  </li>

  <!-- Rekap Izin -->
  <li class="nav-item <?= ($active=='wk_izin')?'active':'' ?>">
    <a class="nav-link" href="<?= site_url('walikelas/izin') ?>">
      <i class="fas fa-door-open"></i>
      <span>Rekap Izin Siswa</span>
    </a>
  </li>

  <hr class="sidebar-divider d-none d-md-block">

  <!-- Logout -->
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
</ul>

<!-- ========== TOPBAR ========== -->
<nav class="navbar navbar-expand navbar-dark topbar mb-4 static-top shadow">
  <button class="btn btn-sm btn-outline-light me-2 d-md-none" id="btnToggleSidebar">
    <i class="fas fa-bars"></i>
</button>


    <ul class="navbar-nav ml-auto align-items-center">

        <li class="nav-item mr-3">
            <button id="toggleMode" class="btn btn-sm btn-outline-light">
                <i class="fas fa-moon"></i>
            </button>
        </li>

        <li class="nav-item dropdown no-arrow">
            <span class="nav-link text-white">
                <?= $this->session->userdata('nama'); ?> |
                <strong>Wali Kelas <?= $this->session->userdata('kelas_nama'); ?></strong>
            </span>
        </li>

    </ul>

</nav>


<!-- ============================ -->

<!-- Content Wrapper -->
 <div class="sidebar-overlay" id="sidebarOverlay"></div>
<div id="content-wrapper" class="d-flex flex-column">

<script>
const btnToggleSidebar = document.getElementById('btnToggleSidebar');
const sidebar = document.getElementById('accordionSidebar');
const overlay = document.getElementById('sidebarOverlay');

if (btnToggleSidebar) {
  btnToggleSidebar.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
  });
}

if (overlay) {
  overlay.addEventListener('click', () => {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
  });
}
</script>



