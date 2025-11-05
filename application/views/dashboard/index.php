<div class="text-center mt-4 mb-5">
  <h3>Selamat Datang, <?= $this->session->userdata('nama'); ?> 👋</h3>
  <p class="text-muted">
    Anda login sebagai <strong><?= ucfirst($this->session->userdata('role_name')); ?></strong><br>
    Tahun Ajaran Aktif: <strong>
      <?php
        $tahun_id = $this->session->userdata('tahun_id');
        $tahun = $this->db->get_where('tahun_ajaran', ['id' => $tahun_id])->row();
        echo $tahun ? $tahun->tahun : '-';
      ?>
    </strong>
  </p>
</div>

<div class="row g-4 mb-4">
  <!-- KELAS -->
  <!-- JUMLAH ROMBEL -->
<div class="col-md-4">
  <div class="card border-left-primary shadow-sm h-100">
    <div class="card-body">
      <h5 class="fw-bold text-primary mb-3"><i class="fas fa-school"></i> Jumlah Rombel / Kelas</h5>
      <table class="table table-bordered table-sm mb-0">
        <thead class="table-light"><tr><th>Tingkat</th><th>Jumlah</th></tr></thead>
        <tbody>
          <tr><td>Kelas X</td><td><?= $rombel['x'] ?></td></tr>
          <tr><td>Kelas XI</td><td><?= $rombel['xi'] ?></td></tr>
          <tr><td>Kelas XII</td><td><?= $rombel['xii'] ?></td></tr>
          <tr class="table-secondary fw-bold"><td>Total</td><td><?= $rombel['total'] ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>


  <!-- SISWA AKTIF -->
  <div class="col-md-4">
    <div class="card border-left-success shadow-sm h-100">
      <div class="card-body">
        <h5 class="fw-bold text-success mb-3"><i class="fas fa-user-graduate"></i> Siswa Aktif</h5>
        <table class="table table-bordered table-sm mb-0">
          <thead class="table-light"><tr><th>Tingkat</th><th>Jumlah</th></tr></thead>
          <tbody>
            <tr><td>Kelas X</td><td><?= $aktif['x'] ?></td></tr>
            <tr><td>Kelas XI</td><td><?= $aktif['xi'] ?></td></tr>
            <tr><td>Kelas XII</td><td><?= $aktif['xii'] ?></td></tr>
            <tr class="table-secondary fw-bold"><td>Total</td><td><?= $aktif['total'] ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- SISWA KELUAR -->
  <div class="col-md-4">
    <div class="card border-left-danger shadow-sm h-100">
      <div class="card-body">
        <h5 class="fw-bold text-danger mb-3"><i class="fas fa-door-open"></i> Siswa Keluar</h5>
        <table class="table table-bordered table-sm mb-0">
          <thead class="table-light"><tr><th>Tingkat</th><th>Jumlah</th></tr></thead>
          <tbody>
            <tr><td>Kelas X</td><td><?= $keluar['x'] ?></td></tr>
            <tr><td>Kelas XI</td><td><?= $keluar['xi'] ?></td></tr>
            <tr><td>Kelas XII</td><td><?= $keluar['xii'] ?></td></tr>
            <tr class="table-secondary fw-bold"><td>Total</td><td><?= $keluar['total'] ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- SISWA LULUS -->
<div class="card shadow-sm border-left-warning mb-4">
  <div class="card-body">
    <h5 class="fw-bold text-warning mb-3"><i class="fas fa-graduation-cap"></i> Siswa Lulus per Tahun Ajaran</h5>
    <table class="table table-bordered table-sm mb-0">
      <thead class="table-light"><tr><th>Tahun Ajaran</th><th>Jumlah Siswa Lulus</th></tr></thead>
      <tbody>
        <?php if ($lulus): foreach ($lulus as $r): ?>
          <tr><td><?= $r->tahun ?></td><td><?= $r->jumlah ?></td></tr>
        <?php endforeach; else: ?>
          <tr><td colspan="2" class="text-center text-muted">Belum ada data siswa lulus.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<!-- CHARTS SECTION -->
<div class="row mt-4">
  <div class="col-md-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="fw-bold text-primary mb-3"><i class="fas fa-chart-bar"></i> Grafik Siswa per Tingkat</h5>
        <canvas id="chartAktifKeluar" height="100"></canvas>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="fw-bold text-info mb-3"><i class="fas fa-chart-pie"></i> Rasio Aktif vs Keluar</h5>
        <canvas id="chartRasio" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
var ctx1 = document.getElementById('chartAktifKeluar').getContext('2d');
new Chart(ctx1, {
  type: 'bar',
  data: {
    labels: ['Kelas X', 'Kelas XI', 'Kelas XII'],
    datasets: [
      {
        label: 'Siswa Aktif',
        data: [<?= $aktif['x'] ?>, <?= $aktif['xi'] ?>, <?= $aktif['xii'] ?>],
        backgroundColor: 'rgba(40, 167, 69, 0.7)'
      },
      {
        label: 'Siswa Keluar',
        data: [<?= $keluar['x'] ?>, <?= $keluar['xi'] ?>, <?= $keluar['xii'] ?>],
        backgroundColor: 'rgba(220, 53, 69, 0.7)'
      }
    ]
  },
  options: {
    responsive: true,
    legend: { position: 'top' },
    scales: {
      yAxes: [{ ticks: { beginAtZero:true } }]
    }
  }
});

var ctx2 = document.getElementById('chartRasio').getContext('2d');
new Chart(ctx2, {
  type: 'doughnut',
  data: {
    labels: ['Siswa Aktif', 'Siswa Keluar'],
    datasets: [{
      data: [<?= $aktif['total'] ?>, <?= $keluar['total'] ?>],
      backgroundColor: ['rgba(40, 167, 69, 0.8)', 'rgba(220, 53, 69, 0.8)']
    }]
  },
  options: { responsive: true, legend: { position: 'bottom' } }
});
</script>

