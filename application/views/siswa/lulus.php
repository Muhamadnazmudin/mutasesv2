<div class="content-wrapper mt-4 px-4">
  <h4 class="fw-bold mb-3">Data Siswa Lulus</h4>
  <table class="table table-bordered table-striped table-responsive-sm">
    <thead class="thead-dark">
      <tr>
        <th>No</th>
        <th>NIS</th>
        <th>Nama</th>
        <th>Kelas Terakhir</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($siswa): $no=1; foreach ($siswa as $s): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $s->nis ?></td>
          <td><?= $s->nama ?></td>
          <td><?= $s->nama_kelas ?></td>
          <td><span class="badge bg-success"><?= ucfirst($s->status) ?></span></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5" class="text-center text-muted">Tidak ada data siswa lulus.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
