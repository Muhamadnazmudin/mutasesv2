<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Mutasi Siswa</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <style>
    :root {
      --bg-light: #f8f9fa;
      --text-light: #333;
      --card-light: #fff;
      --bg-dark: #121212;
      --text-dark: #eaeaea;
      --card-dark: #1f1f1f;
    }

    body {
      background: var(--bg-light);
      color: var(--text-light);
      font-family: 'Segoe UI', sans-serif;
      transition: background 0.3s, color 0.3s;
      overflow-x: hidden;
    }

    body.dark-mode {
      background: var(--bg-dark);
      color: var(--text-dark);
    }

    header {
      background: linear-gradient(90deg, #007bff, #00bcd4);
      color: #fff;
      padding: .8rem 0;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    header .brand {
      font-weight: 700;
      font-size: 1.2rem;
      letter-spacing: .3px;
    }

    header .actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    header a.btn-login {
      color: #007bff;
      background: #fff;
      border-radius: 50px;
      padding: .4rem 1rem;
      font-weight: 500;
      font-size: .9rem;
      transition: 0.3s;
    }

    header a.btn-login:hover {
      background: #e2e6ea;
      text-decoration: none;
    }

    header .btn-toggle {
  background: rgba(255, 255, 255, 0.3);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.4s ease;
  color: #fff;
  position: relative;
  overflow: hidden;
}

    header .btn-toggle:hover {
  background: rgba(255, 255, 255, 0.5);
  transform: rotate(20deg);
}
header .btn-toggle i {
  font-size: 1.1rem;
  transition: transform 0.4s ease, opacity 0.3s ease;
}

body.dark-mode header .btn-toggle i.fa-sun {
  color: #ffeb3b; /* kuning matahari */
}

body:not(.dark-mode) header .btn-toggle i.fa-moon {
  color: #212121; /* abu gelap untuk moon di light mode */
}

    h2.section-title {
      font-weight: 700;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .dark-mode h2.section-title {
      color: #eaeaea;
    }

    footer {
      padding: 1.5rem 0;
      background: var(--card-light);
      border-top: 1px solid #ddd;
      margin-top: 3rem;
      color: #777;
      font-size: .9rem;
      transition: background 0.3s;
    }

    body.dark-mode footer {
      background: var(--card-dark);
      color: #bbb;
      border-color: #333;
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      background: var(--card-light);
      transition: background 0.3s;
    }

    body.dark-mode .card {
      background: var(--card-dark);
      box-shadow: 0 2px 10px rgba(255,255,255,0.05);
    }

    /* 🔹 Responsive tweaks */
    @media (max-width: 768px) {
      header .container {
        flex-direction: column;
        text-align: center;
      }
      header .actions {
        margin-top: .5rem;
      }
      h2.section-title {
        font-size: 1.2rem;
      }
      .card h5 {
        font-size: 1rem;
      }
      table th, table td {
        font-size: .8rem;
        padding: .3rem;
      }
      .btn-login {
        font-size: .8rem;
      }
    }
  </style>
</head>

<body>

<!-- 🔹 HEADER -->
<header>
  <div class="container d-flex justify-content-between align-items-center flex-wrap">
    <div class="brand">
      <i class="fas fa-chart-line"></i> Dashboard Mutasi Siswa
    </div>
    <div class="actions">
  <a href="<?= base_url('index.php/dashboard/mutasi') ?>" class="btn btn-light btn-sm me-2">
    <i class="fas fa-users"></i> Siswa Mutasi
  </a>
  <a href="<?= base_url('index.php/izin/scan') ?>" class="btn btn-outline-warning btn-sm me-2">
  <i class="fas fa-qrcode"></i> Izin Keluar
</a>

  <button class="btn-toggle" id="toggleDark" title="Ganti Tema">
  <i class="fas fa-moon"></i>
</button>

  <!-- DROPDOWN LOGIN -->
<div class="dropdown">
  <button class="btn-login dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-sign-in-alt"></i> Login
  </button>

  <ul class="dropdown-menu dropdown-menu-end shadow">
    <li>
      <a class="dropdown-item" href="<?= base_url('index.php/auth/login') ?>">
        <i class="fas fa-user-shield text-primary"></i> Login Admin
      </a>
    </li>
    <li>
      <a class="dropdown-item" href="<?= base_url('index.php/SiswaAuth') ?>">
        <i class="fas fa-user-graduate text-success"></i> Login Siswa
      </a>
    </li>
  </ul>
</div>

</div>
</header>

<!-- 🔹 MAIN CONTENT -->
<main class="container my-5">

  <div class="text-center mb-4">
    <h3 class="fw-bold mb-2">Selamat Datang di Sistem Mutasi Siswa 👋</h3>
    <p class="text" id="currentTime">Data per <strong>-</strong></p>
  </div>

  <h2 class="section-title mb-5">Statistik Mutasi Siswa Sekolah</h2>

  <?php $this->load->view('dashboard/index', [
      'rombel' => $rombel,
      'aktif' => $aktif,
      'keluar' => $keluar,
      'lulus' => $lulus
  ]); ?>

  <!-- 🔹 TABEL JUMLAH SISWA PER ROMBEL -->
<div class="card shadow-sm mt-5">
  <div class="card-body">
    <h5 class="fw-bold text-primary mb-3">
      <i class="fas fa-users"></i> Jumlah Siswa per Rombongan Belajar
    </h5>

    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0 text-center align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:50px">No</th>
            <th>Nama Kelas</th>
            <th>L</th>
            <th>P</th>
            <th>Total</th>
            <th>Download</th>
            <th>Jumlah Download</th>
          </tr>
        </thead>
        <tbody>
  <?php 
    $no = 1; 
    $sumL = 0;
    $sumP = 0;
    $sumTotal = 0;
    
    if (!empty($per_rombel)):
      foreach($per_rombel as $r):
        $sumL += $r->laki;
        $sumP += $r->perempuan;
        $sumTotal += $r->total;

        // Ambil ID kelas berdasarkan nama (supaya bisa link ke controller)
        $kelas = $this->db->get_where('kelas', ['nama' => $r->nama_kelas])->row();
        $kelas_id = $kelas ? $kelas->id : 0;

        // Ambil jumlah download (kalau ada kolom download_count)
        $count = isset($kelas->download_count) ? (int)$kelas->download_count : 0;
  ?>
    <tr>
      <td><?= $no++; ?></td>
      <td class="text-start"><?= $r->nama_kelas; ?></td>
      <td><?= $r->laki; ?></td>
      <td><?= $r->perempuan; ?></td>
      <td class="fw-bold"><?= $r->total; ?></td>
      <td>
        <?php if ($kelas_id): ?>
          <a href="<?= base_url('index.php/dashboard/download_excel/'.$kelas_id) ?>" 
             class="btn btn-sm btn-success">
             <i class="fas fa-file-excel"></i> Download
          </a>
        <?php else: ?>
          <span class="text-muted">-</span>
        <?php endif; ?>
      </td>
      <td><?= $count > 0 ? $count . 'x download' : '-' ?></td>
    </tr>
  <?php 
      endforeach;
  ?>
    <!-- 🔹 Baris Total Keseluruhan -->
    <tr class="table-secondary fw-bold">
      <td colspan="2" class="text-end">Jumlah Keseluruhan</td>
      <td><?= $sumL; ?></td>
      <td><?= $sumP; ?></td>
      <td><?= $sumTotal; ?></td>
      <td colspan="2"></td>
    </tr>
  <?php else: ?>
    <tr>
      <td colspan="7" class="text-center text-muted">Belum ada data siswa aktif.</td>
    </tr>
  <?php endif; ?>
</tbody>


      </table>
    </div>
  </div>
</div>

</main>

<!-- 🔹 FOOTER -->
<footer class="text-center">
  <div class="container">
    &copy; <?= date('Y') ?> Sistem Mutasi Siswa — Dibuat dengan 💙 oleh <strong>Nazmudin</strong>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// 🕒 Realtime tanggal & jam
// 🕒 Realtime tanggal & jam
function updateTime() {
  const el = document.getElementById('currentTime');
  const now = new Date();
  const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][now.getDay()];
  const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][now.getMonth()];
  const jam = now.getHours().toString().padStart(2,'0');
  const menit = now.getMinutes().toString().padStart(2,'0');
  const detik = now.getSeconds().toString().padStart(2,'0');
  el.innerHTML = `Data per <strong>${hari}, ${now.getDate()} ${bulan} ${now.getFullYear()} — ${jam}:${menit}:${detik} WIB</strong>`;
}
setInterval(updateTime, 1000);
updateTime();

// 🌗 Dark mode toggle with icon animation
const btnToggle = document.getElementById('toggleDark');
const icon = btnToggle.querySelector('i');

btnToggle.addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
  const isDark = document.body.classList.contains('dark-mode');
  localStorage.setItem('darkMode', isDark);
  icon.classList.add('fade');
  setTimeout(() => {
    icon.classList.remove('fa-moon', 'fa-sun');
    icon.classList.add(isDark ? 'fa-sun' : 'fa-moon');
    icon.classList.remove('fade');
  }, 200);
});

// apply saved theme on load
if (localStorage.getItem('darkMode') === 'true') {
  document.body.classList.add('dark-mode');
  icon.classList.replace('fa-moon', 'fa-sun');
}

</script>

</body>
</html>
